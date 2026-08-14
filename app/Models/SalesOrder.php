<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use SoftDeletes;

    public const CHANNEL_ONLINE='online'; public const CHANNEL_OFFLINE='offline';
    protected $fillable=['transaction_number','channel','warehouse_id','customer_id','customer_name','customer_phone','shipping_address','subtotal','discount_total','shipping_cost','grand_total','payment_method','payment_status','order_status','notes','transaction_date','verified_by','verified_at','created_by','updated_by'];
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function customer(): BelongsTo { return $this->belongsTo(User::class,'customer_id')->withTrashed(); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class,'verified_by')->withTrashed(); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class,'created_by')->withTrashed(); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class,'updated_by')->withTrashed(); }
    public function items(): HasMany { return $this->hasMany(SalesOrderItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function returns(): HasMany { return $this->hasMany(SalesReturn::class); }
    public function shipment(): HasOne { return $this->hasOne(Shipment::class); }
    public function channelLabel(): string { return $this->channel==='online'?'Online':'Offline'; }
    public function paymentStatusLabel(): string { return ['unpaid'=>'Belum Bayar','waiting_verification'=>'Menunggu Verifikasi','paid'=>'Lunas','rejected'=>'Ditolak','refunded'=>'Dikembalikan'][$this->payment_status] ?? $this->payment_status; }
    public function orderStatusLabel(): string { return ['waiting_payment'=>'Menunggu Pembayaran','processing'=>'Diproses','packed'=>'Dikemas','shipped'=>'Dalam Perjalanan','completed'=>'Selesai','cancelled'=>'Dibatalkan'][$this->order_status] ?? $this->order_status; }
    public function scopeOnline(Builder $q): Builder { return $q->where('channel','online'); }
    public function scopeOffline(Builder $q): Builder { return $q->where('channel','offline'); }
    protected function casts(): array { return ['subtotal'=>'decimal:2','discount_total'=>'decimal:2','shipping_cost'=>'decimal:2','grand_total'=>'decimal:2','transaction_date'=>'datetime','verified_at'=>'datetime']; }
}
