@extends('layouts.admin')

@section('title', 'Warna & Ukuran')
@section('eyebrow', 'Produk & Persediaan')
@section('page-title', 'Warna & Ukuran')

@section('content')
    <section class="dashboard-heading compact-page-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Atribut Produk</p>
            <h2>Warna dan ukuran dalam satu halaman</h2>
            <p>Kelola atribut variasi tanpa berpindah halaman. Tambah dan edit data akan terbuka sebagai pop-up card.</p>
        </div>
        <form method="GET" action="{{ route('admin.product-attributes.index') }}" class="compact-search-form" data-ajax-filter>
            <input type="search" name="search" value="{{ $search }}" placeholder="Cari warna atau ukuran...">
            <button class="button button--secondary" type="submit">Cari</button>
        </form>
    </section>

    <div class="attribute-master-grid">
        <section class="glass-panel compact-master-panel">
            <div class="compact-master-panel__header">
                <div><p class="dashboard-heading__eyebrow">Master Warna</p><h3>{{ $colors->count() }} warna</h3></div>
                <a href="{{ route('admin.colors.create') }}" class="button button--primary" data-modal-form data-modal-title="Tambah Warna">+ Warna</a>
            </div>
            <div class="compact-master-list">
                @forelse($colors as $color)
                    <article class="compact-master-item">
                        <span class="master-color-swatch {{ $color->hex_code ? '' : 'master-color-swatch--empty' }}" @if($color->hex_code) style="background:{{ $color->hex_code }}" @endif></span>
                        <div><strong>{{ $color->name }}</strong><small>{{ $color->code }} · {{ $color->hex_code ?: 'Tanpa HEX' }}</small></div>
                        <span class="status-pill {{ $color->is_active ? 'status-pill--success' : 'status-pill--neutral' }}">{{ $color->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        <a href="{{ route('admin.colors.edit', $color) }}" class="icon-button compact-master-item__edit" data-modal-form data-modal-title="Edit Warna" aria-label="Edit {{ $color->name }}">✎</a>
                    </article>
                @empty
                    <div class="empty-state compact-empty-state">Belum ada warna.</div>
                @endforelse
            </div>
        </section>

        <section class="glass-panel compact-master-panel">
            <div class="compact-master-panel__header">
                <div><p class="dashboard-heading__eyebrow">Master Ukuran</p><h3>{{ $sizes->count() }} ukuran</h3></div>
                <a href="{{ route('admin.sizes.create') }}" class="button button--primary" data-modal-form data-modal-title="Tambah Ukuran">+ Ukuran</a>
            </div>
            <div class="compact-master-list">
                @forelse($sizes as $size)
                    <article class="compact-master-item">
                        <span class="attribute-size-badge">{{ $size->code }}</span>
                        <div><strong>{{ $size->name }}</strong><small>Urutan {{ $size->sort_order }}</small></div>
                        <span class="status-pill {{ $size->is_active ? 'status-pill--success' : 'status-pill--neutral' }}">{{ $size->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        <a href="{{ route('admin.sizes.edit', $size) }}" class="icon-button compact-master-item__edit" data-modal-form data-modal-title="Edit Ukuran" aria-label="Edit {{ $size->name }}">✎</a>
                    </article>
                @empty
                    <div class="empty-state compact-empty-state">Belum ada ukuran.</div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
