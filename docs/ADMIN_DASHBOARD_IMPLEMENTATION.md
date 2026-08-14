# Implementasi Dashboard Admin Ettra Signature

Dokumen ini menjelaskan file yang dibuat atau diperbarui untuk halaman dashboard admin.

## Tujuan

- Mengadaptasi pola dashboard Mosaic ke Laravel Blade.
- Mempertahankan Laravel, Blade, Tailwind CSS 4, dan JavaScript murni.
- Menggunakan warna dasar klien:
  - Peach `#BB7F73`
  - Hijau `#4A8E04`
  - Putih sebagai warna dominan
- Memberikan sentuhan glassmorphism pada header, hero dashboard, modal pencarian, dan elemen pendukung.
- Menyediakan tampilan responsif untuk desktop, tablet, dan ponsel.

## File baru

- `app/Http/Controllers/Admin/DashboardController.php`
- `resources/views/partials/admin/sidebar.blade.php`
- `resources/views/partials/admin/header.blade.php`
- `resources/views/partials/admin/search-modal.blade.php`
- `docs/ADMIN_DASHBOARD_IMPLEMENTATION.md`
- `THIRD_PARTY_NOTICES.md`

## File yang diganti seluruh isinya

- `routes/web.php`
- `resources/views/layouts/admin.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/css/app.css`
- `resources/js/app.js`

## Menjalankan project

1. Pastikan Laragon, Apache, dan MySQL aktif.
2. Buka terminal pada folder project.
3. Jalankan `npm install` jika folder `node_modules` belum tersedia.
4. Jalankan `npm run dev` selama pengembangan.
5. Buka `http://ettra-signature.test/admin/dashboard`.

## Validasi yang sudah dilakukan

- Pemeriksaan sintaks PHP untuk controller dan route.
- Kompilasi serta pemeriksaan sintaks seluruh file Blade.
- Pemeriksaan sintaks JavaScript menggunakan `node --check`.
- Pemeriksaan route admin melalui `php artisan route:list`.
- Pengujian render HTTP dashboard dengan hasil status `200`.

Build Vite tidak dijalankan di lingkungan penyusunan karena `node_modules` pada ZIP berasal dari Windows dan memakai native binding Windows. Jalankan `npm run dev` atau `npm run build` pada Laragon/Windows Anda setelah mengekstrak project.

## Catatan data

Angka dashboard masih berupa data pratinjau antarmuka dari `DashboardController`. Data nyata akan menggantikannya setelah tabel dan modul bisnis dibuat.
