<?php

namespace App\Services;

use App\Models\DamagedGood;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\WarehouseStock;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DamagedGoodService
{
    public function process(WarehouseStock $warehouseStock, string $action, int $quantity, string $reason, ?string $notes, CarbonInterface $date, User $actor): DamagedGood
    {
        return DB::transaction(function () use ($warehouseStock, $action, $quantity, $reason, $notes, $date, $actor): DamagedGood {
            $stock = WarehouseStock::query()->with(['warehouse:id,code,name,is_active','productVariant.product:id,name,status'])->lockForUpdate()->findOrFail($warehouseStock->id);
            if (! $stock->warehouse?->is_active || ! $stock->productVariant?->is_active) {
                throw ValidationException::withMessages(['warehouse_stock_id' => 'Room atau variasi produk tidak aktif.']);
            }

            $beforeDamaged = (int) $stock->quantity_damaged;
            $beforeAvailable = $stock->availableQuantity();

            if ($action === DamagedGood::ACTION_MARK) {
                if ($quantity > $beforeAvailable) {
                    throw ValidationException::withMessages(['quantity' => "Jumlah rusak melebihi stok tersedia ({$beforeAvailable} unit)."]);
                }
                $afterDamaged = $beforeDamaged + $quantity;
                $movementType = StockMovement::TYPE_DAMAGED;
            } elseif ($action === DamagedGood::ACTION_RECOVER) {
                if ($quantity > $beforeDamaged) {
                    throw ValidationException::withMessages(['quantity' => "Jumlah pemulihan melebihi stok rusak ({$beforeDamaged} unit)."]);
                }
                $afterDamaged = $beforeDamaged - $quantity;
                $movementType = StockMovement::TYPE_DAMAGED_RECOVERY;
            } else {
                throw ValidationException::withMessages(['action' => 'Aksi barang rusak tidak valid.']);
            }

            $record = DamagedGood::query()->create([
                'transaction_number' => $this->number(),
                'warehouse_stock_id' => $stock->id,
                'warehouse_id' => $stock->warehouse_id,
                'product_variant_id' => $stock->product_variant_id,
                'action' => $action,
                'quantity' => $quantity,
                'damaged_before' => $beforeDamaged,
                'damaged_after' => $afterDamaged,
                'available_before' => $beforeAvailable,
                'available_after' => 0,
                'reason' => $reason,
                'notes' => $notes,
                'transaction_date' => $date,
                'processed_by' => $actor->id,
            ]);

            $stock->forceFill(['quantity_damaged' => $afterDamaged, 'updated_by' => $actor->id])->save();
            $stock->refresh();

            $movement = StockMovement::query()->create([
                'transaction_number' => $this->movementNumber(),
                'warehouse_stock_id' => $stock->id,
                'warehouse_id' => $stock->warehouse_id,
                'product_variant_id' => $stock->product_variant_id,
                'movement_type' => $movementType,
                'direction' => StockMovement::DIRECTION_NEUTRAL,
                'quantity' => $quantity,
                'quantity_before' => (int) $stock->quantity_on_hand,
                'quantity_after' => (int) $stock->quantity_on_hand,
                'quantity_reserved_before' => (int) $stock->quantity_reserved,
                'quantity_reserved_after' => (int) $stock->quantity_reserved,
                'quantity_damaged_before' => $beforeDamaged,
                'quantity_damaged_after' => $afterDamaged,
                'quantity_available_before' => $beforeAvailable,
                'quantity_available_after' => $stock->availableQuantity(),
                'reference_type' => 'damaged_good',
                'reference_id' => $record->id,
                'notes' => $reason.($notes ? ': '.$notes : ''),
                'performed_by' => $actor->id,
                'movement_date' => $date,
            ]);

            $record->forceFill(['stock_movement_id' => $movement->id, 'available_after' => $stock->availableQuantity()])->save();
            return $record->fresh(['warehouse:id,code,name','productVariant.product:id,name','productVariant.color:id,name','productVariant.size:id,name','processedBy:id,name']);
        }, 3);
    }

    private function number(): string
    {
        do { $n = 'DMG-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4)); } while (DamagedGood::query()->where('transaction_number',$n)->exists());
        return $n;
    }

    private function movementNumber(): string
    {
        do { $n = 'SM-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4)); } while (StockMovement::query()->where('transaction_number',$n)->exists());
        return $n;
    }
}
