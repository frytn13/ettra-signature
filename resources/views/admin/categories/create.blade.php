@extends('layouts.admin')

@section('title', 'Tambah Kategori')
@section('eyebrow', 'Master Produk')
@section('page-title', 'Tambah Kategori')

@section('content')
    <section class="dashboard-heading category-form-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Kategori Baru</p>
            <h2>Tambahkan kategori produk</h2>
            <p>Buat kode singkat dan nama kategori yang konsisten. Slug untuk halaman pelanggan akan dibuat otomatis oleh sistem.</p>
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

    <form action="{{ route('admin.categories.store') }}" method="POST" class="category-form-layout">
        @csrf

        @include('admin.categories.partials.form', [
            'category' => $category,
            'submitLabel' => 'Simpan Kategori',
            'isEditing' => false,
        ])
    </form>
@endsection
