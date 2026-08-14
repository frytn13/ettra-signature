@extends('layouts.admin')

@section('title', 'Catat Mutasi Stok')
@section('eyebrow', 'Persediaan')
@section('page-title', 'Catat Mutasi Stok')

@section('content')
    <section class="dashboard-heading category-form-heading">
        <div><p class="dashboard-heading__eyebrow">Transaksi Inventory</p><h2>Catat stok masuk atau keluar</h2><p>Saldo stok dan ledger mutasi diperbarui dalam satu database transaction. Stok keluar tidak dapat melebihi stok tersedia.</p></div>
        <div class="dashboard-heading__actions"><a href="{{ route('admin.stock-movements.index') }}" class="button button--secondary">Kembali</a></div>
    </section>

    @if($errors->any())<div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>Periksa kembali form. Terdapat {{ $errors->count() }} bagian yang perlu diperbaiki.</span></div>@endif

    <form action="{{ route('admin.stock-movements.store') }}" method="POST" class="movement-form-layout">
        @csrf
        <div class="movement-form-main">
            <section class="category-form-card glass-panel">
                <div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/></svg></span><div><p class="dashboard-heading__eyebrow">Titik Stok</p><h3>Pilih SKU dan room</h3><p>Daftar hanya menampilkan titik stok pada room dan variasi produk yang masih aktif.</p></div></div>
                @if($stocks->isEmpty())
                    <div class="category-empty-state" style="padding:1rem"><h4>Belum ada titik stok aktif</h4><p>Daftarkan variasi produk ke Stok Room terlebih dahulu.</p><a href="{{ route('admin.warehouse-stocks.create') }}" class="button button--primary button--small">Daftarkan SKU</a></div>
                @else
                    <label class="category-form-field"><span>Titik Stok <em>*</em></span><select name="warehouse_stock_id" required><option value="">Pilih SKU dan room</option>@foreach($stocks as $stock)<option value="{{ $stock->id }}" @selected((string)old('warehouse_stock_id',$selectedStockId) === (string)$stock->id)>{{ $stock->warehouse?->code }} · {{ $stock->warehouse?->name }} | {{ $stock->productVariant?->sku }} · {{ $stock->productVariant?->product?->name }} | tersedia {{ number_format($stock->availableQuantity()) }}</option>@endforeach</select><small class="category-form-help">Stok keluar akan divalidasi terhadap stok tersedia pada saat transaksi disimpan.</small>@error('warehouse_stock_id')<small class="category-form-error">{{ $message }}</small>@enderror</label>
                @endif
            </section>

            <section class="category-form-card glass-panel">
                <div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 7h11l-3-3M17 17H6l3 3"/><path d="M18 7 15 4M6 17l3 3"/></svg></span><div><p class="dashboard-heading__eyebrow">Pergerakan</p><h3>Jenis dan jumlah mutasi</h3><p>Mutasi manual digunakan untuk stok masuk/keluar yang belum berasal dari modul transaksi khusus.</p></div></div>
                <div class="movement-form-grid">
                    <label class="category-form-field"><span>Jenis Mutasi <em>*</em></span><select name="movement_type" required><option value="">Pilih jenis mutasi</option>@foreach($movementTypes as $value => $label)<option value="{{ $value }}" @selected(old('movement_type') === $value)>{{ $label }}</option>@endforeach</select>@error('movement_type')<small class="category-form-error">{{ $message }}</small>@enderror</label>
                    <label class="category-form-field"><span>Jumlah Unit <em>*</em></span><input type="number" name="quantity" min="1" step="1" value="{{ old('quantity') }}" placeholder="Contoh: 10" required>@error('quantity')<small class="category-form-error">{{ $message }}</small>@enderror</label>
                    <label class="category-form-field"><span>Tanggal & Waktu <em>*</em></span><input type="datetime-local" name="movement_date" value="{{ old('movement_date',$defaultMovementDate) }}" max="{{ now()->format('Y-m-d\TH:i') }}" required>@error('movement_date')<small class="category-form-error">{{ $message }}</small>@enderror</label>
                </div>
                <label class="category-form-field"><span>Catatan / Alasan <em>*</em></span><textarea name="notes" rows="5" maxlength="1000" placeholder="Contoh: Saldo awal stok fisik hasil stock opname pembukaan sistem." required>{{ old('notes') }}</textarea><small class="category-form-help">Catatan wajib karena transaksi mutasi tidak dapat diedit atau dihapus setelah tersimpan.</small>@error('notes')<small class="category-form-error">{{ $message }}</small>@enderror</label>
            </section>
        </div>

        <aside class="movement-form-side">
            <section class="category-meta-card glass-panel"><p class="dashboard-heading__eyebrow">Aturan Mutasi</p><h3>Kontrol inventory</h3><dl><div><dt>Stok Masuk</dt><dd>Menambah quantity on hand.</dd></div><div><dt>Stok Keluar</dt><dd>Hanya mengambil stok yang benar-benar tersedia.</dd></div><div><dt>Reservasi</dt><dd>Tidak dapat terpakai oleh stok keluar manual.</dd></div><div><dt>Barang Rusak</dt><dd>Tetap dilindungi dan tidak dihitung tersedia.</dd></div></dl></section>
            <section class="category-form-note glass-panel"><span class="category-form-note__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg></span><div><strong>Atomic transaction</strong><p>Perubahan saldo dan pembuatan ledger disimpan dalam satu transaksi database. Jika salah satu proses gagal, keduanya dibatalkan.</p></div></section>
            <div class="category-form-actions glass-panel"><a href="{{ route('admin.stock-movements.index') }}" class="button button--ghost">Batal</a><button type="submit" class="button button--primary" @disabled($stocks->isEmpty())>Catat Mutasi</button></div>
        </aside>
    </form>
@endsection
