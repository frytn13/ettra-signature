@extends('layouts.admin')

@section('title', 'Tambah Ukuran')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Tambah Ukuran')

@section('content')
    <section class="dashboard-heading category-form-heading"><div><p class="dashboard-heading__eyebrow">Ukuran Baru</p><h2>Tambahkan master ukuran</h2><p>Buat kode, nama, dan urutan tampil yang konsisten agar variasi ukuran mudah dipilih pelanggan.</p></div><div class="dashboard-heading__actions"><a href="{{ route('admin.sizes.index') }}" class="button button--secondary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6"/></svg>Kembali</a></div></section>
    @if ($errors->any())<div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>Periksa kembali data ukuran. Terdapat {{ $errors->count() }} bagian yang perlu diperbaiki.</span></div>@endif
    <form action="{{ route('admin.sizes.store') }}" method="POST" class="category-form-layout">@csrf @include('admin.sizes.partials.form', ['size' => $size, 'submitLabel' => 'Simpan Ukuran', 'isEditing' => false])</form>
@endsection
