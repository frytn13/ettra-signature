<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','discount_type','discount_value','target_type','product_id','category_id','starts_at','ends_at','is_active','created_by','updated_by'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class,'created_by')->withTrashed(); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class,'updated_by')->withTrashed(); }

    public function scopeActiveNow(Builder $query, ?CarbonInterface $at = null): Builder
    {
        $at ??= now();
        return $query->where('is_active',true)->where('starts_at','<=',$at)->where('ends_at','>=',$at);
    }

    public function discountFor(ProductVariant $variant, float $unitPrice): float
    {
        $product = $variant->product;
        $matches = $this->target_type === 'all'
            || ($this->target_type === 'product' && $this->product_id === $product?->id)
            || ($this->target_type === 'category' && $this->category_id === $product?->category_id);
        if (! $matches || $unitPrice <= 0) return 0;
        $discount = $this->discount_type === 'percentage' ? $unitPrice * ((float)$this->discount_value / 100) : (float)$this->discount_value;
        return max(0, min($discount, $unitPrice));
    }

    public function discountLabel(): string
    {
        return $this->discount_type === 'percentage'
            ? rtrim(rtrim(number_format((float)$this->discount_value,2,'.',''),'0'),'.').'%' : 'Rp'.number_format((float)$this->discount_value,0,',','.');
    }

    protected function casts(): array { return ['discount_value'=>'decimal:2','starts_at'=>'datetime','ends_at'=>'datetime','is_active'=>'boolean']; }
}
