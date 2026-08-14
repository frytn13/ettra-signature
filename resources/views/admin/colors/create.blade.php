@extends('layouts.admin')

@section('title', 'Tambah Warna')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Tambah Warna')

@section('content')
    <section class="dashboard-heading category-form-heading">
        <div><p class="dashboard-heading__eyebrow">Warna Baru</p><h2>Tambahkan master warna</h2><p>Gunakan kode singkat dan nama warna yang mudah dikenali. Kode HEX dapat ditambahkan untuk menampilkan preview visual.</p></div>
        <div class="dashboard-heading__actions"><a href="{{ route('admin.colors.index') }}" class="button button--secondary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6"/></svg>Kembali</a></div>
    </section>

    @if ($errors->any())
        <div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>Periksa kembali data warna. Terdapat {{ $errors->count() }} bagian yang perlu diperbaiki.</span></div>
    @endif

    <form action="{{ route('admin.colors.store') }}" method="POST" class="category-form-layout">
        @csrf
        @include('admin.colors.partials.form', ['color' => $color, 'submitLabel' => 'Simpan Warna', 'isEditing' => false])
    </form>
@endsection
