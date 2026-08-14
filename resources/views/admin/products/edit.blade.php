@extends('layouts.admin')

@section('title', 'Edit Produk')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Edit Produk')

@section('content')
    <section class="dashboard-heading category-form-heading">
        <div><p class="dashboard-heading__eyebrow">Perbarui Produk</p><h2>{{ $product->name }}</h2><p>Perubahan data operasional dan komersial dicatat ke Activity Log sesuai role pengguna.</p></div>
        <div class="dashboard-heading__actions"><a href="{{ route('admin.products.show', $product) }}" class="button button--secondary">Detail</a><a href="{{ route('admin.products.index') }}" class="button button--ghost">Kembali</a></div>
    </section>

    @if ($errors->any())
        <div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>Periksa kembali data produk. Terdapat {{ $errors->count() }} bagian yang perlu diperbaiki.</span></div>
    @endif

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="product-form-layout">
        @csrf
        @method('PUT')
        @include('admin.products.partials.form', ['product' => $product, 'submitLabel' => 'Simpan Perubahan', 'isEditing' => true])
    </form>
@endsection
