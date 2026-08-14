<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesReturn extends Model
{
    protected $fillable = [
        'return_number', 'sales_order_id', 'warehouse_id', 'return_date', 'reason', 'notes',
        'refund_amount', 'refund_status', 'processed_by',
    ];

    public function order(): BelongsTo { return $this->belongsTo(SalesOrder::class, 'sales_order_id'); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function processedBy(): BelongsTo { return $this->belongsTo(User::class, 'processed_by')->withTrashed(); }
    public function items(): HasMany { return $this->hasMany(SalesReturnItem::class); }

    public function refundStatusLabel(): string
    {
        return ['not_required' => 'Tidak Ada Refund', 'pending' => 'Menunggu Refund', 'completed' => 'Refund Selesai'][$this->refund_status] ?? $this->refund_status;
    }

    protected function casts(): array
    {
        return ['return_date' => 'datetime', 'refund_amount' => 'decimal:2'];
    }
}
