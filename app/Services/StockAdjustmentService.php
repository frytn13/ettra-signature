<?php

namespace App\Services;

use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    public function process(
        WarehouseStock $warehouseStock,
        int $physicalQuantity,
        string $reason,
        CarbonInterface $adjustmentDate,
        string $notes,
        User $actor,
    ): StockAdjustment {
        if (! array_key_exists($reason, StockAdjustment::reasonOptions())) {
            throw ValidationException::withMessages([
                'reason' => 'Alasan penyesuaian stok tidak didukung.',
            ]);
        }

        return DB::transaction(function () use (
            $warehouseStock,
            $physicalQuantity,
            $reason,
            $adjustmentDate,
            $notes,
            $actor,
        ): StockAdjustment {
            $lockedStock = WarehouseStock::query()
                ->with([
                    'warehouse:id,code,name,is_active',
                    'productVariant:id,product_id,sku,is_active,deleted_at',
                    'productVariant.product:id,name,status,deleted_at',
                ])
                ->lockForUpdate()
                ->findOrFail($warehouseStock->getKey());

            if (! $lockedStock->warehouse || ! $lockedStock->warehouse->is_active) {
                throw ValidationException::withMessages([
                    'warehouse_stock_id' => 'Room pada titik stok ini sedang nonaktif atau tidak tersedia.',
                ]);
            }

            if (! $lockedStock->productVariant || ! $lockedStock->productVariant->is_active) {
                throw ValidationException::withMessages([
                    'warehouse_stock_id' => 'Variasi produk pada titik stok ini sedang nonaktif atau tidak tersedia.',
                ]);
            }

            if (! $lockedStock->productVariant->product || $lockedStock->productVariant->product->status === 'discontinued') {
                throw ValidationException::withMessages([
                    'warehouse_stock_id' => 'Produk pada titik stok ini sudah dihentikan dan tidak dapat disesuaikan.',
                ]);
            }

            $latestMovementDate = StockMovement::query()
                ->where('warehouse_stock_id', $lockedStock->id)
                ->max('movement_date');

            if ($latestMovementDate !== null && $adjustmentDate->lt(Carbon::parse($latestMovementDate))) {
                throw ValidationException::withMessages([
                    'adjustment_date' => 'Tanggal penyesuaian tidak boleh lebih awal dari mutasi stok terakhir pada titik stok ini.',
                ]);
            }

            $beforeOnHand = (int) $lockedStock->quantity_on_hand;
            $beforeReserved = (int) $lockedStock->quantity_reserved;
            $beforeDamaged = (int) $lockedStock->quantity_damaged;
            $beforeAvailable = $lockedStock->availableQuantity();

            if ($physicalQuantity < ($beforeReserved + $beforeDamaged)) {
                throw ValidationException::withMessages([
                    'physical_quantity' => 'Stok fisik hasil pemeriksaan tidak boleh lebih kecil dari total stok reservasi dan barang rusak yang masih tercatat. Selesaikan status reservasi/rusak terlebih dahulu.',
                ]);
            }

            $difference = $physicalQuantity - $beforeOnHand;

            if ($difference === 0) {
                throw ValidationException::withMessages([
                    'physical_quantity' => 'Stok fisik sama dengan stok sistem. Tidak ada penyesuaian yang perlu dicatat.',
                ]);
            }

            $movementType = $difference > 0
                ? StockMovement::TYPE_ADJUSTMENT_IN
                : StockMovement::TYPE_ADJUSTMENT_OUT;

            $direction = $difference > 0
                ? StockMovement::DIRECTION_IN
                : StockMovement::DIRECTION_OUT;

            $adjustment = StockAdjustment::query()->create([
                'adjustment_number' => $this->generateAdjustmentNumber(),
                'warehouse_stock_id' => $lockedStock->id,
                'warehouse_id' => $lockedStock->warehouse_id,
                'product_variant_id' => $lockedStock->product_variant_id,
                'system_quantity' => $beforeOnHand,
                'physical_quantity' => $physicalQuantity,
                'difference_quantity' => $difference,
                'movement_type' => $movementType,
                'reason' => $reason,
                'status' => StockAdjustment::STATUS_PROCESSED,
                'notes' => $notes,
                'adjustment_date' => $adjustmentDate,
                'processed_by' => $actor->getKey(),
            ]);

            $lockedStock->forceFill([
                'quantity_on_hand' => $physicalQuantity,
                'updated_by' => $actor->getKey(),
            ])->save();

            $lockedStock->refresh();

            $movement = StockMovement::query()->create([
                'transaction_number' => $this->generateMovementNumber(),
                'warehouse_stock_id' => $lockedStock->id,
                'warehouse_id' => $lockedStock->warehouse_id,
                'product_variant_id' => $lockedStock->product_variant_id,
                'movement_type' => $movementType,
                'direction' => $direction,
                'quantity' => abs($difference),
                'quantity_before' => $beforeOnHand,
                'quantity_after' => (int) $lockedStock->quantity_on_hand,
                'quantity_reserved_before' => $beforeReserved,
                'quantity_reserved_after' => (int) $lockedStock->quantity_reserved,
                'quantity_damaged_before' => $beforeDamaged,
                'quantity_damaged_after' => (int) $lockedStock->quantity_damaged,
                'quantity_available_before' => $beforeAvailable,
                'quantity_available_after' => $lockedStock->availableQuantity(),
                'reference_type' => 'stock_adjustment',
                'reference_id' => $adjustment->getKey(),
                'notes' => $adjustment->reasonLabel().': '.$notes,
                'performed_by' => $actor->getKey(),
                'movement_date' => $adjustmentDate,
            ]);

            $adjustment->forceFill(['stock_movement_id' => $movement->getKey()])->save();

            return $adjustment->fresh([
                'warehouse:id,code,name',
                'productVariant:id,sku',
                'stockMovement:id,transaction_number,movement_type,direction,quantity',
            ]);
        }, 3);
    }

    private function generateAdjustmentNumber(): string
    {
        do {
            $number = 'ADJ-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        } while (StockAdjustment::query()->where('adjustment_number', $number)->exists());

        return $number;
    }

    private function generateMovementNumber(): string
    {
        do {
            $number = 'SM-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        } while (StockMovement::query()->where('transaction_number', $number)->exists());

        return $number;
    }
}
