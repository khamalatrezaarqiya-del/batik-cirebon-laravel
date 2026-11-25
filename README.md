# Batik Daffa — E-Commerce & Katalog Batik Trusmi

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Inertia + React](https://img.shields.io/badge/Inertia.js-React_18-61DAFB?style=flat-square&logo=react&logoColor=111)](https://inertiajs.com)
[![Filament](https://img.shields.io/badge/Filament-v3_Admin-F59E0B?style=flat-square)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)

Platform katalog dan toko daring **Batik Daffa** — menghadirkan kain batik tulis, batik cap, busana siap pakai, dan aksesori karya pengrajin Trusmi, Cirebon. Dibangun dengan **Laravel 11**, **Inertia.js (React)**, dan **Filament v3**, mengusung antarmuka **Minimal & Shadcn** berpalet putih hangat semi coklat tipis agar detail motif batik tampil menonjol.

---

## Fitur Utama

### Storefront Pembeli
- **Beranda & Koleksi Terkurasi:** Hero *asymmetric split* dengan etalase motif klikable (Mega Mendung, Kawung, Parang), kategori produk, pilihan unggulan, dan koleksi terbaru.
- **Katalog Lengkap & Filter Real-Time:** Filter berdasarkan kategori (*Kain, Pakaian, Aksesoris*), 5 jenis motif (*Mega Mendung, Kawung, Parang, Truntum, Wadasan*), pencarian kata kunci, pengurutan harga/terbaru, serta paginasi server-side.
- **Detail Produk & Varian Ukuran:** Dukungan pemilihan ukuran (`S / M / L / XL`) khusus kategori pakaian, indikator stok real-time, pembatasan jumlah pesanan, serta tombol konsultasi langsung via WhatsApp.
- **Keranjang & Checkout Aman:** Mendukung belanja tanpa login (*guest checkout*) maupun dengan akun. Validasi ketersediaan stok (*row-level locking*), pengurangan stok otomatis, dan kalkulasi total penuh di sisi server untuk mencegah manipulasi harga.
- **Pelacakan & Riwayat Pesanan (`/pesanan`):** Pembeli tamu dapat melacak status pesanan menggunakan kode (`ORD-XXXXXX`), sementara pengguna terdaftar dapat memantau seluruh riwayat pesanan melalui `/pesanan` atau `/dashboard`.
- **Dwi-Bahasa (ID / EN):** Pergantian bahasa Indonesia & Inggris secara instan.
- **Fallback Motif SVG Inline:** 5 motif batik SVG bawaan (*Mega Mendung, Kawung, Parang, Truntum, Wadasan*) otomatis tampil jika produk belum memiliki foto studio, dan langsung tergantikan saat foto asli diunggah dari admin.

### Panel Admin Pengrajin (`/admin`)
- **Proteksi Akses Admin (`FilamentUser`):** Hanya akun dengan hak `is_admin = true` yang dapat mengakses dashboard `/admin`.
- **Manajemen Produk (`ProductResource`):** Tambah/ubah produk, unggah & sunting foto ke `storage/public/products`, atur harga, motif, warna, stok, serta status aktif/nonaktif.
- **Manajemen Pesanan (`OrderResource`):** Pantau pesanan masuk, ubah status (*Baru → Diproses → Dikirim → Selesai / Batal*), input ongkir dengan perhitungan ulang `grand_total` otomatis, serta tombol pintas hubungi pembeli via WhatsApp.

---

## Arsitektur & Teknologi

| Lapisan | Teknologi | Keterangan |
| :--- | :--- | :--- |
| **Backend** | Laravel 11 (PHP 8.2+) | Routing, Eloquent ORM, transaksi database, validasi |
| **Frontend** | Inertia.js v2 + React 18 + Vite | SPA mulus tanpa API boilerplate |
| **Design System** | Minimal & Shadcn (Custom CSS Tokens) | Palet putih semi coklat tipis (`#FAF7F2`), aksesibel & Hallmark-compliant |
| **Admin Panel** | Filament v3 (`/admin`) | Dashboard visual CRUD untuk pengrajin tanpa koding |
| **Database** | SQLite (Lokal) / MySQL (Produksi) | Migrasi & seeder terstruktur |
| **Autentikasi** | Laravel Breeze + `is_admin` guard | Login, registrasi, profil, dan proteksi panel admin |

---

## Struktur Direktori Utama

```text
app/
├── Filament/Resources/
│   ├── ProductResource.php          # CRUD produk & upload foto
│   └── OrderResource.php            # Kelola pesanan, ongkir, status & WA
├── Http/Controllers/
│   └── ShopController.php           # Storefront, katalog, checkout, pelacakan pesanan
├── Http/Middleware/
│   └── HandleInertiaRequests.php    # Shared props (auth.user, flash)
└── Models/
    ├── Category.php
    ├── Product.php                  # Auto-slug unik
    ├── Order.php                    # Auto-kalkulasi grand_total (subtotal + ongkir)
    ├── OrderItem.php
    └── User.php                     # Implementasi FilamentUser (is_admin)

database/
├── migrations/                      # users (+is_admin), categories, products, orders, order_items
└── seeders/
    ├── DatabaseSeeder.php           # Admin default + panggil ProductSeeder
    └── ProductSeeder.php            # 26 katalog awal Batik Daffa

resources/
├── css/index.css                    # Design tokens & stylesheet Minimal & Shadcn
├── views/app.blade.php              # Shell HTML, font Literata & Plus Jakarta Sans
└── js/
    ├── Layouts/ShopLayout.jsx       # Header, navigasi, pencarian, footer
    ├── Components/                  # Motif.jsx, ProductCard.jsx, SectionTitle.jsx
    ├── Pages/Shop/                  # Home, Catalog, ProductDetail, Cart, Checkout, Orders, OrderDetail, Help
    ├── Pages/Auth/ & Dashboard.jsx  # Halaman akun selaras dengan tema toko
    ├── Data/i18n.js                 # Kamus bahasa ID & EN
    └── Lib/                         # cart.js, format.js, i18n.js, whatsapp.js
```

---

## Instalasi & Menjalankan Lokal

### Prasyarat
- PHP **8.2+** dengan ekstensi aktif: `pdo_sqlite` / `pdo_mysql`, `mbstring`, `fileinfo`, `intl`, `openssl`
- Composer 2+
- Node.js **18+** & npm

### Langkah Setup
```bash
git clone https://github.com/khamalatrezaarqiya-del/batik-cirebon-laravel.git
cd batik-cirebon-laravel

# 1. Pasang dependensi backend & siapkan environment
composer install
cp .env.example .env
php artisan key:generate

# 2. Siapkan database lokal (SQLite) & jalankan migrasi + seeder
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link

# 3. Pasang dependensi frontend & kompilasi aset
npm install
npm run build        # atau `npm run dev` untuk mode pengembangan

# 4. Jalankan server lokal
php artisan serve    # buka http://127.0.0.1:8000
```

### Akses Panel Admin Lokal
Seeder otomatis membuat akun admin dummy untuk pengujian lokal:
- **URL:** `http://127.0.0.1:8000/admin`
- **Email:** `Jajareja@gmail.com`
- **Password:** `Jajareja@16`

> **Penting:** Segera ubah kata sandi atau buat akun admin baru sebelum rilis ke produksi.

---

## Pengujian Otomatis

```bash
php artisan test
```
Mencakup **30+ automated tests** termasuk pengujian fitur toko (`tests/Feature/ShopTest.php`):
- Normalisasi kartu produk & URL foto `/storage/` di Beranda
- Validasi & pengurangan stok atomik saat checkout
- Pelacakan kode pesanan di `/pesanan`
- Perhitungan ulang `grand_total` saat ongkir diubah di admin
- Pembatasan akses panel Filament hanya untuk `is_admin = true`

---

## Checklist Sebelum Rilis ke Domain `.com`

Perbarui beberapa placeholder berikut sesuai data final mitra **Batik Daffa**:

| File | Yang Perlu Diperbarui |
| :--- | :--- |
| `resources/js/Lib/whatsapp.js` | Ganti `WA_NUMBER` dengan nomor WhatsApp resmi format `628xxxxxxxxxx` |
| `resources/js/Lib/format.js` | Ubah `PRICE_PLACEHOLDER = false` setelah harga final dikonfirmasi |
| `resources/js/Layouts/ShopLayout.jsx` | Perbarui tautan media sosial (Instagram/Facebook) & alamat email resmi |
| `resources/js/Data/i18n.js` | Sesuaikan alamat lengkap workshop, teks pengumuman, dan deskripsi toko |
| Panel `/admin` | Unggah foto produk asli & ganti password akun admin |

---

## Panduan Deployment (Domain `.com`)

### Opsi A — Shared Hosting (cPanel)
1. Kompilasi aset produksi secara lokal:
   ```bash
   npm run build
   ```
2. Atur `.env` produksi:
   ```env
   APP_NAME="Batik Daffa"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://namadomain.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nama_db_cpanel
   DB_USERNAME=user_db_cpanel
   DB_PASSWORD=password_db_cpanel
   ```
3. Pisahkan file saat upload:
   - Seluruh isi proyek **kecuali** folder `public/` → `/home/USER/batik-daffa-app/`
   - Seluruh isi folder `public/` (termasuk `build/`, `.htaccess`, `index.php`) → `/home/USER/public_html/`
4. Sesuaikan path di `public_html/index.php`:
   ```php
   require __DIR__.'/../batik-daffa-app/vendor/autoload.php';
   $app = require_once __DIR__.'/../batik-daffa-app/bootstrap/app.php';
   ```
5. Jalankan perintah final (via SSH / Terminal cPanel):
   ```bash
   php artisan migrate --force --seed
   php artisan storage:link
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
6. Aktifkan **AutoSSL / Let's Encrypt** di cPanel untuk domain `.com`.

### Opsi B — Cloud VPS (Ubuntu / Nginx)
```bash
git clone https://github.com/khamalatrezaarqiya-del/batik-cirebon-laravel.git
cd batik-cirebon-laravel
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force --seed
php artisan storage:link
php artisan optimize
```
Arahkan `root` Nginx ke direktori `public/` dan aktifkan SSL melalui Certbot.

---

## Lisensi

Proyek ini dikembangkan untuk Program Pengabdian Masyarakat — **Batik Daffa (Trusmi, Cirebon)**.
