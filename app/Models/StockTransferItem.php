<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    protected $fillable = ['stock_transfer_id','product_variant_id','source_stock_id','destination_stock_id','quantity'];

    public function transfer(): BelongsTo { return $this->belongsTo(StockTransfer::class, 'stock_transfer_id'); }
    public function productVariant(): BelongsTo { return $this->belongsTo(ProductVariant::class); }
    public function sourceStock(): BelongsTo { return $this->belongsTo(WarehouseStock::class, 'source_stock_id'); }
    public function destinationStock(): BelongsTo { return $this->belongsTo(WarehouseStock::class, 'destination_stock_id'); }

    protected function casts(): array { return ['quantity' => 'integer']; }
}
