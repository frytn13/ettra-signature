@extends('layouts.admin')

@section('title', 'User Management')
@section('eyebrow', 'Manajemen Akses')
@section('page-title', 'User Management')

@section('content')
    <section class="dashboard-heading user-management-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Owner Only</p>
            <h2>Kelola pengguna internal</h2>
            <p>
                Buat dan kelola akun Owner maupun Admin. Perubahan status dan penghapusan menggunakan proteksi agar sistem selalu memiliki minimal satu Owner aktif.
            </p>
        </div>

        <div class="dashboard-heading__actions">
            <a href="{{ route('admin.users.create') }}" class="button button--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                Tambah Pengguna
            </a>
        </div>
    </section>

    @if (session('success'))
        <div class="user-alert user-alert--success" role="status">
            <span class="user-alert__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg>
            </span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="user-alert user-alert--danger" role="alert">
            <span class="user-alert__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg>
            </span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <section class="user-summary-grid" aria-label="Ringkasan pengguna internal">
        <article class="user-summary-card glass-panel">
            <span class="user-summary-card__icon user-summary-card__icon--peach">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
            </span>
            <span class="user-summary-card__copy">
                <small>Total Pengguna</small>
                <strong>{{ number_format($statistics['total']) }}</strong>
                <span>Akun internal aktif secara data</span>
            </span>
        </article>

        <article class="user-summary-card glass-panel">
            <span class="user-summary-card__icon user-summary-card__icon--peach">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 3 6 6 .9-4.5 4.4 1 6.2L12 17.7 6.5 20.5l1-6.2L3 9.9 9 9l3-6Z"/></svg>
            </span>
            <span class="user-summary-card__copy">
                <small>Owner</small>
                <strong>{{ number_format($statistics['owners']) }}</strong>
                <span>Akses tertinggi sistem</span>
            </span>
        </article>

        <article class="user-summary-card glass-panel">
            <span class="user-summary-card__icon user-summary-card__icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v2"/><circle cx="17" cy="9" r="2"/><path d="M16 14h1a4 4 0 0 1 4 4v2"/></svg>
            </span>
            <span class="user-summary-card__copy">
                <small>Admin</small>
                <strong>{{ number_format($statistics['admins']) }}</strong>
                <span>Pengguna operasional</span>
            </span>
        </article>

        <article class="user-summary-card glass-panel">
            <span class="user-summary-card__icon user-summary-card__icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
            <span class="user-summary-card__copy">
                <small>Akun Aktif</small>
                <strong>{{ number_format($statistics['active']) }}</strong>
                <span>Dapat masuk ke sistem</span>
            </span>
        </article>
    </section>

    <section class="user-management-card glass-panel">
        <div class="user-management-card__header">
            <div>
                <p class="dashboard-heading__eyebrow">Daftar Pengguna</p>
                <h3>Akun internal Ettra Signature</h3>
                <p>Gunakan pencarian dan filter untuk menemukan pengguna dengan cepat.</p>
            </div>
        </div>

        <form action="{{ route('admin.users.index') }}" method="GET" class="user-filter-form">
            <label class="user-filter-field user-filter-field--search">
                <span class="sr-only">Cari pengguna</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Cari nama, email, atau nomor telepon..."
                    autocomplete="off"
                >
            </label>

            <label class="user-filter-field">
                <span class="sr-only">Filter role</span>
                <select name="role">
                    <option value="">Semua role</option>
                    <option value="owner" @selected($filters['role'] === 'owner')>Owner</option>
                    <option value="admin" @selected($filters['role'] === 'admin')>Admin</option>
                </select>
            </label>

            <label class="user-filter-field">
                <span class="sr-only">Filter status</span>
                <select name="status">
                    <option value="">Semua status</option>
                    <option value="active" @selected($filters['status'] === 'active')>Aktif</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option>
                </select>
            </label>

            <button type="submit" class="button button--secondary button--small">Terapkan</button>

            @if ($filters['search'] !== '' || $filters['role'] !== '' || $filters['status'] !== '')
                <a href="{{ route('admin.users.index') }}" class="button button--ghost button--small">Reset</a>
            @endif
        </form>

        @if ($users->isEmpty())
            <div class="user-empty-state">
                <span class="user-empty-state__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v2"/><path d="m17 17 4 4M21 17l-4 4"/></svg>
                </span>
                <h4>Pengguna tidak ditemukan</h4>
                <p>Ubah kata pencarian atau filter, atau buat akun internal baru.</p>
            </div>
        @else
            <div class="responsive-table-wrap user-desktop-table">
                <table class="admin-table user-table">
                    <thead>
                        <tr>
                            <th>Pengguna</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Terakhir Login</th>
                            <th>IP Terakhir</th>
                            <th class="user-table__actions-heading">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $managedUser)
                            @php
                                $initials = collect(preg_split('/\s+/', trim($managedUser->name)) ?: [])
                                    ->filter()
                                    ->take(2)
                                    ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                    ->implode('');
                            @endphp
                            <tr>
                                <td>
                                    <div class="user-identity">
                                        <span class="user-avatar {{ $managedUser->isOwner() ? 'user-avatar--owner' : 'user-avatar--admin' }}">{{ $initials ?: 'ES' }}</span>
                                        <span class="user-identity__copy">
                                            <strong>{{ $managedUser->name }}</strong>
                                            <small>{{ $managedUser->email }}</small>
                                            <small>{{ $managedUser->phone ?: 'Nomor telepon belum diisi' }}</small>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="user-role-badge {{ $managedUser->isOwner() ? 'user-role-badge--owner' : 'user-role-badge--admin' }}">
                                        {{ $managedUser->roleLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge {{ $managedUser->is_active ? 'status-badge--success' : 'status-badge--danger' }}">
                                        {{ $managedUser->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="user-table-primary">
                                        {{ $managedUser->last_login_at?->format('d M Y, H:i') ?? 'Belum pernah' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="user-table-secondary">{{ $managedUser->last_login_ip ?: '-' }}</span>
                                </td>
                                <td>
                                    <div class="user-row-actions">
                                        <a
                                            href="{{ route('admin.users.edit', $managedUser) }}"
                                            class="user-action-button user-action-button--edit"
                                            title="Edit pengguna"
                                            aria-label="Edit {{ $managedUser->name }}"
                                        >
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z"/></svg>
                                        </a>

                                        @if (! auth()->user()->is($managedUser))
                                            <form
                                                action="{{ route('admin.users.toggle-status', $managedUser) }}"
                                                method="POST"
                                                data-confirm-form
                                                data-confirm-message="{{ $managedUser->is_active ? 'Nonaktifkan akun '.$managedUser->name.'?' : 'Aktifkan kembali akun '.$managedUser->name.'?' }}"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    class="user-action-button {{ $managedUser->is_active ? 'user-action-button--disable' : 'user-action-button--enable' }}"
                                                    title="{{ $managedUser->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}"
                                                    aria-label="{{ $managedUser->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $managedUser->name }}"
                                                >
                                                    @if ($managedUser->is_active)
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 8l8 8"/></svg>
                                                    @else
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m5 12 4 4L19 6"/></svg>
                                                    @endif
                                                </button>
                                            </form>

                                            <form
                                                action="{{ route('admin.users.destroy', $managedUser) }}"
                                                method="POST"
                                                data-confirm-form
                                                data-confirm-message="Hapus akun {{ $managedUser->name }}? Data akan dihapus secara soft delete dan tidak tampil pada daftar pengguna aktif."
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="user-action-button user-action-button--delete"
                                                    title="Hapus pengguna"
                                                    aria-label="Hapus {{ $managedUser->name }}"
                                                >
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5"/></svg>
                                                </button>
                                            </form>
                                        @else
                                            <span class="user-self-badge">Akun Anda</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="user-mobile-list">
                @foreach ($users as $managedUser)
                    @php
                        $initials = collect(preg_split('/\s+/', trim($managedUser->name)) ?: [])
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                            ->implode('');
                    @endphp
                    <article class="user-mobile-card">
                        <div class="user-mobile-card__top">
                            <div class="user-identity">
                                <span class="user-avatar {{ $managedUser->isOwner() ? 'user-avatar--owner' : 'user-avatar--admin' }}">{{ $initials ?: 'ES' }}</span>
                                <span class="user-identity__copy">
                                    <strong>{{ $managedUser->name }}</strong>
                                    <small>{{ $managedUser->email }}</small>
                                </span>
                            </div>
                            <span class="status-badge {{ $managedUser->is_active ? 'status-badge--success' : 'status-badge--danger' }}">
                                {{ $managedUser->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <dl class="user-mobile-card__details">
                            <div><dt>Role</dt><dd>{{ $managedUser->roleLabel() }}</dd></div>
                            <div><dt>Telepon</dt><dd>{{ $managedUser->phone ?: '-' }}</dd></div>
                            <div><dt>Login terakhir</dt><dd>{{ $managedUser->last_login_at?->format('d M Y, H:i') ?? 'Belum pernah' }}</dd></div>
                        </dl>

                        <div class="user-mobile-card__actions">
                            <a href="{{ route('admin.users.edit', $managedUser) }}" class="button button--secondary button--small">Edit</a>

                            @if (! auth()->user()->is($managedUser))
                                <form
                                    action="{{ route('admin.users.toggle-status', $managedUser) }}"
                                    method="POST"
                                    data-confirm-form
                                    data-confirm-message="{{ $managedUser->is_active ? 'Nonaktifkan akun '.$managedUser->name.'?' : 'Aktifkan kembali akun '.$managedUser->name.'?' }}"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="button button--secondary button--small">
                                        {{ $managedUser->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>

                                <form
                                    action="{{ route('admin.users.destroy', $managedUser) }}"
                                    method="POST"
                                    data-confirm-form
                                    data-confirm-message="Hapus akun {{ $managedUser->name }}? Data akan dihapus secara soft delete."
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button user-delete-button button--small">Hapus</button>
                                </form>
                            @else
                                <span class="user-self-badge">Akun Anda</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($users->hasPages())
                <div class="user-pagination">
                    {{ $users->links() }}
                </div>
            @endif
        @endif
    </section>
@endsection
