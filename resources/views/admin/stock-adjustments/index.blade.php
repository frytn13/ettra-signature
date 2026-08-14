@extends('layouts.admin')

@section('title', 'Penyesuaian Stok')
@section('eyebrow', 'Persediaan')
@section('page-title', 'Penyesuaian Stok')

@section('content')
    <section class="dashboard-heading category-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Stock Opname</p>
            <h2>Koreksi stok berdasarkan hasil pemeriksaan fisik</h2>
            <p>Penyesuaian tidak mengedit saldo secara bebas. Sistem membandingkan stok fisik dengan stok sistem, lalu membuat ledger ADJUSTMENT_IN atau ADJUSTMENT_OUT secara otomatis.</p>
        </div>
        <div class="dashboard-heading__actions">
            <a href="{{ route('admin.stock-movements.index') }}" class="button button--secondary">Ledger Mutasi</a>
            <a href="{{ route('admin.stock-adjustments.create') }}" class="button button--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Penyesuaian Baru
            </a>
        </div>
    </section>

    @if (session('success'))
        <div class="user-alert user-alert--success" role="status"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span><span>{{ session('success') }}</span></div>
    @endif

    <section class="movement-summary-grid" aria-label="Ringkasan penyesuaian stok">
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M7 4v6M17 4v6M5 12h14v8H5z"/></svg></span><span class="category-summary-card__copy"><small>Total Penyesuaian</small><strong>{{ number_format($statistics['records']) }}</strong><span>Seluruh stock opname tercatat</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20V4M6 10l6-6 6 6"/></svg></span><span class="category-summary-card__copy"><small>Selisih Tambah</small><strong>{{ number_format($statistics['quantity_in']) }}</strong><span>Unit ditemukan/bertambah</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 4v16M6 14l6 6 6-6"/></svg></span><span class="category-summary-card__copy"><small>Selisih Kurang</small><strong>{{ number_format($statistics['quantity_out']) }}</strong><span>Unit dikoreksi keluar</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--muted"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><span class="category-summary-card__copy"><small>Hari Ini</small><strong>{{ number_format($statistics['today']) }}</strong><span>{{ now()->format('d/m/Y') }}</span></span></article>
    </section>

    <section class="category-management-card glass-panel">
        <div class="category-management-card__header">
            <div><p class="dashboard-heading__eyebrow">Audit Penyesuaian</p><h3>Riwayat penyesuaian stok</h3><p>Setiap penyesuaian sudah terhubung ke satu transaksi pada ledger Mutasi Stok.</p></div>
            <div class="category-examples"><span>Read only</span><span>Stock opname</span><span>Ledger linked</span></div>
        </div>

        <form action="{{ route('admin.stock-adjustments.index') }}" method="GET" class="movement-filter-form">
            <label class="category-filter-field category-filter-field--search"><span class="sr-only">Cari penyesuaian</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Nomor ADJ, SM, SKU, produk, room..." autocomplete="off"></label>
            <label class="category-filter-field"><span class="sr-only">Room</span><select name="warehouse"><option value="">Semua room</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected($filters['warehouse'] === (string)$warehouse->id)>{{ $warehouse->code }} · {{ $warehouse->name }}</option>@endforeach</select></label>
            <label class="category-filter-field"><span class="sr-only">Arah</span><select name="direction"><option value="">Semua selisih</option><option value="in" @selected($filters['direction']==='in')>Penambahan</option><option value="out" @selected($filters['direction']==='out')>Pengurangan</option></select></label>
            <label class="category-filter-field"><span class="sr-only">Alasan</span><select name="reason"><option value="">Semua alasan</option>@foreach($reasons as $value => $label)<option value="{{ $value }}" @selected($filters['reason']===$value)>{{ $label }}</option>@endforeach</select></label>
            <label class="category-filter-field"><span class="sr-only">Tanggal awal</span><input type="date" name="date_from" value="{{ $filters['date_from'] }}" title="Tanggal awal"></label>
            <label class="category-filter-field"><span class="sr-only">Tanggal akhir</span><input type="date" name="date_to" value="{{ $filters['date_to'] }}" title="Tanggal akhir"></label>
            <button type="submit" class="button button--secondary button--small">Terapkan</button>
            @if(collect($filters)->filter(fn($value) => $value !== '')->isNotEmpty())<a href="{{ route('admin.stock-adjustments.index') }}" class="button button--ghost button--small">Reset</a>@endif
        </form>

        @if($adjustments->isEmpty())
            <div class="category-empty-state"><span class="category-empty-state__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 5h14v14H5z"/><path d="M8 9h8M8 13h5"/></svg></span><h4>Belum ada penyesuaian stok</h4><p>Gunakan fitur ini saat hasil pemeriksaan fisik berbeda dari stok sistem.</p><a href="{{ route('admin.stock-adjustments.create') }}" class="button button--primary button--small">Buat Penyesuaian</a></div>
        @else
            <div class="responsive-table-wrap movement-desktop-table">
                <table class="admin-table category-table movement-table">
                    <thead><tr><th>Penyesuaian</th><th>Waktu</th><th>SKU / Produk</th><th>Room</th><th>Alasan</th><th>Sistem</th><th>Fisik</th><th>Selisih</th><th>Diproses</th><th class="category-table__actions-heading">Aksi</th></tr></thead>
                    <tbody>
                        @foreach($adjustments as $adjustment)
                            <tr>
                                <td><span class="movement-transaction">{{ $adjustment->adjustment_number }}</span><small class="category-table-secondary">{{ $adjustment->stockMovement?->transaction_number ?? '-' }}</small></td>
                                <td><span class="category-table-primary">{{ $adjustment->adjustment_date?->format('d/m/Y') }}</span><small class="category-table-secondary">{{ $adjustment->adjustment_date?->format('H:i') }}</small></td>
                                <td><div class="stock-product-cell"><span class="category-code-badge">{{ $adjustment->productVariant?->sku ?? '-' }}</span><span><strong>{{ $adjustment->productVariant?->product?->name ?? '-' }}</strong><small>{{ $adjustment->productVariant?->color?->name ?? '-' }} / {{ $adjustment->productVariant?->size?->name ?? '-' }}</small></span></div></td>
                                <td><span class="category-table-primary">{{ $adjustment->warehouse?->name ?? '-' }}</span><small class="category-table-secondary">{{ $adjustment->warehouse?->code ?? '-' }}</small></td>
                                <td><span class="category-table-primary">{{ $adjustment->reasonLabel() }}</span></td>
                                <td><strong>{{ number_format($adjustment->system_quantity) }}</strong></td>
                                <td><strong>{{ number_format($adjustment->physical_quantity) }}</strong></td>
                                <td><strong class="movement-quantity movement-quantity--{{ $adjustment->direction() }}">{{ $adjustment->differenceSign() }}{{ number_format(abs($adjustment->difference_quantity)) }}</strong></td>
                                <td><span class="category-table-primary">{{ $adjustment->processedBy?->name ?? 'Sistem' }}</span><small class="category-table-secondary">{{ ucfirst($adjustment->processedBy?->role ?? 'system') }}</small></td>
                                <td><div class="category-actions"><a href="{{ route('admin.stock-adjustments.show',$adjustment) }}" class="category-action-button category-action-button--enable" title="Detail penyesuaian"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></a></div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($adjustments->hasPages())<div class="category-pagination">{{ $adjustments->links() }}</div>@endif
        @endif
    </section>

    <section class="category-policy glass-panel"><span class="category-policy__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg></span><div><strong>Penyesuaian tidak dapat diedit</strong><p>Jika hasil stock opname salah, buat penyesuaian baru sebagai koreksi. Ledger lama tetap dipertahankan agar audit persediaan tidak terputus.</p></div></section>
@endsection
