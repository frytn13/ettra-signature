@extends('layouts.admin')
@section('title', 'Retur Pelanggan')
@section('eyebrow', 'Penjualan')
@section('page-title', 'Retur Pelanggan')
@section('content')
<div class="compact-page">
    <section class="compact-page__head">
        <div><p class="dashboard-heading__eyebrow">Retur Penjualan</p><h2>Retur Pelanggan</h2><p>Proses barang kembali dari transaksi lunas. Stok dan riwayat inventory diperbarui otomatis.</p></div>
        <a href="{{ route('admin.sales-returns.create') }}" class="button button--primary" data-modal-form data-modal-title="Retur Pelanggan Baru">+ Retur Baru</a>
    </section>

    <section class="compact-card">
        <form action="{{ route('admin.sales-returns.index') }}" method="GET" class="compact-filters" data-ajax-filter>
            <input type="search" name="search" value="{{ $search }}" placeholder="Cari nomor retur, transaksi, pelanggan...">
            <select name="refund_status"><option value="">Semua status refund</option><option value="not_required" @selected($status==='not_required')>Tidak ada refund</option><option value="pending" @selected($status==='pending')>Menunggu refund</option><option value="completed" @selected($status==='completed')>Refund selesai</option></select>
            <button class="button button--secondary" type="submit">Terapkan</button>
            <a href="{{ route('admin.sales-returns.index') }}" class="button button--ghost">Reset</a>
        </form>
        <div class="compact-table-wrap">
            <table class="compact-table">
                <thead><tr><th>Nomor Retur</th><th>Transaksi</th><th>Pelanggan</th><th>Room</th><th>Nilai Retur</th><th>Status Refund</th><th>Tanggal</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($returns as $return)
                        <tr>
                            <td><strong>{{ $return->return_number }}</strong></td>
                            <td>{{ $return->order?->transaction_number ?? '-' }}</td>
                            <td>{{ $return->order?->customer_name ?? '-' }}</td>
                            <td>{{ $return->warehouse?->name ?? '-' }}</td>
                            <td>Rp{{ number_format((float)$return->refund_amount,0,',','.') }}</td>
                            <td><span class="status-pill status-pill--{{ $return->refund_status === 'completed' ? 'green' : ($return->refund_status === 'pending' ? 'peach' : 'muted') }}">{{ $return->refundStatusLabel() }}</span></td>
                            <td>{{ $return->return_date?->format('d/m/Y H:i') }}</td>
                            <td><a href="{{ route('admin.sales-returns.show',$return) }}" class="compact-action">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="compact-empty">Belum ada retur pelanggan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($returns->hasPages())<div class="category-pagination">{{ $returns->links() }}</div>@endif
    </section>
</div>
@endsection
