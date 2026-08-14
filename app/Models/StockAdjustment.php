<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    public const STATUS_PROCESSED = 'processed';

    public const REASON_STOCK_OPNAME = 'stock_opname';
    public const REASON_INITIAL_CORRECTION = 'initial_correction';
    public const REASON_FOUND_STOCK = 'found_stock';
    public const REASON_PHYSICAL_DIFFERENCE = 'physical_difference';
    public const REASON_OTHER = 'other';

    protected $fillable = [
        'adjustment_number',
        'warehouse_stock_id',
        'warehouse_id',
        'product_variant_id',
        'system_quantity',
        'physical_quantity',
        'difference_quantity',
        'movement_type',
        'reason',
        'status',
        'notes',
        'adjustment_date',
        'processed_by',
        'stock_movement_id',
    ];

    public function warehouseStock(): BelongsTo
    {
        return $this->belongsTo(WarehouseStock::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by')->withTrashed();
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }

    /** @return array<string, string> */
    public static function reasonOptions(): array
    {
        return [
            self::REASON_STOCK_OPNAME => 'Stock Opname Rutin',
            self::REASON_INITIAL_CORRECTION => 'Koreksi Data Awal',
            self::REASON_FOUND_STOCK => 'Barang Ditemukan',
            self::REASON_PHYSICAL_DIFFERENCE => 'Selisih Stok Fisik',
            self::REASON_OTHER => 'Lainnya',
        ];
    }

    public function reasonLabel(): string
    {
        return self::reasonOptions()[$this->reason]
            ?? str($this->reason)->replace('_', ' ')->title()->toString();
    }

    public function direction(): string
    {
        if ($this->difference_quantity > 0) {
            return StockMovement::DIRECTION_IN;
        }

        if ($this->difference_quantity < 0) {
            return StockMovement::DIRECTION_OUT;
        }

        return StockMovement::DIRECTION_NEUTRAL;
    }

    public function directionLabel(): string
    {
        return match ($this->direction()) {
            StockMovement::DIRECTION_IN => 'Penambahan',
            StockMovement::DIRECTION_OUT => 'Pengurangan',
            default => 'Tanpa Selisih',
        };
    }

    public function differenceSign(): string
    {
        return $this->difference_quantity > 0 ? '+' : ($this->difference_quantity < 0 ? '−' : '±');
    }

    public function statusLabel(): string
    {
        return $this->status === self::STATUS_PROCESSED ? 'Diproses' : ucfirst($this->status);
    }

    public function scopeInWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    protected function casts(): array
    {
        return [
            'system_quantity' => 'integer',
            'physical_quantity' => 'integer',
            'difference_quantity' => 'integer',
            'adjustment_date' => 'datetime',
        ];
    }
}
