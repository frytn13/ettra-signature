@extends('layouts.admin')

@section('title', 'Detail Room')
@section('eyebrow', 'Persediaan')
@section('page-title', 'Detail Room')

@section('content')
    <section class="dashboard-heading category-form-heading">
        <div><p class="dashboard-heading__eyebrow">Detail Lokasi</p><h2>{{ $warehouse->name }}</h2><p>{{ $warehouse->description ?: 'Informasi lengkap room dan fondasi untuk pengelolaan stok per lokasi.' }}</p></div>
        <div class="dashboard-heading__actions"><a href="{{ route('admin.warehouses.index') }}" class="button button--secondary">Kembali</a><a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="button button--primary">Edit Room</a></div>
    </section>

    @if (session('success'))<div class="user-alert user-alert--success" role="status"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span><span>{{ session('success') }}</span></div>@endif
    @if (session('error'))<div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>{{ session('error') }}</span></div>@endif

    <section class="category-summary-grid" aria-label="Ringkasan stok room">
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 9 9-6 9 6v11H3V9Z"/></svg></span><span class="category-summary-card__copy"><small>Kode Room</small><strong style="font-size:1.25rem">{{ $warehouse->code }}</strong><span>Identitas lokasi</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 8 4-8 4-8-4 8-4Z"/></svg></span><span class="category-summary-card__copy"><small>SKU Tersimpan</small><strong>{{ number_format($stockSummary['sku_count']) }}</strong><span>Titik stok pada room ini</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16v12H4zM8 4h8v3"/></svg></span><span class="category-summary-card__copy"><small>Stok Fisik</small><strong>{{ number_format($stockSummary['on_hand']) }}</strong><span>Quantity on hand</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg></span><span class="category-summary-card__copy"><small>Stok Tersedia</small><strong>{{ number_format($stockSummary['available']) }}</strong><span>Setelah reservasi/rusak</span></span></article>
    </section>

    <div class="category-form-layout">
        <div class="category-form-main">
            <section class="category-form-card glass-panel">
                <div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 9 9-6 9 6v11H3V9Z"/><path d="M8 20v-7h8v7"/></svg></span><div><p class="dashboard-heading__eyebrow">Informasi Room</p><h3>{{ $warehouse->name }}</h3><p>Data lokasi yang digunakan sebagai referensi pengelolaan persediaan.</p></div></div>
                <dl class="category-meta-card" style="padding:0;box-shadow:none;background:transparent;border:0">
                    <div><dt>Kode</dt><dd>{{ $warehouse->code }}</dd></div>
                    <div><dt>Status</dt><dd><span class="status-badge {{ $warehouse->is_active ? 'status-badge--success' : 'status-badge--danger' }}">{{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}</span></dd></div>
                    <div><dt>Alamat</dt><dd>{{ $warehouse->address ?: 'Belum diisi' }}</dd></div>
                    <div><dt>Deskripsi</dt><dd>{{ $warehouse->description ?: 'Belum diisi' }}</dd></div>
                </dl>
            </section>

            <section class="category-form-card glass-panel">
                <div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12h4l2-5 4 10 2-5h6"/></svg></span><div><p class="dashboard-heading__eyebrow">Inventory Aktif</p><h3>Ringkasan Stok Room</h3><p>Data berikut berasal dari pencatatan Stok Room untuk lokasi ini. Perubahan kuantitas akan menggunakan Mutasi Stok agar histori tetap utuh.</p></div></div>
                <div class="category-examples"><span>On Hand {{ number_format($stockSummary['on_hand']) }}</span><span>Reserved {{ number_format($stockSummary['reserved']) }}</span><span>Damaged {{ number_format($stockSummary['damaged']) }}</span><a href="{{ route('admin.warehouse-stocks.index',['warehouse'=>$warehouse->id]) }}">Lihat stok room</a></div>
            </section>
        </div>

        <aside class="category-form-side">
            <section class="category-meta-card glass-panel"><p class="dashboard-heading__eyebrow">Metadata</p><h3>Riwayat data</h3><dl><div><dt>Dibuat</dt><dd>{{ $warehouse->created_at?->format('d M Y, H:i') ?? '-' }}</dd></div><div><dt>Oleh</dt><dd>{{ $warehouse->createdBy?->name ?? 'Sistem' }}</dd></div><div><dt>Diperbarui</dt><dd>{{ $warehouse->updated_at?->format('d M Y, H:i') ?? '-' }}</dd></div><div><dt>Oleh</dt><dd>{{ $warehouse->updatedBy?->name ?? 'Sistem' }}</dd></div></dl></section>
            <section class="category-form-note glass-panel"><span class="category-form-note__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></svg></span><div><strong>Histori room dilindungi</strong><p>Room yang sudah memiliki stok atau transaksi nantinya tidak dapat dihapus. Nonaktifkan room bila tidak digunakan lagi.</p></div></section>
            <div class="category-form-actions glass-panel">
                <form action="{{ route('admin.warehouses.toggle-status', $warehouse) }}" method="POST" data-confirm-form data-confirm-message="{{ $warehouse->is_active ? 'Nonaktifkan room '.$warehouse->name.'?' : 'Aktifkan kembali room '.$warehouse->name.'?' }}">@csrf @method('PATCH')<button type="submit" class="button button--ghost">{{ $warehouse->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
            </div>
        </aside>
    </div>
@endsection
