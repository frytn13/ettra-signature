@extends('layouts.admin')
@section('title','Detail Transfer')
@section('eyebrow','Produk & Persediaan')
@section('page-title','Detail Transfer Room')
@section('content')
<section class="dashboard-heading compact-page-heading"><div><p class="dashboard-heading__eyebrow">{{ $stockTransfer->transfer_number }}</p><h2>{{ $stockTransfer->sourceWarehouse?->name }} → {{ $stockTransfer->destinationWarehouse?->name }}</h2><p>{{ $stockTransfer->transfer_date?->format('d M Y H:i') }} · {{ $stockTransfer->processedBy?->name }}</p></div><a href="{{ route('admin.stock-transfers.index') }}" class="button button--secondary">Kembali</a></section>
<section class="glass-panel compact-table-card"><div class="table-scroll"><table class="compact-table"><thead><tr><th>SKU</th><th>Produk</th><th>Variasi</th><th>Jumlah</th></tr></thead><tbody>@foreach($stockTransfer->items as $item)<tr><td><strong>{{ $item->productVariant?->sku }}</strong></td><td>{{ $item->productVariant?->product?->name }}</td><td>{{ $item->productVariant?->color?->name }} / {{ $item->productVariant?->size?->name }}</td><td>{{ $item->quantity }} unit</td></tr>@endforeach</tbody></table></div></section>
@endsection
