@extends('layouts.admin')

@section('title', 'Activity Log')
@section('eyebrow', 'Audit Trail')
@section('page-title', 'Activity Log')

@section('content')
    <section class="dashboard-heading activity-log-heading">
        <div>
            <p class="dashboard-heading__eyebrow">Owner Only</p>
            <h2>Jejak aktivitas sistem</h2>
            <p>
                Pantau aktivitas penting pengguna internal. Data audit bersifat read-only dan tidak menyediakan fitur edit maupun hapus dari antarmuka.
            </p>
        </div>

        <div class="activity-log-heading__security glass-panel">
            <span class="activity-log-heading__security-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 3 5 6v5c0 4.6 2.8 8.1 7 10 4.2-1.9 7-5.4 7-10V6l-7-3Z"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
            </span>
            <span>
                <strong>Audit trail aktif</strong>
                <small>Login, logout, serta perubahan User Management dicatat otomatis.</small>
            </span>
        </div>
    </section>

    <section class="activity-summary-grid" aria-label="Ringkasan activity log">
        <article class="activity-summary-card glass-panel">
            <span class="activity-summary-card__icon activity-summary-card__icon--peach">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12h4l2-5 4 10 2-5h6"/><circle cx="12" cy="12" r="9"/></svg>
            </span>
            <span class="activity-summary-card__copy">
                <small>Total Aktivitas</small>
                <strong>{{ number_format($statistics['total']) }}</strong>
                <span>Seluruh audit trail tersimpan</span>
            </span>
        </article>

        <article class="activity-summary-card glass-panel">
            <span class="activity-summary-card__icon activity-summary-card__icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            </span>
            <span class="activity-summary-card__copy">
                <small>Hari Ini</small>
                <strong>{{ number_format($statistics['today']) }}</strong>
                <span>Aktivitas sejak pukul 00.00</span>
            </span>
        </article>

        <article class="activity-summary-card glass-panel">
            <span class="activity-summary-card__icon activity-summary-card__icon--peach">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M14 4h5a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-5"/></svg>
            </span>
            <span class="activity-summary-card__copy">
                <small>Autentikasi</small>
                <strong>{{ number_format($statistics['authentication']) }}</strong>
                <span>Login, logout, dan login gagal</span>
            </span>
        </article>

        <article class="activity-summary-card glass-panel">
            <span class="activity-summary-card__icon activity-summary-card__icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v2"/><path d="m17 14 4 4M21 14l-4 4"/></svg>
            </span>
            <span class="activity-summary-card__copy">
                <small>User Management</small>
                <strong>{{ number_format($statistics['user_management']) }}</strong>
                <span>Perubahan akun internal</span>
            </span>
        </article>
    </section>

    <section class="activity-log-card glass-panel">
        <div class="activity-log-card__header">
            <div>
                <p class="dashboard-heading__eyebrow">Audit Trail</p>
                <h3>Riwayat aktivitas</h3>
                <p>Gunakan pencarian, kategori aktivitas, modul, dan rentang tanggal untuk mempersempit hasil.</p>
            </div>
        </div>

        <form action="{{ route('admin.activity-logs.index') }}" method="GET" class="activity-filter-form">
            <label class="activity-filter-field activity-filter-field--search">
                <span class="sr-only">Cari activity log</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Cari pengguna, deskripsi, email, atau IP..."
                    autocomplete="off"
                >
            </label>

            <label class="activity-filter-field">
                <span class="sr-only">Filter aksi</span>
                <select name="action">
                    <option value="">Semua aktivitas</option>
                    @foreach ($actionOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['action'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="activity-filter-field">
                <span class="sr-only">Filter modul</span>
                <select name="module">
                    <option value="">Semua modul</option>
                    @foreach ($moduleOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['module'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="activity-filter-field activity-filter-field--date">
                <span>Dari</span>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}">
            </label>

            <label class="activity-filter-field activity-filter-field--date">
                <span>Sampai</span>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}">
            </label>

            <button type="submit" class="button button--secondary button--small">Terapkan</button>

            @if (collect($filters)->contains(fn ($value) => $value !== ''))
                <a href="{{ route('admin.activity-logs.index') }}" class="button button--ghost button--small">Reset</a>
            @endif
        </form>

        @if ($logs->isEmpty())
            <div class="activity-log-empty">
                <span class="activity-log-empty__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 12h4l2-5 4 10 2-5h6"/><circle cx="12" cy="12" r="9"/></svg>
                </span>
                <h4>Belum ada activity log</h4>
                <p>Audit trail akan muncul setelah pengguna melakukan login, logout, atau perubahan melalui User Management.</p>
            </div>
        @else
            <div class="responsive-table-wrap activity-log-desktop-table">
                <table class="admin-table activity-log-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aktivitas</th>
                            <th>Modul</th>
                            <th>Deskripsi</th>
                            <th>IP</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td>
                                    <span class="activity-log-time">
                                        <strong>{{ $log->created_at?->format('d M Y') ?? '-' }}</strong>
                                        <small>{{ $log->created_at?->format('H:i:s') ?? '-' }}</small>
                                    </span>
                                </td>
                                <td>
                                    @if ($log->user)
                                        <span class="activity-log-user">
                                            <span class="activity-log-user__avatar activity-log-user__avatar--{{ $log->user->isOwner() ? 'owner' : 'admin' }}">
                                                {{ mb_strtoupper(mb_substr($log->user->name, 0, 1)) }}
                                            </span>
                                            <span>
                                                <strong>{{ $log->user->name }}</strong>
                                                <small>{{ $log->user->roleLabel() }}</small>
                                            </span>
                                        </span>
                                    @else
                                        <span class="activity-log-system-user">
                                            <strong>Guest / Sistem</strong>
                                            <small>Tanpa sesi pengguna</small>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="activity-action-badge activity-action-badge--{{ $log->tone() }}">
                                        {{ $log->actionLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="activity-module-badge">{{ $log->moduleLabel() }}</span>
                                </td>
                                <td class="activity-log-description">{{ $log->description }}</td>
                                <td><code class="activity-log-ip">{{ $log->ip_address ?: '-' }}</code></td>
                                <td>
                                    <details class="activity-detail">
                                        <summary aria-label="Lihat detail activity log">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
                                        </summary>
                                        <div class="activity-detail__panel">
                                            <div class="activity-detail__header">
                                                <strong>Detail Audit #{{ $log->id }}</strong>
                                                <small>{{ $log->created_at?->format('d M Y H:i:s') }}</small>
                                            </div>

                                            <div class="activity-detail__meta">
                                                <span><strong>IP</strong>{{ $log->ip_address ?: '-' }}</span>
                                                <span><strong>User Agent</strong>{{ $log->user_agent ?: '-' }}</span>
                                            </div>

                                            @if ($log->old_values)
                                                <div class="activity-detail__values">
                                                    <strong>Nilai Sebelum</strong>
                                                    <pre>{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                                </div>
                                            @endif

                                            @if ($log->new_values)
                                                <div class="activity-detail__values">
                                                    <strong>Nilai Sesudah / Konteks</strong>
                                                    <pre>{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                                </div>
                                            @endif

                                            @if (! $log->old_values && ! $log->new_values)
                                                <p class="activity-detail__empty">Aktivitas ini tidak memiliki perubahan nilai tambahan.</p>
                                            @endif
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="activity-log-mobile-list">
                @foreach ($logs as $log)
                    <article class="activity-log-mobile-card">
                        <div class="activity-log-mobile-card__top">
                            <span class="activity-action-badge activity-action-badge--{{ $log->tone() }}">{{ $log->actionLabel() }}</span>
                            <time>{{ $log->created_at?->format('d M Y, H:i') ?? '-' }}</time>
                        </div>

                        <h4>{{ $log->description }}</h4>

                        <dl>
                            <div><dt>Pengguna</dt><dd>{{ $log->user?->name ?? 'Guest / Sistem' }}</dd></div>
                            <div><dt>Modul</dt><dd>{{ $log->moduleLabel() }}</dd></div>
                            <div><dt>IP</dt><dd>{{ $log->ip_address ?: '-' }}</dd></div>
                        </dl>

                        <details class="activity-detail activity-detail--mobile">
                            <summary>Lihat detail</summary>
                            <div class="activity-detail__panel activity-detail__panel--mobile">
                                <div class="activity-detail__meta">
                                    <span><strong>User Agent</strong>{{ $log->user_agent ?: '-' }}</span>
                                </div>

                                @if ($log->old_values)
                                    <div class="activity-detail__values">
                                        <strong>Nilai Sebelum</strong>
                                        <pre>{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                @endif

                                @if ($log->new_values)
                                    <div class="activity-detail__values">
                                        <strong>Nilai Sesudah / Konteks</strong>
                                        <pre>{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                @endif
                            </div>
                        </details>
                    </article>
                @endforeach
            </div>

            <div class="user-pagination activity-log-pagination">
                <div class="user-pagination__summary">
                    Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ $logs->total() }} aktivitas
                </div>

                <div class="user-pagination__controls">
                    @if ($logs->onFirstPage())
                        <span class="user-pagination__button is-disabled">Sebelumnya</span>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}" class="user-pagination__button">Sebelumnya</a>
                    @endif

                    <span class="user-pagination__page">Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</span>

                    @if ($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}" class="user-pagination__button">Berikutnya</a>
                    @else
                        <span class="user-pagination__button is-disabled">Berikutnya</span>
                    @endif
                </div>
            </div>
        @endif
    </section>

    <section class="activity-log-policy glass-panel">
        <span class="activity-log-policy__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 4h12v16H6z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
        </span>
        <div>
            <strong>Kebijakan audit trail</strong>
            <p>
                Password, token, dan kredensial sensitif tidak disimpan ke activity log. Antarmuka ini hanya menyediakan akses baca untuk Owner agar riwayat aktivitas tidak dapat dimanipulasi melalui halaman admin.
            </p>
        </div>
    </section>
@endsection
