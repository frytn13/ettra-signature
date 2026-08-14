@extends('layouts.admin')
@section('title', 'Retur Pelanggan Baru')
@section('eyebrow', 'Penjualan')
@section('page-title', 'Retur Pelanggan Baru')
@section('content')
<div class="compact-page">
    <section class="compact-page__head"><div><p class="dashboard-heading__eyebrow">Input Retur</p><h2>Retur Pelanggan Baru</h2><p>Pilih transaksi lunas dan jumlah item yang dikembalikan.</p></div></section>
    @if($errors->any())<div class="user-alert user-alert--danger">{{ $errors->first() }}</div>@endif
    <form action="{{ route('admin.sales-returns.store') }}" method="POST" class="compact-form" data-customer-return-form>
        @csrf
        <div class="compact-form-grid">
            <label class="compact-form-field compact-form-field--full"><span>Transaksi Lunas *</span><select name="sales_order_id" data-return-order required><option value="">Pilih transaksi</option>@foreach($orders as $order)<option value="{{ $order->id }}" @selected((string)old('sales_order_id',$selectedOrderId)===(string)$order->id)>{{ $order->transaction_number }} · {{ $order->customer_name }} · Rp{{ number_format((float)$order->grand_total,0,',','.') }}</option>@endforeach</select></label>
            <label class="compact-form-field"><span>Tanggal Retur *</span><input type="datetime-local" name="return_date" value="{{ old('return_date', now()->format('Y-m-d\TH:i')) }}" required></label>
            <label class="compact-form-field"><span>Alasan *</span><input type="text" name="reason" value="{{ old('reason') }}" maxlength="180" placeholder="Contoh: ukuran tidak sesuai" required></label>
            <label class="compact-form-field compact-form-field--full"><span>Catatan</span><textarea name="notes" placeholder="Catatan tambahan jika diperlukan">{{ old('notes') }}</textarea></label>
            <label class="compact-form-field compact-form-field--full return-refund-check"><span><input type="checkbox" name="refund_requested" value="1" @checked(old('refund_requested'))> Tandai perlu proses refund</span><small class="compact-form-help">Refund keuangan akan diselesaikan pada modul Keuangan oleh tahap pengembangan berikutnya.</small></label>
        </div>

        <div class="compact-card return-items-card">
            <div class="compact-card__head"><div><h3>Item yang Dikembalikan</h3><p>Isi jumlah retur. Barang rusak tetap masuk secara fisik, tetapi langsung tercatat sebagai stok rusak.</p></div></div>
            <div class="return-order-items">
                @foreach($orders as $order)
                    <div class="return-order-block" data-return-order-block="{{ $order->id }}" hidden>
                        @foreach($order->items as $index => $item)
                            <div class="return-item-row">
                                <input type="hidden" name="items[{{ $order->id }}_{{ $index }}][sales_order_item_id]" value="{{ $item->id }}" disabled data-return-input>
                                <div><strong>{{ $item->sku_snapshot }}</strong><small>{{ $item->product_name_snapshot }} · {{ $item->variant_snapshot ?: 'Tanpa variasi' }} · Dibeli {{ $item->quantity }} unit</small></div>
                                <label><span>Jumlah</span><input type="number" name="items[{{ $order->id }}_{{ $index }}][quantity]" min="0" max="{{ $item->quantity }}" value="0" disabled data-return-input></label>
                                <label><span>Kondisi</span><select name="items[{{ $order->id }}_{{ $index }}][condition]" disabled data-return-input><option value="sellable">Layak jual</option><option value="damaged">Rusak</option></select></label>
                            </div>
                        @endforeach
                    </div>
                @endforeach
                <p class="compact-empty" data-return-empty>Pilih transaksi terlebih dahulu.</p>
            </div>
        </div>

        <div class="compact-form-actions"><a href="{{ route('admin.sales-returns.index') }}" class="button button--ghost">Batal</a><button type="submit" class="button button--primary">Proses Retur</button></div>
    </form>
</div>
@endsection
