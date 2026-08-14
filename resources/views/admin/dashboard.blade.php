@extends('layouts.admin')

@section('title', 'Dashboard ' . $roleLabel)
@section('eyebrow', 'Ringkasan Operasional')
@section('page-title', 'Dashboard')

@section('content')
    <div class="compact-page dashboard-compact">
        <section class="compact-page__head">
            <div>
                <p class="dashboard-heading__eyebrow">{{ ucfirst(now()->locale('id')->translatedFormat('l, d F Y')) }}</p>
                <h2>Selamat datang, {{ auth()->user()->name }}</h2>
                <p>Ringkasan penjualan dan persediaan yang relevan dengan akses {{ $roleLabel }}.</p>
            </div>
            <div class="compact-actions">
                <a href="{{ route('admin.sales.create') }}" class="button button--primary" data-modal-form data-modal-title="Transaksi Baru">+ Transaksi</a>
                <a href="{{ route('admin.products.create') }}" class="button button--secondary" data-modal-form data-modal-title="Produk Baru">+ Produk</a>
            </div>
        </section>

        <form action="{{ route('admin.dashboard') }}" method="GET" class="dashboard-period-filter glass-panel" data-ajax-filter>
            <label>
                <span>Periode</span>
                <select name="period">
                    <option value="day" @selected($period === 'day')>Hari ini</option>
                    <option value="week" @selected($period === 'week')>Minggu ini</option>
                    <option value="month" @selected($period === 'month')>Bulan ini</option>
                    <option value="year" @selected($period === 'year')>Tahun ini</option>
                    <option value="custom" @selected($period === 'custom')>Rentang tanggal</option>
                </select>
            </label>
            <label>
                <span>Mulai</span>
                <input type="date" name="start_date" value="{{ $start->toDateString() }}">
            </label>
            <label>
                <span>Sampai</span>
                <input type="date" name="end_date" value="{{ $end->toDateString() }}">
            </label>
            <button class="button button--secondary" type="submit">Terapkan</button>
        </form>

        <section class="compact-stats dashboard-stat-grid">
            @foreach($summaryCards as $card)
                <article class="compact-stat dashboard-stat-card dashboard-stat-card--{{ $card['accent'] }}">
                    <small>{{ $card['label'] }}</small>
                    <strong>{{ $card['value'] }}</strong>
                    <span>{{ $card['caption'] }}</span>
                </article>
            @endforeach
        </section>

        <section class="dashboard-compact-grid">
            <article class="compact-card dashboard-sales-card">
                <div class="compact-card__head">
                    <div>
                        <p class="dashboard-heading__eyebrow">7 Hari Terakhir</p>
                        <h3>Grafik Penjualan Lunas</h3>
                        <p>Nilai penjualan yang sudah diverifikasi sebagai lunas.</p>
                    </div>
                    <a href="{{ route('admin.sales.index') }}" class="compact-action">Lihat transaksi</a>
                </div>
                <div class="dashboard-mini-chart" aria-label="Grafik penjualan tujuh hari terakhir">
                    @foreach($salesChart as $point)
                        <div class="dashboard-mini-chart__item">
                            <span class="dashboard-mini-chart__value">Rp{{ number_format($point['amount'], 0, ',', '.') }}</span>
                            <div class="dashboard-mini-chart__track"><span style="height: {{ max(3, $point['value']) }}%"></span></div>
                            <small>{{ $point['label'] }}</small>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="compact-card">
                <div class="compact-card__head">
                    <div><p class="dashboard-heading__eyebrow">Status Pesanan</p><h3>Distribusi Transaksi</h3><p>Sesuai periode yang dipilih.</p></div>
                </div>
                <div class="dashboard-status-list">
                    @forelse($orderStatuses as $status)
                        <div><span class="dashboard-status-dot dashboard-status-dot--{{ $status['class'] }}"></span><span>{{ $status['label'] }}</span><strong>{{ number_format($status['count']) }}</strong></div>
                    @empty
                        <p class="compact-empty">Belum ada transaksi.</p>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="dashboard-compact-grid dashboard-compact-grid--wide">
            <article class="compact-card">
                <div class="compact-card__head">
                    <div><p class="dashboard-heading__eyebrow">Kontrol Persediaan</p><h3>Peringatan Restock</h3><p>SKU pada atau di bawah batas minimum.</p></div>
                    <a href="{{ route('admin.warehouse-stocks.index', ['status' => 'low']) }}" class="compact-action">Analisis stok</a>
                </div>
                <div class="compact-table-wrap">
                    <table class="compact-table">
                        <thead><tr><th>SKU / Produk</th><th>Room</th><th>Tersedia</th><th>Minimum</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($restockAlerts as $stock)
                                <tr>
                                    <td><strong>{{ $stock->productVariant?->sku ?? '-' }}</strong><br><small>{{ $stock->productVariant?->product?->name ?? '-' }}</small></td>
                                    <td>{{ $stock->warehouse?->name ?? '-' }}</td>
                                    <td>{{ number_format($stock->availableQuantity()) }}</td>
                                    <td>{{ number_format($stock->minimum_stock) }}</td>
                                    <td><span class="status-pill status-pill--{{ $stock->availableQuantity() <= 0 ? 'red' : 'peach' }}">{{ $stock->stockStatusLabel() }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="compact-empty">Tidak ada SKU yang membutuhkan perhatian.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <aside class="dashboard-quick-stack">
                <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="dashboard-quick-card glass-panel">
                    <span class="dashboard-quick-card__icon">@include('partials.admin.icon', ['icon' => 'payment'])</span>
                    <span><small>Pembayaran Menunggu</small><strong>{{ number_format($salesSummary['pending_payments']) }} transaksi</strong><em>Periksa bukti pembayaran</em></span>
                </a>
                <a href="{{ route('admin.shipments.index') }}" class="dashboard-quick-card glass-panel">
                    <span class="dashboard-quick-card__icon">@include('partials.admin.icon', ['icon' => 'shipping'])</span>
                    <span><small>Pengiriman</small><strong>Kelola status & resi</strong><em>Buka daftar pengiriman</em></span>
                </a>
                <button type="button" class="dashboard-quick-card glass-panel" data-coming-soon="Purchase Request dan Pembelian akan dikerjakan pada bagian berikutnya oleh tim pengembangan.">
                    <span class="dashboard-quick-card__icon">@include('partials.admin.icon', ['icon' => 'purchase'])</span>
                    <span><small>Permintaan Pembelian</small><strong>Tahap berikutnya</strong><em>Lihat informasi pengembangan</em></span>
                </button>
                <button type="button" class="dashboard-quick-card glass-panel" data-coming-soon="Arus Kas dan Laporan Keuangan akan dikerjakan pada bagian berikutnya oleh tim pengembangan.">
                    <span class="dashboard-quick-card__icon">@include('partials.admin.icon', ['icon' => 'report'])</span>
                    <span><small>Arus Kas & Laporan</small><strong>Tahap berikutnya</strong><em>Lihat informasi pengembangan</em></span>
                </button>
            </aside>
        </section>

        <section class="dashboard-performance-grid">
            <article class="compact-card">
                <div class="compact-card__head"><div><p class="dashboard-heading__eyebrow">30 Hari</p><h3>Fast Moving</h3><p>Produk dengan jumlah penjualan tertinggi.</p></div></div>
                <div class="dashboard-product-list">
                    @forelse($fastMovingProducts as $index => $product)
                        <div><span>{{ $index + 1 }}</span><strong>{{ $product->name }}</strong><em>{{ number_format($product->sold) }} unit</em></div>
                    @empty
                        <p class="compact-empty">Belum ada data penjualan.</p>
                    @endforelse
                </div>
            </article>

            <article class="compact-card">
                <div class="compact-card__head"><div><p class="dashboard-heading__eyebrow">30 Hari</p><h3>Slow Moving</h3><p>Produk terjual dengan pergerakan paling rendah.</p></div></div>
                <div class="dashboard-product-list dashboard-product-list--slow">
                    @forelse($slowMovingProducts as $index => $product)
                        <div><span>{{ $index + 1 }}</span><strong>{{ $product->name }}</strong><em>{{ number_format($product->sold) }} unit</em></div>
                    @empty
                        <p class="compact-empty">Belum ada data penjualan.</p>
                    @endforelse
                </div>
            </article>

            @if($isOwner)
                <article class="compact-card">
                    <div class="compact-card__head"><div><p class="dashboard-heading__eyebrow">Audit Trail</p><h3>Aktivitas Terbaru</h3><p>Aktivitas penting dalam sistem.</p></div><a href="{{ route('admin.activity-logs.index') }}" class="compact-action">Lihat log</a></div>
                    <div class="dashboard-activity-list">
                        @forelse($activities as $activity)
                            <div><span class="dashboard-activity-dot"></span><span><strong>{{ $activity->actionLabel() }}</strong><small>{{ $activity->description }}</small><time>{{ $activity->created_at?->diffForHumans() }}</time></span></div>
                        @empty
                            <p class="compact-empty">Belum ada aktivitas.</p>
                        @endforelse
                    </div>
                </article>
            @endif
        </section>

        <section class="compact-card">
            <div class="compact-card__head">
                <div><p class="dashboard-heading__eyebrow">Transaksi Terbaru</p><h3>Penjualan Online & Offline</h3><p>Data nyata yang masuk melalui modul Penjualan.</p></div>
                <a href="{{ route('admin.sales.index') }}" class="compact-action">Lihat semua</a>
            </div>
            <div class="compact-table-wrap">
                <table class="compact-table">
                    <thead><tr><th>Nomor</th><th>Pelanggan</th><th>Channel</th><th>Total</th><th>Pembayaran</th><th>Pesanan</th><th>Waktu</th></tr></thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td><a href="{{ route('admin.sales.show', $transaction) }}" class="transaction-number">{{ $transaction->transaction_number }}</a></td>
                                <td>{{ $transaction->customer_name }}</td>
                                <td>{{ $transaction->channelLabel() }}</td>
                                <td><strong>Rp{{ number_format((float)$transaction->grand_total, 0, ',', '.') }}</strong></td>
                                <td><span class="status-pill status-pill--{{ $transaction->payment_status }}">{{ $transaction->paymentStatusLabel() }}</span></td>
                                <td><span class="status-pill status-pill--{{ $transaction->order_status }}">{{ $transaction->orderStatusLabel() }}</span></td>
                                <td>{{ $transaction->transaction_date?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="compact-empty">Belum ada transaksi penjualan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
