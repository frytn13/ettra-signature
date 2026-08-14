@extends('layouts.admin')

@section('title', 'Produk')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Produk')

@section('content')
    <section class="dashboard-heading category-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Master Data</p>
            <h2>Kelola produk Ettra Signature</h2>
            <p>Atur identitas produk, kategori, harga jual, foto, status, dan visibilitas katalog. Harga beli, harga modal, margin, dan estimasi keuntungan hanya tersedia untuk Owner.</p>
        </div>

        <div class="dashboard-heading__actions">
            <a href="{{ route('admin.products.create') }}" class="button button--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Produk
            </a>
        </div>
    </section>

    @if (session('success'))
        <div class="user-alert user-alert--success" role="status">
            <span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="user-alert user-alert--danger" role="alert">
            <span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <section class="category-summary-grid" aria-label="Ringkasan produk">
        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/></svg></span>
            <span class="category-summary-card__copy"><small>Total Produk</small><strong>{{ number_format($statistics['total']) }}</strong><span>Produk aktif dan nonaktif</span></span>
        </article>
        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg></span>
            <span class="category-summary-card__copy"><small>Produk Aktif</small><strong>{{ number_format($statistics['active']) }}</strong><span>Siap dikelola dan dijual</span></span>
        </article>
        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></span>
            <span class="category-summary-card__copy"><small>Tampil di Katalog</small><strong>{{ number_format($statistics['visible']) }}</strong><span>Aktif dan terlihat pelanggan</span></span>
        </article>
        <article class="category-summary-card glass-panel">
            <span class="category-summary-card__icon category-summary-card__icon--muted"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 11v6M15 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg></span>
            <span class="category-summary-card__copy"><small>Diarsipkan</small><strong>{{ number_format($statistics['archived']) }}</strong><span>Data soft delete tersimpan</span></span>
        </article>
    </section>

    <section class="category-management-card glass-panel">
        <div class="category-management-card__header">
            <div>
                <p class="dashboard-heading__eyebrow">Daftar Produk</p>
                <h3>Katalog internal</h3>
                <p>Variasi warna dan ukuran kini dapat dikelola melalui Modul Variasi Produk. Stok akan dihubungkan setelah Master Room tersedia.</p>
            </div>
            @if ($isOwner)
                <span class="product-owner-chip">Owner · Data komersial aktif</span>
            @else
                <span class="product-admin-chip">Admin · Data komersial dilindungi</span>
            @endif
        </div>

        <form action="{{ route('admin.products.index') }}" method="GET" class="category-filter-form product-filter-form">
            <label class="category-filter-field category-filter-field--search">
                <span class="sr-only">Cari produk</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Cari kode, nama, kategori, atau deskripsi..." autocomplete="off">
            </label>

            <label class="category-filter-field">
                <span class="sr-only">Kategori</span>
                <select name="category">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $filters['category'] === (string) $category->id)>{{ $category->code }} · {{ $category->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="category-filter-field">
                <span class="sr-only">Status produk</span>
                <select name="status">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="category-filter-field">
                <span class="sr-only">Ketersediaan</span>
                <select name="availability">
                    <option value="">Semua ketersediaan</option>
                    @foreach ($availabilityOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['availability'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="category-filter-field">
                <span class="sr-only">Visibilitas</span>
                <select name="visibility">
                    <option value="">Semua visibilitas</option>
                    <option value="visible" @selected($filters['visibility'] === 'visible')>Tampil</option>
                    <option value="hidden" @selected($filters['visibility'] === 'hidden')>Disembunyikan</option>
                </select>
            </label>

            <button type="submit" class="button button--secondary button--small">Terapkan</button>
            @if (collect($filters)->filter(fn ($value) => $value !== '')->isNotEmpty())
                <a href="{{ route('admin.products.index') }}" class="button button--ghost button--small">Reset</a>
            @endif
        </form>

        @if ($products->isEmpty())
            <div class="category-empty-state">
                <span class="category-empty-state__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/><path d="M17 13v6M14 16h6"/></svg></span>
                <h4>Belum ada produk yang ditampilkan</h4>
                <p>Tambahkan produk pertama atau ubah filter untuk menampilkan data yang tersedia.</p>
                <a href="{{ route('admin.products.create') }}" class="button button--primary button--small">Tambah Produk</a>
            </div>
        @else
            <div class="responsive-table-wrap product-desktop-table">
                <table class="admin-table product-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Harga Jual</th>
                            @if ($isOwner)<th>Harga Modal</th>@endif
                            <th>Status</th>
                            <th>Katalog</th>
                            <th class="category-table__actions-heading">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>
                                    <div class="product-identity">
                                        @if ($product->primaryImage)
                                            <img src="{{ asset('storage/'.$product->primaryImage->path) }}" alt="{{ $product->name }}" class="product-thumb">
                                        @else
                                            <span class="product-thumb product-thumb--empty">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                                        @endif
                                        <span class="category-identity__copy">
                                            <strong><a href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a></strong>
                                            <small>{{ $product->code }}</small>
                                            <small>{{ $product->availabilityLabel() }}</small>
                                        </span>
                                    </div>
                                </td>
                                <td><span class="category-code-badge">{{ $product->category?->code ?? '-' }}</span><small class="category-table-secondary">{{ $product->category?->name ?? 'Kategori tidak tersedia' }}</small></td>
                                <td><span class="category-table-primary">Rp{{ number_format((float) $product->selling_price, 0, ',', '.') }}</span></td>
                                @if ($isOwner)
                                    <td>
                                        <span class="category-table-primary">{{ $product->cost_price !== null ? 'Rp'.number_format((float) $product->cost_price, 0, ',', '.') : '-' }}</span>
                                        @if ($product->grossMarginPercentage() !== null)<small class="category-table-secondary">Margin {{ number_format($product->grossMarginPercentage(), 1, ',', '.') }}%</small>@endif
                                    </td>
                                @endif
                                <td><span class="status-badge {{ $product->status === 'active' ? 'status-badge--success' : ($product->status === 'inactive' ? 'status-badge--warning' : 'status-badge--danger') }}">{{ $product->statusLabel() }}</span></td>
                                <td><span class="status-badge {{ $product->is_visible ? 'status-badge--success' : 'status-badge--danger' }}">{{ $product->is_visible ? 'Tampil' : 'Tersembunyi' }}</span></td>
                                <td>
                                    <div class="category-actions">
                                        <a href="{{ route('admin.products.show', $product) }}" class="category-action-button category-action-button--enable" title="Detail" aria-label="Detail {{ $product->name }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></a>
                                        <a href="{{ route('admin.product-variants.index', ['product' => $product->id]) }}" class="category-action-button category-action-button--enable" title="Kelola variasi" aria-label="Kelola variasi {{ $product->name }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m8 4 4 2 4-2 4 2v5l-4 2-4-2-4 2-4-2V6l4-2Z"/><path d="M12 6v5"/></svg></a>
                                        <a href="{{ route('admin.products.edit', $product) }}" class="category-action-button category-action-button--edit" title="Edit" aria-label="Edit {{ $product->name }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m4 20 4.2-1 10.7-10.7a2.1 2.1 0 0 0-3-3L5.2 16 4 20Z"/></svg></a>
                                        <form action="{{ route('admin.products.toggle-visibility', $product) }}" method="POST" data-confirm-form data-confirm-message="{{ $product->is_visible ? 'Sembunyikan '.$product->name.' dari katalog pelanggan?' : 'Tampilkan '.$product->name.' pada katalog pelanggan?' }}">@csrf @method('PATCH')<button type="submit" class="category-action-button {{ $product->is_visible ? 'category-action-button--disable' : 'category-action-button--enable' }}" title="{{ $product->is_visible ? 'Sembunyikan' : 'Tampilkan' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">@if($product->is_visible)<path d="M3 3l18 18M10.6 10.7a2 2 0 0 0 2.7 2.7M9.9 5.2A9.8 9.8 0 0 1 12 5c5.5 0 9 7 9 7a15.4 15.4 0 0 1-2.2 3.1M6.6 6.6C4.4 8.1 3 12 3 12s3.5 7 9 7c1.5 0 2.8-.5 4-1.2"/>@else<path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z"/><circle cx="12" cy="12" r="2.5"/>@endif</svg></button></form>
                                        <form action="{{ route('admin.products.toggle-status', $product) }}" method="POST" data-confirm-form data-confirm-message="{{ $product->status === 'active' ? 'Nonaktifkan '.$product->name.'?' : 'Aktifkan kembali '.$product->name.'?' }}">@csrf @method('PATCH')<button type="submit" class="category-action-button {{ $product->status === 'active' ? 'category-action-button--disable' : 'category-action-button--enable' }}" title="{{ $product->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></button></form>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" data-confirm-form data-confirm-message="Arsipkan produk {{ $product->name }}? Data akan menggunakan soft delete.">@csrf @method('DELETE')<button type="submit" class="category-action-button category-action-button--delete" title="Arsipkan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 11v6M15 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg></button></form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="product-mobile-list">
                @foreach ($products as $product)
                    <article class="product-mobile-card">
                        <div class="product-mobile-card__head">
                            <div class="product-identity">
                                @if ($product->primaryImage)<img src="{{ asset('storage/'.$product->primaryImage->path) }}" alt="{{ $product->name }}" class="product-thumb">@else<span class="product-thumb product-thumb--empty">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>@endif
                                <span class="category-identity__copy"><strong>{{ $product->name }}</strong><small>{{ $product->code }}</small></span>
                            </div>
                            <span class="status-badge {{ $product->status === 'active' ? 'status-badge--success' : 'status-badge--warning' }}">{{ $product->statusLabel() }}</span>
                        </div>
                        <dl>
                            <div><dt>Kategori</dt><dd>{{ $product->category?->code }} · {{ $product->category?->name }}</dd></div>
                            <div><dt>Harga Jual</dt><dd>Rp{{ number_format((float) $product->selling_price, 0, ',', '.') }}</dd></div>
                            @if ($isOwner)<div><dt>Harga Modal</dt><dd>{{ $product->cost_price !== null ? 'Rp'.number_format((float) $product->cost_price, 0, ',', '.') : '-' }}</dd></div>@endif
                            <div><dt>Ketersediaan</dt><dd>{{ $product->availabilityLabel() }}</dd></div>
                            <div><dt>Katalog</dt><dd>{{ $product->is_visible ? 'Tampil' : 'Tersembunyi' }}</dd></div>
                        </dl>
                        <div class="category-mobile-card__actions">
                            <a href="{{ route('admin.products.show', $product) }}" class="button button--secondary button--small">Detail</a>
                            <a href="{{ route('admin.product-variants.index', ['product' => $product->id]) }}" class="button button--ghost button--small">Variasi</a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="button button--ghost button--small">Edit</a>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($products->hasPages())<div class="category-pagination">{{ $products->links() }}</div>@endif
        @endif
    </section>

    <section class="category-policy glass-panel">
        <span class="category-policy__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg></span>
        <div><strong>Pemisahan akses data produk</strong><p>Admin dapat mengelola data operasional dan harga jual. Harga beli awal, harga modal, margin, serta estimasi keuntungan hanya dirender dan diproses untuk Owner.</p></div>
    </section>
@endsection
