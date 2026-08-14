@php
    $rawPreviewHex = old('hex_code', $color->hex_code);
    $previewHex = is_string($rawPreviewHex) && preg_match('/^#[0-9A-Fa-f]{6}$/', $rawPreviewHex)
        ? strtoupper($rawPreviewHex)
        : null;
@endphp

<div class="category-form-main">
    <section class="category-form-card glass-panel">
        <div class="category-form-card__header">
            <span class="category-form-card__icon category-form-card__icon--peach" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><path d="M8 12a4 4 0 0 1 8 0c0 2.2-1.8 4-4 4"/></svg></span>
            <div><p class="dashboard-heading__eyebrow">Informasi Warna</p><h3>Identitas master warna</h3><p>Kode dipakai sebagai identitas singkat. Nama warna akan tampil pada Admin dan pilihan variasi produk pelanggan.</p></div>
        </div>

        <div class="category-form-grid">
            <label class="category-form-field">
                <span>Kode Warna <em>*</em></span>
                <input type="text" name="code" value="{{ old('code', $color->code) }}" maxlength="12" placeholder="Contoh: PCH" autocomplete="off" required>
                <small>Maksimal 12 karakter. Huruf otomatis menjadi kapital.</small>
                @error('code')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>
            <label class="category-form-field">
                <span>Nama Warna <em>*</em></span>
                <input type="text" name="name" value="{{ old('name', $color->name) }}" maxlength="80" placeholder="Contoh: Peach" autocomplete="off" required>
                <small>Gunakan nama yang mudah dipahami pelanggan.</small>
                @error('name')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>
            <label class="category-form-field category-form-field--full">
                <span>Kode HEX</span>
                <div class="master-hex-input-wrap">
                    <input type="text" name="hex_code" value="{{ old('hex_code', $color->hex_code) }}" maxlength="7" placeholder="#BB7F73" autocomplete="off">
                    <span class="master-color-swatch master-color-swatch--form {{ $previewHex ? '' : 'master-color-swatch--empty' }}" @if($previewHex) style="background-color: {{ $previewHex }}" @endif aria-hidden="true"></span>
                </div>
                <small>Opsional. Gunakan format #RRGGBB. Kosongkan untuk warna motif atau warna yang tidak tepat direpresentasikan oleh satu HEX.</small>
                @error('hex_code')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>
        </div>
    </section>

    <section class="category-form-card glass-panel">
        <div class="category-form-card__header">
            <span class="category-form-card__icon category-form-card__icon--green" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg></span>
            <div><p class="dashboard-heading__eyebrow">Ketersediaan</p><h3>Status warna</h3><p>Warna aktif dapat dipilih pada variasi produk baru. Warna nonaktif tetap tersimpan untuk menjaga data variasi lama.</p></div>
        </div>
        <div class="category-status-options">
            <label class="category-status-option"><input type="radio" name="is_active" value="1" @checked((string) old('is_active', $color->is_active ? '1' : '0') === '1')><span class="category-status-option__surface"><span class="category-status-option__icon category-status-option__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg></span><span><strong>Aktif</strong><small>Dapat dipakai pada variasi produk baru.</small></span></span></label>
            <label class="category-status-option"><input type="radio" name="is_active" value="0" @checked((string) old('is_active', $color->is_active ? '1' : '0') === '0')><span class="category-status-option__surface"><span class="category-status-option__icon category-status-option__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg></span><span><strong>Nonaktif</strong><small>Tidak tersedia untuk variasi baru.</small></span></span></label>
        </div>
        @error('is_active')<span class="category-field-error">{{ $message }}</span>@enderror
    </section>
</div>

<aside class="category-form-side">
    <section class="category-preview-card glass-panel">
        <p class="dashboard-heading__eyebrow">Preview</p><h3>Format warna</h3>
        <div class="category-preview-card__sample">
            <span class="master-color-swatch {{ $previewHex ? '' : 'master-color-swatch--empty' }}" @if($previewHex) style="background-color: {{ $previewHex }}" @endif aria-hidden="true"></span>
            <span><strong>{{ old('name', $color->name ?: 'Peach') }}</strong><small>{{ old('code', $color->code ?: 'PCH') }} · {{ old('hex_code', $color->hex_code ?: '#BB7F73') }}</small></span>
        </div>
        <p>HEX digunakan sebagai preview visual. Nilai warna sebenarnya tetap ditentukan oleh nama variasi yang dipilih pengguna.</p>
    </section>

    @if ($isEditing)
        <section class="category-meta-card glass-panel"><p class="dashboard-heading__eyebrow">Metadata</p><h3>Riwayat data</h3><dl><div><dt>Dibuat</dt><dd>{{ $color->created_at?->format('d M Y, H:i') ?? '-' }}</dd></div><div><dt>Diperbarui</dt><dd>{{ $color->updated_at?->format('d M Y, H:i') ?? '-' }}</dd></div></dl></section>
    @endif

    <section class="category-form-note glass-panel"><span class="category-form-note__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></svg></span><div><strong>Jaga konsistensi nama</strong><p>Jika warna sudah digunakan produk, gunakan status nonaktif daripada mengganti identitasnya secara drastis.</p></div></section>

    <div class="category-form-actions glass-panel"><a href="{{ route('admin.colors.index') }}" class="button button--secondary">Batal</a><button type="submit" class="button button--primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12.5 9 16l10-10"/></svg>{{ $submitLabel }}</button></div>
</aside>
