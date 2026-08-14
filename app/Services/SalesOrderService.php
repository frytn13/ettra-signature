<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Promotion;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Shipment;
use App\Models\ShipmentHistory;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesOrderService
{
    public function create(array $data, array $items, ?string $proofPath, User $actor): SalesOrder
    {
        return DB::transaction(function () use ($data, $items, $proofPath, $actor): SalesOrder {
            $date = $data['transaction_date'] instanceof CarbonInterface ? $data['transaction_date'] : Carbon::parse($data['transaction_date']);
            $promotions = Promotion::query()->activeNow($date)->get();

            $order = SalesOrder::query()->create([
                'transaction_number' => $this->orderNumber(),
                'channel' => $data['channel'],
                'warehouse_id' => (int)$data['warehouse_id'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'subtotal' => 0,
                'discount_total' => 0,
                'shipping_cost' => (float)($data['shipping_cost'] ?? 0),
                'grand_total' => 0,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'unpaid',
                'order_status' => 'waiting_payment',
                'notes' => $data['notes'] ?? null,
                'transaction_date' => $date,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $subtotal = 0.0;
            $discountTotal = 0.0;
            $lockedStocks = [];

            foreach ($items as $item) {
                $variantId = (int)$item['product_variant_id'];
                $qty = (int)$item['quantity'];
                $stock = WarehouseStock::query()
                    ->with(['productVariant.product','productVariant.color','productVariant.size'])
                    ->where('warehouse_id', $order->warehouse_id)
                    ->where('product_variant_id', $variantId)
                    ->lockForUpdate()
                    ->first();

                if (! $stock || ! $stock->productVariant?->is_active || ! $stock->productVariant?->product) {
                    throw ValidationException::withMessages(['items' => 'Salah satu variasi belum tersedia pada room yang dipilih.']);
                }
                if ($qty > $stock->availableQuantity()) {
                    throw ValidationException::withMessages(['items' => "Stok {$stock->productVariant->sku} tidak mencukupi. Tersedia {$stock->availableQuantity()} unit."]);
                }

                $variant = $stock->productVariant;
                $unitPrice = $variant->finalSellingPrice();
                $discountPerUnit = 0.0;
                foreach ($promotions as $promotion) {
                    $discountPerUnit = max($discountPerUnit, $promotion->discountFor($variant, $unitPrice));
                }
                $gross = $unitPrice * $qty;
                $lineDiscount = min($gross, $discountPerUnit * $qty);
                $lineSubtotal = max(0, $gross - $lineDiscount);

                SalesOrderItem::query()->create([
                    'sales_order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'sku_snapshot' => $variant->sku,
                    'product_name_snapshot' => $variant->product->name,
                    'variant_snapshot' => trim(($variant->color?->name ?? '').' / '.($variant->size?->name ?? ''), ' /'),
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $lineDiscount,
                    'subtotal' => $lineSubtotal,
                    'cost_price_snapshot' => $variant->product->cost_price,
                ]);

                $stock->forceFill([
                    'quantity_reserved' => (int)$stock->quantity_reserved + $qty,
                    'updated_by' => $actor->id,
                ])->save();

                $lockedStocks[$variantId] = $stock->fresh();
                $subtotal += $gross;
                $discountTotal += $lineDiscount;
            }

            $grandTotal = max(0, $subtotal - $discountTotal + (float)$order->shipping_cost);
            $order->forceFill([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'grand_total' => $grandTotal,
            ])->save();

            $payment = Payment::query()->create([
                'sales_order_id' => $order->id,
                'payment_number' => $this->paymentNumber(),
                'method' => $order->payment_method,
                'amount' => $grandTotal,
                'status' => $order->payment_method === 'cash' ? 'verified' : 'pending',
                'proof_path' => $proofPath,
                'verified_by' => $order->payment_method === 'cash' ? $actor->id : null,
                'verified_at' => $order->payment_method === 'cash' ? now() : null,
                'paid_at' => $order->payment_method === 'cash' ? now() : null,
                'created_by' => $actor->id,
            ]);

            if ($order->channel === 'online' || filled($order->shipping_address)) {
                $shipment = Shipment::query()->create(['sales_order_id'=>$order->id,'status'=>'pending','updated_by'=>$actor->id]);
                ShipmentHistory::query()->create(['shipment_id'=>$shipment->id,'status'=>'pending','description'=>'Pesanan dibuat dan menunggu proses pengemasan.','updated_by'=>$actor->id,'created_at'=>now()]);
            }

            if ($order->payment_method === 'cash') {
                $this->finalizeReservedStock($order, $actor);
                $order->forceFill(['payment_status'=>'paid','order_status'=>'completed','verified_by'=>$actor->id,'verified_at'=>now()])->save();
            } else {
                $order->forceFill(['payment_status'=>$proofPath ? 'waiting_verification' : 'unpaid','order_status'=>'waiting_payment'])->save();
            }

            return $order->fresh(['items','payments','shipment','warehouse']);
        }, 3);
    }

    public function verifyPayment(Payment $payment, User $actor, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($payment,$actor,$notes): Payment {
            $payment = Payment::query()->with('order.items')->lockForUpdate()->findOrFail($payment->id);
            $order = SalesOrder::query()->lockForUpdate()->findOrFail($payment->sales_order_id);
            if ($payment->status === 'verified' || $order->payment_status === 'paid') return $payment;
            if ($order->order_status === 'cancelled') throw ValidationException::withMessages(['decision'=>'Pesanan sudah dibatalkan.']);

            $this->ensureReservations($order, $actor);
            $this->finalizeReservedStock($order, $actor);

            $payment->forceFill(['status'=>'verified','rejection_reason'=>null,'notes'=>$notes,'verified_by'=>$actor->id,'verified_at'=>now(),'paid_at'=>now()])->save();
            $order->forceFill(['payment_status'=>'paid','order_status'=>$order->channel==='offline'?'completed':'processing','verified_by'=>$actor->id,'verified_at'=>now(),'updated_by'=>$actor->id])->save();
            return $payment->fresh(['order']);
        }, 3);
    }

    public function rejectPayment(Payment $payment, User $actor, string $reason, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($payment,$actor,$reason,$notes): Payment {
            $payment = Payment::query()->with('order.items')->lockForUpdate()->findOrFail($payment->id);
            $order = SalesOrder::query()->lockForUpdate()->findOrFail($payment->sales_order_id);
            if ($payment->status === 'verified' || $order->payment_status === 'paid') throw ValidationException::withMessages(['decision'=>'Pembayaran yang sudah lunas tidak dapat ditolak.']);
            $this->releaseReservations($order, $actor);
            $payment->forceFill(['status'=>'rejected','rejection_reason'=>$reason,'notes'=>$notes,'verified_by'=>$actor->id,'verified_at'=>now()])->save();
            $order->forceFill(['payment_status'=>'rejected','order_status'=>'waiting_payment','verified_by'=>$actor->id,'verified_at'=>now(),'updated_by'=>$actor->id])->save();
            return $payment->fresh(['order']);
        }, 3);
    }

    public function cancel(SalesOrder $order, User $actor): SalesOrder
    {
        return DB::transaction(function () use ($order,$actor): SalesOrder {
            $order = SalesOrder::query()->with('items')->lockForUpdate()->findOrFail($order->id);
            if ($order->payment_status === 'paid') throw ValidationException::withMessages(['order'=>'Pesanan yang sudah lunas memerlukan proses refund dan tidak dapat dibatalkan langsung.']);
            if ($order->order_status !== 'cancelled') $this->releaseReservations($order,$actor);
            $order->forceFill(['order_status'=>'cancelled','updated_by'=>$actor->id])->save();
            return $order;
        },3);
    }

    private function ensureReservations(SalesOrder $order, User $actor): void
    {
        foreach ($order->items as $item) {
            $stock = WarehouseStock::query()->where('warehouse_id',$order->warehouse_id)->where('product_variant_id',$item->product_variant_id)->lockForUpdate()->firstOrFail();
            // If current reserved quantity is insufficient, reserve from available inventory.
            if ((int)$stock->quantity_reserved < $item->quantity) {
                $needed = $item->quantity - (int)$stock->quantity_reserved;
                if ($needed > $stock->availableQuantity()) throw ValidationException::withMessages(['decision'=>"Stok {$item->sku_snapshot} tidak lagi mencukupi untuk verifikasi pembayaran."]);
                $stock->forceFill(['quantity_reserved'=>(int)$stock->quantity_reserved+$needed,'updated_by'=>$actor->id])->save();
            }
        }
    }

    private function finalizeReservedStock(SalesOrder $order, User $actor): void
    {
        $order->loadMissing('items');
        foreach ($order->items as $item) {
            $stock = WarehouseStock::query()->where('warehouse_id',$order->warehouse_id)->where('product_variant_id',$item->product_variant_id)->lockForUpdate()->firstOrFail();
            $beforeOnHand=(int)$stock->quantity_on_hand; $beforeReserved=(int)$stock->quantity_reserved; $beforeDamaged=(int)$stock->quantity_damaged; $beforeAvailable=$stock->availableQuantity();
            if ($beforeReserved < $item->quantity || $beforeOnHand < $item->quantity) throw ValidationException::withMessages(['items'=>"Reservasi stok {$item->sku_snapshot} tidak valid."]);
            $stock->forceFill(['quantity_reserved'=>$beforeReserved-$item->quantity,'quantity_on_hand'=>$beforeOnHand-$item->quantity,'updated_by'=>$actor->id])->save(); $stock->refresh();
            StockMovement::query()->create([
                'transaction_number'=>$this->movementNumber(),'warehouse_stock_id'=>$stock->id,'warehouse_id'=>$stock->warehouse_id,'product_variant_id'=>$stock->product_variant_id,
                'movement_type'=>StockMovement::TYPE_SALE,'direction'=>StockMovement::DIRECTION_OUT,'quantity'=>$item->quantity,'quantity_before'=>$beforeOnHand,'quantity_after'=>(int)$stock->quantity_on_hand,
                'quantity_reserved_before'=>$beforeReserved,'quantity_reserved_after'=>(int)$stock->quantity_reserved,'quantity_damaged_before'=>$beforeDamaged,'quantity_damaged_after'=>(int)$stock->quantity_damaged,
                'quantity_available_before'=>$beforeAvailable,'quantity_available_after'=>$stock->availableQuantity(),'reference_type'=>'sales_order','reference_id'=>$order->id,
                'notes'=>'Penjualan '.$order->transaction_number,'performed_by'=>$actor->id,'movement_date'=>now(),
            ]);
        }
    }

    private function releaseReservations(SalesOrder $order, User $actor): void
    {
        $order->loadMissing('items');
        foreach ($order->items as $item) {
            $stock = WarehouseStock::query()->where('warehouse_id',$order->warehouse_id)->where('product_variant_id',$item->product_variant_id)->lockForUpdate()->first();
            if (! $stock) continue;
            $release=min((int)$stock->quantity_reserved,(int)$item->quantity);
            if($release>0) $stock->forceFill(['quantity_reserved'=>(int)$stock->quantity_reserved-$release,'updated_by'=>$actor->id])->save();
        }
    }

    private function orderNumber(): string { do{$n='TRX-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));}while(SalesOrder::query()->where('transaction_number',$n)->exists()); return $n; }
    private function paymentNumber(): string { do{$n='PAY-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));}while(Payment::query()->where('payment_number',$n)->exists()); return $n; }
    private function movementNumber(): string { do{$n='SM-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));}while(StockMovement::query()->where('transaction_number',$n)->exists()); return $n; }
}
