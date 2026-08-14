@extends('layouts.admin')

@section('title', 'Edit Pengguna')
@section('eyebrow', 'User Management')
@section('page-title', 'Edit Pengguna')

@section('content')
    <section class="dashboard-heading user-management-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Perbarui Akun Internal</p>
            <h2>Edit {{ $managedUser->name }}</h2>
            <p>Perbarui identitas, role, status, atau kata sandi pengguna. Biarkan kolom password kosong jika password tidak ingin diubah.</p>
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

    <form action="{{ route('admin.users.update', $managedUser) }}" method="POST" class="user-form-layout">
        @csrf
        @method('PUT')

        @include('admin.users.partials.form', [
            'managedUser' => $managedUser,
            'roles' => $roles,
            'submitLabel' => 'Simpan Perubahan',
            'isEdit' => true,
        ])
    </form>
@endsection
