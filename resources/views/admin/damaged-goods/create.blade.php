@extends('layouts.admin')
@section('title','Catat Barang Rusak')
@section('eyebrow','Produk & Persediaan')
@section('page-title','Catat Barang Rusak')
@section('content')
<section class="dashboard-heading category-form-heading"><div><p class="dashboard-heading__eyebrow">Transaksi Persediaan</p><h2>Barang rusak / pemulihan</h2><p>Transaksi diproses langsung ke stok room dan ledger mutasi.</p></div></section>
@if($errors->any())<div class="user-alert user-alert--danger">Periksa kembali data yang diisi.</div>@endif
<form action="{{ route('admin.damaged-goods.store') }}" method="POST" class="modal-friendly-form"><div class="category-form-card glass-panel"><div class="category-form-grid">
@csrf
<label class="category-form-field category-form-field--full"><span>Titik Stok <em>*</em></span><select name="warehouse_stock_id" required><option value="">Pilih room dan SKU</option>@foreach($stocks as $s)<option value="{{ $s->id }}" @selected((string)old('warehouse_stock_id',$selectedStockId)===(string)$s->id)>{{ $s->warehouse?->name }} · {{ $s->productVariant?->sku }} · {{ $s->productVariant?->product?->name }} · tersedia {{ $s->availableQuantity() }} · rusak {{ $s->quantity_damaged }}</option>@endforeach</select>@error('warehouse_stock_id')<span class="category-field-error">{{ $message }}</span>@enderror</label>
<label class="category-form-field"><span>Aksi <em>*</em></span><select name="action" required><option value="mark_damaged" @selected(old('action')==='mark_damaged')>Tandai sebagai rusak</option><option value="recover" @selected(old('action')==='recover')>Pulihkan ke stok layak jual</option></select>@error('action')<span class="category-field-error">{{ $message }}</span>@enderror</label>
<label class="category-form-field"><span>Jumlah <em>*</em></span><input type="number" min="1" name="quantity" value="{{ old('quantity',1) }}" required>@error('quantity')<span class="category-field-error">{{ $message }}</span>@enderror</label>
<label class="category-form-field"><span>Alasan <em>*</em></span><select name="reason" required><option value="cacat_produk">Cacat produk</option><option value="penyimpanan">Rusak saat penyimpanan</option><option value="noda">Noda</option><option value="sobek">Sobek</option><option value="warna">Kerusakan warna</option><option value="pemeriksaan_ulang">Pemeriksaan ulang</option><option value="lainnya">Lainnya</option></select></label>
<label class="category-form-field"><span>Tanggal <em>*</em></span><input type="datetime-local" name="transaction_date" value="{{ old('transaction_date',now()->format('Y-m-d\TH:i')) }}" required></label>
<label class="category-form-field category-form-field--full"><span>Catatan</span><textarea name="notes" rows="4" placeholder="Kondisi barang atau informasi tambahan">{{ old('notes') }}</textarea></label>
</div></div><div class="modal-form-actions"><button type="button" class="button button--secondary" data-data-modal-close>Batal</button><button type="submit" class="button button--primary">Simpan Transaksi</button></div></form>
@endsection
