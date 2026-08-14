<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Shipment extends Model
{
    protected $fillable=['sales_order_id','courier','tracking_number','status','packed_at','shipped_at','delivered_at','updated_by'];
    public function order(): BelongsTo { return $this->belongsTo(SalesOrder::class,'sales_order_id'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class,'updated_by')->withTrashed(); }
    public function histories(): HasMany { return $this->hasMany(ShipmentHistory::class)->latest('created_at'); }
    public function statusLabel(): string { return ['pending'=>'Belum Dikemas','packed'=>'Dikemas','in_transit'=>'Sedang dalam perjalanan','delivered'=>'Diterima'][$this->status] ?? $this->status; }
    protected function casts(): array { return ['packed_at'=>'datetime','shipped_at'=>'datetime','delivered_at'=>'datetime']; }
}
