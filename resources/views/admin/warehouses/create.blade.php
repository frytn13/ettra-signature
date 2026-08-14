@extends('layouts.admin')

@section('title', 'Tambah Room')
@section('eyebrow', 'Persediaan')
@section('page-title', 'Tambah Room')

@section('content')
    <section class="dashboard-heading category-form-heading">
        <div><p class="dashboard-heading__eyebrow">Room Baru</p><h2>Tambahkan lokasi penyimpanan</h2><p>Isi identitas room. Kode telah disiapkan otomatis dan masih dapat disesuaikan sebelum data disimpan.</p></div>
        <div class="dashboard-heading__actions"><a href="{{ route('admin.warehouses.index') }}" class="button button--secondary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6"/></svg>Kembali</a></div>
    </section>

    @if ($errors->any())
        <div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>Periksa kembali data room. Terdapat {{ $errors->count() }} bagian yang perlu diperbaiki.</span></div>
    @endif

    <form action="{{ route('admin.warehouses.store') }}" method="POST" class="category-form-layout">
        @csrf
        @include('admin.warehouses.partials.form', ['warehouse' => $warehouse, 'submitLabel' => 'Simpan Room', 'isEditing' => false])
    </form>
@endsection
