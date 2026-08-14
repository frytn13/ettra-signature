<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#BB7F73">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'Ettra Signature') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body" data-authenticated="{{ auth()->check() ? '1' : '0' }}" data-login-url="{{ route('admin.login') }}" data-logout-url="{{ route('admin.logout') }}">
    <div class="admin-background" aria-hidden="true">
        <span class="admin-orb admin-orb--peach"></span>
        <span class="admin-orb admin-orb--green"></span>
        <span class="admin-orb admin-orb--small"></span>
    </div>

    <div id="sidebar-overlay" class="sidebar-overlay" aria-hidden="true" data-sidebar-close></div>

    @include('partials.admin.sidebar')

    <div id="admin-shell" class="admin-shell">
        @include('partials.admin.header')

        <main class="admin-main">
            <div class="admin-container" id="admin-page-content" data-admin-page-content>
                @yield('content')
            </div>
        </main>

        <footer class="admin-footer">
            <div class="admin-container admin-footer__inner"><p>Ettra Signature Administration System</p><p>&copy; {{ now()->year }}. Seluruh hak dilindungi.</p></div>
        </footer>
    </div>

    @include('partials.admin.search-modal')

    <div id="admin-data-modal" class="admin-modal" hidden aria-hidden="true">
        <div class="admin-modal__backdrop" data-data-modal-close></div>
        <section class="admin-modal__card glass-panel" role="dialog" aria-modal="true" aria-labelledby="admin-data-modal-title">
            <header class="admin-modal__header">
                <div><p class="dashboard-heading__eyebrow" id="admin-data-modal-eyebrow">Input Data</p><h2 id="admin-data-modal-title">Form Data</h2></div>
                <button type="button" class="icon-button" data-data-modal-close aria-label="Tutup form"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
            </header>
            <div class="admin-modal__body" id="admin-data-modal-body"><div class="admin-modal__skeleton">Menyiapkan form...</div></div>
        </section>
    </div>

    <div id="admin-alert-modal" class="admin-alert-modal" hidden aria-hidden="true">
        <div class="admin-alert-modal__backdrop" aria-hidden="true"></div>
        <section class="admin-alert-card" role="alertdialog" aria-modal="true" aria-labelledby="admin-alert-title" aria-describedby="admin-alert-message">
            <div class="admin-alert-card__header">
                <span class="admin-alert-card__icon" id="admin-alert-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                </span>
                <div class="admin-alert-card__copy">
                    <p class="admin-alert-card__eyebrow" id="admin-alert-eyebrow">Informasi Sistem</p>
                    <h2 id="admin-alert-title">Perhatian</h2>
                </div>
            </div>
            <p class="admin-alert-card__message" id="admin-alert-message">Informasi sistem.</p>
            <div class="admin-alert-card__actions">
                <button type="button" class="button button--secondary" id="admin-alert-cancel" hidden>Batal</button>
                <button type="button" class="button button--primary" id="admin-alert-confirm">Mengerti</button>
            </div>
        </section>
    </div>

    @stack('scripts')
</body>
</html>
