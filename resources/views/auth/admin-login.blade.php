<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#FBFAF8">

    <title>Login Admin | {{ config('app.name', 'Ettra Signature') }}</title>

    @fonts
    @vite(['resources/css/admin-login.css', 'resources/js/admin-login.js'])
</head>
<body class="auth-body">
    <div class="auth-background" aria-hidden="true">
        <span class="auth-glow auth-glow--peach"></span>
        <span class="auth-glow auth-glow--green"></span>
        <span class="auth-grid"></span>
    </div>

    <main class="auth-shell">
        <section class="auth-story" aria-labelledby="auth-brand-title">
            <div class="auth-brand">
                <span class="auth-brand__mark" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none">
                        <path d="M12 12.5C12 9.46 14.46 7 17.5 7h13C33.54 7 36 9.46 36 12.5v23C36 38.54 33.54 41 30.5 41h-13C14.46 41 12 38.54 12 35.5v-23Z" fill="currentColor" opacity=".18" />
                        <path d="M16.5 17.25c3.14-4.36 11.15-6.53 15.43-1.75 2.2 2.46 1.58 6.42-1.13 8.06-2.22 1.35-5.37.91-7.9.91h-2.4v6.78h11.2" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>

                <span class="auth-brand__copy">
                    <strong id="auth-brand-title">Ettra Signature</strong>
                    <small>Administration System</small>
                </span>
            </div>

            <div class="auth-story__content">
                <span class="auth-eyebrow">
                    <span class="auth-eyebrow__dot" aria-hidden="true"></span>
                    Akses internal terproteksi
                </span>

                <h1>Kelola operasional usaha dalam satu ruang kerja yang terintegrasi.</h1>

                <p>
                    Pantau penjualan, persediaan, pembelian, pembayaran, dan aktivitas operasional Ettra Signature dengan akses yang disesuaikan untuk setiap pengguna internal.
                </p>

                <div class="auth-feature-grid" aria-label="Fitur sistem internal">
                    <article class="auth-feature-card auth-glass">
                        <span class="auth-feature-card__icon auth-feature-card__icon--peach" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M4 19V9m5 10V5m5 14v-7m5 7V3" />
                            </svg>
                        </span>
                        <div>
                            <strong>Ringkasan usaha</strong>
                            <small>Data operasional dalam satu dashboard.</small>
                        </div>
                    </article>

                    <article class="auth-feature-card auth-glass">
                        <span class="auth-feature-card__icon auth-feature-card__icon--green" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="m12 3 8 4-8 4-8-4 8-4Z" />
                                <path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z" />
                            </svg>
                        </span>
                        <div>
                            <strong>Stok terkendali</strong>
                            <small>Pantau persediaan dan kebutuhan restock.</small>
                        </div>
                    </article>

                    <article class="auth-feature-card auth-glass">
                        <span class="auth-feature-card__icon auth-feature-card__icon--neutral" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                                <path d="m9 12 2 2 4-4" />
                            </svg>
                        </span>
                        <div>
                            <strong>Akses terkontrol</strong>
                            <small>Setiap akun hanya mengakses data yang diizinkan.</small>
                        </div>
                    </article>
                </div>
            </div>

            <div class="auth-story__footer">
                <span>Ettra Signature</span>
                <span aria-hidden="true">•</span>
                <span>Internal Management System</span>
            </div>
        </section>

        <section class="auth-form-area" aria-labelledby="login-title">
            <div class="auth-mobile-brand">
                <span class="auth-brand__mark auth-brand__mark--mobile" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none">
                        <path d="M12 12.5C12 9.46 14.46 7 17.5 7h13C33.54 7 36 9.46 36 12.5v23C36 38.54 33.54 41 30.5 41h-13C14.46 41 12 38.54 12 35.5v-23Z" fill="currentColor" opacity=".18" />
                        <path d="M16.5 17.25c3.14-4.36 11.15-6.53 15.43-1.75 2.2 2.46 1.58 6.42-1.13 8.06-2.22 1.35-5.37.91-7.9.91h-2.4v6.78h11.2" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <div>
                    <strong>Ettra Signature</strong>
                    <small>Administration System</small>
                </div>
            </div>

            <div class="auth-card auth-glass">
                <div class="auth-card__accent" aria-hidden="true"></div>

                <header class="auth-card__header">
                    <span class="auth-card__badge">Area Admin</span>
                    <h2 id="login-title">Selamat datang kembali</h2>
                    <p>Masuk menggunakan akun internal Ettra Signature untuk melanjutkan ke dashboard.</p>
                </header>

                @if (session('status'))
                    <div class="auth-alert auth-alert--success" role="status">
                        <span class="auth-alert__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m7 12 3 3 7-7" />
                                <circle cx="12" cy="12" r="9" />
                            </svg>
                        </span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="auth-alert auth-alert--error" role="alert">
                        <span class="auth-alert__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 8v5M12 16h.01" />
                            </svg>
                        </span>
                        <div>
                            <strong>Login belum berhasil</strong>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <form
                    id="admin-login-form"
                    class="auth-form"
                    action="{{ route('admin.login.store') }}"
                    method="POST"
                    novalidate
                >
                    @csrf

                    <div class="auth-field {{ $errors->has('login') ? 'has-error' : '' }}">
                        <label for="login">Email atau nomor telepon</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-wrap__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 5h16v14H4z" />
                                    <path d="m4 7 8 6 8-6" />
                                </svg>
                            </span>
                            <input
                                id="login"
                                name="login"
                                type="text"
                                value="{{ old('login') }}"
                                placeholder="owner@ettrasignature.com atau 08xxxxxxxxxx"
                                autocomplete="username"
                                autocapitalize="none"
                                spellcheck="false"
                                required
                                autofocus
                            >
                        </div>
                        @error('login')
                            <small class="auth-field__error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="auth-field {{ $errors->has('password') ? 'has-error' : '' }}">
                        <div class="auth-field__label-row">
                            <label for="password">Kata sandi</label>
                            <button type="button" class="auth-help-link" data-auth-help>Ada kendala?</button>
                        </div>

                        <div class="auth-input-wrap">
                            <span class="auth-input-wrap__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="5" y="10" width="14" height="10" rx="2" />
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                </svg>
                            </span>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Masukkan kata sandi"
                                autocomplete="current-password"
                                required
                            >
                            <button
                                type="button"
                                class="auth-password-toggle"
                                data-password-toggle
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                            >
                                <svg class="auth-password-toggle__show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
                                    <circle cx="12" cy="12" r="2.5" />
                                </svg>
                                <svg class="auth-password-toggle__hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="m3 3 18 18" />
                                    <path d="M10.6 6.2A8.7 8.7 0 0 1 12 6c6 0 9.5 6 9.5 6a16.4 16.4 0 0 1-2.1 2.7M6.6 6.6C4 8.3 2.5 12 2.5 12s3.5 6 9.5 6a9.8 9.8 0 0 0 3.1-.5M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <small class="auth-field__error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="auth-form__options">
                        <label class="auth-checkbox">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <span class="auth-checkbox__box" aria-hidden="true">
                                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path d="m3 8 3 3 7-7" />
                                </svg>
                            </span>
                            <span>Ingat saya</span>
                        </label>

                        <span class="auth-security-note">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                            </svg>
                            Sesi terlindungi
                        </span>
                    </div>

                    <div id="auth-help-panel" class="auth-help-panel" hidden>
                        <strong>Tidak dapat masuk?</strong>
                        <p>Akun internal dibuat dan dikelola oleh Owner. Hubungi Owner atau administrator sistem untuk aktivasi akun atau penggantian kata sandi.</p>
                    </div>

                    <button id="admin-login-submit" type="submit" class="auth-submit">
                        <span class="auth-submit__label">
                            Masuk ke Dashboard
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </span>
                        <span class="auth-submit__loading" aria-hidden="true">
                            <span class="auth-spinner"></span>
                            Memeriksa akun...
                        </span>
                    </button>
                </form>

                <div class="auth-card__footer">
                    <span class="auth-card__footer-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                    </span>
                    <p>Halaman ini hanya untuk pengguna internal yang memiliki akun aktif.</p>
                </div>
            </div>

            <footer class="auth-copyright">
                &copy; {{ now()->year }} Ettra Signature. Seluruh hak dilindungi.
            </footer>
        </section>
    </main>
</body>
</html>
