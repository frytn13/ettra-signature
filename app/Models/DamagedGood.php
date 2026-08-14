<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamagedGood extends Model
{
    public const ACTION_MARK = 'mark_damaged';
    public const ACTION_RECOVER = 'recover';

    protected $fillable = [
        'transaction_number', 'warehouse_stock_id', 'warehouse_id', 'product_variant_id',
        'action', 'quantity', 'damaged_before', 'damaged_after', 'available_before',
        'available_after', 'reason', 'notes', 'transaction_date', 'processed_by', 'stock_movement_id',
    ];

    public function warehouseStock(): BelongsTo { return $this->belongsTo(WarehouseStock::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function productVariant(): BelongsTo { return $this->belongsTo(ProductVariant::class); }
    public function processedBy(): BelongsTo { return $this->belongsTo(User::class, 'processed_by')->withTrashed(); }
    public function stockMovement(): BelongsTo { return $this->belongsTo(StockMovement::class); }

    public function actionLabel(): string
    {
        return $this->action === self::ACTION_RECOVER ? 'Pulihkan Barang' : 'Tandai Rusak';
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'integer', 'damaged_before' => 'integer', 'damaged_after' => 'integer',
            'available_before' => 'integer', 'available_after' => 'integer', 'transaction_date' => 'datetime',
        ];
    }
}
