@extends('layouts.admin')

@section('title', 'Mutasi Stok')
@section('eyebrow', 'Persediaan')
@section('page-title', 'Mutasi Stok')

@section('content')
    <section class="dashboard-heading category-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Inventory Ledger</p>
            <h2>Riwayat seluruh pergerakan stok</h2>
            <p>Setiap perubahan stok dicatat sebagai transaksi yang tidak diedit atau dihapus. Koreksi dilakukan melalui transaksi baru agar histori inventory tetap dapat diaudit.</p>
        </div>
        <div class="dashboard-heading__actions">
            <a href="{{ route('admin.warehouse-stocks.index') }}" class="button button--secondary">Stok Room</a>
            <a href="{{ route('admin.stock-movements.create') }}" class="button button--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Catat Mutasi
            </a>
        </div>
    </section>

    @if (session('success'))
        <div class="user-alert user-alert--success" role="status"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span><span>{{ session('success') }}</span></div>
    @endif

    <section class="movement-summary-grid" aria-label="Ringkasan mutasi stok">
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 7h11l-3-3M17 17H6l3 3"/><path d="M18 7 15 4M6 17l3 3"/></svg></span><span class="category-summary-card__copy"><small>Total Mutasi</small><strong>{{ number_format($statistics['records']) }}</strong><span>Seluruh transaksi tercatat</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20V4M6 10l6-6 6 6"/></svg></span><span class="category-summary-card__copy"><small>Total Barang Masuk</small><strong>{{ number_format($statistics['quantity_in']) }}</strong><span>Akumulasi unit masuk</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 4v16M6 14l6 6 6-6"/></svg></span><span class="category-summary-card__copy"><small>Total Barang Keluar</small><strong>{{ number_format($statistics['quantity_out']) }}</strong><span>Akumulasi unit keluar</span></span></article>
        <article class="category-summary-card glass-panel"><span class="category-summary-card__icon category-summary-card__icon--muted"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><span class="category-summary-card__copy"><small>Hari Ini</small><strong>{{ number_format($statistics['today']) }}</strong><span>Transaksi pada {{ now()->format('d/m/Y') }}</span></span></article>
    </section>

    <section class="category-management-card glass-panel">
        <div class="category-management-card__header">
            <div><p class="dashboard-heading__eyebrow">Ledger Persediaan</p><h3>Daftar mutasi stok</h3><p>Gunakan filter untuk menelusuri pergerakan berdasarkan SKU, room, jenis mutasi, pengguna, atau periode.</p></div>
            <div class="category-examples"><span>Read only</span><span>Audit ready</span><span>DB transaction</span></div>
        </div>

        <form action="{{ route('admin.stock-movements.index') }}" method="GET" class="movement-filter-form">
            <label class="category-filter-field category-filter-field--search"><span class="sr-only">Cari mutasi</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Nomor transaksi, SKU, produk, room..." autocomplete="off"></label>
            <label class="category-filter-field"><span class="sr-only">Room</span><select name="warehouse"><option value="">Semua room</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected($filters['warehouse'] === (string)$warehouse->id)>{{ $warehouse->code }} · {{ $warehouse->name }}</option>@endforeach</select></label>
            <label class="category-filter-field"><span class="sr-only">Jenis mutasi</span><select name="type"><option value="">Semua jenis</option>@foreach($movementTypes as $value => $label)<option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>@endforeach</select></label>
            <label class="category-filter-field"><span class="sr-only">Tanggal awal</span><input type="date" name="date_from" value="{{ $filters['date_from'] }}" title="Tanggal awal"></label>
            <label class="category-filter-field"><span class="sr-only">Tanggal akhir</span><input type="date" name="date_to" value="{{ $filters['date_to'] }}" title="Tanggal akhir"></label>
            <button type="submit" class="button button--secondary button--small">Terapkan</button>
            @if(collect($filters)->filter(fn($value) => $value !== '')->isNotEmpty())<a href="{{ route('admin.stock-movements.index') }}" class="button button--ghost button--small">Reset</a>@endif
        </form>

        @if($movements->isEmpty())
            <div class="category-empty-state"><span class="category-empty-state__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 7h11l-3-3M17 17H6l3 3"/><path d="M18 7 15 4M6 17l3 3"/></svg></span><h4>Belum ada mutasi stok</h4><p>Catat transaksi stok masuk atau stok keluar pertama. Setiap transaksi akan memperbarui saldo room secara otomatis.</p><a href="{{ route('admin.stock-movements.create') }}" class="button button--primary button--small">Catat Mutasi</a></div>
        @else
            <div class="responsive-table-wrap movement-desktop-table">
                <table class="admin-table category-table movement-table">
                    <thead><tr><th>Transaksi</th><th>Waktu</th><th>SKU / Produk</th><th>Room</th><th>Jenis</th><th>Jumlah</th><th>Fisik</th><th>Tersedia</th><th>Diproses</th><th class="category-table__actions-heading">Aksi</th></tr></thead>
                    <tbody>
                        @foreach($movements as $movement)
                            <tr>
                                <td><span class="movement-transaction">{{ $movement->transaction_number }}</span></td>
                                <td><span class="category-table-primary">{{ $movement->movement_date?->format('d/m/Y') }}</span><small class="category-table-secondary">{{ $movement->movement_date?->format('H:i') }}</small></td>
                                <td><div class="stock-product-cell"><span class="category-code-badge">{{ $movement->productVariant?->sku ?? '-' }}</span><span><strong>{{ $movement->productVariant?->product?->name ?? '-' }}</strong><small>{{ $movement->productVariant?->color?->name ?? '-' }} / {{ $movement->productVariant?->size?->name ?? '-' }}</small></span></div></td>
                                <td><span class="category-table-primary">{{ $movement->warehouse?->name ?? '-' }}</span><small class="category-table-secondary">{{ $movement->warehouse?->code ?? '-' }}</small></td>
                                <td><span class="movement-type movement-type--{{ $movement->direction }}">{{ $movement->typeLabel() }}</span></td>
                                <td><strong class="movement-quantity movement-quantity--{{ $movement->direction }}">{{ $movement->directionSign() }}{{ number_format($movement->quantity) }}</strong></td>
                                <td><span class="movement-snapshot">{{ number_format($movement->quantity_before) }} → {{ number_format($movement->quantity_after) }}</span></td>
                                <td><span class="movement-snapshot">{{ number_format($movement->quantity_available_before) }} → {{ number_format($movement->quantity_available_after) }}</span></td>
                                <td><span class="category-table-primary">{{ $movement->performedBy?->name ?? 'Sistem' }}</span><small class="category-table-secondary">{{ ucfirst($movement->performedBy?->role ?? 'system') }}</small></td>
                                <td><div class="category-actions"><a href="{{ route('admin.stock-movements.show',$movement) }}" class="category-action-button category-action-button--enable" title="Detail mutasi"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></a></div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="movement-mobile-list">
                @foreach($movements as $movement)
                    <article class="movement-mobile-card"><div class="movement-mobile-card__head"><div><span class="movement-transaction">{{ $movement->transaction_number }}</span><h4>{{ $movement->productVariant?->sku ?? '-' }} · {{ $movement->productVariant?->product?->name ?? '-' }}</h4><p>{{ $movement->warehouse?->name ?? '-' }} · {{ $movement->movement_date?->format('d/m/Y H:i') }}</p></div><span class="movement-type movement-type--{{ $movement->direction }}">{{ $movement->typeLabel() }}</span></div><div class="movement-mobile-card__numbers"><div><small>Jumlah</small><strong class="movement-quantity movement-quantity--{{ $movement->direction }}">{{ $movement->directionSign() }}{{ number_format($movement->quantity) }}</strong></div><div><small>Fisik</small><strong>{{ number_format($movement->quantity_before) }} → {{ number_format($movement->quantity_after) }}</strong></div><div><small>Tersedia</small><strong>{{ number_format($movement->quantity_available_before) }} → {{ number_format($movement->quantity_available_after) }}</strong></div></div><div class="category-mobile-card__actions"><span class="movement-mobile-card__actor">{{ $movement->performedBy?->name ?? 'Sistem' }}</span><a href="{{ route('admin.stock-movements.show',$movement) }}" class="button button--ghost button--small">Detail</a></div></article>
                @endforeach
            </div>

            @if($movements->hasPages())<div class="category-pagination">{{ $movements->links() }}</div>@endif
        @endif
    </section>

    <section class="category-policy glass-panel"><span class="category-policy__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg></span><div><strong>Ledger mutasi bersifat permanen</strong><p>Transaksi yang sudah tercatat tidak menyediakan fungsi edit atau hapus. Jika terjadi kesalahan, lakukan transaksi koreksi baru agar jejak audit stok tetap lengkap.</p></div></section>
@endsection
