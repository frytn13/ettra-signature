@extends('layouts.admin')

@section('title', 'Daftarkan SKU ke Room')
@section('eyebrow', 'Persediaan')
@section('page-title', 'Daftarkan Stok')

@section('content')
    <section class="dashboard-heading category-form-heading"><div><p class="dashboard-heading__eyebrow">Penempatan SKU</p><h2>Daftarkan variasi ke room</h2><p>Pilih satu room dan satu atau beberapa SKU. Sistem hanya membuat titik stok dengan kuantitas 0, tanpa memanipulasi stok fisik.</p></div><div class="dashboard-heading__actions"><a href="{{ route('admin.warehouse-stocks.index') }}" class="button button--secondary">Kembali</a></div></section>

    @if($errors->any())<div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>Periksa kembali form. Terdapat {{ $errors->count() }} bagian yang perlu diperbaiki.</span></div>@endif

    <form action="{{ route('admin.warehouse-stocks.store') }}" method="POST" class="stock-register-layout">
        @csrf
        <div class="stock-register-main">
            <section class="category-form-card glass-panel">
                <div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 9 9-6 9 6v11H3V9Z"/><path d="M8 20v-7h8v7"/></svg></span><div><p class="dashboard-heading__eyebrow">Langkah 1</p><h3>Pilih room</h3><p>Hanya room aktif yang dapat menerima pendaftaran SKU baru.</p></div></div>
                <label class="category-form-field"><span>Room <em>*</em></span><select name="warehouse_id" required><option value="">Pilih room</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected((string)old('warehouse_id',$selectedWarehouseId) === (string)$warehouse->id)>{{ $warehouse->code }} · {{ $warehouse->name }}</option>@endforeach</select>@error('warehouse_id')<small class="category-form-error">{{ $message }}</small>@enderror</label>
            </section>

            <section class="category-form-card glass-panel">
                <div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/></svg></span><div><p class="dashboard-heading__eyebrow">Langkah 2</p><h3>Pilih variasi produk</h3><p>Gunakan Ctrl/Command untuk memilih beberapa SKU pada daftar berikut.</p></div></div>
                @if($variants->isEmpty())
                    <div class="category-empty-state" style="padding:1rem"><h4>Belum ada variasi aktif</h4><p>Buat Variasi Produk terlebih dahulu sebelum mendaftarkan stok ke room.</p></div>
                @else
                    <label class="category-form-field"><span>Variasi Produk <em>*</em></span><select name="product_variant_ids[]" multiple size="14" required class="stock-multi-select">@foreach($variants as $variant)<option value="{{ $variant->id }}" @selected(in_array((string)$variant->id, array_map('strval', old('product_variant_ids', [])), true))>{{ $variant->sku }} · {{ $variant->product?->name }} · {{ $variant->color?->name }} / {{ $variant->size?->name }}</option>@endforeach</select><small class="category-form-help">Maksimal 100 SKU per proses. SKU yang sudah terdaftar pada room terpilih akan dilewati otomatis.</small>@error('product_variant_ids')<small class="category-form-error">{{ $message }}</small>@enderror @error('product_variant_ids.*')<small class="category-form-error">{{ $message }}</small>@enderror</label>
                @endif
            </section>
        </div>

        <aside class="stock-register-side">
            <section class="category-meta-card glass-panel"><p class="dashboard-heading__eyebrow">Batas Minimum</p><h3>Atur peringatan stok</h3><label class="category-form-field"><span>Stok minimum <em>*</em></span><input type="number" name="minimum_stock" min="0" step="1" value="{{ old('minimum_stock',5) }}" required><small class="category-form-help">Status Menipis aktif saat stok tersedia ≤ batas ini.</small>@error('minimum_stock')<small class="category-form-error">{{ $message }}</small>@enderror</label></section>
            <section class="category-form-note glass-panel"><span class="category-form-note__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></svg></span><div><strong>Kuantitas awal selalu 0</strong><p>Penambahan stok fisik akan dilakukan melalui Mutasi Stok/Barang Masuk pada tahap berikutnya agar jejak perubahan tersimpan.</p></div></section>
            <div class="category-form-actions glass-panel"><a href="{{ route('admin.warehouse-stocks.index') }}" class="button button--ghost">Batal</a><button type="submit" class="button button--primary" @disabled($variants->isEmpty() || $warehouses->isEmpty())>Daftarkan SKU</button></div>
        </aside>
    </form>
@endsection
