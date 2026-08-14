<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_DISCONTINUED = 'discontinued';

    public const AVAILABILITY_AVAILABLE = 'available';

    public const AVAILABILITY_UNAVAILABLE = 'unavailable';

    public const AVAILABILITY_PREORDER = 'preorder';

    protected $fillable = [
        'category_id',
        'category_sequence',
        'code',
        'name',
        'slug',
        'description',
        'initial_purchase_price',
        'cost_price',
        'selling_price',
        'status',
        'availability_status',
        'is_visible',
        'weight_grams',
        'entry_date',
        'created_by',
        'updated_by',
    ];

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Nonaktif',
            self::STATUS_DISCONTINUED => 'Dihentikan',
        ];
    }

    /** @return array<string, string> */
    public static function availabilityOptions(): array
    {
        return [
            self::AVAILABILITY_AVAILABLE => 'Tersedia',
            self::AVAILABILITY_UNAVAILABLE => 'Tidak Tersedia',
            self::AVAILABILITY_PREORDER => 'Pre-order',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sku');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst($this->status);
    }

    public function availabilityLabel(): string
    {
        return self::availabilityOptions()[$this->availability_status] ?? ucfirst($this->availability_status);
    }

    public function estimatedProfit(): ?float
    {
        if ($this->cost_price === null) {
            return null;
        }

        return (float) $this->selling_price - (float) $this->cost_price;
    }

    public function grossMarginPercentage(): ?float
    {
        $sellingPrice = (float) $this->selling_price;
        $profit = $this->estimatedProfit();

        if ($profit === null || $sellingPrice <= 0) {
            return null;
        }

        return ($profit / $sellingPrice) * 100;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    protected function casts(): array
    {
        return [
            'category_sequence' => 'integer',
            'initial_purchase_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'is_visible' => 'boolean',
            'weight_grams' => 'integer',
            'entry_date' => 'date',
        ];
    }
}
