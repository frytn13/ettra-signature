@extends('layouts.admin')

@section('title', 'Stok Room')
@section('eyebrow', 'Persediaan')
@section('page-title', 'Stok Room')

@section('content')
    <section class="dashboard-heading category-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Inventory Multiroom</p>
            <h2>Pantau stok per SKU dan lokasi</h2>
            <p>Stok dihitung untuk setiap kombinasi Variasi Produk + Room. Kuantitas fisik tidak diedit langsung agar histori persediaan tetap dapat diaudit.</p>
        </div>
        <div class="dashboard-heading__actions">
            <a href="{{ route('admin.stock-adjustments.index') }}" class="button button--secondary">Penyesuaian</a><a href="{{ route('admin.stock-movements.index') }}" class="button button--secondary">Riwayat Mutasi</a>
            <a href="{{ route('admin.warehouse-stocks.create') }}" class="button button--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Daftarkan SKU
            </a>
        </div>
    </section>

    @if (session('success'))
        <div class="user-alert user-alert--success" role="status"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span><span>{{ session('success') }}</span></div>
    @endif
    @if (session('error'))
        <div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>{{ session('error') }}</span></div>
    @endif

    <section class="stock-summary-grid" aria-label="Ringkasan stok room">
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/></svg></span><span class="category-summary-card__copy"><small>SKU × Room</small><strong>{{ number_format($statistics['records']) }}</strong><span>{{ number_format($statistics['warehouses']) }} room terpakai</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16v12H4zM8 4h8v3"/></svg></span><span class="category-summary-card__copy"><small>Stok Fisik</small><strong>{{ number_format($statistics['on_hand']) }}</strong><span>Quantity on hand</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg></span><span class="category-summary-card__copy"><small>Stok Tersedia</small><strong>{{ number_format($statistics['available']) }}</strong><span>Setelah reservasi & rusak</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--muted"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 17h.01"/></svg></span><span class="category-summary-card__copy"><small>Perlu Perhatian</small><strong>{{ number_format($statistics['low'] + $statistics['out']) }}</strong><span>{{ number_format($statistics['out']) }} habis · {{ number_format($statistics['low']) }} menipis</span></span></article>
    </section>

    <section class="category-management-card glass-panel">
        <div class="category-management-card__header">
            <div><p class="dashboard-heading__eyebrow">Daftar Stok</p><h3>Persediaan seluruh lokasi</h3><p>Stok tersedia = stok fisik − reservasi − rusak. Nilai stok minimum menentukan status Menipis dan kebutuhan restock.</p></div>
            <div class="category-examples"><span>Reserved {{ number_format($statistics['reserved']) }}</span><span>Rusak {{ number_format($statistics['damaged']) }}</span><span>Real-time foundation</span></div>
        </div>

        <form action="{{ route('admin.warehouse-stocks.index') }}" method="GET" class="stock-filter-form">
            <label class="category-filter-field category-filter-field--search"><span class="sr-only">Cari stok</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Cari SKU, produk, atau room..." autocomplete="off"></label>
            <label class="category-filter-field"><span class="sr-only">Room</span><select name="warehouse"><option value="">Semua room</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected($filters['warehouse'] === (string)$warehouse->id)>{{ $warehouse->code }} · {{ $warehouse->name }}</option>@endforeach</select></label>
            <label class="category-filter-field"><span class="sr-only">Kategori</span><select name="category"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected($filters['category'] === (string)$category->id)>{{ $category->code }} · {{ $category->name }}</option>@endforeach</select></label>
            <label class="category-filter-field"><span class="sr-only">Status stok</span><select name="status"><option value="">Semua status</option><option value="safe" @selected($filters['status']==='safe')>Aman</option><option value="low" @selected($filters['status']==='low')>Menipis</option><option value="out" @selected($filters['status']==='out')>Habis</option></select></label>
            <button type="submit" class="button button--secondary button--small">Terapkan</button>
            @if(collect($filters)->filter(fn($value) => $value !== '')->isNotEmpty())<a href="{{ route('admin.warehouse-stocks.index') }}" class="button button--ghost button--small">Reset</a>@endif
        </form>

        @if($stocks->isEmpty())
            <div class="category-empty-state"><span class="category-empty-state__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/></svg></span><h4>Belum ada stok yang ditampilkan</h4><p>Daftarkan variasi produk ke room terlebih dahulu. Kuantitas awal dibuat 0 dan dapat diubah melalui transaksi Mutasi Stok.</p><a href="{{ route('admin.warehouse-stocks.create') }}" class="button button--primary button--small">Daftarkan SKU</a></div>
        @else
            <div class="responsive-table-wrap stock-desktop-table">
                <table class="admin-table category-table stock-table">
                    <thead><tr><th>SKU / Produk</th><th>Room</th><th>Fisik</th><th>Reservasi</th><th>Rusak</th><th>Tersedia</th><th>Minimum</th><th>Status</th><th class="category-table__actions-heading">Aksi</th></tr></thead>
                    <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td><div class="stock-product-cell"><span class="category-code-badge">{{ $stock->productVariant?->sku }}</span><span><strong>{{ $stock->productVariant?->product?->name ?? '-' }}</strong><small>{{ $stock->productVariant?->color?->name ?? '-' }} / {{ $stock->productVariant?->size?->name ?? '-' }}</small></span></div></td>
                                <td><span class="category-table-primary">{{ $stock->warehouse?->name ?? '-' }}</span><small class="category-table-secondary">{{ $stock->warehouse?->code ?? '-' }}</small></td>
                                <td><strong class="stock-number">{{ number_format($stock->quantity_on_hand) }}</strong></td>
                                <td><span class="stock-number stock-number--muted">{{ number_format($stock->quantity_reserved) }}</span></td>
                                <td><span class="stock-number stock-number--danger">{{ number_format($stock->quantity_damaged) }}</span></td>
                                <td><strong class="stock-number stock-number--available">{{ number_format($stock->availableQuantity()) }}</strong></td>
                                <td><span class="stock-number">{{ number_format($stock->minimum_stock) }}</span></td>
                                <td><span class="stock-status stock-status--{{ $stock->stockStatus() }}">{{ $stock->stockStatusLabel() }}</span></td>
                                <td><div class="category-actions"><a href="{{ route('admin.warehouse-stocks.show',$stock) }}" class="category-action-button category-action-button--enable" title="Detail stok"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></a><a href="{{ route('admin.warehouse-stocks.edit',$stock) }}" class="category-action-button category-action-button--edit" title="Atur stok minimum"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m4 20 4.2-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z"/></svg></a></div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="stock-mobile-list">
                @foreach($stocks as $stock)
                    <article class="stock-mobile-card"><div class="stock-mobile-card__head"><div><span class="category-code-badge">{{ $stock->productVariant?->sku }}</span><h4>{{ $stock->productVariant?->product?->name ?? '-' }}</h4><p>{{ $stock->productVariant?->color?->name ?? '-' }} / {{ $stock->productVariant?->size?->name ?? '-' }} · {{ $stock->warehouse?->name ?? '-' }}</p></div><span class="stock-status stock-status--{{ $stock->stockStatus() }}">{{ $stock->stockStatusLabel() }}</span></div><div class="stock-mobile-quantities"><div><small>Fisik</small><strong>{{ number_format($stock->quantity_on_hand) }}</strong></div><div><small>Reserved</small><strong>{{ number_format($stock->quantity_reserved) }}</strong></div><div><small>Rusak</small><strong>{{ number_format($stock->quantity_damaged) }}</strong></div><div><small>Tersedia</small><strong>{{ number_format($stock->availableQuantity()) }}</strong></div><div><small>Minimum</small><strong>{{ number_format($stock->minimum_stock) }}</strong></div></div><div class="category-mobile-card__actions"><a href="{{ route('admin.warehouse-stocks.show',$stock) }}" class="button button--ghost button--small">Detail</a><a href="{{ route('admin.warehouse-stocks.edit',$stock) }}" class="button button--secondary button--small">Atur Minimum</a></div></article>
                @endforeach
            </div>

            @if($stocks->hasPages())<div class="category-pagination">{{ $stocks->links() }}</div>@endif
        @endif
    </section>

    <section class="compact-card inventory-analysis-card">
        <div class="compact-card__head">
            <div>
                <p class="dashboard-heading__eyebrow">Analisis 30 Hari</p>
                <h3>Fast moving, slow moving & rekomendasi restock</h3>
                <p>Perhitungan menggunakan penjualan lunas 30 hari terakhir. Target restock mempertimbangkan lead time 14 hari, safety stock 7 hari, dan stok minimum.</p>
            </div>
        </div>

        <div class="compact-stats inventory-analysis-stats">
            <article class="compact-stat"><small>Fast Moving</small><strong>{{ number_format($analysisSummary['fast']) }}</strong><span>Rata-rata ≥ 0,5 unit/hari</span></article>
            <article class="compact-stat"><small>Slow Moving</small><strong>{{ number_format($analysisSummary['slow']) }}</strong><span>Terjual, tetapi &lt; 0,1 unit/hari</span></article>
            <article class="compact-stat"><small>Belum Terjual</small><strong>{{ number_format($analysisSummary['no_sale']) }}</strong><span>Tidak ada penjualan 30 hari</span></article>
            <article class="compact-stat"><small>Disarankan Restock</small><strong>{{ number_format($analysisSummary['restock']) }}</strong><span>Berdasarkan kebutuhan stok target</span></article>
        </div>

        @if($analysisRows->isEmpty())
            <div class="compact-empty">Analisis akan muncul setelah SKU didaftarkan ke room.</div>
        @else
            <div class="compact-table-wrap">
                <table class="compact-table">
                    <thead><tr><th>SKU / Produk</th><th>Room</th><th>Terjual 30 hari</th><th>Rata-rata/hari</th><th>Estimasi habis</th><th>Klasifikasi</th><th>Saran restock</th></tr></thead>
                    <tbody>
                        @foreach($analysisRows as $row)
                            @php
                                $movementLabels = ['fast'=>'Fast Moving','normal'=>'Normal','slow'=>'Slow Moving','no_sale'=>'Belum Terjual'];
                                $movementClasses = ['fast'=>'green','normal'=>'blue','slow'=>'peach','no_sale'=>'muted'];
                            @endphp
                            <tr>
                                <td><strong>{{ $row['stock']->productVariant?->sku ?? '-' }}</strong><br><small>{{ $row['stock']->productVariant?->product?->name ?? '-' }}</small></td>
                                <td>{{ $row['stock']->warehouse?->name ?? '-' }}</td>
                                <td>{{ number_format($row['sold_30d']) }} unit</td>
                                <td>{{ number_format($row['average_daily_sales'], 2, ',', '.') }}</td>
                                <td>{{ $row['days_to_stockout'] === null ? 'Tidak terukur' : number_format($row['days_to_stockout'], 1, ',', '.').' hari' }}</td>
                                <td><span class="status-pill status-pill--{{ $movementClasses[$row['movement']] }}">{{ $movementLabels[$row['movement']] }}</span></td>
                                <td><strong>{{ number_format($row['recommended_restock']) }} unit</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="category-policy glass-panel"><span class="category-policy__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg></span><div><strong>Kuantitas tidak diedit bebas</strong><p>Halaman ini hanya mengelola penempatan SKU dan batas minimum. Stok fisik berubah melalui Mutasi Stok atau Penyesuaian Stok dan seluruh perubahan tersimpan sebagai ledger. Reservasi serta barang rusak akan dikelola melalui modul transaksi terkait pada tahap berikutnya.</p></div></section>
@endsection
