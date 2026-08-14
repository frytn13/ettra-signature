@extends('layouts.admin')

@section('title', 'Master Warna')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Master Warna')

@section('content')
    <section class="dashboard-heading category-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Variasi Produk</p>
            <h2>Kelola master warna</h2>
            <p>Siapkan pilihan warna yang konsisten untuk variasi produk. Kode HEX bersifat opsional dan digunakan sebagai preview visual pada Admin maupun katalog pelanggan.</p>
        </div>

        <div class="dashboard-heading__actions master-heading-actions">
            <a href="{{ route('admin.sizes.index') }}" class="button button--secondary">Master Ukuran</a>
            <a href="{{ route('admin.colors.create') }}" class="button button--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Warna
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

    <section class="category-summary-grid" aria-label="Ringkasan master warna">
        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><path d="M8 12a4 4 0 0 1 8 0c0 2.2-1.8 4-4 4"/></svg></span>
            <span class="category-summary-card__copy"><small>Total Warna</small><strong>{{ number_format($statistics['total']) }}</strong><span>Master warna tersedia</span></span>
        </article>
        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg></span>
            <span class="category-summary-card__copy"><small>Warna Aktif</small><strong>{{ number_format($statistics['active']) }}</strong><span>Dapat dipakai pada variasi</span></span>
        </article>
        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--muted"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg></span>
            <span class="category-summary-card__copy"><small>Nonaktif</small><strong>{{ number_format($statistics['inactive']) }}</strong><span>Tidak dipakai untuk variasi baru</span></span>
        </article>
        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 11v6M15 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg></span>
            <span class="category-summary-card__copy"><small>Diarsipkan</small><strong>{{ number_format($statistics['archived']) }}</strong><span>Soft delete tetap tersimpan</span></span>
        </article>
    </section>

    <section class="category-management-card glass-panel">
        <div class="category-management-card__header">
            <div>
                <p class="dashboard-heading__eyebrow">Daftar Warna</p>
                <h3>Master warna Ettra Signature</h3>
                <p>Gunakan nama dan kode yang konsisten. Kode HEX dapat dikosongkan untuk warna yang tidak dapat direpresentasikan secara akurat dengan satu warna solid.</p>
            </div>
            <div class="category-examples" aria-label="Contoh warna">
                <span>PCH · Peach</span><span>GRN · Green</span><span>BLK · Black</span>
            </div>
        </div>

        <form action="{{ route('admin.colors.index') }}" method="GET" class="category-filter-form">
            <label class="category-filter-field category-filter-field--search">
                <span class="sr-only">Cari warna</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Cari kode, nama, atau HEX warna..." autocomplete="off">
            </label>
            <label class="category-filter-field">
                <span class="sr-only">Filter status warna</span>
                <select name="status">
                    <option value="">Semua status</option>
                    <option value="active" @selected($filters['status'] === 'active')>Aktif</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option>
                </select>
            </label>
            <button type="submit" class="button button--secondary button--small">Terapkan</button>
            @if ($filters['search'] !== '' || $filters['status'] !== '')
                <a href="{{ route('admin.colors.index') }}" class="button button--ghost button--small">Reset</a>
            @endif
        </form>

        @if ($colors->isEmpty())
            <div class="category-empty-state">
                <span class="category-empty-state__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="10" cy="10" r="6"/><path d="M17.5 15v6M14.5 18h6"/></svg></span>
                <h4>Belum ada warna yang ditampilkan</h4>
                <p>Tambahkan master warna pertama atau ubah filter untuk melihat data yang tersedia.</p>
                <a href="{{ route('admin.colors.create') }}" class="button button--primary button--small">Tambah Warna</a>
            </div>
        @else
            <div class="responsive-table-wrap category-desktop-table">
                <table class="admin-table category-table">
                    <thead><tr><th>Warna</th><th>Kode</th><th>HEX</th><th>Status</th><th>Diperbarui</th><th>Pengguna</th><th class="category-table__actions-heading">Aksi</th></tr></thead>
                    <tbody>
                        @foreach ($colors as $color)
                            <tr>
                                <td>
                                    <div class="category-identity">
                                        <span class="master-color-swatch {{ $color->hex_code ? '' : 'master-color-swatch--empty' }}" @if($color->hex_code) style="background-color: {{ $color->hex_code }}" @endif aria-hidden="true"></span>
                                        <span class="category-identity__copy"><strong>{{ $color->name }}</strong><small>Warna variasi produk</small></span>
                                    </div>
                                </td>
                                <td><span class="category-code-badge">{{ $color->code }}</span></td>
                                <td><span class="master-hex-code">{{ $color->hex_code ?: '—' }}</span></td>
                                <td><span class="status-badge {{ $color->is_active ? 'status-badge--success' : 'status-badge--danger' }}">{{ $color->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td><span class="category-table-primary">{{ $color->updated_at?->format('d M Y, H:i') ?? '-' }}</span><small class="category-table-secondary">Dibuat {{ $color->created_at?->format('d M Y') ?? '-' }}</small></td>
                                <td><span class="category-table-primary">{{ $color->updatedBy?->name ?? $color->createdBy?->name ?? 'Sistem' }}</span><small class="category-table-secondary">Perubahan terakhir</small></td>
                                <td>
                                    <div class="category-actions">
                                        <a href="{{ route('admin.colors.edit', $color) }}" class="category-action-button category-action-button--edit" aria-label="Edit {{ $color->name }}" title="Edit warna"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m4 20 4.2-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z"/><path d="m13.8 7.2 3 3"/></svg></a>
                                        <form action="{{ route('admin.colors.toggle-status', $color) }}" method="POST" data-confirm-form data-confirm-message="{{ $color->is_active ? 'Nonaktifkan warna '.$color->name.'?' : 'Aktifkan kembali warna '.$color->name.'?' }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="category-action-button {{ $color->is_active ? 'category-action-button--disable' : 'category-action-button--enable' }}" title="{{ $color->is_active ? 'Nonaktifkan' : 'Aktifkan' }} warna">
                                                @if($color->is_active)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg>@else<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg>@endif
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.colors.destroy', $color) }}" method="POST" data-confirm-form data-confirm-message="Hapus warna {{ $color->name }}? Data akan diarsipkan menggunakan soft delete.">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="category-action-button category-action-button--delete" title="Hapus warna"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 11v6M15 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="category-mobile-list">
                @foreach ($colors as $color)
                    <article class="category-mobile-card">
                        <div class="category-mobile-card__top">
                            <div class="category-identity">
                                <span class="master-color-swatch {{ $color->hex_code ? '' : 'master-color-swatch--empty' }}" @if($color->hex_code) style="background-color: {{ $color->hex_code }}" @endif aria-hidden="true"></span>
                                <span class="category-identity__copy"><strong>{{ $color->name }}</strong><small>{{ $color->code }}</small></span>
                            </div>
                            <span class="status-badge {{ $color->is_active ? 'status-badge--success' : 'status-badge--danger' }}">{{ $color->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <dl>
                            <div><dt>HEX</dt><dd>{{ $color->hex_code ?: 'Belum ditentukan' }}</dd></div>
                            <div><dt>Diperbarui</dt><dd>{{ $color->updated_at?->format('d M Y, H:i') ?? '-' }}</dd></div>
                            <div><dt>Pengguna</dt><dd>{{ $color->updatedBy?->name ?? $color->createdBy?->name ?? 'Sistem' }}</dd></div>
                        </dl>
                        <div class="category-mobile-card__actions">
                            <a href="{{ route('admin.colors.edit', $color) }}" class="button button--secondary button--small">Edit</a>
                            <form action="{{ route('admin.colors.toggle-status', $color) }}" method="POST" data-confirm-form data-confirm-message="{{ $color->is_active ? 'Nonaktifkan warna '.$color->name.'?' : 'Aktifkan kembali warna '.$color->name.'?' }}">@csrf @method('PATCH')<button type="submit" class="button button--ghost button--small">{{ $color->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                            <form action="{{ route('admin.colors.destroy', $color) }}" method="POST" data-confirm-form data-confirm-message="Hapus warna {{ $color->name }}? Data akan diarsipkan menggunakan soft delete.">@csrf @method('DELETE')<button type="submit" class="button button--danger-soft button--small">Hapus</button></form>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($colors->hasPages())<div class="category-pagination">{{ $colors->links() }}</div>@endif
        @endif
    </section>

    <section class="category-policy glass-panel">
        <span class="category-policy__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="M9 12h6M12 9v6"/></svg></span>
        <div><strong>Aturan master warna</strong><p>Kode dan nama warna harus unik. Setelah warna digunakan pada variasi produk, sistem menolak penghapusan dan menyarankan penggunaan status nonaktif agar histori produk tetap konsisten.</p></div>
    </section>
@endsection
