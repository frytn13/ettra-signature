@extends('layouts.admin')

@section('title', 'Edit Warna')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Edit Warna')

@section('content')
    <section class="dashboard-heading category-form-heading">
        <div><p class="dashboard-heading__eyebrow">Perbarui Warna</p><h2>{{ $color->name }}</h2><p>Perbarui kode, nama, HEX, atau status warna. Seluruh perubahan penting akan dicatat pada Activity Log.</p></div>
        <div class="dashboard-heading__actions"><a href="{{ route('admin.colors.index') }}" class="button button--secondary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6"/></svg>Kembali</a></div>
    </section>

    @if ($errors->any())
        <div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>Periksa kembali data warna. Terdapat {{ $errors->count() }} bagian yang perlu diperbaiki.</span></div>
    @endif

    <form action="{{ route('admin.colors.update', $color) }}" method="POST" class="category-form-layout">
        @csrf @method('PUT')
        @include('admin.colors.partials.form', ['color' => $color, 'submitLabel' => 'Simpan Perubahan', 'isEditing' => true])
    </form>
@endsection
