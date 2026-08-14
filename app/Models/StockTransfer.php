<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    protected $fillable = ['transfer_number','source_warehouse_id','destination_warehouse_id','status','transfer_date','notes','processed_by'];

    public function sourceWarehouse(): BelongsTo { return $this->belongsTo(Warehouse::class, 'source_warehouse_id'); }
    public function destinationWarehouse(): BelongsTo { return $this->belongsTo(Warehouse::class, 'destination_warehouse_id'); }
    public function processedBy(): BelongsTo { return $this->belongsTo(User::class, 'processed_by')->withTrashed(); }
    public function items(): HasMany { return $this->hasMany(StockTransferItem::class); }

    protected function casts(): array { return ['transfer_date' => 'datetime']; }
}
