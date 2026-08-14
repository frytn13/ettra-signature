<?php

namespace App\Services;

use App\Models\StockMovement;
use App\Models\User;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockMovementService
{
    public function recordManualMovement(
        WarehouseStock $warehouseStock,
        string $movementType,
        int $quantity,
        CarbonInterface $movementDate,
        string $notes,
        User $actor,
    ): StockMovement {
        if (! array_key_exists($movementType, StockMovement::manualTypeOptions())) {
            throw ValidationException::withMessages([
                'movement_type' => 'Jenis mutasi manual tidak didukung.',
            ]);
        }

        return DB::transaction(function () use (
            $warehouseStock,
            $movementType,
            $quantity,
            $movementDate,
            $notes,
            $actor,
        ): StockMovement {
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
                    'warehouse_stock_id' => 'Produk pada titik stok ini sudah dihentikan dan tidak dapat menerima mutasi manual.',
                ]);
            }

            $latestMovementDate = StockMovement::query()
                ->where('warehouse_stock_id', $lockedStock->id)
                ->max('movement_date');

            if ($latestMovementDate !== null && $movementDate->lt(Carbon::parse($latestMovementDate))) {
                throw ValidationException::withMessages([
                    'movement_date' => 'Tanggal mutasi tidak boleh lebih awal dari mutasi terakhir pada titik stok ini. Gunakan urutan waktu yang kronologis agar snapshot ledger tetap konsisten.',
                ]);
            }

            $beforeOnHand = (int) $lockedStock->quantity_on_hand;
            $beforeReserved = (int) $lockedStock->quantity_reserved;
            $beforeDamaged = (int) $lockedStock->quantity_damaged;
            $beforeAvailable = $lockedStock->availableQuantity();
            $direction = StockMovement::directionForType($movementType);

            if ($direction === StockMovement::DIRECTION_OUT && $quantity > $beforeAvailable) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok keluar melebihi stok tersedia. Stok tersedia saat ini {$beforeAvailable} unit.",
                ]);
            }

            $afterOnHand = match ($direction) {
                StockMovement::DIRECTION_IN => $beforeOnHand + $quantity,
                StockMovement::DIRECTION_OUT => $beforeOnHand - $quantity,
                default => $beforeOnHand,
            };

            $lockedStock->forceFill([
                'quantity_on_hand' => $afterOnHand,
                'updated_by' => $actor->getKey(),
            ])->save();

            $lockedStock->refresh();

            return StockMovement::query()->create([
                'transaction_number' => $this->generateTransactionNumber(),
                'warehouse_stock_id' => $lockedStock->id,
                'warehouse_id' => $lockedStock->warehouse_id,
                'product_variant_id' => $lockedStock->product_variant_id,
                'movement_type' => $movementType,
                'direction' => $direction,
                'quantity' => $quantity,
                'quantity_before' => $beforeOnHand,
                'quantity_after' => (int) $lockedStock->quantity_on_hand,
                'quantity_reserved_before' => $beforeReserved,
                'quantity_reserved_after' => (int) $lockedStock->quantity_reserved,
                'quantity_damaged_before' => $beforeDamaged,
                'quantity_damaged_after' => (int) $lockedStock->quantity_damaged,
                'quantity_available_before' => $beforeAvailable,
                'quantity_available_after' => $lockedStock->availableQuantity(),
                'reference_type' => 'manual_stock_movement',
                'reference_id' => null,
                'notes' => $notes,
                'performed_by' => $actor->getKey(),
                'movement_date' => $movementDate,
            ]);
        }, 3);
    }

    private function generateTransactionNumber(): string
    {
        do {
            $number = 'SM-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        } while (StockMovement::query()->where('transaction_number', $number)->exists());

        return $number;
    }
}
