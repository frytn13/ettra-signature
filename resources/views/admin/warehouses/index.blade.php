@extends('layouts.admin')

@section('title', 'Master Room')
@section('eyebrow', 'Persediaan')
@section('page-title', 'Master Room')

@section('content')
    <section class="dashboard-heading category-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Master Persediaan</p>
            <h2>Kelola lokasi room</h2>
            <p>Daftarkan seluruh lokasi penyimpanan barang. Setiap room akan menjadi dasar pencatatan stok per variasi produk pada tahap berikutnya.</p>
        </div>

        <div class="dashboard-heading__actions">
            <a href="{{ route('admin.warehouses.create') }}" class="button button--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Room
            </a>
        </div>
    </section>

    @if (session('success'))
        <div class="user-alert user-alert--success" role="status">
            <span class="user-alert__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="user-alert user-alert--danger" role="alert">
            <span class="user-alert__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <section class="category-summary-grid" aria-label="Ringkasan room">
        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 9 9-6 9 6v11H3V9Z"/><path d="M8 20v-7h8v7"/></svg></span>
            <span class="category-summary-card__copy"><small>Total Room</small><strong>{{ number_format($statistics['total']) }}</strong><span>Lokasi tersimpan</span></span>
        </article>

        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg></span>
            <span class="category-summary-card__copy"><small>Room Aktif</small><strong>{{ number_format($statistics['active']) }}</strong><span>Dapat digunakan operasional</span></span>
        </article>

        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--muted"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg></span>
            <span class="category-summary-card__copy"><small>Nonaktif</small><strong>{{ number_format($statistics['inactive']) }}</strong><span>Tidak dipakai untuk data baru</span></span>
        </article>

        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 11v6M15 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg></span>
            <span class="category-summary-card__copy"><small>Diarsipkan</small><strong>{{ number_format($statistics['archived']) }}</strong><span>Soft delete tetap tersimpan</span></span>
        </article>
    </section>

    <section class="category-management-card glass-panel">
        <div class="category-management-card__header">
            <div>
                <p class="dashboard-heading__eyebrow">Daftar Room</p>
                <h3>Lokasi penyimpanan Ettra Signature</h3>
                <p>Kode room dibuat otomatis dengan format GD-001, tetapi tetap dapat disesuaikan bila diperlukan.</p>
            </div>
            <div class="category-examples" aria-label="Informasi room"><span>Multiroom</span><span>Stok per lokasi</span><span>Riwayat terlindungi</span></div>
        </div>

        <form action="{{ route('admin.warehouses.index') }}" method="GET" class="category-filter-form">
            <label class="category-filter-field category-filter-field--search">
                <span class="sr-only">Cari room</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Cari kode, nama, alamat, atau deskripsi room..." autocomplete="off">
            </label>

            <label class="category-filter-field">
                <span class="sr-only">Filter status room</span>
                <select name="status">
                    <option value="">Semua status</option>
                    <option value="active" @selected($filters['status'] === 'active')>Aktif</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option>
                </select>
            </label>

            <button type="submit" class="button button--secondary button--small">Terapkan</button>
            @if ($filters['search'] !== '' || $filters['status'] !== '')
                <a href="{{ route('admin.warehouses.index') }}" class="button button--ghost button--small">Reset</a>
            @endif
        </form>

        @if ($warehouses->isEmpty())
            <div class="category-empty-state">
                <span class="category-empty-state__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m3 9 9-6 9 6v11H3V9Z"/><path d="M8 20v-7h8v7M16 5v4"/></svg></span>
                <h4>Belum ada room yang ditampilkan</h4>
                <p>Tambahkan room pertama untuk menyiapkan fondasi pengelolaan stok multiroom.</p>
                <a href="{{ route('admin.warehouses.create') }}" class="button button--primary button--small">Tambah Room</a>
            </div>
        @else
            <div class="responsive-table-wrap category-desktop-table">
                <table class="admin-table category-table">
                    <thead>
                        <tr><th>Room</th><th>Kode</th><th>Alamat</th><th>Status</th><th>Diperbarui</th><th class="category-table__actions-heading">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($warehouses as $warehouse)
                            <tr>
                                <td>
                                    <div class="category-identity">
                                        <span class="category-identity__mark">{{ mb_strtoupper(mb_substr($warehouse->name, 0, 1)) }}</span>
                                        <span class="category-identity__copy"><strong>{{ $warehouse->name }}</strong><small>{{ $warehouse->description ?: 'Belum ada deskripsi room.' }}</small></span>
                                    </div>
                                </td>
                                <td><span class="category-code-badge">{{ $warehouse->code }}</span></td>
                                <td><span class="category-table-primary">{{ $warehouse->address ?: 'Alamat belum diisi' }}</span></td>
                                <td><span class="status-badge {{ $warehouse->is_active ? 'status-badge--success' : 'status-badge--danger' }}">{{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td><span class="category-table-primary">{{ $warehouse->updated_at?->format('d M Y, H:i') ?? '-' }}</span><small class="category-table-secondary">{{ $warehouse->updatedBy?->name ?? $warehouse->createdBy?->name ?? 'Sistem' }}</small></td>
                                <td>
                                    <div class="category-actions">
                                        <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="category-action-button category-action-button--enable" aria-label="Detail {{ $warehouse->name }}" title="Detail room"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></a>
                                        <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="category-action-button category-action-button--edit" aria-label="Edit {{ $warehouse->name }}" title="Edit room"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m4 20 4.2-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z"/><path d="m13.8 7.2 3 3"/></svg></a>
                                        <form action="{{ route('admin.warehouses.toggle-status', $warehouse) }}" method="POST" data-confirm-form data-confirm-message="{{ $warehouse->is_active ? 'Nonaktifkan room '.$warehouse->name.'?' : 'Aktifkan kembali room '.$warehouse->name.'?' }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="category-action-button {{ $warehouse->is_active ? 'category-action-button--disable' : 'category-action-button--enable' }}" title="{{ $warehouse->is_active ? 'Nonaktifkan' : 'Aktifkan' }} room">
                                                @if ($warehouse->is_active)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg>@else<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg>@endif
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.warehouses.destroy', $warehouse) }}" method="POST" data-confirm-form data-confirm-message="Hapus room {{ $warehouse->name }}? Data akan diarsipkan menggunakan soft delete.">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="category-action-button category-action-button--delete" title="Hapus room"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 11v6M15 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="category-mobile-list">
                @foreach ($warehouses as $warehouse)
                    <article class="category-mobile-card">
                        <div class="category-mobile-card__top">
                            <div class="category-identity"><span class="category-identity__mark">{{ mb_strtoupper(mb_substr($warehouse->name, 0, 1)) }}</span><span class="category-identity__copy"><strong>{{ $warehouse->name }}</strong><small>{{ $warehouse->code }}</small></span></div>
                            <span class="status-badge {{ $warehouse->is_active ? 'status-badge--success' : 'status-badge--danger' }}">{{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <p>{{ $warehouse->address ?: 'Alamat room belum diisi.' }}</p>
                        <dl><div><dt>Deskripsi</dt><dd>{{ $warehouse->description ?: '-' }}</dd></div><div><dt>Diperbarui</dt><dd>{{ $warehouse->updated_at?->format('d M Y, H:i') ?? '-' }}</dd></div></dl>
                        <div class="category-mobile-card__actions">
                            <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="button button--ghost button--small">Detail</a>
                            <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="button button--secondary button--small">Edit</a>
                            <form action="{{ route('admin.warehouses.toggle-status', $warehouse) }}" method="POST" data-confirm-form data-confirm-message="{{ $warehouse->is_active ? 'Nonaktifkan room '.$warehouse->name.'?' : 'Aktifkan kembali room '.$warehouse->name.'?' }}">@csrf @method('PATCH')<button type="submit" class="button button--ghost button--small">{{ $warehouse->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($warehouses->hasPages())<div class="category-pagination">{{ $warehouses->links() }}</div>@endif
        @endif
    </section>

    <section class="category-policy glass-panel">
        <span class="category-policy__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="M9 12h6M12 9v6"/></svg></span>
        <div><strong>Room menjaga histori persediaan</strong><p>Setelah room memiliki stok atau transaksi, sistem akan menolak penghapusan. Gunakan status Nonaktif agar histori tetap utuh.</p></div>
    </section>
@endsection
