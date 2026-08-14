<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ShipmentHistory extends Model
{
    public $timestamps=false;
    protected $fillable=['shipment_id','status','description','updated_by','created_at'];
    public function shipment(): BelongsTo { return $this->belongsTo(Shipment::class); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class,'updated_by')->withTrashed(); }
    protected function casts(): array { return ['created_at'=>'datetime']; }
}
