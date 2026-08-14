@extends('layouts.admin')

@section('title', 'Master Ukuran')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Master Ukuran')

@section('content')
    <section class="dashboard-heading category-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Variasi Produk</p>
            <h2>Kelola master ukuran</h2>
            <p>Atur ukuran produk beserta urutan tampilnya agar pilihan ukuran konsisten pada Admin dan katalog pelanggan.</p>
        </div>

        <div class="dashboard-heading__actions master-heading-actions">
            <a href="{{ route('admin.colors.index') }}" class="button button--secondary">Master Warna</a>
            <a href="{{ route('admin.sizes.create') }}" class="button button--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Ukuran
            </a>
        </div>
    </section>

    @if (session('success'))
        <div class="user-alert user-alert--success" role="status"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span><span>{{ session('success') }}</span></div>
    @endif
    @if (session('error'))
        <div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>{{ session('error') }}</span></div>
    @endif

    <section class="category-summary-grid" aria-label="Ringkasan master ukuran">
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 8h16M4 16h16M7 5v6M12 5v6M17 5v6"/></svg></span><span class="category-summary-card__copy"><small>Total Ukuran</small><strong>{{ number_format($statistics['total']) }}</strong><span>Master ukuran tersedia</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg></span><span class="category-summary-card__copy"><small>Ukuran Aktif</small><strong>{{ number_format($statistics['active']) }}</strong><span>Dapat dipakai pada variasi</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--muted"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg></span><span class="category-summary-card__copy"><small>Nonaktif</small><strong>{{ number_format($statistics['inactive']) }}</strong><span>Tidak dipakai untuk variasi baru</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 11v6M15 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg></span><span class="category-summary-card__copy"><small>Diarsipkan</small><strong>{{ number_format($statistics['archived']) }}</strong><span>Soft delete tetap tersimpan</span></span></article>
    </section>

    <section class="category-management-card glass-panel">
        <div class="category-management-card__header">
            <div><p class="dashboard-heading__eyebrow">Daftar Ukuran</p><h3>Master ukuran Ettra Signature</h3><p>Urutan tampilan menentukan susunan ukuran ketika pelanggan memilih variasi, misalnya S, M, L, XL, lalu XXL.</p></div>
            <div class="category-examples" aria-label="Contoh ukuran"><span>S · Small</span><span>M · Medium</span><span>ALL · All Size</span></div>
        </div>

        <form action="{{ route('admin.sizes.index') }}" method="GET" class="category-filter-form">
            <label class="category-filter-field category-filter-field--search"><span class="sr-only">Cari ukuran</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Cari kode atau nama ukuran..." autocomplete="off"></label>
            <label class="category-filter-field"><span class="sr-only">Filter status ukuran</span><select name="status"><option value="">Semua status</option><option value="active" @selected($filters['status'] === 'active')>Aktif</option><option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option></select></label>
            <button type="submit" class="button button--secondary button--small">Terapkan</button>
            @if ($filters['search'] !== '' || $filters['status'] !== '')<a href="{{ route('admin.sizes.index') }}" class="button button--ghost button--small">Reset</a>@endif
        </form>

        @if ($sizes->isEmpty())
            <div class="category-empty-state"><span class="category-empty-state__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 8h14M5 16h14M12 4v16"/></svg></span><h4>Belum ada ukuran yang ditampilkan</h4><p>Tambahkan master ukuran pertama atau ubah filter untuk melihat data yang tersedia.</p><a href="{{ route('admin.sizes.create') }}" class="button button--primary button--small">Tambah Ukuran</a></div>
        @else
            <div class="responsive-table-wrap category-desktop-table">
                <table class="admin-table category-table">
                    <thead><tr><th>Ukuran</th><th>Kode</th><th>Urutan</th><th>Status</th><th>Diperbarui</th><th>Pengguna</th><th class="category-table__actions-heading">Aksi</th></tr></thead>
                    <tbody>
                        @foreach ($sizes as $size)
                            <tr>
                                <td><div class="category-identity"><span class="master-size-mark">{{ $size->code }}</span><span class="category-identity__copy"><strong>{{ $size->name }}</strong><small>Pilihan ukuran produk</small></span></div></td>
                                <td><span class="category-code-badge">{{ $size->code }}</span></td>
                                <td><span class="master-sort-badge">{{ $size->sort_order }}</span></td>
                                <td><span class="status-badge {{ $size->is_active ? 'status-badge--success' : 'status-badge--danger' }}">{{ $size->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td><span class="category-table-primary">{{ $size->updated_at?->format('d M Y, H:i') ?? '-' }}</span><small class="category-table-secondary">Dibuat {{ $size->created_at?->format('d M Y') ?? '-' }}</small></td>
                                <td><span class="category-table-primary">{{ $size->updatedBy?->name ?? $size->createdBy?->name ?? 'Sistem' }}</span><small class="category-table-secondary">Perubahan terakhir</small></td>
                                <td>
                                    <div class="category-actions">
                                        <a href="{{ route('admin.sizes.edit', $size) }}" class="category-action-button category-action-button--edit" title="Edit ukuran"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m4 20 4.2-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z"/><path d="m13.8 7.2 3 3"/></svg></a>
                                        <form action="{{ route('admin.sizes.toggle-status', $size) }}" method="POST" data-confirm-form data-confirm-message="{{ $size->is_active ? 'Nonaktifkan ukuran '.$size->name.'?' : 'Aktifkan kembali ukuran '.$size->name.'?' }}">@csrf @method('PATCH')<button type="submit" class="category-action-button {{ $size->is_active ? 'category-action-button--disable' : 'category-action-button--enable' }}" title="{{ $size->is_active ? 'Nonaktifkan' : 'Aktifkan' }} ukuran">@if($size->is_active)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg>@else<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg>@endif</button></form>
                                        <form action="{{ route('admin.sizes.destroy', $size) }}" method="POST" data-confirm-form data-confirm-message="Hapus ukuran {{ $size->name }}? Data akan diarsipkan menggunakan soft delete.">@csrf @method('DELETE')<button type="submit" class="category-action-button category-action-button--delete" title="Hapus ukuran"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 11v6M15 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg></button></form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="category-mobile-list">
                @foreach ($sizes as $size)
                    <article class="category-mobile-card">
                        <div class="category-mobile-card__top"><div class="category-identity"><span class="master-size-mark">{{ $size->code }}</span><span class="category-identity__copy"><strong>{{ $size->name }}</strong><small>Kode {{ $size->code }}</small></span></div><span class="status-badge {{ $size->is_active ? 'status-badge--success' : 'status-badge--danger' }}">{{ $size->is_active ? 'Aktif' : 'Nonaktif' }}</span></div>
                        <dl><div><dt>Urutan</dt><dd>{{ $size->sort_order }}</dd></div><div><dt>Diperbarui</dt><dd>{{ $size->updated_at?->format('d M Y, H:i') ?? '-' }}</dd></div><div><dt>Pengguna</dt><dd>{{ $size->updatedBy?->name ?? $size->createdBy?->name ?? 'Sistem' }}</dd></div></dl>
                        <div class="category-mobile-card__actions"><a href="{{ route('admin.sizes.edit', $size) }}" class="button button--secondary button--small">Edit</a><form action="{{ route('admin.sizes.toggle-status', $size) }}" method="POST" data-confirm-form data-confirm-message="{{ $size->is_active ? 'Nonaktifkan ukuran '.$size->name.'?' : 'Aktifkan kembali ukuran '.$size->name.'?' }}">@csrf @method('PATCH')<button type="submit" class="button button--ghost button--small">{{ $size->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form><form action="{{ route('admin.sizes.destroy', $size) }}" method="POST" data-confirm-form data-confirm-message="Hapus ukuran {{ $size->name }}? Data akan diarsipkan menggunakan soft delete.">@csrf @method('DELETE')<button type="submit" class="button button--danger-soft button--small">Hapus</button></form></div>
                    </article>
                @endforeach
            </div>

            @if ($sizes->hasPages())<div class="category-pagination">{{ $sizes->links() }}</div>@endif
        @endif
    </section>

    <section class="category-policy glass-panel"><span class="category-policy__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="M9 12h6M12 9v6"/></svg></span><div><strong>Aturan master ukuran</strong><p>Kode dan nama harus unik. Urutan tampilan menentukan susunan pilihan ukuran. Setelah ukuran digunakan variasi produk, sistem menolak penghapusan dan menyediakan status nonaktif.</p></div></section>
@endsection
