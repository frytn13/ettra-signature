<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReturnItem extends Model
{
    protected $fillable = ['sales_return_id', 'sales_order_item_id', 'product_variant_id', 'quantity', 'condition', 'unit_refund_amount'];
    public function salesReturn(): BelongsTo { return $this->belongsTo(SalesReturn::class); }
    public function orderItem(): BelongsTo { return $this->belongsTo(SalesOrderItem::class, 'sales_order_item_id'); }
    public function productVariant(): BelongsTo { return $this->belongsTo(ProductVariant::class); }
    protected function casts(): array { return ['quantity' => 'integer', 'unit_refund_amount' => 'decimal:2']; }
}
