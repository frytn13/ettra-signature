<div class="category-form-main">
    <section class="category-form-card glass-panel">
        <div class="category-form-card__header">
            <span class="category-form-card__icon category-form-card__icon--peach" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 9 9-6 9 6v11H3V9Z"/><path d="M8 20v-7h8v7"/></svg></span>
            <div><p class="dashboard-heading__eyebrow">Identitas Room</p><h3>Informasi utama</h3><p>Kode room menjadi identitas singkat untuk stok, transfer, penerimaan, dan riwayat persediaan.</p></div>
        </div>

        <div class="category-form-grid">
            <label class="category-form-field">
                <span>Kode Room</span>
                <input type="text" name="code" value="{{ old('code', $warehouse->code) }}" maxlength="20" placeholder="Contoh: GD-001" autocomplete="off">
                <small>Kosongkan saat tambah jika ingin sistem membuat kode otomatis.</small>
                @error('code')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>

            <label class="category-form-field">
                <span>Nama Room <em>*</em></span>
                <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" maxlength="150" placeholder="Contoh: Room Utama" autocomplete="off" required>
                <small>Gunakan nama lokasi yang mudah dikenali pengguna internal.</small>
                @error('name')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>

            <label class="category-form-field category-form-field--full">
                <span>Alamat Room</span>
                <textarea name="address" rows="4" maxlength="2000" placeholder="Masukkan alamat atau lokasi room secara jelas...">{{ old('address', $warehouse->address) }}</textarea>
                <small>Opsional. Informasi ini membantu identifikasi lokasi fisik.</small>
                @error('address')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>

            <label class="category-form-field category-form-field--full">
                <span>Deskripsi</span>
                <textarea name="description" rows="4" maxlength="1000" placeholder="Contoh: Room utama untuk penyimpanan seluruh stok masuk dari pemasok...">{{ old('description', $warehouse->description) }}</textarea>
                <small>Opsional. Maksimal 1000 karakter.</small>
                @error('description')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>
        </div>
    </section>

    <section class="category-form-card glass-panel">
        <div class="category-form-card__header">
            <span class="category-form-card__icon category-form-card__icon--green" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg></span>
            <div><p class="dashboard-heading__eyebrow">Ketersediaan</p><h3>Status room</h3><p>Room aktif dapat dipilih pada proses stok. Room nonaktif tetap tersimpan untuk menjaga histori.</p></div>
        </div>

        <div class="category-status-options">
            <label class="category-status-option"><input type="radio" name="is_active" value="1" @checked((string) old('is_active', $warehouse->is_active ? '1' : '0') === '1')><span class="category-status-option__surface"><span class="category-status-option__icon category-status-option__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg></span><span><strong>Aktif</strong><small>Dapat digunakan untuk operasional persediaan.</small></span></span></label>
            <label class="category-status-option"><input type="radio" name="is_active" value="0" @checked((string) old('is_active', $warehouse->is_active ? '1' : '0') === '0')><span class="category-status-option__surface"><span class="category-status-option__icon category-status-option__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg></span><span><strong>Nonaktif</strong><small>Tidak digunakan pada transaksi baru, histori tetap aman.</small></span></span></label>
        </div>
        @error('is_active')<span class="category-field-error">{{ $message }}</span>@enderror
    </section>
</div>

<aside class="category-form-side">
    <section class="category-preview-card glass-panel">
        <p class="dashboard-heading__eyebrow">Preview</p><h3>Identitas room</h3>
        <div class="category-preview-card__sample"><span class="category-preview-card__code">{{ old('code', $warehouse->code ?: 'GD-001') }}</span><span><strong>{{ old('name', $warehouse->name ?: 'Room Utama') }}</strong><small>Lokasi persediaan</small></span></div>
        <p>Setelah modul Stok Room aktif, setiap SKU dapat memiliki jumlah stok berbeda pada masing-masing lokasi.</p>
    </section>

    @if ($isEditing)
        <section class="category-meta-card glass-panel"><p class="dashboard-heading__eyebrow">Metadata</p><h3>Riwayat data</h3><dl><div><dt>Dibuat</dt><dd>{{ $warehouse->created_at?->format('d M Y, H:i') ?? '-' }}</dd></div><div><dt>Diperbarui</dt><dd>{{ $warehouse->updated_at?->format('d M Y, H:i') ?? '-' }}</dd></div></dl></section>
    @endif

    <section class="category-form-note glass-panel"><span class="category-form-note__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></svg></span><div><strong>Jangan hapus histori lokasi</strong><p>Jika room nantinya sudah mempunyai stok atau transaksi, gunakan Nonaktif daripada menghapus data.</p></div></section>

    <div class="category-form-actions glass-panel"><a href="{{ $isEditing ? route('admin.warehouses.show', $warehouse) : route('admin.warehouses.index') }}" class="button button--secondary">Batal</a><button type="submit" class="button button--primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12.5 9 16l10-10"/></svg>{{ $submitLabel }}</button></div>
</aside>
