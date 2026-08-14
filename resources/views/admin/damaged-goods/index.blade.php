@extends('layouts.admin')
@section('title','Barang Rusak')
@section('eyebrow','Produk & Persediaan')
@section('page-title','Barang Rusak')
@section('content')
<section class="dashboard-heading compact-page-heading"><div><p class="dashboard-heading__eyebrow">Kualitas Persediaan</p><h2>Barang rusak dan pemulihan</h2><p>Catat barang yang tidak layak jual atau pulihkan barang yang sudah dinyatakan layak. Stok tersedia berubah tanpa menghapus histori.</p></div><a href="{{ route('admin.damaged-goods.create') }}" class="button button--primary" data-modal-form data-modal-title="Catat Barang Rusak">+ Catat</a></section>
<form method="GET" action="{{ route('admin.damaged-goods.index') }}" class="compact-filter-bar glass-panel" data-ajax-filter>
<input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Nomor transaksi atau SKU">
<select name="warehouse"><option value="">Semua room</option>@foreach($warehouses as $w)<option value="{{ $w->id }}" @selected((string)$filters['warehouse']===(string)$w->id)>{{ $w->code }} · {{ $w->name }}</option>@endforeach</select>
<select name="action"><option value="">Semua aksi</option><option value="mark_damaged" @selected($filters['action']==='mark_damaged')>Tandai Rusak</option><option value="recover" @selected($filters['action']==='recover')>Pemulihan</option></select><button class="button button--secondary">Terapkan</button></form>
<section class="glass-panel compact-table-card"><div class="table-scroll"><table class="compact-table"><thead><tr><th>Transaksi</th><th>Produk / SKU</th><th>Room</th><th>Aksi</th><th>Jumlah</th><th>Stok Rusak</th><th>Petugas</th><th></th></tr></thead><tbody>
@forelse($records as $r)<tr><td><strong>{{ $r->transaction_number }}</strong><small>{{ $r->transaction_date?->format('d M Y H:i') }}</small></td><td><strong>{{ $r->productVariant?->product?->name }}</strong><small>{{ $r->productVariant?->sku }}</small></td><td>{{ $r->warehouse?->name }}</td><td><span class="status-pill {{ $r->action==='recover'?'status-pill--success':'status-pill--danger' }}">{{ $r->actionLabel() }}</span></td><td>{{ number_format($r->quantity) }}</td><td>{{ $r->damaged_before }} → {{ $r->damaged_after }}</td><td>{{ $r->processedBy?->name ?? '-' }}</td><td><a class="table-action-link" href="{{ route('admin.damaged-goods.show',$r) }}">Detail</a></td></tr>@empty<tr><td colspan="8"><div class="empty-state">Belum ada transaksi barang rusak.</div></td></tr>@endforelse
</tbody></table></div><div class="compact-pagination">{{ $records->links() }}</div></section>
@endsection
