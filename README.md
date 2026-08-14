# Ettra Signature

Sistem manajemen penjualan dan persediaan berbasis website untuk Ettra Signature.

Project ini dikembangkan menggunakan Laravel, Blade, Tailwind CSS, Vite, dan MySQL. Aplikasi memiliki area internal untuk Owner/Admin dan menangani produk, variasi, Room, persediaan, mutasi stok, penjualan, pembayaran, pengiriman, promosi, serta fitur operasional lainnya.

## Teknologi

- PHP >= 8.3
- Laravel 13.23.0
- MySQL
- Composer 2.x
- Node.js >= 20.19.0 atau >= 22.12.0
- NPM
- Vite 8
- Tailwind CSS 4
- Laragon direkomendasikan untuk Windows

## Persyaratan Sebelum Menjalankan Project

Pastikan komputer sudah memiliki:

1. Laragon
2. PHP 8.3 atau lebih baru
3. MySQL
4. Composer
5. Node.js dan NPM
6. Git
7. Visual Studio Code

Extension PHP yang disarankan aktif:

- curl
- fileinfo
- mbstring
- openssl
- pdo_mysql
- mysqli
- intl
- zip
- dom
- xml
- xmlwriter

Periksa versi melalui terminal:

```bash
php -v
composer -V
node -v
npm -v
git --version
```

## Cara Instalasi dari GitHub

### 1. Clone Repository

Simpan project di dalam document root Laragon:

```text
C:\laragon\www
```

Clone repository:

```bash
git clone URL_REPOSITORY_GITHUB ettra-signature
```

Masuk ke folder project:

```bash
cd ettra-signature
```

Jika menggunakan Visual Studio Code:

```bash
code .
```

## 2. Install Dependency PHP

Jalankan:

```bash
composer install
```

Tunggu sampai seluruh dependency selesai diunduh.

Jangan menyalin folder `vendor` dari komputer lain. Folder tersebut dibuat kembali melalui Composer.

## 3. Install Dependency Frontend

Jalankan:

```bash
npm install
```

Tunggu sampai selesai.

Jangan menyalin folder `node_modules` dari komputer lain. Folder tersebut dibuat kembali melalui NPM.

## 4. Buat File Environment

Pada Windows CMD atau terminal Laragon:

```bash
copy .env.example .env
```

Jika menggunakan Git Bash:

```bash
cp .env.example .env
```

File `.env` bersifat lokal dan tidak boleh di-upload ke GitHub.

## 5. Buat Database MySQL

Jalankan MySQL dari Laragon.

Buat database baru dengan nama:

```text
ettra_signature
```

Database dapat dibuat melalui HeidiSQL, phpMyAdmin, atau terminal MySQL.

Contoh SQL:

```sql
CREATE DATABASE ettra_signature
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

## 6. Periksa Konfigurasi `.env`

Pastikan konfigurasi berikut sesuai:

```env
APP_NAME="Ettra Signature"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://ettra-signature.test
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ettra_signature
DB_USERNAME=root
DB_PASSWORD=
```

Jika MySQL lokal memiliki password, isi `DB_PASSWORD` dengan password yang digunakan.

Contoh:

```env
DB_PASSWORD="password_mysql"
```

## 7. Generate Application Key

Jalankan:

```bash
php artisan key:generate
```

Hasil yang benar:

```text
INFO  Application key set successfully.
```

Jangan menyalin `APP_KEY` dari komputer developer lain. Setiap instalasi lokal dapat membuat key sendiri.

## 8. Bersihkan Cache Laravel

Jalankan:

```bash
php artisan optimize:clear
```

## 9. Jalankan Migration Database

Jalankan:

```bash
php artisan migrate
```

Periksa hasil migration:

```bash
php artisan migrate:status
```

Semua migration seharusnya berstatus `Ran`.

Perintah ini membuat struktur tabel aplikasi. Data MySQL dari komputer developer lain tidak ikut tersimpan di GitHub.

## 10. Buat Storage Link

Project menggunakan Laravel Storage untuk file seperti foto produk.

Jalankan:

```bash
php artisan storage:link
```

Jika muncul bahwa symbolic link sudah tersedia, tidak perlu dibuat kembali.

## 11. Buat Akun Owner Pertama

Untuk instalasi database baru, buat akun Owner melalui:

```bash
php artisan ettra:create-owner
```

Isi data yang diminta:

```text
Nama Owner
Email Owner
Nomor telepon
Kata sandi
Konfirmasi kata sandi
```

Password minimal 8 karakter.

Setelah akun Owner dibuat, login melalui:

```text
http://ettra-signature.test/admin/login
```

Akun Admin selanjutnya dapat dikelola melalui User Management oleh Owner.

Jika diperlukan, Admin juga dapat dibuat dari terminal:

```bash
php artisan ettra:create-admin
```

## 12. Jalankan Laragon

Pastikan Laragon menjalankan:

```text
Apache
MySQL
```

Project harus berada di:

```text
C:\laragon\www\ettra-signature
```

Restart Laragon setelah clone project agar Auto Virtual Hosts mendeteksi folder project.

Alamat aplikasi:

```text
http://ettra-signature.test
```

Alamat login internal:

```text
http://ettra-signature.test/admin/login
```

Jika domain `.test` belum terbaca, gunakan alternatif:

```bash
php artisan serve
```

Kemudian buka:

```text
http://127.0.0.1:8000
```

## 13. Jalankan Vite

Buka terminal terpisah dan jalankan:

```bash
npm run dev
```

Biarkan terminal ini tetap aktif selama proses development.

Jangan membuka `http://localhost:5173` sebagai aplikasi utama. Port tersebut merupakan development server Vite.

Aplikasi tetap dibuka melalui:

```text
http://ettra-signature.test
```

atau:

```text
http://127.0.0.1:8000
```

jika menggunakan `php artisan serve`.

## Urutan Menjalankan Project Setiap Hari

Setelah instalasi pertama selesai, penggunaan harian lebih sederhana:

1. Buka Laragon.
2. Klik `Start All`.
3. Pastikan Apache dan MySQL aktif.
4. Buka folder project di Visual Studio Code.
5. Buka terminal project.
6. Jalankan:

```bash
npm run dev
```

7. Buka:

```text
http://ettra-signature.test
```

Tidak perlu menjalankan `composer install`, `npm install`, `php artisan migrate`, atau `php artisan key:generate` setiap kali membuka project.

Perintah tersebut hanya dijalankan saat diperlukan.

## Jika Ada Update dari GitHub

Sebelum mengambil update, pastikan perubahan lokal sudah disimpan atau di-commit.

Ambil perubahan terbaru:

```bash
git pull origin main
```

Jika `composer.json` atau `composer.lock` berubah:

```bash
composer install
```

Jika `package.json` atau `package-lock.json` berubah:

```bash
npm install
```

Jika terdapat migration baru:

```bash
php artisan migrate
```

Kemudian:

```bash
php artisan optimize:clear
```

Jalankan kembali:

```bash
npm run dev
```

## Build Frontend

Untuk membuat asset production:

```bash
npm run build
```

Hasil build dibuat di:

```text
public/build
```

Folder tersebut tidak perlu di-commit karena sudah diabaikan oleh Git.

## Menjalankan Test

Jalankan:

```bash
php artisan test
```

Pastikan extension PHP yang diperlukan PHPUnit seperti `dom`, `mbstring`, `xml`, dan `xmlwriter` aktif.

## Struktur File Penting

```text
app/
    Http/
    Models/
    Services/

database/
    migrations/
    seeders/

resources/
    css/
    js/
    views/

routes/
    web.php
    console.php

public/
storage/
```

## Git dan File yang Tidak Boleh Di-upload

Jangan upload:

```text
.env
/vendor
/node_modules
/public/build
/public/hot
/public/storage
```

File-file tersebut sudah seharusnya masuk `.gitignore`.

Yang harus tersedia di repository:

```text
.env.example
composer.json
composer.lock
package.json
package-lock.json
artisan
app/
bootstrap/
config/
database/
public/
resources/
routes/
tests/
```

## Database dan Data Lokal

GitHub menyimpan source code, bukan isi database MySQL lokal.

Jika teman hanya membutuhkan struktur database, cukup jalankan:

```bash
php artisan migrate
```

Jika teman membutuhkan data yang sama persis dengan komputer developer utama, database perlu di-export secara terpisah ke file `.sql`, lalu di-import pada komputer tujuan.

File database yang berisi data pelanggan atau data sensitif jangan disimpan pada repository GitHub.

## Troubleshooting

### `Unsupported cipher or incorrect key length`

Pastikan `.env` memiliki `APP_KEY` yang valid.

Jalankan:

```bash
php artisan key:generate --force
php artisan config:clear
```

### `SQLSTATE[HY000] [1049] Unknown database`

Pastikan database berikut sudah dibuat:

```text
ettra_signature
```

dan `.env` menggunakan:

```env
DB_DATABASE=ettra_signature
```

### `SQLSTATE[HY000] [1045] Access denied`

Periksa:

```env
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan password MySQL jika digunakan.

### `could not find driver`

Aktifkan extension:

```text
pdo_mysql
```

pada PHP Laragon.

### `Vite manifest not found`

Jalankan:

```bash
npm install
npm run dev
```

atau untuk production:

```bash
npm run build
```

### CSS atau JavaScript Tidak Berubah

Jalankan:

```bash
php artisan optimize:clear
```

Restart:

```bash
npm run dev
```

Kemudian lakukan hard refresh browser:

```text
Ctrl + F5
```

### Domain `.test` Tidak Bisa Dibuka

Pastikan project berada di:

```text
C:\laragon\www\ettra-signature
```

Kemudian restart Laragon.

Sebagai alternatif:

```bash
php artisan serve
```

### Foto Produk Tidak Tampil

Pastikan storage link tersedia:

```bash
php artisan storage:link
```

## Workflow Kolaborasi Git

Sebaiknya setiap developer mengerjakan fitur pada branch terpisah.

Contoh:

```bash
git checkout -b feature/pembelian
```

Setelah selesai:

```bash
git add .
git commit -m "feat: implement purchase module"
git push -u origin feature/pembelian
```

Kemudian buat Pull Request ke branch `main`.

Sebelum mulai mengerjakan perubahan baru:

```bash
git checkout main
git pull origin main
```

Dengan cara ini perubahan antar developer lebih mudah dipisahkan dan risiko konflik pada branch utama berkurang.

## Catatan

- Jangan commit file `.env`.
- Jangan membagikan `APP_KEY`, password database, token, atau credential lain.
- Jangan mengedit struktur database langsung melalui phpMyAdmin/HeidiSQL jika perubahan tersebut merupakan bagian source code. Gunakan migration Laravel.
- Jangan mengedit file di dalam `vendor` atau `node_modules`.
- Gunakan Activity Log untuk menjaga jejak aktivitas penting pada sistem.
