@php
    $selectedRole = old('role', $managedUser?->role ?? \App\Models\User::ROLE_ADMIN);
    $defaultStatus = $managedUser ? ($managedUser->is_active ? '1' : '0') : '1';
    $selectedStatus = (string) old('is_active', $defaultStatus);
@endphp

<section class="user-form-card glass-panel">
    <div class="user-form-card__header">
        <div>
            <p class="dashboard-heading__eyebrow">Informasi Akun</p>
            <h3>{{ $isEdit ? 'Data pengguna internal' : 'Identitas pengguna baru' }}</h3>
            <p>Kolom bertanda wajib harus diisi dengan data yang valid.</p>
        </div>
        <span class="user-form-card__mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v2"/><path d="M17 8v6M20 11h-6"/></svg>
        </span>
    </div>

    @if ($errors->any())
        <div class="user-validation-summary" role="alert">
            <strong>Periksa kembali data berikut:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="user-form-grid">
        <label class="user-form-field user-form-field--full">
            <span>Nama lengkap <em>*</em></span>
            <input
                type="text"
                name="name"
                value="{{ old('name', $managedUser?->name) }}"
                maxlength="100"
                autocomplete="name"
                required
                @class(['is-invalid' => $errors->has('name')])
                placeholder="Contoh: Ayu Ettra"
            >
            @error('name')<small class="user-field-error">{{ $message }}</small>@enderror
        </label>

        <label class="user-form-field">
            <span>Email <em>*</em></span>
            <input
                type="email"
                name="email"
                value="{{ old('email', $managedUser?->email) }}"
                maxlength="255"
                autocomplete="email"
                required
                @class(['is-invalid' => $errors->has('email')])
                placeholder="nama@ettrasignature.com"
            >
            @error('email')<small class="user-field-error">{{ $message }}</small>@enderror
        </label>

        <label class="user-form-field">
            <span>Nomor telepon</span>
            <input
                type="tel"
                name="phone"
                value="{{ old('phone', $managedUser?->phone) }}"
                maxlength="20"
                autocomplete="tel"
                inputmode="tel"
                @class(['is-invalid' => $errors->has('phone')])
                placeholder="081234567890"
            >
            <small class="user-field-hint">Format +62, 62, atau 08 akan dinormalisasi menjadi 08.</small>
            @error('phone')<small class="user-field-error">{{ $message }}</small>@enderror
        </label>

        <label class="user-form-field">
            <span>Role <em>*</em></span>
            <select name="role" required @class(['is-invalid' => $errors->has('role')])>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected($selectedRole === $role)>
                        {{ $role === \App\Models\User::ROLE_OWNER ? 'Owner' : 'Admin' }}
                    </option>
                @endforeach
            </select>
            <small class="user-field-hint">Owner memiliki akses tertinggi. Admin hanya memiliki akses operasional sesuai ketentuan sistem.</small>
            @error('role')<small class="user-field-error">{{ $message }}</small>@enderror
        </label>

        <label class="user-form-field">
            <span>Status akun <em>*</em></span>
            <select name="is_active" required @class(['is-invalid' => $errors->has('is_active')])>
                <option value="1" @selected($selectedStatus === '1')>Aktif</option>
                <option value="0" @selected($selectedStatus === '0')>Nonaktif</option>
            </select>
            <small class="user-field-hint">Akun nonaktif tetap tersimpan tetapi tidak dapat login.</small>
            @error('is_active')<small class="user-field-error">{{ $message }}</small>@enderror
        </label>

        <label class="user-form-field">
            <span>Kata sandi {{ $isEdit ? '' : '*' }}</span>
            <div class="user-password-field">
                <input
                    type="password"
                    name="password"
                    {{ $isEdit ? '' : 'required' }}
                    autocomplete="new-password"
                    @class(['is-invalid' => $errors->has('password')])
                    placeholder="Minimal 8 karakter"
                    data-user-password
                >
                <button type="button" aria-label="Tampilkan kata sandi" data-user-password-toggle>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            <small class="user-field-hint">Gunakan minimal 8 karakter yang mengandung huruf dan angka. {{ $isEdit ? 'Kosongkan jika tidak ingin mengganti password.' : '' }}</small>
            @error('password')<small class="user-field-error">{{ $message }}</small>@enderror
        </label>

        <label class="user-form-field">
            <span>Konfirmasi kata sandi {{ $isEdit ? '' : '*' }}</span>
            <input
                type="password"
                name="password_confirmation"
                {{ $isEdit ? '' : 'required' }}
                autocomplete="new-password"
                placeholder="Ulangi kata sandi"
            >
        </label>
    </div>
</section>

<aside class="user-form-side">
    @if ($isEdit && $managedUser)
        <section class="user-profile-summary glass-panel">
            @php
                $initials = collect(preg_split('/\s+/', trim($managedUser->name)) ?: [])
                    ->filter()
                    ->take(2)
                    ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                    ->implode('');
            @endphp

            <span class="user-profile-summary__avatar {{ $managedUser->isOwner() ? 'user-profile-summary__avatar--owner' : 'user-profile-summary__avatar--admin' }}">{{ $initials ?: 'ES' }}</span>
            <h3>{{ $managedUser->name }}</h3>
            <p>{{ $managedUser->email }}</p>

            <div class="user-profile-summary__badges">
                <span class="user-role-badge {{ $managedUser->isOwner() ? 'user-role-badge--owner' : 'user-role-badge--admin' }}">{{ $managedUser->roleLabel() }}</span>
                <span class="status-badge {{ $managedUser->is_active ? 'status-badge--success' : 'status-badge--danger' }}">{{ $managedUser->is_active ? 'Aktif' : 'Nonaktif' }}</span>
            </div>

            <dl class="user-profile-summary__meta">
                <div>
                    <dt>Login terakhir</dt>
                    <dd>{{ $managedUser->last_login_at?->format('d M Y, H:i') ?? 'Belum pernah login' }}</dd>
                </div>
                <div>
                    <dt>IP terakhir</dt>
                    <dd>{{ $managedUser->last_login_ip ?: '-' }}</dd>
                </div>
                <div>
                    <dt>Dibuat</dt>
                    <dd>{{ $managedUser->created_at?->format('d M Y, H:i') ?? '-' }}</dd>
                </div>
            </dl>
        </section>
    @endif

    <section class="user-security-note glass-panel">
        <span class="user-security-note__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
        </span>
        <div>
            <h3>Proteksi akun Owner</h3>
            <p>Sistem mencegah penghapusan, penonaktifan, atau penurunan role jika tindakan tersebut menyebabkan tidak ada Owner aktif.</p>
        </div>
    </section>

    <div class="user-form-actions glass-panel">
        <a href="{{ route('admin.users.index') }}" class="button button--secondary">Batal</a>
        <button type="submit" class="button button--primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12.5 9 16l10-10"/></svg>
            {{ $submitLabel }}
        </button>
    </div>
</aside>
