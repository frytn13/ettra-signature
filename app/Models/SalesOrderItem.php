<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SalesOrderItem extends Model
{
    protected $fillable=['sales_order_id','product_variant_id','sku_snapshot','product_name_snapshot','variant_snapshot','quantity','unit_price','discount_amount','subtotal','cost_price_snapshot'];
    public function order(): BelongsTo { return $this->belongsTo(SalesOrder::class,'sales_order_id'); }
    public function productVariant(): BelongsTo { return $this->belongsTo(ProductVariant::class); }
    protected function casts(): array { return ['quantity'=>'integer','unit_price'=>'decimal:2','discount_amount'=>'decimal:2','subtotal'=>'decimal:2','cost_price_snapshot'=>'decimal:2']; }
}
