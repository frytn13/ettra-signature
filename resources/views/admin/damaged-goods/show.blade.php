@extends('layouts.admin')
@section('title','Detail Barang Rusak')
@section('eyebrow','Produk & Persediaan')
@section('page-title','Detail Barang Rusak')
@section('content')
<section class="dashboard-heading compact-page-heading"><div><p class="dashboard-heading__eyebrow">{{ $damagedGood->transaction_number }}</p><h2>{{ $damagedGood->actionLabel() }}</h2><p>{{ $damagedGood->productVariant?->product?->name }} · {{ $damagedGood->productVariant?->sku }}</p></div><a href="{{ route('admin.damaged-goods.index') }}" class="button button--secondary">Kembali</a></section>
<section class="glass-panel detail-grid-card"><dl class="detail-definition-grid"><div><dt>Room</dt><dd>{{ $damagedGood->warehouse?->name }}</dd></div><div><dt>Jumlah</dt><dd>{{ $damagedGood->quantity }} unit</dd></div><div><dt>Stok rusak</dt><dd>{{ $damagedGood->damaged_before }} → {{ $damagedGood->damaged_after }}</dd></div><div><dt>Stok tersedia</dt><dd>{{ $damagedGood->available_before }} → {{ $damagedGood->available_after }}</dd></div><div><dt>Alasan</dt><dd>{{ str($damagedGood->reason)->replace('_',' ')->title() }}</dd></div><div><dt>Petugas</dt><dd>{{ $damagedGood->processedBy?->name ?? '-' }}</dd></div><div class="detail-definition-grid__full"><dt>Catatan</dt><dd>{{ $damagedGood->notes ?: '-' }}</dd></div></dl></section>
@endsection
