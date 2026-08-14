<?php

namespace App\Services;

use App\Models\SalesOrderItem;
use App\Models\WarehouseStock;
use Illuminate\Support\Collection;

class InventoryAnalysisService
{
    public const PERIOD_DAYS = 30;
    public const DEFAULT_LEAD_TIME_DAYS = 14;
    public const SAFETY_STOCK_DAYS = 7;

    /**
     * @return array{summary: array<string,int>, rows: Collection<int,array<string,mixed>>}
     */
    public function analyze(?int $warehouseId = null, int $limit = 12): array
    {
        $soldByVariant = SalesOrderItem::query()
            ->selectRaw('sales_order_items.product_variant_id, SUM(sales_order_items.quantity) as sold_quantity')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
            ->where('sales_orders.payment_status', 'paid')
            ->whereNull('sales_orders.deleted_at')
            ->where('sales_orders.transaction_date', '>=', now()->subDays(self::PERIOD_DAYS)->startOfDay())
            ->when($warehouseId, fn ($query) => $query->where('sales_orders.warehouse_id', $warehouseId))
            ->groupBy('sales_order_items.product_variant_id')
            ->pluck('sold_quantity', 'sales_order_items.product_variant_id');

        $stocks = WarehouseStock::query()
            ->with([
                'warehouse:id,code,name',
                'productVariant:id,product_id,color_id,size_id,sku',
                'productVariant.product:id,code,name',
                'productVariant.color:id,name',
                'productVariant.size:id,name',
            ])
            ->when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
            ->get();

        $rows = $stocks->map(function (WarehouseStock $stock) use ($soldByVariant): array {
            $sold = (int) ($soldByVariant[$stock->product_variant_id] ?? 0);
            $averageDailySales = $sold / self::PERIOD_DAYS;
            $available = $stock->availableQuantity();
            $daysToStockout = $averageDailySales > 0 ? $available / $averageDailySales : null;
            $targetStock = (int) ceil($averageDailySales * (self::DEFAULT_LEAD_TIME_DAYS + self::SAFETY_STOCK_DAYS));
            $targetStock = max((int) $stock->minimum_stock, $targetStock);
            $recommendedRestock = max(0, $targetStock - $available);

            $movement = match (true) {
                $averageDailySales >= 0.5 => 'fast',
                $averageDailySales >= 0.1 => 'normal',
                $averageDailySales > 0 => 'slow',
                default => 'no_sale',
            };

            return [
                'stock' => $stock,
                'sold_30d' => $sold,
                'average_daily_sales' => $averageDailySales,
                'days_to_stockout' => $daysToStockout,
                'recommended_restock' => $recommendedRestock,
                'movement' => $movement,
            ];
        });

        $summary = [
            'fast' => $rows->where('movement', 'fast')->count(),
            'normal' => $rows->where('movement', 'normal')->count(),
            'slow' => $rows->where('movement', 'slow')->count(),
            'no_sale' => $rows->where('movement', 'no_sale')->count(),
            'restock' => $rows->where('recommended_restock', '>', 0)->count(),
        ];

        $priority = $rows
            ->sortBy(function (array $row): array {
                return [
                    $row['recommended_restock'] > 0 ? 0 : 1,
                    $row['days_to_stockout'] ?? PHP_INT_MAX,
                    -$row['sold_30d'],
                ];
            })
            ->take($limit)
            ->values();

        return ['summary' => $summary, 'rows' => $priority];
    }
}
