@php
    $user = auth()->user();
    $role = $user?->role;
    $productGroupActive = request()->routeIs([
        'admin.products.*', 'admin.categories.*', 'admin.product-attributes.*', 'admin.colors.*',
        'admin.sizes.*', 'admin.product-variants.*', 'admin.warehouses.*', 'admin.warehouse-stocks.*',
        'admin.stock-movements.*', 'admin.stock-adjustments.*', 'admin.damaged-goods.*', 'admin.stock-transfers.*',
    ]);
    $salesGroupActive = request()->routeIs(['admin.sales.*', 'admin.sales-returns.*', 'admin.payments.*', 'admin.shipments.*', 'admin.promotions.*']);
    $systemGroupActive = request()->routeIs(['admin.users.*', 'admin.activity-logs.*']);
@endphp

<aside id="admin-sidebar" class="admin-sidebar admin-sidebar--simple" aria-label="Navigasi utama">
    <div class="admin-sidebar__header">
        <a href="{{ route('admin.dashboard') }}" class="brand" aria-label="Ettra Signature Dashboard">
            <span class="brand__mark" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none">
                    <path d="M12 12.5C12 9.46 14.46 7 17.5 7h13C33.54 7 36 9.46 36 12.5v23C36 38.54 33.54 41 30.5 41h-13C14.46 41 12 38.54 12 35.5v-23Z" fill="currentColor" opacity=".18"/>
                    <path d="M16.5 17.25c3.14-4.36 11.15-6.53 15.43-1.75 2.2 2.46 1.58 6.42-1.13 8.06-2.22 1.35-5.37.91-7.9.91h-2.4v6.78h11.2" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="brand__text"><strong>Ettra Signature</strong><small>Administration System</small></span>
        </a>
    </div>

    <div class="admin-sidebar__content no-scrollbar">
        <div class="sidebar-owner-card glass-panel">
            <span class="sidebar-owner-card__avatar">{{ strtoupper(substr($user?->name ?? 'ES', 0, 2)) }}</span>
            <span class="sidebar-owner-card__copy"><strong>{{ $user?->name ?? 'Ettra Signature' }}</strong><small>{{ $user?->roleLabel() ?? 'Internal' }}</small></span>
            <span class="sidebar-owner-card__status" title="Sistem aktif"></span>
        </div>

        <nav class="admin-navigation admin-navigation--compact" data-admin-sidebar-nav>
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                <span class="admin-nav-link__icon">@include('partials.admin.icon', ['icon' => 'dashboard'])</span>
                <span class="admin-nav-link__label">Dashboard</span>
            </a>

            <details class="admin-nav-group" @if($salesGroupActive) open @endif>
                <summary class="admin-nav-group__summary {{ $salesGroupActive ? 'is-active' : '' }}">
                    <span class="admin-nav-link__icon">@include('partials.admin.icon', ['icon' => 'sales'])</span>
                    <span>Penjualan</span>
                    <svg class="admin-nav-group__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="admin-nav-group__items">
                    <a href="{{ route('admin.sales.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.sales.*') ? 'is-active' : '' }}">Transaksi</a>
                    <a href="{{ route('admin.sales-returns.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.sales-returns.*') ? 'is-active' : '' }}">Retur Pelanggan</a>
                    <a href="{{ route('admin.payments.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.payments.*') ? 'is-active' : '' }}">Pembayaran</a>
                    <a href="{{ route('admin.shipments.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.shipments.*') ? 'is-active' : '' }}">Pengiriman</a>
                    <a href="{{ route('admin.promotions.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.promotions.*') ? 'is-active' : '' }}">Diskon & Promosi</a>
                </div>
            </details>

            <details class="admin-nav-group" @if($productGroupActive) open @endif>
                <summary class="admin-nav-group__summary {{ $productGroupActive ? 'is-active' : '' }}">
                    <span class="admin-nav-link__icon">@include('partials.admin.icon', ['icon' => 'products'])</span>
                    <span>Produk & Persediaan</span>
                    <svg class="admin-nav-group__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                </summary>
                <div class="admin-nav-group__items">
                    <a href="{{ route('admin.products.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}">Produk</a>
                    <a href="{{ route('admin.categories.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.categories.*') ? 'is-active' : '' }}">Kategori</a>
                    <a href="{{ route('admin.product-attributes.index') }}" class="admin-nav-sublink {{ request()->routeIs(['admin.product-attributes.*','admin.colors.*','admin.sizes.*']) ? 'is-active' : '' }}">Warna & Ukuran</a>
                    <a href="{{ route('admin.product-variants.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.product-variants.*') ? 'is-active' : '' }}">Variasi Produk</a>
                    <a href="{{ route('admin.warehouses.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.warehouses.*') ? 'is-active' : '' }}">Room</a>
                    <a href="{{ route('admin.warehouse-stocks.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.warehouse-stocks.*') ? 'is-active' : '' }}">Stok Room</a>
                    <a href="{{ route('admin.stock-movements.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.stock-movements.*') ? 'is-active' : '' }}">Riwayat Stok</a>
                    <a href="{{ route('admin.stock-adjustments.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.stock-adjustments.*') ? 'is-active' : '' }}">Penyesuaian Stok</a>
                    <a href="{{ route('admin.damaged-goods.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.damaged-goods.*') ? 'is-active' : '' }}">Barang Rusak</a>
                    <a href="{{ route('admin.stock-transfers.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.stock-transfers.*') ? 'is-active' : '' }}">Transfer Room</a>
                </div>
            </details>

            <a href="#" class="admin-nav-link" data-coming-soon="Pembelian dan Vendor akan dikerjakan pada bagian berikutnya">
                <span class="admin-nav-link__icon">@include('partials.admin.icon', ['icon' => 'purchase'])</span>
                <span class="admin-nav-link__label">Pembelian</span>
            </a>

            <a href="#" class="admin-nav-link" data-coming-soon="Keuangan dan Laporan akan dikerjakan pada bagian berikutnya">
                <span class="admin-nav-link__icon">@include('partials.admin.icon', ['icon' => 'report'])</span>
                <span class="admin-nav-link__label">Keuangan & Laporan</span>
            </a>

            @if($role === \App\Models\User::ROLE_OWNER)
                <details class="admin-nav-group" @if($systemGroupActive) open @endif>
                    <summary class="admin-nav-group__summary {{ $systemGroupActive ? 'is-active' : '' }}">
                        <span class="admin-nav-link__icon">@include('partials.admin.icon', ['icon' => 'settings'])</span>
                        <span>Sistem</span>
                        <svg class="admin-nav-group__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    </summary>
                    <div class="admin-nav-group__items">
                        <a href="{{ route('admin.users.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">User Management</a>
                        <a href="{{ route('admin.activity-logs.index') }}" class="admin-nav-sublink {{ request()->routeIs('admin.activity-logs.*') ? 'is-active' : '' }}">Activity Log</a>
                    </div>
                </details>
            @endif
        </nav>
    </div>

    <div class="admin-sidebar__footer admin-sidebar__footer--mobile">
        <button type="button" class="sidebar-mobile-close" data-sidebar-close aria-label="Tutup menu navigasi">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            <span>Tutup menu</span>
        </button>
    </div>
</aside>
