@php
    $selectedCategory = old('category_id', $product->category_id);
    $selectedStatus = old('status', $product->status ?: \App\Models\Product::STATUS_ACTIVE);
    $selectedAvailability = old('availability_status', $product->availability_status ?: \App\Models\Product::AVAILABILITY_AVAILABLE);
    $selectedVisibility = (string) old('is_visible', $product->is_visible ? '1' : '0');
    $currentImages = $isEditing ? $product->images : collect();
@endphp

<div class="product-form-main">
    <section class="category-form-card glass-panel">
        <div class="category-form-card__header">
            <span class="category-form-card__icon category-form-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/></svg></span>
            <div><p class="dashboard-heading__eyebrow">Identitas Produk</p><h3>Informasi dasar</h3><p>Kategori menjadi dasar kode otomatis produk. Kode yang dibuat otomatis tetap dapat disesuaikan setelah produk tersimpan.</p></div>
        </div>

        <div class="category-form-grid product-form-grid">
            <label class="category-form-field">
                <span>Kategori <em>*</em></span>
                <select name="category_id" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $selectedCategory === (string) $category->id)>
                            {{ $category->code }} · {{ $category->name }}{{ isset($category->is_active) && ! $category->is_active ? ' (Nonaktif)' : '' }}
                        </option>
                    @endforeach
                </select>
                <small>Produk baru hanya dapat memakai kategori aktif.</small>
                @error('category_id')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>

            <label class="category-form-field">
                <span>Kode Produk {{ $isEditing ? '*' : '' }}</span>
                <input type="text" name="code" value="{{ old('code', $product->code) }}" maxlength="40" placeholder="{{ $isEditing ? 'Contoh: HD-0001' : 'Kosongkan untuk kode otomatis' }}" autocomplete="off" {{ $isEditing ? 'required' : '' }}>
                <small>{{ $isEditing ? 'Kode dapat diubah manual dan harus unik.' : 'Format otomatis: KODEKATEGORI-0001.' }}</small>
                @error('code')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>

            <label class="category-form-field category-form-field--full">
                <span>Nama Produk <em>*</em></span>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" maxlength="180" placeholder="Contoh: Home Dress Aster" autocomplete="off" required>
                <small>Nama akan digunakan pada katalog pelanggan dan pencarian.</small>
                @error('name')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>

            <label class="category-form-field category-form-field--full">
                <span>Deskripsi Produk</span>
                <textarea name="description" rows="6" maxlength="5000" placeholder="Tuliskan bahan, karakter produk, detail desain, atau informasi penting lainnya...">{{ old('description', $product->description) }}</textarea>
                <small>Maksimal 5000 karakter.</small>
                @error('description')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>
        </div>
    </section>

    <section class="category-form-card glass-panel">
        <div class="category-form-card__header">
            <span class="category-form-card__icon category-form-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8M12 8v8"/></svg></span>
            <div><p class="dashboard-heading__eyebrow">Harga</p><h3>Harga jual {{ $isOwner ? 'dan data komersial' : '' }}</h3><p>{{ $isOwner ? 'Harga beli awal dan harga modal bersifat rahasia dan hanya tersedia untuk Owner.' : 'Admin hanya dapat mengelola harga jual. Harga beli, modal, margin, dan profit dilindungi oleh sistem.' }}</p></div>
        </div>

        <div class="product-price-grid {{ $isOwner ? '' : 'product-price-grid--admin' }}" data-product-pricing>
            @if ($isOwner)
                <label class="category-form-field">
                    <span>Harga Beli Awal</span>
                    <div class="product-money-field"><span>Rp</span><input type="number" name="initial_purchase_price" value="{{ old('initial_purchase_price', $product->initial_purchase_price) }}" min="0" step="0.01" placeholder="0" data-product-initial-price></div>
                    <small>Harga pembelian pertama produk. Dapat dikosongkan bila belum diketahui.</small>
                    @error('initial_purchase_price')<span class="category-field-error">{{ $message }}</span>@enderror
                </label>

                <label class="category-form-field">
                    <span>Harga Modal</span>
                    <div class="product-money-field"><span>Rp</span><input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" min="0" step="0.01" placeholder="0" data-product-cost-price></div>
                    <small>Digunakan sebagai dasar estimasi profit dan margin.</small>
                    @error('cost_price')<span class="category-field-error">{{ $message }}</span>@enderror
                </label>
            @endif

            <label class="category-form-field">
                <span>Harga Jual <em>*</em></span>
                <div class="product-money-field"><span>Rp</span><input type="number" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" min="0.01" step="0.01" placeholder="0" required data-product-selling-price></div>
                <small>Harga yang ditampilkan dan digunakan pada transaksi.</small>
                @error('selling_price')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>

            @if ($isOwner)
                <div class="product-financial-preview">
                    <span><small>Estimasi Profit</small><strong data-product-profit>-</strong></span>
                    <span><small>Gross Margin</small><strong data-product-margin>-</strong></span>
                </div>
            @else
                <div class="product-protected-panel">
                    <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
                    <div><strong>Data komersial Owner dilindungi</strong><p>Harga beli, harga modal, margin, dan estimasi keuntungan tidak dikirim melalui formulir Admin.</p></div>
                </div>
            @endif
        </div>
    </section>

    <section class="category-form-card glass-panel">
        <div class="category-form-card__header">
            <span class="category-form-card__icon category-form-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v14H4z"/><path d="m4 15 4-4 4 4 3-3 5 5"/><circle cx="15.5" cy="8.5" r="1.5"/></svg></span>
            <div><p class="dashboard-heading__eyebrow">Media Produk</p><h3>Foto utama dan galeri</h3><p>Gunakan JPG, PNG, atau WebP maksimal 5 MB per gambar. Total foto produk maksimal 8 gambar.</p></div>
        </div>

        <div class="product-upload-grid">
            <label class="product-upload-card">
                <span class="product-upload-card__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 16V4m0 0-4 4m4-4 4 4"/><path d="M5 14v5h14v-5"/></svg></span>
                <span><strong>Foto Utama</strong><small>{{ $isEditing ? 'Unggah untuk mengganti foto utama saat ini.' : 'Foto pertama yang mewakili produk.' }}</small></span>
                <input type="file" name="primary_image" accept="image/jpeg,image/png,image/webp" data-product-primary-input>
            </label>

            <label class="product-upload-card">
                <span class="product-upload-card__icon product-upload-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m6 16 4-4 3 3 2-2 3 3"/></svg></span>
                <span><strong>Foto Tambahan</strong><small>Pilih maksimal 6 gambar tambahan sekaligus.</small></span>
                <input type="file" name="additional_images[]" accept="image/jpeg,image/png,image/webp" multiple data-product-gallery-input>
            </label>
        </div>

        <div class="product-upload-preview" data-product-image-preview hidden></div>
        @error('primary_image')<span class="category-field-error product-upload-error">{{ $message }}</span>@enderror
        @error('additional_images')<span class="category-field-error product-upload-error">{{ $message }}</span>@enderror
        @error('additional_images.*')<span class="category-field-error product-upload-error">{{ $message }}</span>@enderror

        @if ($isEditing && $currentImages->isNotEmpty())
            <div class="product-existing-images">
                <div class="product-existing-images__heading"><strong>Foto tersimpan</strong><small>Centang foto yang ingin dihapus saat perubahan disimpan.</small></div>
                <div class="product-existing-images__grid">
                    @foreach ($currentImages as $image)
                        <label class="product-existing-image">
                            <img src="{{ asset('storage/'.$image->path) }}" alt="Foto {{ $product->name }}">
                            @if ($image->is_primary)<span class="product-image-primary-badge">Utama</span>@endif
                            <span class="product-existing-image__remove"><input type="checkbox" name="remove_image_ids[]" value="{{ $image->id }}" @checked(in_array($image->id, old('remove_image_ids', [])))> Hapus</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <section class="category-form-card glass-panel">
        <div class="category-form-card__header">
            <span class="category-form-card__icon category-form-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg></span>
            <div><p class="dashboard-heading__eyebrow">Status & Informasi Fisik</p><h3>Ketersediaan produk</h3><p>Status produk dan visibilitas menentukan apakah produk dapat diproses dan ditampilkan pada halaman pelanggan.</p></div>
        </div>

        <div class="category-form-grid product-form-grid">
            <label class="category-form-field">
                <span>Status Produk <em>*</em></span>
                <select name="status" required>
                    @foreach ($statusOptions as $value => $label)<option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>@endforeach
                </select>
                <small>Produk nonaktif atau dihentikan otomatis disembunyikan dari katalog saat disimpan.</small>
                @error('status')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>

            <label class="category-form-field">
                <span>Status Ketersediaan <em>*</em></span>
                <select name="availability_status" required>
                    @foreach ($availabilityOptions as $value => $label)<option value="{{ $value }}" @selected($selectedAvailability === $value)>{{ $label }}</option>@endforeach
                </select>
                <small>Stok riil akan dikelola terpisah setelah modul Room dan Stok dibuat.</small>
                @error('availability_status')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>

            <label class="category-form-field">
                <span>Berat Produk</span>
                <div class="product-unit-field"><input type="number" name="weight_grams" value="{{ old('weight_grams', $product->weight_grams) }}" min="1" max="1000000" step="1" placeholder="Contoh: 450"><span>gram</span></div>
                <small>Digunakan untuk kebutuhan pengiriman pada tahap berikutnya.</small>
                @error('weight_grams')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>

            <label class="category-form-field">
                <span>Tanggal Produk Masuk</span>
                <input type="date" name="entry_date" value="{{ old('entry_date', $product->entry_date?->format('Y-m-d') ?? $product->entry_date) }}" max="{{ now()->toDateString() }}">
                <small>Tanggal awal produk dicatat masuk ke usaha.</small>
                @error('entry_date')<span class="category-field-error">{{ $message }}</span>@enderror
            </label>
        </div>

        <div class="product-visibility-options">
            <label class="category-status-option"><input type="radio" name="is_visible" value="1" @checked($selectedVisibility === '1')><span class="category-status-option__surface"><span class="category-status-option__icon category-status-option__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></span><span><strong>Tampilkan di Katalog</strong><small>Produk dapat muncul pada halaman pelanggan jika statusnya Aktif.</small></span></span></label>
            <label class="category-status-option"><input type="radio" name="is_visible" value="0" @checked($selectedVisibility === '0')><span class="category-status-option__surface"><span class="category-status-option__icon category-status-option__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3l18 18M10.6 10.7a2 2 0 0 0 2.7 2.7M9.9 5.2A9.8 9.8 0 0 1 12 5c5.5 0 9 7 9 7a15.4 15.4 0 0 1-2.2 3.1M6.6 6.6C4.4 8.1 3 12 3 12s3.5 7 9 7c1.5 0 2.8-.5 4-1.2"/></svg></span><span><strong>Sembunyikan dari Katalog</strong><small>Produk tetap tersedia di sistem internal tanpa tampil ke pelanggan.</small></span></span></label>
        </div>
        @error('is_visible')<span class="category-field-error">{{ $message }}</span>@enderror
    </section>
</div>

<aside class="product-form-side">
    <section class="product-form-summary glass-panel">
        <p class="dashboard-heading__eyebrow">Ringkasan</p>
        <h3>{{ $isEditing ? 'Produk tersimpan' : 'Produk baru' }}</h3>
        <div class="product-form-summary__mark">{{ mb_strtoupper(mb_substr(old('name', $product->name ?: 'E'), 0, 1)) }}</div>
        <strong>{{ old('name', $product->name ?: 'Nama Produk') }}</strong>
        <small>{{ old('code', $product->code ?: 'Kode dibuat otomatis') }}</small>
        @if ($isOwner)<span class="product-owner-chip">Akses Owner</span>@else<span class="product-admin-chip">Akses Admin</span>@endif
    </section>

    @if ($isEditing)
        <section class="category-meta-card glass-panel">
            <p class="dashboard-heading__eyebrow">Metadata</p><h3>Riwayat data</h3>
            <dl><div><dt>Dibuat</dt><dd>{{ $product->created_at?->format('d M Y, H:i') ?? '-' }}</dd></div><div><dt>Diperbarui</dt><dd>{{ $product->updated_at?->format('d M Y, H:i') ?? '-' }}</dd></div></dl>
        </section>
    @endif

    <section class="category-form-note glass-panel">
        <span class="category-form-note__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></svg></span>
        <div><strong>Variasi dan stok menyusul</strong><p>Warna, ukuran, SKU variasi, room, dan stok tidak ditempel langsung pada produk dasar agar struktur persediaan tetap akurat.</p></div>
    </section>

    <div class="category-form-actions glass-panel"><a href="{{ route('admin.products.index') }}" class="button button--secondary">Batal</a><button type="submit" class="button button--primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12.5 9 16l10-10"/></svg>{{ $submitLabel }}</button></div>
</aside>
