@extends('layouts.admin')

@section('title', 'Kategori Produk')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Kategori Produk')

@section('content')
    <section class="dashboard-heading category-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Master Data</p>
            <h2>Kelola kategori produk</h2>
            <p>
                Kelompokkan produk menggunakan kode yang konsisten. Kategori aktif dapat dipakai pada produk, sedangkan kategori nonaktif tetap tersimpan tanpa digunakan untuk data baru.
            </p>
        </div>

        <div class="dashboard-heading__actions">
            <a href="{{ route('admin.categories.create') }}" class="button button--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                Tambah Kategori
            </a>
        </div>
    </section>

    @if (session('success'))
        <div class="user-alert user-alert--success" role="status">
            <span class="user-alert__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg>
            </span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="user-alert user-alert--danger" role="alert">
            <span class="user-alert__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg>
            </span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <section class="category-summary-grid" aria-label="Ringkasan kategori produk">
        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--peach">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>
            </span>
            <span class="category-summary-card__copy">
                <small>Total Kategori</small>
                <strong>{{ number_format($statistics['total']) }}</strong>
                <span>Master kategori tersedia</span>
            </span>
        </article>

        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
            <span class="category-summary-card__copy">
                <small>Kategori Aktif</small>
                <strong>{{ number_format($statistics['active']) }}</strong>
                <span>Dapat digunakan pada produk</span>
            </span>
        </article>

        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--muted">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg>
            </span>
            <span class="category-summary-card__copy">
                <small>Nonaktif</small>
                <strong>{{ number_format($statistics['inactive']) }}</strong>
                <span>Tersimpan tetapi tidak dipakai</span>
            </span>
        </article>

        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--peach">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 11v6M15 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg>
            </span>
            <span class="category-summary-card__copy">
                <small>Diarsipkan</small>
                <strong>{{ number_format($statistics['archived']) }}</strong>
                <span>Data soft delete tersimpan</span>
            </span>
        </article>
    </section>

    <section class="category-management-card glass-panel">
        <div class="category-management-card__header">
            <div>
                <p class="dashboard-heading__eyebrow">Daftar Kategori</p>
                <h3>Master kategori Ettra Signature</h3>
                <p>Kode kategori akan digunakan sebagai dasar pembentukan identitas produk pada tahap berikutnya.</p>
            </div>

            <div class="category-examples" aria-label="Contoh kode kategori">
                <span>MK · Mukenah</span>
                <span>HD · Home Dress</span>
                <span>JB · Jilbab</span>
            </div>
        </div>

        <form action="{{ route('admin.categories.index') }}" method="GET" class="category-filter-form">
            <label class="category-filter-field category-filter-field--search">
                <span class="sr-only">Cari kategori</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Cari kode, nama, atau deskripsi kategori..."
                    autocomplete="off"
                >
            </label>

            <label class="category-filter-field">
                <span class="sr-only">Filter status kategori</span>
                <select name="status">
                    <option value="">Semua status</option>
                    <option value="active" @selected($filters['status'] === 'active')>Aktif</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option>
                </select>
            </label>

            <button type="submit" class="button button--secondary button--small">Terapkan</button>

            @if ($filters['search'] !== '' || $filters['status'] !== '')
                <a href="{{ route('admin.categories.index') }}" class="button button--ghost button--small">Reset</a>
            @endif
        </form>

        @if ($categories->isEmpty())
            <div class="category-empty-state">
                <span class="category-empty-state__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><path d="M17.5 15v6M14.5 18h6"/></svg>
                </span>
                <h4>Belum ada kategori yang ditampilkan</h4>
                <p>Tambahkan kategori pertama atau ubah filter pencarian untuk melihat data yang tersedia.</p>
                <a href="{{ route('admin.categories.create') }}" class="button button--primary button--small">Tambah Kategori</a>
            </div>
        @else
            <div class="responsive-table-wrap category-desktop-table">
                <table class="admin-table category-table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Kode</th>
                            <th>Status</th>
                            <th>Terakhir Diperbarui</th>
                            <th>Pengguna</th>
                            <th class="category-table__actions-heading">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td>
                                    <div class="category-identity">
                                        <span class="category-identity__mark">{{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}</span>
                                        <span class="category-identity__copy">
                                            <strong>{{ $category->name }}</strong>
                                            <small>{{ $category->description ?: 'Belum ada deskripsi kategori.' }}</small>
                                            <small class="category-slug">/{{ $category->slug }}</small>
                                        </span>
                                    </div>
                                </td>
                                <td><span class="category-code-badge">{{ $category->code }}</span></td>
                                <td>
                                    <span class="status-badge {{ $category->is_active ? 'status-badge--success' : 'status-badge--danger' }}">
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="category-table-primary">{{ $category->updated_at?->format('d M Y, H:i') ?? '-' }}</span>
                                    <small class="category-table-secondary">Dibuat {{ $category->created_at?->format('d M Y') ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="category-table-primary">{{ $category->updatedBy?->name ?? $category->createdBy?->name ?? 'Sistem' }}</span>
                                    <small class="category-table-secondary">Perubahan terakhir</small>
                                </td>
                                <td>
                                    <div class="category-actions">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="category-action-button category-action-button--edit" aria-label="Edit {{ $category->name }}" title="Edit kategori">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m4 20 4.2-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z"/><path d="m13.8 7.2 3 3"/></svg>
                                        </a>

                                        <form
                                            action="{{ route('admin.categories.toggle-status', $category) }}"
                                            method="POST"
                                            data-confirm-form
                                            data-confirm-message="{{ $category->is_active ? 'Nonaktifkan kategori '.$category->name.'?' : 'Aktifkan kembali kategori '.$category->name.'?' }}"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="category-action-button {{ $category->is_active ? 'category-action-button--disable' : 'category-action-button--enable' }}" aria-label="{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $category->name }}" title="{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }} kategori">
                                                @if ($category->is_active)
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg>
                                                @else
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg>
                                                @endif
                                            </button>
                                        </form>

                                        <form
                                            action="{{ route('admin.categories.destroy', $category) }}"
                                            method="POST"
                                            data-confirm-form
                                            data-confirm-message="Hapus kategori {{ $category->name }}? Data akan diarsipkan menggunakan soft delete."
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="category-action-button category-action-button--delete" aria-label="Hapus {{ $category->name }}" title="Hapus kategori">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 11v6M15 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="category-mobile-list">
                @foreach ($categories as $category)
                    <article class="category-mobile-card">
                        <div class="category-mobile-card__top">
                            <div class="category-identity">
                                <span class="category-identity__mark">{{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}</span>
                                <span class="category-identity__copy">
                                    <strong>{{ $category->name }}</strong>
                                    <small>{{ $category->code }}</small>
                                </span>
                            </div>

                            <span class="status-badge {{ $category->is_active ? 'status-badge--success' : 'status-badge--danger' }}">
                                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <p>{{ $category->description ?: 'Belum ada deskripsi kategori.' }}</p>

                        <dl>
                            <div><dt>Slug</dt><dd>/{{ $category->slug }}</dd></div>
                            <div><dt>Diperbarui</dt><dd>{{ $category->updated_at?->format('d M Y, H:i') ?? '-' }}</dd></div>
                            <div><dt>Pengguna</dt><dd>{{ $category->updatedBy?->name ?? $category->createdBy?->name ?? 'Sistem' }}</dd></div>
                        </dl>

                        <div class="category-mobile-card__actions">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="button button--secondary button--small">Edit</a>

                            <form action="{{ route('admin.categories.toggle-status', $category) }}" method="POST" data-confirm-form data-confirm-message="{{ $category->is_active ? 'Nonaktifkan kategori '.$category->name.'?' : 'Aktifkan kembali kategori '.$category->name.'?' }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="button button--ghost button--small">{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>

                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" data-confirm-form data-confirm-message="Hapus kategori {{ $category->name }}? Data akan diarsipkan menggunakan soft delete.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button button--danger-soft button--small">Hapus</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($categories->hasPages())
                <div class="category-pagination">
                    {{ $categories->links() }}
                </div>
            @endif
        @endif
    </section>

    <section class="category-policy glass-panel">
        <span class="category-policy__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="M9 12h6M12 9v6"/></svg>
        </span>
        <div>
            <strong>Aturan master kategori</strong>
            <p>Kode dan nama kategori harus unik. Penghapusan menggunakan soft delete, dan setelah modul Produk tersedia sistem otomatis menolak penghapusan kategori yang masih digunakan oleh produk aktif.</p>
        </div>
    </section>
@endsection
