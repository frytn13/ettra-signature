@extends('layouts.admin')

@section('title', 'Penyesuaian Stok Baru')
@section('eyebrow', 'Persediaan')
@section('page-title', 'Penyesuaian Stok Baru')

@section('content')
    <section class="dashboard-heading category-form-heading">
        <div><p class="dashboard-heading__eyebrow">Stock Opname</p><h2>Catat hasil pemeriksaan stok fisik</h2><p>Masukkan jumlah fisik yang benar. Sistem menghitung selisih dan membuat ledger penyesuaian secara otomatis.</p></div>
        <div class="dashboard-heading__actions"><a href="{{ route('admin.stock-adjustments.index') }}" class="button button--secondary">Kembali</a></div>
    </section>

    @if($errors->any())
        <div class="user-alert user-alert--danger" role="alert"><span class="user-alert__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span>{{ $errors->first() }}</span></div>
    @endif

    <form action="{{ route('admin.stock-adjustments.store') }}" method="POST" class="category-form-layout">
        @csrf
        <div class="category-form-main">
            <section class="category-form-card glass-panel">
                <div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--peach"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/></svg></span><div><p class="dashboard-heading__eyebrow">Titik Stok</p><h3>Pilih SKU dan room</h3><p>Jumlah sistem pada pilihan ini akan menjadi dasar perbandingan stock opname.</p></div></div>
                @if($stocks->isEmpty())
                    <div class="category-empty-state"><h4>Belum ada titik stok</h4><p>Daftarkan SKU ke room terlebih dahulu sebelum membuat penyesuaian.</p><a href="{{ route('admin.warehouse-stocks.create') }}" class="button button--primary button--small">Daftarkan SKU</a></div>
                @else
                    <label class="category-form-field"><span>SKU / Room <em>*</em></span><select name="warehouse_stock_id" required><option value="">Pilih titik stok</option>@foreach($stocks as $stock)<option value="{{ $stock->id }}" @selected((string)old('warehouse_stock_id',$selectedStockId)===(string)$stock->id)>{{ $stock->warehouse?->code }} · {{ $stock->warehouse?->name }} — {{ $stock->productVariant?->sku }} · {{ $stock->productVariant?->product?->name }} — Sistem: {{ number_format($stock->quantity_on_hand) }} | Reservasi: {{ number_format($stock->quantity_reserved) }} | Rusak: {{ number_format($stock->quantity_damaged) }}</option>@endforeach</select>@error('warehouse_stock_id')<small class="category-form-error">{{ $message }}</small>@enderror</label>
                @endif
            </section>

            <section class="category-form-card glass-panel">
                <div class="category-form-card__header"><span class="category-form-card__icon category-form-card__icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 5h14v14H5z"/><path d="M8 9h8M8 13h5"/></svg></span><div><p class="dashboard-heading__eyebrow">Hasil Pemeriksaan</p><h3>Masukkan stok fisik aktual</h3><p>Nilai ini menggantikan quantity on hand setelah transaksi berhasil diproses.</p></div></div>
                <div class="movement-form-grid">
                    <label class="category-form-field"><span>Stok Fisik Aktual <em>*</em></span><input type="number" name="physical_quantity" min="0" step="1" value="{{ old('physical_quantity') }}" placeholder="Contoh: 18" required>@error('physical_quantity')<small class="category-form-error">{{ $message }}</small>@enderror</label>
                    <label class="category-form-field"><span>Alasan <em>*</em></span><select name="reason" required><option value="">Pilih alasan</option>@foreach($reasons as $value => $label)<option value="{{ $value }}" @selected(old('reason')===$value)>{{ $label }}</option>@endforeach</select>@error('reason')<small class="category-form-error">{{ $message }}</small>@enderror</label>
                    <label class="category-form-field"><span>Tanggal & Waktu <em>*</em></span><input type="datetime-local" name="adjustment_date" value="{{ old('adjustment_date',$defaultAdjustmentDate) }}" max="{{ now()->format('Y-m-d\TH:i') }}" required>@error('adjustment_date')<small class="category-form-error">{{ $message }}</small>@enderror</label>
                </div>
                <label class="category-form-field"><span>Catatan Pemeriksaan <em>*</em></span><textarea name="notes" rows="5" maxlength="1000" placeholder="Contoh: Stock opname rak A ditemukan selisih 2 unit dibanding sistem." required>{{ old('notes') }}</textarea><small class="category-form-help">Jelaskan konteks pemeriksaan karena transaksi yang sudah diproses tidak dapat diedit atau dihapus.</small>@error('notes')<small class="category-form-error">{{ $message }}</small>@enderror</label>
            </section>
        </div>

        <aside class="movement-form-side">
            <section class="category-meta-card glass-panel"><p class="dashboard-heading__eyebrow">Cara Kerja</p><h3>Koreksi otomatis</h3><dl><div><dt>Fisik &gt; Sistem</dt><dd>Membuat ADJUSTMENT_IN.</dd></div><div><dt>Fisik &lt; Sistem</dt><dd>Membuat ADJUSTMENT_OUT.</dd></div><div><dt>Fisik = Sistem</dt><dd>Tidak ada transaksi yang dibuat.</dd></div><div><dt>Reservasi + Rusak</dt><dd>Tetap dilindungi dan tidak boleh melebihi stok fisik aktual.</dd></div></dl></section>
            <section class="category-form-note glass-panel"><span class="category-form-note__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/></svg></span><div><strong>Atomic transaction</strong><p>Record penyesuaian, saldo room, dan ledger Mutasi Stok disimpan dalam satu database transaction.</p></div></section>
            <div class="category-form-actions glass-panel"><a href="{{ route('admin.stock-adjustments.index') }}" class="button button--ghost">Batal</a><button type="submit" class="button button--primary" @disabled($stocks->isEmpty())>Proses Penyesuaian</button></div>
        </aside>
    </form>
@endsection
