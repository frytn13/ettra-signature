@extends('layouts.admin')

@section('title', 'Generate Variasi Produk')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Generate Variasi')

@section('content')
    <section class="dashboard-heading category-form-heading"><div><p class="dashboard-heading__eyebrow">Generator Variasi</p><h2>Buat kombinasi secara massal</h2><p>Pilih beberapa warna dan ukuran. Sistem membuat seluruh kombinasi yang belum ada dan melewati kombinasi yang sudah tersedia atau pernah diarsipkan.</p></div><div class="dashboard-heading__actions"><a href="{{ route('admin.product-variants.index') }}" class="button button--secondary">Kembali</a></div></section>

    @if($errors->any())<div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>Periksa kembali produk, warna, ukuran, atau nilai tambahan yang dipilih.</span></div>@endif

    <form action="{{ route('admin.product-variants.generate') }}" method="POST" class="variant-generator-layout" data-variant-generator>
        @csrf
        <div class="variant-generator-main">
            <section class="category-form-card glass-panel">
                <div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/></svg></span><div><p class="dashboard-heading__eyebrow">Produk Dasar</p><h3>Pilih satu produk</h3><p>SKU setiap kombinasi akan diawali kode produk yang dipilih.</p></div></div>
                <label class="category-form-field"><span>Produk <em>*</em></span><select name="product_id" required data-generator-product><option value="">Pilih produk</option>@foreach($products as $product)<option value="{{ $product->id }}" data-code="{{ $product->code }}" data-price="{{ (float)$product->selling_price }}" data-weight="{{ $product->weight_grams }}" @selected((string)old('product_id',$selectedProductId)===(string)$product->id)>{{ $product->code }} · {{ $product->name }}</option>@endforeach</select>@error('product_id')<span class="category-field-error">{{ $message }}</span>@enderror</label>
            </section>

            <section class="category-form-card glass-panel">
                <div class="variant-generator-section-head"><div><p class="dashboard-heading__eyebrow">Warna</p><h3>Pilih warna</h3></div><button type="button" class="button button--ghost button--small" data-variant-select-all="color">Pilih Semua</button></div>
                <div class="variant-choice-grid" data-variant-choice-group="color">
                    @foreach($colors as $color)
                        <label class="variant-choice-card"><input type="checkbox" name="color_ids[]" value="{{ $color->id }}" data-variant-choice="color" data-code="{{ $color->code }}" @checked(in_array((string)$color->id,array_map('strval',(array)old('color_ids',[])),true))><span class="variant-choice-card__surface"><span class="master-color-swatch {{ $color->hex_code ? '' : 'master-color-swatch--empty' }}" @if($color->hex_code) style="background-color:{{ $color->hex_code }}" @endif></span><span><strong>{{ $color->name }}</strong><small>{{ $color->code }}</small></span></span></label>
                    @endforeach
                </div>
                @error('color_ids')<span class="category-field-error">{{ $message }}</span>@enderror @error('color_ids.*')<span class="category-field-error">{{ $message }}</span>@enderror
            </section>

            <section class="category-form-card glass-panel">
                <div class="variant-generator-section-head"><div><p class="dashboard-heading__eyebrow">Ukuran</p><h3>Pilih ukuran</h3></div><button type="button" class="button button--ghost button--small" data-variant-select-all="size">Pilih Semua</button></div>
                <div class="variant-choice-grid variant-choice-grid--sizes" data-variant-choice-group="size">
                    @foreach($sizes as $size)
                        <label class="variant-choice-card"><input type="checkbox" name="size_ids[]" value="{{ $size->id }}" data-variant-choice="size" data-code="{{ $size->code }}" @checked(in_array((string)$size->id,array_map('strval',(array)old('size_ids',[])),true))><span class="variant-choice-card__surface variant-choice-card__surface--size"><strong>{{ $size->code }}</strong><small>{{ $size->name }}</small></span></label>
                    @endforeach
                </div>
                @error('size_ids')<span class="category-field-error">{{ $message }}</span>@enderror @error('size_ids.*')<span class="category-field-error">{{ $message }}</span>@enderror
            </section>

            <section class="category-form-card glass-panel">
                <div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8M12 8v8"/></svg></span><div><p class="dashboard-heading__eyebrow">Nilai Bersama</p><h3>Harga, berat, dan status</h3><p>Nilai berikut diterapkan ke semua kombinasi yang dibuat pada proses ini.</p></div></div>
                <div class="category-form-grid"><label class="category-form-field"><span>Tambahan Harga <em>*</em></span><input type="number" name="additional_price" value="{{ old('additional_price',0) }}" min="0" step="1" required data-generator-additional-price>@error('additional_price')<span class="category-field-error">{{ $message }}</span>@enderror</label><label class="category-form-field"><span>Berat Khusus (gram)</span><input type="number" name="weight_grams" value="{{ old('weight_grams') }}" min="1" step="1" placeholder="Kosong = ikut produk">@error('weight_grams')<span class="category-field-error">{{ $message }}</span>@enderror</label></div>
                <div class="category-status-options"><label class="category-status-option"><input type="radio" name="is_active" value="1" @checked((string)old('is_active','1')==='1')><span class="category-status-option__surface"><span class="category-status-option__icon category-status-option__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg></span><span><strong>Aktif</strong><small>Semua kombinasi langsung aktif.</small></span></span></label><label class="category-status-option"><input type="radio" name="is_active" value="0" @checked((string)old('is_active','1')==='0')><span class="category-status-option__surface"><span class="category-status-option__icon category-status-option__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg></span><span><strong>Nonaktif</strong><small>Dibuat untuk persiapan data.</small></span></span></label></div>
            </section>
        </div>

        <aside class="variant-generator-side">
            <section class="variant-generator-summary glass-panel"><p class="dashboard-heading__eyebrow">Ringkasan Generator</p><span class="variant-generator-summary__number" data-variant-combination-count>0</span><strong>kombinasi dipilih</strong><p><span data-variant-color-count>0</span> warna × <span data-variant-size-count>0</span> ukuran</p><div class="variant-generator-price"><small>Estimasi harga akhir</small><strong data-generator-final-price>Rp0</strong></div><small>Maksimal 100 kombinasi per proses.</small></section>
            <section class="category-form-note glass-panel"><span class="category-form-note__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></svg></span><div><strong>Kombinasi duplikat dilewati</strong><p>Generator tidak menimpa variasi yang sudah ada maupun yang pernah diarsipkan.</p></div></section>
            <div class="category-form-actions glass-panel"><a href="{{ route('admin.product-variants.index') }}" class="button button--secondary">Batal</a><button type="submit" class="button button--primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M7 4v6M17 4v6M4 17h16M7 14v6M17 14v6"/></svg>Generate Variasi</button></div>
        </aside>
    </form>
@endsection
