@extends('layouts.admin')

@section('title', 'Tambah Pengguna')
@section('eyebrow', 'User Management')
@section('page-title', 'Tambah Pengguna')

@section('content')
    <section class="dashboard-heading user-management-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Akun Internal Baru</p>
            <h2>Tambah pengguna</h2>
            <p>Buat akun Owner atau Admin baru. Password disimpan menggunakan hashing Laravel dan tidak pernah disimpan dalam bentuk teks biasa.</p>
        </div>

        <div class="dashboard-heading__actions">
            <a href="{{ route('admin.users.index') }}" class="button button--secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
        </div>
    </section>

    @if (session('error'))
        <div class="user-alert user-alert--danger" role="alert">
            <span class="user-alert__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg>
            </span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" class="user-form-layout">
        @csrf

        @include('admin.users.partials.form', [
            'managedUser' => null,
            'roles' => $roles,
            'submitLabel' => 'Simpan Pengguna',
            'isEdit' => false,
        ])
    </form>
@endsection
