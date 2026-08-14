@extends('layouts.admin')

@section('title', 'Edit Room')
@section('eyebrow', 'Persediaan')
@section('page-title', 'Edit Room')

@section('content')
    <section class="dashboard-heading category-form-heading">
        <div><p class="dashboard-heading__eyebrow">Perbarui Room</p><h2>{{ $warehouse->name }}</h2><p>Perbarui identitas, alamat, deskripsi, atau status room. Perubahan penting tercatat pada Activity Log.</p></div>
        <div class="dashboard-heading__actions"><a href="{{ route('admin.warehouses.show', $warehouse) }}" class="button button--secondary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6"/></svg>Kembali</a></div>
    </section>

    @if ($errors->any())
        <div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>Periksa kembali data room. Terdapat {{ $errors->count() }} bagian yang perlu diperbaiki.</span></div>
    @endif

    <form action="{{ route('admin.warehouses.update', $warehouse) }}" method="POST" class="category-form-layout">
        @csrf
        @method('PUT')
        @include('admin.warehouses.partials.form', ['warehouse' => $warehouse, 'submitLabel' => 'Simpan Perubahan', 'isEditing' => true])
    </form>
@endsection
