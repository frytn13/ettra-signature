@extends('layouts.admin')

@section('title', 'Detail Mutasi Stok')
@section('eyebrow', 'Persediaan')
@section('page-title', 'Detail Mutasi Stok')

@section('content')
    <section class="dashboard-heading category-form-heading">
        <div><p class="dashboard-heading__eyebrow">{{ $movement->transaction_number }}</p><h2>{{ $movement->typeLabel() }}</h2><p>{{ $movement->productVariant?->sku }} · {{ $movement->productVariant?->product?->name }} · {{ $movement->warehouse?->name }}</p></div>
        <div class="dashboard-heading__actions"><a href="{{ route('admin.stock-movements.index') }}" class="button button--secondary">Kembali</a><a href="{{ route('admin.stock-movements.create',['stock'=>$movement->warehouse_stock_id]) }}" class="button button--primary">Mutasi Baru</a></div>
    </section>

    @if(session('success'))<div class="user-alert user-alert--success" role="status"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span><span>{{ session('success') }}</span></div>@endif

    <section class="movement-detail-summary">
        <article class="stock-quantity-card glass-panel"><small>Jenis</small><strong class="movement-type movement-type--{{ $movement->direction }}">{{ $movement->typeLabel() }}</strong><span>{{ $movement->directionLabel() }}</span></article>
        <article class="stock-quantity-card glass-panel"><small>Jumlah</small><strong class="movement-quantity movement-quantity--{{ $movement->direction }}">{{ $movement->directionSign() }}{{ number_format($movement->quantity) }}</strong><span>Unit</span></article>
        <article class="stock-quantity-card glass-panel"><small>Stok Fisik</small><strong>{{ number_format($movement->quantity_before) }} → {{ number_format($movement->quantity_after) }}</strong><span>Sebelum → sesudah</span></article>
        <article class="stock-quantity-card stock-quantity-card--available glass-panel"><small>Stok Tersedia</small><strong>{{ number_format($movement->quantity_available_before) }} → {{ number_format($movement->quantity_available_after) }}</strong><span>Sebelum → sesudah</span></article>
    </section>

    <div class="category-form-layout">
        <div class="category-form-main">
            <section class="category-form-card glass-panel"><div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/></svg></span><div><p class="dashboard-heading__eyebrow">SKU & Lokasi</p><h3>{{ $movement->productVariant?->product?->name }}</h3><p>Identitas barang dan room pada saat mutasi dicatat.</p></div></div><dl class="category-meta-card stock-detail-list"><div><dt>SKU</dt><dd>{{ $movement->productVariant?->sku ?? '-' }}</dd></div><div><dt>Kode Produk</dt><dd>{{ $movement->productVariant?->product?->code ?? '-' }}</dd></div><div><dt>Kategori</dt><dd>{{ $movement->productVariant?->product?->category?->name ?? '-' }}</dd></div><div><dt>Warna</dt><dd>{{ $movement->productVariant?->color?->name ?? '-' }}</dd></div><div><dt>Ukuran</dt><dd>{{ $movement->productVariant?->size?->name ?? '-' }}</dd></div><div><dt>Room</dt><dd>{{ $movement->warehouse?->code }} · {{ $movement->warehouse?->name }}</dd></div></dl></section>

            <section class="category-form-card glass-panel"><div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 7h11l-3-3M17 17H6l3 3"/><path d="M18 7 15 4M6 17l3 3"/></svg></span><div><p class="dashboard-heading__eyebrow">Snapshot Stok</p><h3>Kondisi sebelum dan sesudah</h3><p>Snapshot ini disimpan di ledger sehingga nilai historis tidak bergantung pada saldo stok saat ini.</p></div></div><div class="movement-snapshot-grid"><div><small>Fisik</small><strong>{{ number_format($movement->quantity_before) }} → {{ number_format($movement->quantity_after) }}</strong></div><div><small>Reservasi</small><strong>{{ number_format($movement->quantity_reserved_before) }} → {{ number_format($movement->quantity_reserved_after) }}</strong></div><div><small>Rusak</small><strong>{{ number_format($movement->quantity_damaged_before) }} → {{ number_format($movement->quantity_damaged_after) }}</strong></div><div><small>Tersedia</small><strong>{{ number_format($movement->quantity_available_before) }} → {{ number_format($movement->quantity_available_after) }}</strong></div></div></section>

            <section class="category-form-card glass-panel"><div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 4h12v16H6z"/><path d="M9 8h6M9 12h6"/></svg></span><div><p class="dashboard-heading__eyebrow">Catatan</p><h3>Alasan transaksi</h3></div></div><p class="movement-notes">{{ $movement->notes ?: 'Tidak ada catatan.' }}</p></section>
        </div>

        <aside class="category-form-side">
            <section class="category-meta-card glass-panel"><p class="dashboard-heading__eyebrow">Metadata</p><h3>{{ $movement->transaction_number }}</h3><dl><div><dt>Waktu Mutasi</dt><dd>{{ $movement->movement_date?->format('d/m/Y H:i') }}</dd></div><div><dt>Diproses Oleh</dt><dd>{{ $movement->performedBy?->name ?? 'Sistem' }}</dd></div><div><dt>Role</dt><dd>{{ ucfirst($movement->performedBy?->role ?? 'system') }}</dd></div><div><dt>Sumber</dt><dd>{{ $movement->reference_type === 'manual_stock_movement' ? 'Mutasi Manual' : ($movement->reference_type === 'stock_adjustment' ? 'Penyesuaian Stok' : ($movement->reference_type ?? 'Sistem')) }}</dd></div><div><dt>Dibuat</dt><dd>{{ $movement->created_at?->format('d/m/Y H:i:s') }}</dd></div></dl></section>
            <section class="category-form-note glass-panel"><span class="category-form-note__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg></span><div><strong>Transaksi tidak dapat diubah</strong><p>Jika data ini salah, jangan mengedit ledger. Buat transaksi koreksi baru agar riwayat stok tetap dapat ditelusuri.</p></div></section>
            <div class="category-form-actions glass-panel"><a href="{{ route('admin.warehouse-stocks.show',$movement->warehouse_stock_id) }}" class="button button--ghost">Lihat Stok Room</a></div>
        </aside>
    </div>
@endsection
