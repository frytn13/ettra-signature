<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isOwner = $user->isOwner();
        $roleLabel = $user->roleLabel();
        [$start, $end, $period] = $this->resolvePeriod($request);

        $stockSnapshot = ['available' => 0, 'sku_count' => 0, 'warehouse_count' => 0, 'low' => 0, 'out' => 0];
        $restockAlerts = collect();

        if (Schema::hasTable('warehouse_stocks')) {
            $availableSql = WarehouseStock::availableSql();
            $stockSnapshot = [
                'available' => (int) (WarehouseStock::query()->selectRaw("COALESCE(SUM({$availableSql}), 0) AS total")->value('total') ?? 0),
                'sku_count' => WarehouseStock::query()->distinct()->count('product_variant_id'),
                'warehouse_count' => WarehouseStock::query()->distinct()->count('warehouse_id'),
                'low' => WarehouseStock::query()->whereRaw("({$availableSql}) > 0")->whereRaw("({$availableSql}) <= minimum_stock")->count(),
                'out' => WarehouseStock::query()->whereRaw("({$availableSql}) = 0")->count(),
            ];

            $restockAlerts = WarehouseStock::query()
                ->with(['warehouse:id,name', 'productVariant:id,product_id,color_id,size_id,sku', 'productVariant.product:id,name', 'productVariant.color:id,name', 'productVariant.size:id,name'])
                ->whereRaw("({$availableSql}) <= minimum_stock")
                ->orderByRaw("CASE WHEN ({$availableSql}) = 0 THEN 0 ELSE 1 END")
                ->orderByRaw("({$availableSql}) ASC")
                ->limit(5)
                ->get();
        }

        $salesReady = Schema::hasTable('sales_orders') && Schema::hasTable('sales_order_items');
        $salesSummary = ['transactions' => 0, 'paid_sales' => 0.0, 'revenue' => 0.0, 'profit' => 0.0, 'pending_payments' => 0];
        $orderStatuses = collect();
        $transactions = collect();
        $salesChart = collect();
        $fastMovingProducts = collect();
        $slowMovingProducts = collect();

        if ($salesReady) {
            $periodOrders = SalesOrder::query()->whereBetween('transaction_date', [$start, $end]);
            $salesSummary['transactions'] = (clone $periodOrders)->count();
            $salesSummary['paid_sales'] = (float) (clone $periodOrders)->where('payment_status', 'paid')->sum('grand_total');
            $salesSummary['revenue'] = (float) (clone $periodOrders)->where('payment_status', 'paid')->sum(DB::raw('subtotal - discount_total'));

            if ($isOwner) {
                $salesSummary['profit'] = (float) SalesOrderItem::query()
                    ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
                    ->whereNull('sales_orders.deleted_at')
                    ->where('sales_orders.payment_status', 'paid')
                    ->whereBetween('sales_orders.transaction_date', [$start, $end])
                    ->selectRaw('COALESCE(SUM(sales_order_items.subtotal - (COALESCE(sales_order_items.cost_price_snapshot, 0) * sales_order_items.quantity)), 0) AS profit')
                    ->value('profit');
            }

            if (Schema::hasTable('payments')) {
                $salesSummary['pending_payments'] = Payment::query()->where('status', 'pending')->count();
            }

            $statusCounts = SalesOrder::query()
                ->whereBetween('transaction_date', [$start, $end])
                ->selectRaw('order_status, COUNT(*) AS total')
                ->groupBy('order_status')
                ->pluck('total', 'order_status');
            $orderStatuses = collect([
                ['label' => 'Menunggu Pembayaran', 'count' => (int) ($statusCounts['waiting_payment'] ?? 0), 'class' => 'peach'],
                ['label' => 'Diproses', 'count' => (int) ($statusCounts['processing'] ?? 0), 'class' => 'green'],
                ['label' => 'Dikemas / Dikirim', 'count' => (int) (($statusCounts['packed'] ?? 0) + ($statusCounts['shipped'] ?? 0)), 'class' => 'soft-green'],
                ['label' => 'Selesai', 'count' => (int) ($statusCounts['completed'] ?? 0), 'class' => 'soft-peach'],
            ]);

            $transactions = SalesOrder::query()
                ->latest('transaction_date')
                ->limit(6)
                ->get();

            $chartStart = now()->subDays(6)->startOfDay();
            $dailySales = SalesOrder::query()
                ->where('payment_status', 'paid')
                ->whereBetween('transaction_date', [$chartStart, now()->endOfDay()])
                ->selectRaw('DATE(transaction_date) AS sale_date, SUM(grand_total) AS total')
                ->groupBy('sale_date')
                ->pluck('total', 'sale_date');
            $maxDaily = max(1, (float) $dailySales->max());
            $salesChart = collect(CarbonPeriod::create($chartStart, '1 day', now()->startOfDay()))->map(function ($date) use ($dailySales, $maxDaily): array {
                $key = $date->format('Y-m-d');
                $amount = (float) ($dailySales[$key] ?? 0);
                return ['label' => $date->locale('id')->translatedFormat('D'), 'value' => (int) round(($amount / $maxDaily) * 100), 'amount' => $amount];
            });

            $performance = SalesOrderItem::query()
                ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
                ->whereNull('sales_orders.deleted_at')
                ->where('sales_orders.payment_status', 'paid')
                ->where('sales_orders.transaction_date', '>=', now()->subDays(30)->startOfDay())
                ->selectRaw('sales_order_items.product_name_snapshot AS name, SUM(sales_order_items.quantity) AS sold')
                ->groupBy('sales_order_items.product_name_snapshot')
                ->get();

            $fastMovingProducts = $performance->sortByDesc('sold')->take(4)->values();
            $slowMovingProducts = $performance->filter(fn ($row) => (int) $row->sold > 0)->sortBy('sold')->take(4)->values();
        }

        $productCount = Schema::hasTable('products') ? Product::query()->count() : 0;

        $summaryCards = collect([
            ['label' => 'Penjualan Lunas', 'value' => $this->rupiah($salesSummary['paid_sales']), 'caption' => $this->periodCaption($period, $start, $end), 'accent' => 'peach'],
            ['label' => 'Total Transaksi', 'value' => number_format($salesSummary['transactions'], 0, ',', '.'), 'caption' => number_format($salesSummary['pending_payments'], 0, ',', '.').' pembayaran menunggu', 'accent' => 'green'],
            ['label' => 'Pendapatan Produk', 'value' => $this->rupiah($salesSummary['revenue']), 'caption' => 'Tidak termasuk ongkos kirim', 'accent' => 'green'],
            ['label' => 'Jumlah Produk', 'value' => number_format($productCount, 0, ',', '.'), 'caption' => number_format($stockSnapshot['sku_count'], 0, ',', '.').' SKU aktif di stok', 'accent' => 'peach'],
            ['label' => 'Profit Kotor', 'value' => $this->rupiah($salesSummary['profit']), 'caption' => 'Hanya terlihat oleh Owner', 'accent' => 'green', 'owner_only' => true],
            ['label' => 'Stok Tersedia', 'value' => number_format($stockSnapshot['available'], 0, ',', '.'), 'caption' => ($stockSnapshot['low'] + $stockSnapshot['out']).' SKU perlu perhatian', 'accent' => 'peach'],
        ])->filter(fn (array $card) => $isOwner || ! ($card['owner_only'] ?? false))->values();

        $activities = collect();
        if ($isOwner && Schema::hasTable('activity_logs')) {
            $activities = ActivityLog::query()->with('user:id,name,email,role,deleted_at')->latest('created_at')->latest('id')->limit(5)->get();
        }

        return view('admin.dashboard', compact(
            'summaryCards', 'salesChart', 'orderStatuses', 'restockAlerts', 'transactions',
            'fastMovingProducts', 'slowMovingProducts', 'activities', 'isOwner', 'roleLabel',
            'stockSnapshot', 'salesSummary', 'period', 'start', 'end',
        ));
    }

    private function resolvePeriod(Request $request): array
    {
        $period = $request->string('period', 'month')->toString();
        $today = now();
        return match ($period) {
            'day' => [$today->copy()->startOfDay(), $today->copy()->endOfDay(), 'day'],
            'week' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek(), 'week'],
            'year' => [$today->copy()->startOfYear(), $today->copy()->endOfYear(), 'year'],
            'custom' => [
                Carbon::parse($request->input('start_date', $today->copy()->startOfMonth()->toDateString()))->startOfDay(),
                Carbon::parse($request->input('end_date', $today->toDateString()))->endOfDay(),
                'custom',
            ],
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth(), 'month'],
        };
    }

    private function periodCaption(string $period, Carbon $start, Carbon $end): string
    {
        return $period === 'custom'
            ? $start->format('d/m/Y').' - '.$end->format('d/m/Y')
            : ['day' => 'Hari ini', 'week' => 'Minggu ini', 'month' => 'Bulan ini', 'year' => 'Tahun ini'][$period] ?? 'Bulan ini';
    }

    private function rupiah(float|int $value): string
    {
        return 'Rp'.number_format((float) $value, 0, ',', '.');
    }
}
