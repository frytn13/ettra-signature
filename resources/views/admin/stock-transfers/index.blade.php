@extends('layouts.admin')
@section('title','Transfer Room')
@section('eyebrow','Produk & Persediaan')
@section('page-title','Transfer Room')
@section('content')
<section class="dashboard-heading compact-page-heading"><div><p class="dashboard-heading__eyebrow">Perpindahan Stok</p><h2>Transfer antar room</h2><p>Pindahkan satu atau beberapa SKU dengan histori keluar dan masuk yang tercatat otomatis.</p></div><a href="{{ route('admin.stock-transfers.create') }}" class="button button--primary" data-modal-form data-modal-title="Transfer Room">+ Transfer</a></section>
<form method="GET" action="{{ route('admin.stock-transfers.index') }}" class="compact-filter-bar glass-panel" data-ajax-filter><input type="search" name="search" value="{{ $search }}" placeholder="Nomor transfer atau nama room"><button class="button button--secondary">Cari</button></form>
<section class="glass-panel compact-table-card"><div class="table-scroll"><table class="compact-table"><thead><tr><th>Nomor</th><th>Room Asal</th><th>Room Tujuan</th><th>SKU</th><th>Tanggal</th><th>Petugas</th><th></th></tr></thead><tbody>@forelse($transfers as $t)<tr><td><strong>{{ $t->transfer_number }}</strong></td><td>{{ $t->sourceWarehouse?->name }}</td><td>{{ $t->destinationWarehouse?->name }}</td><td>{{ $t->items_count }} item</td><td>{{ $t->transfer_date?->format('d M Y H:i') }}</td><td>{{ $t->processedBy?->name ?? '-' }}</td><td><a class="table-action-link" href="{{ route('admin.stock-transfers.show',$t) }}">Detail</a></td></tr>@empty<tr><td colspan="7"><div class="empty-state">Belum ada transfer room.</div></td></tr>@endforelse</tbody></table></div><div class="compact-pagination">{{ $transfers->links() }}</div></section>
@endsection
