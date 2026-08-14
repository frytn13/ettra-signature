@extends('layouts.admin')

@section('title', 'Tambah Produk')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Tambah Produk')

@section('content')
    <section class="dashboard-heading category-form-heading">
        <div><p class="dashboard-heading__eyebrow">Produk Baru</p><h2>Tambahkan produk</h2><p>Isi informasi dasar produk. Kode dapat dikosongkan agar sistem membentuk kode otomatis dari kategori.</p></div>
        <div class="dashboard-heading__actions"><a href="{{ route('admin.products.index') }}" class="button button--secondary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6"/></svg>Kembali</a></div>
    </section>

    @if ($errors->any())
        <div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>Periksa kembali data produk. Terdapat {{ $errors->count() }} bagian yang perlu diperbaiki.</span></div>
    @endif

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="product-form-layout">
        @csrf
        @include('admin.products.partials.form', ['product' => $product, 'submitLabel' => 'Simpan Produk', 'isEditing' => false])
    </form>
@endsection
