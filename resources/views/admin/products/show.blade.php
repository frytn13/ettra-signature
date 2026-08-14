@extends('layouts.admin')

@section('title', 'Detail Produk')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Detail Produk')

@section('content')
    <section class="dashboard-heading category-form-heading">
        <div><p class="dashboard-heading__eyebrow">Detail Produk</p><h2>{{ $product->name }}</h2><p>{{ $product->code }} · {{ $product->category?->code }} {{ $product->category?->name }}</p></div>
        <div class="dashboard-heading__actions"><a href="{{ route('admin.product-variants.index', ['product' => $product->id]) }}" class="button button--secondary">Kelola Variasi</a><a href="{{ route('admin.products.edit', $product) }}" class="button button--primary">Edit Produk</a><a href="{{ route('admin.products.index') }}" class="button button--secondary">Kembali</a></div>
    </section>

    @if (session('success'))<div class="user-alert user-alert--success" role="status"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span><span>{{ session('success') }}</span></div>@endif

    <div class="product-detail-grid">
        <section class="product-detail-gallery glass-panel">
            @php $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first(); @endphp
            <div class="product-detail-main-image">
                @if ($primaryImage)<img src="{{ asset('storage/'.$primaryImage->path) }}" alt="{{ $product->name }}">@else<span>{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>@endif
            </div>
            @if ($product->images->count() > 1)
                <div class="product-detail-thumbs">
                    @foreach ($product->images as $image)<img src="{{ asset('storage/'.$image->path) }}" alt="Foto {{ $product->name }}">@endforeach
                </div>
            @endif
        </section>

        <section class="product-detail-info glass-panel">
            <div class="product-detail-info__top">
                <div><span class="category-code-badge">{{ $product->code }}</span><h3>{{ $product->name }}</h3><p>{{ $product->description ?: 'Belum ada deskripsi produk.' }}</p></div>
                <span class="status-badge {{ $product->status === 'active' ? 'status-badge--success' : ($product->status === 'inactive' ? 'status-badge--warning' : 'status-badge--danger') }}">{{ $product->statusLabel() }}</span>
            </div>

            <div class="product-detail-price"><small>Harga Jual</small><strong>Rp{{ number_format((float) $product->selling_price, 0, ',', '.') }}</strong></div>

            <dl class="product-detail-list">
                <div><dt>Kategori</dt><dd>{{ $product->category?->code }} · {{ $product->category?->name }}</dd></div>
                <div><dt>Ketersediaan</dt><dd>{{ $product->availabilityLabel() }}</dd></div>
                <div><dt>Katalog Pelanggan</dt><dd>{{ $product->is_visible ? 'Ditampilkan' : 'Disembunyikan' }}</dd></div>
                <div><dt>Berat</dt><dd>{{ $product->weight_grams ? number_format($product->weight_grams).' gram' : '-' }}</dd></div>
                <div><dt>Tanggal Masuk</dt><dd>{{ $product->entry_date?->format('d M Y') ?? '-' }}</dd></div>
                <div><dt>Slug</dt><dd>/{{ $product->slug }}</dd></div>
            </dl>
        </section>

        @if ($isOwner)
            <section class="product-commercial-card glass-panel">
                <div class="product-commercial-card__header"><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span><div><p class="dashboard-heading__eyebrow">Owner Only</p><h3>Data komersial</h3></div></div>
                <div class="product-commercial-grid">
                    <div><small>Harga Beli Awal</small><strong>{{ $product->initial_purchase_price !== null ? 'Rp'.number_format((float) $product->initial_purchase_price, 0, ',', '.') : '-' }}</strong></div>
                    <div><small>Harga Modal</small><strong>{{ $product->cost_price !== null ? 'Rp'.number_format((float) $product->cost_price, 0, ',', '.') : '-' }}</strong></div>
                    <div><small>Estimasi Profit</small><strong class="{{ ($product->estimatedProfit() ?? 0) < 0 ? 'product-negative-value' : '' }}">{{ $product->estimatedProfit() !== null ? 'Rp'.number_format($product->estimatedProfit(), 0, ',', '.') : '-' }}</strong></div>
                    <div><small>Gross Margin</small><strong>{{ $product->grossMarginPercentage() !== null ? number_format($product->grossMarginPercentage(), 1, ',', '.').'%' : '-' }}</strong></div>
                </div>
            </section>
        @else
            <section class="product-commercial-card product-commercial-card--protected glass-panel"><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span><div><strong>Data komersial dilindungi</strong><p>Harga beli, harga modal, margin, dan profit hanya dapat diakses oleh Owner sesuai ketentuan sistem.</p></div></section>
        @endif

        <section class="product-detail-meta glass-panel">
            <p class="dashboard-heading__eyebrow">Metadata</p><h3>Riwayat produk</h3>
            <dl><div><dt>Dibuat</dt><dd>{{ $product->created_at?->format('d M Y, H:i') ?? '-' }}</dd></div><div><dt>Dibuat oleh</dt><dd>{{ $product->createdBy?->name ?? 'Sistem' }}</dd></div><div><dt>Diperbarui</dt><dd>{{ $product->updated_at?->format('d M Y, H:i') ?? '-' }}</dd></div><div><dt>Diperbarui oleh</dt><dd>{{ $product->updatedBy?->name ?? 'Sistem' }}</dd></div></dl>
        </section>
    </div>
@endsection
