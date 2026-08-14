<div id="admin-search-modal" class="search-modal" hidden>
    <div class="search-modal__backdrop" data-search-close></div>

    <section class="search-modal__panel glass-panel" role="dialog" aria-modal="true" aria-labelledby="search-modal-title">
        <div class="search-modal__header">
            <div>
                <p class="search-modal__eyebrow">Pencarian cepat</p>
                <h2 id="search-modal-title">Buka menu dengan cepat</h2>
            </div>

            <button type="button" class="icon-button" aria-label="Tutup pencarian" data-search-close>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </div>

        <label class="search-modal__input-wrap" for="admin-global-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
            <input id="admin-global-search" type="search" placeholder="Cari transaksi, produk, stok, room..." autocomplete="off">
            <kbd>Esc</kbd>
        </label>

        <div class="search-modal__content">
            <p class="search-modal__section-title">Menu utama</p>

            <div class="search-quick-grid search-quick-grid--simple">
                @php
                    $quickMenus = [
                        ['route' => 'admin.dashboard', 'icon' => 'dashboard', 'title' => 'Dashboard', 'description' => 'Ringkasan operasional'],
                        ['route' => 'admin.sales.index', 'icon' => 'sales', 'title' => 'Transaksi', 'description' => 'Penjualan online & offline'],
                        ['route' => 'admin.sales-returns.index', 'icon' => 'return', 'title' => 'Retur Pelanggan', 'description' => 'Barang kembali dari transaksi lunas'],
                        ['route' => 'admin.payments.index', 'icon' => 'payment', 'title' => 'Pembayaran', 'description' => 'Verifikasi QRIS & transfer'],
                        ['route' => 'admin.shipments.index', 'icon' => 'shipping', 'title' => 'Pengiriman', 'description' => 'Status dan nomor resi'],
                        ['route' => 'admin.promotions.index', 'icon' => 'promotion', 'title' => 'Diskon & Promosi', 'description' => 'Promo produk atau kategori'],
                        ['route' => 'admin.products.index', 'icon' => 'products', 'title' => 'Produk', 'description' => 'Data produk dan foto'],
                        ['route' => 'admin.categories.index', 'icon' => 'category', 'title' => 'Kategori', 'description' => 'Kode dan kelompok produk'],
                        ['route' => 'admin.product-attributes.index', 'icon' => 'attributes', 'title' => 'Warna & Ukuran', 'description' => 'Atribut variasi produk'],
                        ['route' => 'admin.product-variants.index', 'icon' => 'variants', 'title' => 'Variasi Produk', 'description' => 'SKU warna dan ukuran'],
                        ['route' => 'admin.warehouses.index', 'icon' => 'warehouse', 'title' => 'Room', 'description' => 'Lokasi penyimpanan'],
                        ['route' => 'admin.warehouse-stocks.index', 'icon' => 'stock', 'title' => 'Stok Room', 'description' => 'Persediaan per SKU & lokasi'],
                        ['route' => 'admin.stock-movements.index', 'icon' => 'movement', 'title' => 'Riwayat Stok', 'description' => 'Ledger pergerakan persediaan'],
                        ['route' => 'admin.stock-adjustments.index', 'icon' => 'adjustment', 'title' => 'Penyesuaian Stok', 'description' => 'Stock opname dan koreksi'],
                        ['route' => 'admin.damaged-goods.index', 'icon' => 'damaged', 'title' => 'Barang Rusak', 'description' => 'Barang tidak layak jual'],
                        ['route' => 'admin.stock-transfers.index', 'icon' => 'transfer', 'title' => 'Transfer Room', 'description' => 'Perpindahan stok antar lokasi'],
                    ];
                @endphp

                @foreach($quickMenus as $menu)
                    <a href="{{ route($menu['route']) }}" class="search-quick-item">
                        <span class="search-quick-item__icon">@include('partials.admin.icon', ['icon' => $menu['icon']])</span>
                        <span><strong>{{ $menu['title'] }}</strong><small>{{ $menu['description'] }}</small></span>
                    </a>
                @endforeach

                @if(auth()->user()?->isOwner())
                    <a href="{{ route('admin.users.index') }}" class="search-quick-item">
                        <span class="search-quick-item__icon">@include('partials.admin.icon', ['icon' => 'users'])</span>
                        <span><strong>User Management</strong><small>Kelola akun internal</small></span>
                    </a>
                    <a href="{{ route('admin.activity-logs.index') }}" class="search-quick-item">
                        <span class="search-quick-item__icon">@include('partials.admin.icon', ['icon' => 'activity'])</span>
                        <span><strong>Activity Log</strong><small>Audit trail sistem</small></span>
                    </a>
                @endif

                <a href="#" class="search-quick-item" data-coming-soon="Pembelian dan Vendor akan dikerjakan pada bagian berikutnya oleh tim pengembangan.">
                    <span class="search-quick-item__icon">@include('partials.admin.icon', ['icon' => 'purchase'])</span>
                    <span><strong>Pembelian</strong><small>Tahap pengembangan berikutnya</small></span>
                </a>
                <a href="#" class="search-quick-item" data-coming-soon="Keuangan dan Laporan akan dikerjakan pada bagian berikutnya oleh tim pengembangan.">
                    <span class="search-quick-item__icon">@include('partials.admin.icon', ['icon' => 'report'])</span>
                    <span><strong>Keuangan & Laporan</strong><small>Tahap pengembangan berikutnya</small></span>
                </a>
            </div>

            <div id="admin-search-empty" class="search-modal__empty" hidden>Tidak ada menu yang cocok dengan pencarian Anda.</div>
        </div>
    </section>
</div>
