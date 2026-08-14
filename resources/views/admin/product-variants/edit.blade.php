@extends('layouts.admin')

@section('title', 'Edit Variasi Produk')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Edit Variasi')

@section('content')
    <section class="dashboard-heading category-form-heading"><div><p class="dashboard-heading__eyebrow">Variasi Produk</p><h2>Edit {{ $productVariant->sku }}</h2><p>Perubahan kombinasi harus tetap unik. SKU dapat disesuaikan manual bila struktur kode internal berubah.</p></div><div class="dashboard-heading__actions"><a href="{{ route('admin.product-variants.index', ['product'=>$productVariant->product_id]) }}" class="button button--secondary">Kembali</a></div></section>

    @if($errors->any())<div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>Periksa kembali data variasi yang ditandai pada formulir.</span></div>@endif

    <form action="{{ route('admin.product-variants.update',$productVariant) }}" method="POST" class="category-form-layout" data-product-variant-form>
        @csrf @method('PUT')
        @include('admin.product-variants.partials.form', ['isEditing'=>true,'submitLabel'=>'Simpan Perubahan'])
    </form>
@endsection
