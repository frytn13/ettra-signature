<div class="category-form-main">
    <section class="category-form-card glass-panel">
        <div class="category-form-card__header">
            <span class="category-form-card__icon category-form-card__icon--peach" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>
            </span>
            <div>
                <p class="dashboard-heading__eyebrow">Identitas Kategori</p>
                <h3>Informasi utama</h3>
                <p>Kode kategori dipakai sebagai identitas singkat, sedangkan nama akan tampil pada halaman Admin dan katalog pelanggan.</p>
            </div>
        </div>

        <div class="category-form-grid">
            <label class="category-form-field">
                <span>Kode Kategori <em>*</em></span>
                <input
                    type="text"
                    name="code"
                    value="{{ old('code', $category->code) }}"
                    maxlength="10"
                    placeholder="Contoh: MK"
                    autocomplete="off"
                    required
                >
                <small>2–10 karakter. Sistem otomatis mengubah huruf menjadi kapital.</small>
                @error('code')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>

            <label class="category-form-field">
                <span>Nama Kategori <em>*</em></span>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $category->name) }}"
                    maxlength="100"
                    placeholder="Contoh: Mukenah"
                    autocomplete="off"
                    required
                >
                <small>Gunakan nama yang mudah dipahami pelanggan dan pengguna internal.</small>
                @error('name')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>

            <label class="category-form-field category-form-field--full">
                <span>Deskripsi</span>
                <textarea
                    name="description"
                    rows="5"
                    maxlength="1000"
                    placeholder="Jelaskan jenis produk yang termasuk dalam kategori ini..."
                >{{ old('description', $category->description) }}</textarea>
                <small>Opsional. Maksimal 1000 karakter.</small>
                @error('description')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>
        </div>
    </section>

    <section class="category-form-card glass-panel">
        <div class="category-form-card__header">
            <span class="category-form-card__icon category-form-card__icon--green" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
            </span>
            <div>
                <p class="dashboard-heading__eyebrow">Ketersediaan</p>
                <h3>Status kategori</h3>
                <p>Kategori aktif dapat digunakan saat membuat produk. Kategori nonaktif tetap tersimpan untuk menjaga konsistensi data lama.</p>
            </div>
        </div>

        <div class="category-status-options">
            <label class="category-status-option">
                <input type="radio" name="is_active" value="1" @checked((string) old('is_active', $category->is_active ? '1' : '0') === '1')>
                <span class="category-status-option__surface">
                    <span class="category-status-option__icon category-status-option__icon--green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                    <span><strong>Aktif</strong><small>Dapat digunakan pada data produk baru.</small></span>
                </span>
            </label>

            <label class="category-status-option">
                <input type="radio" name="is_active" value="0" @checked((string) old('is_active', $category->is_active ? '1' : '0') === '0')>
                <span class="category-status-option__surface">
                    <span class="category-status-option__icon category-status-option__icon--peach">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg>
                    </span>
                    <span><strong>Nonaktif</strong><small>Tidak tersedia untuk produk baru tetapi data tetap tersimpan.</small></span>
                </span>
            </label>
        </div>
        @error('is_active')<span class="category-field-error">{{ $message }}</span>@enderror
    </section>
</div>

<aside class="category-form-side">
    <section class="category-preview-card glass-panel">
        <p class="dashboard-heading__eyebrow">Preview</p>
        <h3>Format kategori</h3>
        <div class="category-preview-card__sample">
            <span class="category-preview-card__code">{{ old('code', $category->code ?: 'MK') }}</span>
            <span>
                <strong>{{ old('name', $category->name ?: 'Mukenah') }}</strong>
                <small>Slug dibuat otomatis</small>
            </span>
        </div>
        <p>Contoh kategori awal dari kebutuhan sistem: MK untuk Mukenah, HD untuk Home Dress, dan JB untuk Jilbab.</p>
    </section>

    @if ($isEditing)
        <section class="category-meta-card glass-panel">
            <p class="dashboard-heading__eyebrow">Metadata</p>
            <h3>Riwayat data</h3>
            <dl>
                <div><dt>Slug</dt><dd>/{{ $category->slug }}</dd></div>
                <div><dt>Dibuat</dt><dd>{{ $category->created_at?->format('d M Y, H:i') ?? '-' }}</dd></div>
                <div><dt>Diperbarui</dt><dd>{{ $category->updated_at?->format('d M Y, H:i') ?? '-' }}</dd></div>
            </dl>
        </section>
    @endif

    <section class="category-form-note glass-panel">
        <span class="category-form-note__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></svg>
        </span>
        <div>
            <strong>Kode harus konsisten</strong>
            <p>Setelah kategori dipakai oleh produk, perubahan kode sebaiknya dilakukan hanya jika memang diperlukan agar identitas produk tetap mudah dilacak.</p>
        </div>
    </section>

    <div class="category-form-actions glass-panel">
        <a href="{{ route('admin.categories.index') }}" class="button button--secondary">Batal</a>
        <button type="submit" class="button button--primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12.5 9 16l10-10"/></svg>
            {{ $submitLabel }}
        </button>
    </div>
</aside>
