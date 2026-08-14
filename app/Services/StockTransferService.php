<?php

namespace App\Services;

use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use App\Models\WarehouseStock;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockTransferService
{
    public function process(int $sourceWarehouseId, int $destinationWarehouseId, array $items, CarbonInterface $date, ?string $notes, User $actor): StockTransfer
    {
        if ($sourceWarehouseId === $destinationWarehouseId) {
            throw ValidationException::withMessages(['destination_warehouse_id' => 'Room tujuan harus berbeda dari room asal.']);
        }

        return DB::transaction(function () use ($sourceWarehouseId,$destinationWarehouseId,$items,$date,$notes,$actor): StockTransfer {
            $transfer = StockTransfer::query()->create([
                'transfer_number' => $this->transferNumber(),
                'source_warehouse_id' => $sourceWarehouseId,
                'destination_warehouse_id' => $destinationWarehouseId,
                'status' => 'processed',
                'transfer_date' => $date,
                'notes' => $notes,
                'processed_by' => $actor->id,
            ]);

            $seen = [];
            foreach ($items as $item) {
                $variantId = (int)($item['product_variant_id'] ?? 0);
                $quantity = (int)($item['quantity'] ?? 0);
                if ($variantId <= 0 || $quantity <= 0 || isset($seen[$variantId])) {
                    throw ValidationException::withMessages(['items' => 'Setiap variasi hanya boleh muncul satu kali dan jumlah harus lebih dari 0.']);
                }
                $seen[$variantId] = true;

                $source = WarehouseStock::query()->where('warehouse_id',$sourceWarehouseId)->where('product_variant_id',$variantId)->lockForUpdate()->first();
                if (! $source) {
                    throw ValidationException::withMessages(['items' => 'Salah satu SKU tidak memiliki stok di room asal.']);
                }
                if ($quantity > $source->availableQuantity()) {
                    throw ValidationException::withMessages(['items' => "Transfer {$quantity} unit melebihi stok tersedia untuk SKU #{$variantId} ({$source->availableQuantity()} unit)."]);
                }

                $destination = WarehouseStock::query()->where('warehouse_id',$destinationWarehouseId)->where('product_variant_id',$variantId)->lockForUpdate()->first();
                if (! $destination) {
                    $destination = WarehouseStock::query()->create([
                        'warehouse_id'=>$destinationWarehouseId,'product_variant_id'=>$variantId,
                        'quantity_on_hand'=>0,'quantity_reserved'=>0,'quantity_damaged'=>0,'minimum_stock'=>0,
                        'created_by'=>$actor->id,'updated_by'=>$actor->id,
                    ]);
                    $destination = WarehouseStock::query()->lockForUpdate()->findOrFail($destination->id);
                }

                $sourceBefore = (int)$source->quantity_on_hand;
                $sourceAvailableBefore = $source->availableQuantity();
                $destBefore = (int)$destination->quantity_on_hand;
                $destAvailableBefore = $destination->availableQuantity();

                $source->forceFill(['quantity_on_hand'=>$sourceBefore-$quantity,'updated_by'=>$actor->id])->save();
                $destination->forceFill(['quantity_on_hand'=>$destBefore+$quantity,'updated_by'=>$actor->id])->save();
                $source->refresh(); $destination->refresh();

                StockTransferItem::query()->create([
                    'stock_transfer_id'=>$transfer->id,'product_variant_id'=>$variantId,'source_stock_id'=>$source->id,'destination_stock_id'=>$destination->id,'quantity'=>$quantity,
                ]);

                StockMovement::query()->create($this->movementPayload($source, StockMovement::TYPE_TRANSFER_OUT, StockMovement::DIRECTION_OUT, $quantity, $sourceBefore, $sourceAvailableBefore, $transfer, $date, $actor));
                StockMovement::query()->create($this->movementPayload($destination, StockMovement::TYPE_TRANSFER_IN, StockMovement::DIRECTION_IN, $quantity, $destBefore, $destAvailableBefore, $transfer, $date, $actor));
            }

            return $transfer->fresh(['sourceWarehouse:id,code,name','destinationWarehouse:id,code,name','items.productVariant.product:id,name','items.productVariant.color:id,name','items.productVariant.size:id,name','processedBy:id,name']);
        }, 3);
    }

    private function movementPayload(WarehouseStock $stock, string $type, string $direction, int $quantity, int $beforeOnHand, int $beforeAvailable, StockTransfer $transfer, CarbonInterface $date, User $actor): array
    {
        return [
            'transaction_number'=>$this->movementNumber(),'warehouse_stock_id'=>$stock->id,'warehouse_id'=>$stock->warehouse_id,'product_variant_id'=>$stock->product_variant_id,
            'movement_type'=>$type,'direction'=>$direction,'quantity'=>$quantity,'quantity_before'=>$beforeOnHand,'quantity_after'=>(int)$stock->quantity_on_hand,
            'quantity_reserved_before'=>(int)$stock->quantity_reserved,'quantity_reserved_after'=>(int)$stock->quantity_reserved,
            'quantity_damaged_before'=>(int)$stock->quantity_damaged,'quantity_damaged_after'=>(int)$stock->quantity_damaged,
            'quantity_available_before'=>$beforeAvailable,'quantity_available_after'=>$stock->availableQuantity(),
            'reference_type'=>'stock_transfer','reference_id'=>$transfer->id,'notes'=>'Transfer '.$transfer->transfer_number,'performed_by'=>$actor->id,'movement_date'=>$date,
        ];
    }

    private function transferNumber(): string { do { $n='TRF-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4)); } while(StockTransfer::query()->where('transfer_number',$n)->exists()); return $n; }
    private function movementNumber(): string { do { $n='SM-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4)); } while(StockMovement::query()->where('transaction_number',$n)->exists()); return $n; }
}
