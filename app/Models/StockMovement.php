<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const TYPE_STOCK_IN = 'stock_in';
    public const TYPE_STOCK_OUT = 'stock_out';
    public const TYPE_ADJUSTMENT_IN = 'adjustment_in';
    public const TYPE_ADJUSTMENT_OUT = 'adjustment_out';
    public const TYPE_DAMAGED = 'damaged';
    public const TYPE_DAMAGED_RECOVERY = 'damaged_recovery';
    public const TYPE_CUSTOMER_RETURN = 'customer_return';
    public const TYPE_VENDOR_RETURN = 'vendor_return';
    public const TYPE_TRANSFER_IN = 'transfer_in';
    public const TYPE_TRANSFER_OUT = 'transfer_out';
    public const TYPE_PURCHASE_RECEIPT = 'purchase_receipt';
    public const TYPE_SALE = 'sale';

    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';
    public const DIRECTION_NEUTRAL = 'neutral';

    protected $fillable = [
        'transaction_number',
        'warehouse_stock_id',
        'warehouse_id',
        'product_variant_id',
        'movement_type',
        'direction',
        'quantity',
        'quantity_before',
        'quantity_after',
        'quantity_reserved_before',
        'quantity_reserved_after',
        'quantity_damaged_before',
        'quantity_damaged_after',
        'quantity_available_before',
        'quantity_available_after',
        'reference_type',
        'reference_id',
        'notes',
        'performed_by',
        'movement_date',
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

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by')->withTrashed();
    }

    /** @return array<string, string> */
    public static function manualTypeOptions(): array
    {
        return [
            self::TYPE_STOCK_IN => 'Stok Masuk Manual',
            self::TYPE_STOCK_OUT => 'Stok Keluar Manual',
        ];
    }

    /** @return array<string, string> */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_STOCK_IN => 'Stok Masuk Manual',
            self::TYPE_STOCK_OUT => 'Stok Keluar Manual',
            self::TYPE_ADJUSTMENT_IN => 'Penyesuaian Stok +',
            self::TYPE_ADJUSTMENT_OUT => 'Penyesuaian Stok −',
            self::TYPE_DAMAGED => 'Barang Rusak',
            self::TYPE_DAMAGED_RECOVERY => 'Pemulihan Barang Rusak',
            self::TYPE_CUSTOMER_RETURN => 'Retur Pelanggan',
            self::TYPE_VENDOR_RETURN => 'Retur Vendor',
            self::TYPE_TRANSFER_IN => 'Transfer Masuk',
            self::TYPE_TRANSFER_OUT => 'Transfer Keluar',
            self::TYPE_PURCHASE_RECEIPT => 'Penerimaan Pembelian',
            self::TYPE_SALE => 'Penjualan',
        ];
    }

    public static function directionForType(string $movementType): string
    {
        return match ($movementType) {
            self::TYPE_STOCK_IN,
            self::TYPE_ADJUSTMENT_IN,
            self::TYPE_CUSTOMER_RETURN,
            self::TYPE_TRANSFER_IN,
            self::TYPE_PURCHASE_RECEIPT => self::DIRECTION_IN,

            self::TYPE_STOCK_OUT,
            self::TYPE_ADJUSTMENT_OUT,
            self::TYPE_VENDOR_RETURN,
            self::TYPE_TRANSFER_OUT,
            self::TYPE_SALE => self::DIRECTION_OUT,

            default => self::DIRECTION_NEUTRAL,
        };
    }

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->movement_type]
            ?? str($this->movement_type)->replace('_', ' ')->title()->toString();
    }

    public function directionLabel(): string
    {
        return match ($this->direction) {
            self::DIRECTION_IN => 'Masuk',
            self::DIRECTION_OUT => 'Keluar',
            default => 'Perubahan',
        };
    }

    public function directionSign(): string
    {
        return match ($this->direction) {
            self::DIRECTION_IN => '+',
            self::DIRECTION_OUT => '−',
            default => '±',
        };
    }

    public function scopeInWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
            'quantity_reserved_before' => 'integer',
            'quantity_reserved_after' => 'integer',
            'quantity_damaged_before' => 'integer',
            'quantity_damaged_after' => 'integer',
            'quantity_available_before' => 'integer',
            'quantity_available_after' => 'integer',
            'movement_date' => 'datetime',
        ];
    }
}
