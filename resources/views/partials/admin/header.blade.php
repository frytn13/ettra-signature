@php
    $authenticatedUser = auth()->user();
    $displayName = $authenticatedUser?->name ?? 'Pengguna';
    $displayEmail = $authenticatedUser?->email ?? '-';
    $roleLabel = $authenticatedUser?->roleLabel() ?? 'Pengguna Internal';
    $initials = collect(preg_split('/\s+/', trim($displayName)) ?: [])->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $initials = $initials !== '' ? $initials : 'ES';

    $pageIcon = match (true) {
        request()->routeIs('admin.sales.*') => 'sales',
        request()->routeIs('admin.sales-returns.*') => 'return',
        request()->routeIs('admin.payments.*') => 'payment',
        request()->routeIs('admin.shipments.*') => 'shipping',
        request()->routeIs('admin.promotions.*') => 'promotion',
        request()->routeIs('admin.products.*') => 'products',
        request()->routeIs('admin.categories.*') => 'category',
        request()->routeIs(['admin.product-attributes.*', 'admin.colors.*', 'admin.sizes.*']) => 'attributes',
        request()->routeIs('admin.product-variants.*') => 'variants',
        request()->routeIs('admin.warehouses.*') => 'warehouse',
        request()->routeIs('admin.warehouse-stocks.*') => 'stock',
        request()->routeIs('admin.stock-movements.*') => 'movement',
        request()->routeIs('admin.stock-adjustments.*') => 'adjustment',
        request()->routeIs('admin.damaged-goods.*') => 'damaged',
        request()->routeIs('admin.stock-transfers.*') => 'transfer',
        request()->routeIs('admin.users.*') => 'users',
        request()->routeIs('admin.activity-logs.*') => 'activity',
        default => 'dashboard',
    };
@endphp

<header class="admin-header glass-header">
    <div class="admin-header__inner" data-admin-header-inner>
        <div class="admin-header__left">
            <button type="button" class="icon-button admin-header__menu-button admin-header__page-icon" aria-label="Buka menu navigasi pada perangkat kecil" aria-controls="admin-sidebar" aria-expanded="false" data-sidebar-open>
                @include('partials.admin.icon', ['icon' => $pageIcon])
            </button>

            <div class="admin-header__title-wrap">
                <p class="admin-header__eyebrow">@yield('eyebrow', 'Halaman Admin')</p>
                <h1 class="admin-header__title">@yield('page-title', 'Dashboard')</h1>
            </div>
        </div>

        <div class="admin-header__actions">
            <button type="button" class="header-search-trigger" data-search-open aria-label="Buka pencarian">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <span>Cari menu atau data</span><kbd>Ctrl K</kbd>
            </button>

            <div class="admin-dropdown">
                <button type="button" class="icon-button icon-button--header" aria-label="Notifikasi" aria-expanded="false" data-dropdown-trigger="notification-menu">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                    <span class="notification-dot" aria-hidden="true"></span>
                </button>
                <div id="notification-menu" class="dropdown-menu dropdown-menu--notifications" hidden>
                    <div class="dropdown-menu__header"><div><strong>Notifikasi</strong><small>Informasi operasional terbaru</small></div></div>
                    <div class="notification-list">
                        <a href="{{ route('admin.payments.index') }}" class="notification-item">
                            <span class="notification-item__icon notification-item__icon--peach">@include('partials.admin.icon', ['icon' => 'payment'])</span>
                            <span><strong>Pembayaran menunggu verifikasi</strong><small>Periksa transaksi QRIS atau transfer.</small></span>
                        </a>
                        <a href="{{ route('admin.warehouse-stocks.index', ['status' => 'low']) }}" class="notification-item">
                            <span class="notification-item__icon notification-item__icon--green">@include('partials.admin.icon', ['icon' => 'stock'])</span>
                            <span><strong>Stok perlu perhatian</strong><small>Lihat SKU yang mencapai stok minimum.</small></span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="admin-dropdown">
                <button type="button" class="profile-trigger" aria-expanded="false" data-dropdown-trigger="profile-menu">
                    <span class="profile-trigger__avatar">{{ $initials }}</span>
                    <span class="profile-trigger__copy"><strong>{{ $displayName }}</strong><small>{{ $roleLabel }}</small></span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m8 10 4 4 4-4"/></svg>
                </button>
                <div id="profile-menu" class="dropdown-menu dropdown-menu--profile" hidden>
                    <div class="profile-menu__identity"><span class="profile-trigger__avatar profile-trigger__avatar--large">{{ $initials }}</span><span><strong>{{ $displayName }}</strong><small>{{ $displayEmail }} · {{ $roleLabel }}</small></span></div>
                    <form action="{{ route('admin.logout') }}" method="POST" data-logout-form data-confirm-message="Keluar dari sistem sekarang?">
                        @csrf
                        <button type="submit" class="profile-menu__logout">@include('partials.admin.icon', ['icon' => 'sales'])<span>Keluar</span></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
