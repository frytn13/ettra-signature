<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseStock extends Model
{
    public const STATUS_SAFE = 'safe';
    public const STATUS_LOW = 'low';
    public const STATUS_OUT = 'out';

    protected $fillable = [
        'warehouse_id',
        'product_variant_id',
        'quantity_on_hand',
        'quantity_reserved',
        'quantity_damaged',
        'minimum_stock',
        'created_by',
        'updated_by',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function damagedGoods(): HasMany
    {
        return $this->hasMany(DamagedGood::class);
    }

    public function sourceTransferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'source_stock_id');
    }

    public function destinationTransferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'destination_stock_id');
    }

    public function availableQuantity(): int
    {
        return max(
            0,
            (int) $this->quantity_on_hand - (int) $this->quantity_reserved - (int) $this->quantity_damaged,
        );
    }

    public function stockStatus(): string
    {
        $available = $this->availableQuantity();

        if ($available <= 0) {
            return self::STATUS_OUT;
        }

        if ($available <= (int) $this->minimum_stock) {
            return self::STATUS_LOW;
        }

        return self::STATUS_SAFE;
    }

    public function stockStatusLabel(): string
    {
        return match ($this->stockStatus()) {
            self::STATUS_OUT => 'Habis',
            self::STATUS_LOW => 'Menipis',
            default => 'Aman',
        };
    }

    public function needsRestock(): bool
    {
        return $this->availableQuantity() <= (int) $this->minimum_stock;
    }

    public function scopeInWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public static function availableSql(): string
    {
        return 'CASE WHEN quantity_on_hand > (quantity_reserved + quantity_damaged) '
            .'THEN quantity_on_hand - quantity_reserved - quantity_damaged ELSE 0 END';
    }

    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'integer',
            'quantity_reserved' => 'integer',
            'quantity_damaged' => 'integer',
            'minimum_stock' => 'integer',
        ];
    }
}
