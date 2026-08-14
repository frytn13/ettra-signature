@extends('layouts.admin')

@section('title', 'Edit Kategori')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Edit Kategori')

@section('content')
    <section class="dashboard-heading category-form-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Perbarui Kategori</p>
            <h2>{{ $category->name }}</h2>
            <p>Perbarui kode, nama, deskripsi, atau status kategori. Seluruh perubahan penting akan tercatat pada Activity Log.</p>
        </div>

        <div class="dashboard-heading__actions">
            <a href="{{ route('admin.categories.index') }}" class="button button--secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
        </div>
    </section>

    @if ($errors->any())
        <div class="user-alert user-alert--danger" role="alert">
            <span class="user-alert__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg>
            </span>
            <span>Periksa kembali data kategori. Terdapat {{ $errors->count() }} bagian yang perlu diperbaiki.</span>
        </div>
    @endif

    <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="category-form-layout">
        @csrf
        @method('PUT')

        @include('admin.categories.partials.form', [
            'category' => $category,
            'submitLabel' => 'Simpan Perubahan',
            'isEditing' => true,
        ])
    </form>
@endsection
