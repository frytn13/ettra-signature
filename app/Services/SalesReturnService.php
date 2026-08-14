<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\WarehouseStock;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesReturnService
{
    public function create(SalesOrder $order, array $items, string $reason, ?string $notes, CarbonInterface $date, bool $refundRequested, User $actor): SalesReturn
    {
        return DB::transaction(function () use ($order, $items, $reason, $notes, $date, $refundRequested, $actor): SalesReturn {
            $order = SalesOrder::query()->with('items')->lockForUpdate()->findOrFail($order->id);
            if ($order->payment_status !== 'paid') {
                throw ValidationException::withMessages(['sales_order_id' => 'Retur hanya dapat dibuat untuk transaksi yang sudah lunas.']);
            }

            $return = SalesReturn::query()->create([
                'return_number' => $this->returnNumber(),
                'sales_order_id' => $order->id,
                'warehouse_id' => $order->warehouse_id,
                'return_date' => $date,
                'reason' => $reason,
                'notes' => $notes,
                'refund_amount' => 0,
                'refund_status' => $refundRequested ? 'pending' : 'not_required',
                'processed_by' => $actor->id,
            ]);

            $refundAmount = 0.0;
            $processedCount = 0;

            foreach ($items as $input) {
                $quantity = (int) ($input['quantity'] ?? 0);
                if ($quantity <= 0) continue;

                $orderItem = SalesOrderItem::query()->lockForUpdate()->findOrFail((int) $input['sales_order_item_id']);
                if ((int) $orderItem->sales_order_id !== (int) $order->id) {
                    throw ValidationException::withMessages(['items' => 'Salah satu item retur tidak berasal dari transaksi yang dipilih.']);
                }

                $alreadyReturned = (int) SalesReturnItem::query()
                    ->where('sales_order_item_id', $orderItem->id)
                    ->sum('quantity');
                $remaining = max(0, (int) $orderItem->quantity - $alreadyReturned);
                if ($quantity > $remaining) {
                    throw ValidationException::withMessages(['items' => "Jumlah retur {$orderItem->sku_snapshot} melebihi sisa yang dapat diretur ({$remaining} unit)."]);
                }

                $stock = WarehouseStock::query()
                    ->where('warehouse_id', $order->warehouse_id)
                    ->where('product_variant_id', $orderItem->product_variant_id)
                    ->lockForUpdate()
                    ->first();
                if (! $stock) {
                    throw ValidationException::withMessages(['items' => "Titik stok {$orderItem->sku_snapshot} tidak ditemukan pada room transaksi."]);
                }

                $condition = $input['condition'];
                $beforeOnHand = (int) $stock->quantity_on_hand;
                $beforeReserved = (int) $stock->quantity_reserved;
                $beforeDamaged = (int) $stock->quantity_damaged;
                $beforeAvailable = $stock->availableQuantity();

                $afterOnHand = $beforeOnHand + $quantity;
                $afterDamaged = $condition === 'damaged' ? $beforeDamaged + $quantity : $beforeDamaged;
                $stock->forceFill([
                    'quantity_on_hand' => $afterOnHand,
                    'quantity_damaged' => $afterDamaged,
                    'updated_by' => $actor->id,
                ])->save();
                $stock->refresh();

                $unitRefund = (int) $orderItem->quantity > 0 ? (float) $orderItem->subtotal / (int) $orderItem->quantity : 0.0;
                SalesReturnItem::query()->create([
                    'sales_return_id' => $return->id,
                    'sales_order_item_id' => $orderItem->id,
                    'product_variant_id' => $orderItem->product_variant_id,
                    'quantity' => $quantity,
                    'condition' => $condition,
                    'unit_refund_amount' => $unitRefund,
                ]);

                StockMovement::query()->create([
                    'transaction_number' => $this->movementNumber(),
                    'warehouse_stock_id' => $stock->id,
                    'warehouse_id' => $stock->warehouse_id,
                    'product_variant_id' => $stock->product_variant_id,
                    'movement_type' => StockMovement::TYPE_CUSTOMER_RETURN,
                    'direction' => StockMovement::DIRECTION_IN,
                    'quantity' => $quantity,
                    'quantity_before' => $beforeOnHand,
                    'quantity_after' => (int) $stock->quantity_on_hand,
                    'quantity_reserved_before' => $beforeReserved,
                    'quantity_reserved_after' => (int) $stock->quantity_reserved,
                    'quantity_damaged_before' => $beforeDamaged,
                    'quantity_damaged_after' => (int) $stock->quantity_damaged,
                    'quantity_available_before' => $beforeAvailable,
                    'quantity_available_after' => $stock->availableQuantity(),
                    'reference_type' => 'sales_return',
                    'reference_id' => $return->id,
                    'notes' => "Retur pelanggan {$return->return_number} · ".($condition === 'damaged' ? 'Barang rusak' : 'Layak jual'),
                    'performed_by' => $actor->id,
                    'movement_date' => $date,
                ]);

                $refundAmount += $unitRefund * $quantity;
                $processedCount += $quantity;
            }

            if ($processedCount === 0) {
                throw ValidationException::withMessages(['items' => 'Tidak ada item yang dapat diproses sebagai retur.']);
            }

            $return->forceFill(['refund_amount' => $refundAmount])->save();
            return $return->fresh(['order', 'warehouse', 'items.orderItem', 'processedBy']);
        }, 3);
    }

    private function returnNumber(): string
    {
        do { $number = 'RET-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4)); }
        while (SalesReturn::query()->where('return_number', $number)->exists());
        return $number;
    }

    private function movementNumber(): string
    {
        do { $number = 'SM-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4)); }
        while (StockMovement::query()->where('transaction_number', $number)->exists());
        return $number;
    }
}
