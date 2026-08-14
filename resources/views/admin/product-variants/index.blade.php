@extends('layouts.admin')

@section('title', 'Variasi Produk')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Variasi Produk')

@section('content')
    <section class="dashboard-heading category-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Variasi Produk</p>
            <h2>Kelola kombinasi warna dan ukuran</h2>
            <p>Setiap kombinasi memiliki SKU sendiri. Stok belum disimpan di modul ini karena jumlah stok akan dicatat per variasi dan per room pada tahap berikutnya.</p>
        </div>
        <div class="dashboard-heading__actions">
            <a href="{{ route('admin.product-variants.generate-form', request()->filled('product') ? ['product' => request('product')] : []) }}" class="button button--secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M7 4v6M17 4v6M4 17h16M7 14v6M17 14v6"/></svg>
                Generate Kombinasi
            </a>
            <a href="{{ route('admin.product-variants.create', request()->filled('product') ? ['product' => request('product')] : []) }}" class="button button--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Variasi
            </a>
        </div>
    </section>

    @if (session('success'))
        <div class="user-alert user-alert--success" role="status"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span><span>{{ session('success') }}</span></div>
    @endif
    @if (session('error'))
        <div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>{{ session('error') }}</span></div>
    @endif

    <section class="category-summary-grid" aria-label="Ringkasan variasi produk">
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m8 4 4 2 4-2 4 2v5l-4 2-4-2-4 2-4-2V6l4-2Z"/><path d="M8 13v5l4 2 4-2v-5"/></svg></span><span class="category-summary-card__copy"><small>Total Variasi</small><strong>{{ number_format($statistics['total']) }}</strong><span>Variasi aktif dan nonaktif</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg></span><span class="category-summary-card__copy"><small>Aktif</small><strong>{{ number_format($statistics['active']) }}</strong><span>Siap digunakan untuk transaksi</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg></span><span class="category-summary-card__copy"><small>Nonaktif</small><strong>{{ number_format($statistics['inactive']) }}</strong><span>Tersimpan tetapi tidak digunakan</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--muted"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 11v6M15 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg></span><span class="category-summary-card__copy"><small>Diarsipkan</small><strong>{{ number_format($statistics['archived']) }}</strong><span>Soft delete tetap tersimpan</span></span></article>
    </section>

    <section class="category-management-card glass-panel">
        <div class="category-management-card__header"><div><p class="dashboard-heading__eyebrow">Daftar Variasi</p><h3>SKU produk</h3><p>Harga akhir dihitung dari harga jual dasar produk ditambah nilai tambahan harga variasi.</p></div></div>

        <form action="{{ route('admin.product-variants.index') }}" method="GET" class="category-filter-form variant-filter-form">
            <label class="category-filter-field category-filter-field--search"><span class="sr-only">Cari variasi</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Cari SKU, produk, warna, atau ukuran..." autocomplete="off"></label>
            <label class="category-filter-field"><span class="sr-only">Produk</span><select name="product"><option value="">Semua produk</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected((string)$filters['product']===(string)$product->id)>{{ $product->code }} · {{ $product->name }}</option>@endforeach</select></label>
            <label class="category-filter-field"><span class="sr-only">Warna</span><select name="color"><option value="">Semua warna</option>@foreach($colors as $color)<option value="{{ $color->id }}" @selected((string)$filters['color']===(string)$color->id)>{{ $color->code }} · {{ $color->name }}</option>@endforeach</select></label>
            <label class="category-filter-field"><span class="sr-only">Ukuran</span><select name="size"><option value="">Semua ukuran</option>@foreach($sizes as $size)<option value="{{ $size->id }}" @selected((string)$filters['size']===(string)$size->id)>{{ $size->code }} · {{ $size->name }}</option>@endforeach</select></label>
            <label class="category-filter-field"><span class="sr-only">Status</span><select name="status"><option value="">Semua status</option><option value="active" @selected($filters['status']==='active')>Aktif</option><option value="inactive" @selected($filters['status']==='inactive')>Nonaktif</option></select></label>
            <button type="submit" class="button button--secondary button--small">Terapkan</button>
            @if(collect($filters)->filter(fn($value)=>$value!=='')->isNotEmpty())<a href="{{ route('admin.product-variants.index') }}" class="button button--ghost button--small">Reset</a>@endif
        </form>

        @if($variants->isEmpty())
            <div class="category-empty-state"><span class="category-empty-state__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m8 4 4 2 4-2 4 2v5l-4 2-4-2-4 2-4-2V6l4-2Z"/><path d="M12 13v8M8 17h8"/></svg></span><h4>Belum ada variasi yang ditampilkan</h4><p>Buat satu variasi secara manual atau generate beberapa kombinasi warna dan ukuran sekaligus.</p><a href="{{ route('admin.product-variants.generate-form') }}" class="button button--primary button--small">Generate Variasi</a></div>
        @else
            <div class="responsive-table-wrap variant-desktop-table">
                <table class="admin-table variant-table">
                    <thead><tr><th>Produk & SKU</th><th>Warna</th><th>Ukuran</th><th>Harga Akhir</th><th>Berat</th><th>Status</th><th class="category-table__actions-heading">Aksi</th></tr></thead>
                    <tbody>
                        @foreach($variants as $variant)
                            <tr>
                                <td><div class="product-identity">@if($variant->product?->primaryImage)<img src="{{ asset('storage/'.$variant->product->primaryImage->path) }}" alt="{{ $variant->product->name }}" class="product-thumb">@else<span class="product-thumb product-thumb--empty">{{ mb_strtoupper(mb_substr($variant->product?->name ?? 'V',0,1)) }}</span>@endif<span class="category-identity__copy"><strong>{{ $variant->product?->name ?? 'Produk tidak tersedia' }}</strong><small>{{ $variant->product?->code }}</small><small>{{ $variant->sku }}</small></span></div></td>
                                <td><div class="variant-master-value"><span class="master-color-swatch {{ $variant->color?->hex_code ? '' : 'master-color-swatch--empty' }}" @if($variant->color?->hex_code) style="background-color: {{ $variant->color->hex_code }}" @endif></span><span><strong>{{ $variant->color?->name ?? '-' }}</strong><small>{{ $variant->color?->code }}</small></span></div></td>
                                <td><span class="variant-size-badge">{{ $variant->size?->code ?? '-' }}</span><small class="category-table-secondary">{{ $variant->size?->name }}</small></td>
                                <td><span class="category-table-primary">Rp{{ number_format($variant->finalSellingPrice(),0,',','.') }}</span><small class="category-table-secondary">Tambahan Rp{{ number_format((float)$variant->additional_price,0,',','.') }}</small></td>
                                <td><span class="category-table-primary">{{ $variant->effectiveWeight() ? number_format($variant->effectiveWeight()).' g' : '-' }}</span><small class="category-table-secondary">{{ $variant->weight_grams ? 'Khusus variasi' : 'Mengikuti produk' }}</small></td>
                                <td><span class="status-badge {{ $variant->is_active ? 'status-badge--success' : 'status-badge--danger' }}">{{ $variant->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td><div class="category-actions"><a href="{{ route('admin.product-variants.edit',$variant) }}" class="category-action-button category-action-button--edit" title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m4 20 4.2-1 10.7-10.7a2.1 2.1 0 0 0-3-3L5.2 16 4 20Z"/></svg></a><form action="{{ route('admin.product-variants.toggle-status',$variant) }}" method="POST" data-confirm-form data-confirm-message="{{ $variant->is_active ? 'Nonaktifkan variasi '.$variant->sku.'?' : 'Aktifkan variasi '.$variant->sku.'?' }}">@csrf @method('PATCH')<button type="submit" class="category-action-button {{ $variant->is_active ? 'category-action-button--disable' : 'category-action-button--enable' }}" title="{{ $variant->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg></button></form><form action="{{ route('admin.product-variants.destroy',$variant) }}" method="POST" data-confirm-form data-confirm-message="Arsipkan variasi {{ $variant->sku }}?">@csrf @method('DELETE')<button type="submit" class="category-action-button category-action-button--delete" title="Arsipkan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 11v6M15 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/></svg></button></form></div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="variant-mobile-list">
                @foreach($variants as $variant)
                    <article class="product-mobile-card"><div class="product-mobile-card__head"><div class="category-identity__copy"><strong>{{ $variant->product?->name }}</strong><small>{{ $variant->sku }}</small></div><span class="status-badge {{ $variant->is_active ? 'status-badge--success' : 'status-badge--danger' }}">{{ $variant->is_active ? 'Aktif' : 'Nonaktif' }}</span></div><dl><div><dt>Warna</dt><dd>{{ $variant->color?->code }} · {{ $variant->color?->name }}</dd></div><div><dt>Ukuran</dt><dd>{{ $variant->size?->code }} · {{ $variant->size?->name }}</dd></div><div><dt>Harga</dt><dd>Rp{{ number_format($variant->finalSellingPrice(),0,',','.') }}</dd></div><div><dt>Berat</dt><dd>{{ $variant->effectiveWeight() ? number_format($variant->effectiveWeight()).' g' : '-' }}</dd></div></dl><div class="category-mobile-card__actions"><a href="{{ route('admin.product-variants.edit',$variant) }}" class="button button--secondary button--small">Edit</a><form action="{{ route('admin.product-variants.toggle-status',$variant) }}" method="POST" data-confirm-form data-confirm-message="Ubah status variasi {{ $variant->sku }}?">@csrf @method('PATCH')<button type="submit" class="button button--ghost button--small">{{ $variant->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></div></article>
                @endforeach
            </div>

            @if($variants->hasPages())<div class="category-pagination">{{ $variants->links() }}</div>@endif
        @endif
    </section>

    <section class="category-policy glass-panel"><span class="category-policy__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg></span><div><strong>Satu kombinasi hanya satu kali</strong><p>Kombinasi Produk + Warna + Ukuran tidak boleh duplikat. Jika variasi sudah memiliki histori stok atau transaksi pada tahap berikutnya, sistem akan menolak penghapusan dan menggunakan status nonaktif.</p></div></section>
@endsection
