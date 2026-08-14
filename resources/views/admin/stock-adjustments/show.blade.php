@extends('layouts.admin')

@section('title', 'Detail Penyesuaian Stok')
@section('eyebrow', 'Persediaan')
@section('page-title', 'Detail Penyesuaian Stok')

@section('content')
    <section class="dashboard-heading category-form-heading">
        <div><p class="dashboard-heading__eyebrow">{{ $adjustment->adjustment_number }}</p><h2>{{ $adjustment->reasonLabel() }}</h2><p>{{ $adjustment->productVariant?->sku }} · {{ $adjustment->productVariant?->product?->name }} · {{ $adjustment->warehouse?->name }}</p></div>
        <div class="dashboard-heading__actions"><a href="{{ route('admin.stock-adjustments.index') }}" class="button button--secondary">Kembali</a><a href="{{ route('admin.stock-adjustments.create',['stock'=>$adjustment->warehouse_stock_id]) }}" class="button button--primary">Penyesuaian Baru</a></div>
    </section>

    @if(session('success'))<div class="user-alert user-alert--success" role="status"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span><span>{{ session('success') }}</span></div>@endif

    <section class="movement-detail-summary">
        <article class="stock-quantity-card glass-panel"><small>Stok Sistem</small><strong>{{ number_format($adjustment->system_quantity) }}</strong><span>Sebelum pemeriksaan</span></article>
        <article class="stock-quantity-card glass-panel"><small>Stok Fisik</small><strong>{{ number_format($adjustment->physical_quantity) }}</strong><span>Hasil stock opname</span></article>
        <article class="stock-quantity-card glass-panel"><small>Selisih</small><strong class="movement-quantity movement-quantity--{{ $adjustment->direction() }}">{{ $adjustment->differenceSign() }}{{ number_format(abs($adjustment->difference_quantity)) }}</strong><span>{{ $adjustment->directionLabel() }}</span></article>
        <article class="stock-quantity-card stock-quantity-card--available glass-panel"><small>Status</small><strong>{{ $adjustment->statusLabel() }}</strong><span>Sudah masuk ledger</span></article>
    </section>

    <div class="category-form-layout">
        <div class="category-form-main">
            <section class="category-form-card glass-panel"><div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/></svg></span><div><p class="dashboard-heading__eyebrow">SKU & Lokasi</p><h3>{{ $adjustment->productVariant?->product?->name }}</h3><p>Identitas barang yang diperiksa.</p></div></div><dl class="category-meta-card stock-detail-list"><div><dt>SKU</dt><dd>{{ $adjustment->productVariant?->sku ?? '-' }}</dd></div><div><dt>Kode Produk</dt><dd>{{ $adjustment->productVariant?->product?->code ?? '-' }}</dd></div><div><dt>Kategori</dt><dd>{{ $adjustment->productVariant?->product?->category?->name ?? '-' }}</dd></div><div><dt>Warna</dt><dd>{{ $adjustment->productVariant?->color?->name ?? '-' }}</dd></div><div><dt>Ukuran</dt><dd>{{ $adjustment->productVariant?->size?->name ?? '-' }}</dd></div><div><dt>Room</dt><dd>{{ $adjustment->warehouse?->code }} · {{ $adjustment->warehouse?->name }}</dd></div></dl></section>

            <section class="category-form-card glass-panel"><div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 7h11l-3-3M17 17H6l3 3"/><path d="M18 7 15 4M6 17l3 3"/></svg></span><div><p class="dashboard-heading__eyebrow">Ledger Mutasi</p><h3>{{ $adjustment->stockMovement?->transaction_number ?? '-' }}</h3><p>Transaksi koreksi yang dihasilkan otomatis dari penyesuaian ini.</p></div></div>@if($adjustment->stockMovement)<div class="movement-snapshot-grid"><div><small>Jenis</small><strong>{{ $adjustment->stockMovement->typeLabel() }}</strong></div><div><small>Jumlah</small><strong>{{ $adjustment->stockMovement->directionSign() }}{{ number_format($adjustment->stockMovement->quantity) }}</strong></div><div><small>Fisik</small><strong>{{ number_format($adjustment->stockMovement->quantity_before) }} → {{ number_format($adjustment->stockMovement->quantity_after) }}</strong></div><div><small>Tersedia</small><strong>{{ number_format($adjustment->stockMovement->quantity_available_before) }} → {{ number_format($adjustment->stockMovement->quantity_available_after) }}</strong></div></div><div class="category-examples"><a href="{{ route('admin.stock-movements.show',$adjustment->stockMovement) }}">Buka detail ledger</a></div>@endif</section>

            <section class="category-form-card glass-panel"><div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 4h12v16H6z"/><path d="M9 8h6M9 12h6"/></svg></span><div><p class="dashboard-heading__eyebrow">Catatan Pemeriksaan</p><h3>{{ $adjustment->reasonLabel() }}</h3></div></div><p class="movement-notes">{{ $adjustment->notes }}</p></section>
        </div>

        <aside class="category-form-side">
            <section class="category-meta-card glass-panel"><p class="dashboard-heading__eyebrow">Metadata</p><h3>{{ $adjustment->adjustment_number }}</h3><dl><div><dt>Waktu Pemeriksaan</dt><dd>{{ $adjustment->adjustment_date?->format('d/m/Y H:i') }}</dd></div><div><dt>Diproses Oleh</dt><dd>{{ $adjustment->processedBy?->name ?? 'Sistem' }}</dd></div><div><dt>Role</dt><dd>{{ ucfirst($adjustment->processedBy?->role ?? 'system') }}</dd></div><div><dt>Status</dt><dd>{{ $adjustment->statusLabel() }}</dd></div><div><dt>Dibuat</dt><dd>{{ $adjustment->created_at?->format('d/m/Y H:i:s') }}</dd></div></dl></section>
            <section class="category-form-note glass-panel"><span class="category-form-note__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg></span><div><strong>Dokumen audit</strong><p>Penyesuaian ini tidak dapat diedit atau dihapus. Jika ada kesalahan, catat penyesuaian baru untuk mengoreksi saldo.</p></div></section>
            <div class="category-form-actions glass-panel"><a href="{{ route('admin.warehouse-stocks.show',$adjustment->warehouse_stock_id) }}" class="button button--ghost">Lihat Stok Room</a></div>
        </aside>
    </div>
@endsection
