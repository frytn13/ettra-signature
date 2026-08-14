<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model
{
    protected $fillable=['sales_order_id','payment_number','method','amount','status','proof_path','rejection_reason','notes','verified_by','verified_at','paid_at','created_by'];
    public function order(): BelongsTo { return $this->belongsTo(SalesOrder::class,'sales_order_id'); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class,'verified_by')->withTrashed(); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class,'created_by')->withTrashed(); }
    public function methodLabel(): string { return ['cash'=>'Tunai','bank_transfer'=>'Transfer Bank','qris'=>'QRIS'][$this->method] ?? $this->method; }
    public function statusLabel(): string { return ['pending'=>'Menunggu Verifikasi','verified'=>'Lunas','rejected'=>'Ditolak','refunded'=>'Dikembalikan'][$this->status] ?? $this->status; }
    protected function casts(): array { return ['amount'=>'decimal:2','verified_at'=>'datetime','paid_at'=>'datetime']; }
}
