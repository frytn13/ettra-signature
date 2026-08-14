@extends('layouts.admin')
@section('title', 'Detail Retur ' . $salesReturn->return_number)
@section('eyebrow', 'Penjualan')
@section('page-title', 'Detail Retur Pelanggan')
@section('content')
<div class="compact-page">
    <section class="compact-page__head"><div><p class="dashboard-heading__eyebrow">{{ $salesReturn->return_number }}</p><h2>Detail Retur Pelanggan</h2><p>Riwayat retur bersifat read-only untuk menjaga audit stok.</p></div><a href="{{ route('admin.sales-returns.index') }}" class="button button--secondary">Kembali</a></section>
    <section class="compact-card"><div class="compact-card__head"><div><h3>Informasi Retur</h3></div></div><div class="detail-grid" style="padding:1rem">
        <div class="detail-item"><small>Transaksi</small><strong>{{ $salesReturn->order?->transaction_number ?? '-' }}</strong></div>
        <div class="detail-item"><small>Pelanggan</small><strong>{{ $salesReturn->order?->customer_name ?? '-' }}</strong></div>
        <div class="detail-item"><small>Room</small><strong>{{ $salesReturn->warehouse?->name ?? '-' }}</strong></div>
        <div class="detail-item"><small>Tanggal</small><strong>{{ $salesReturn->return_date?->format('d/m/Y H:i') }}</strong></div>
        <div class="detail-item"><small>Nilai Retur</small><strong>Rp{{ number_format((float)$salesReturn->refund_amount,0,',','.') }}</strong></div>
        <div class="detail-item"><small>Status Refund</small><strong>{{ $salesReturn->refundStatusLabel() }}</strong></div>
        <div class="detail-item"><small>Alasan</small><span>{{ $salesReturn->reason }}</span></div>
        <div class="detail-item"><small>Diproses Oleh</small><span>{{ $salesReturn->processedBy?->name ?? '-' }}</span></div>
        <div class="detail-item"><small>Catatan</small><span>{{ $salesReturn->notes ?: '-' }}</span></div>
    </div></section>
    <section class="compact-card"><div class="compact-card__head"><div><h3>Item Retur</h3></div></div><div class="compact-table-wrap"><table class="compact-table"><thead><tr><th>SKU</th><th>Produk</th><th>Jumlah</th><th>Kondisi</th><th>Nilai/unit</th></tr></thead><tbody>@foreach($salesReturn->items as $item)<tr><td><strong>{{ $item->orderItem?->sku_snapshot ?? '-' }}</strong></td><td>{{ $item->orderItem?->product_name_snapshot ?? '-' }}<br><small>{{ $item->orderItem?->variant_snapshot }}</small></td><td>{{ number_format($item->quantity) }}</td><td><span class="status-pill status-pill--{{ $item->condition === 'damaged' ? 'red' : 'green' }}">{{ $item->condition === 'damaged' ? 'Rusak' : 'Layak jual' }}</span></td><td>Rp{{ number_format((float)$item->unit_refund_amount,0,',','.') }}</td></tr>@endforeach</tbody></table></div></section>
</div>
@endsection
