# Batik Daffa — E-Commerce & Katalog Batik Trusmi

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Inertia + React](https://img.shields.io/badge/Inertia.js-React_18-61DAFB?style=flat-square&logo=react&logoColor=111)](https://inertiajs.com)
[![Filament](https://img.shields.io/badge/Filament-v3_Admin-F59E0B?style=flat-square)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)

Platform e-commerce dan katalog digital **Batik Daffa** — menghadirkan kain batik tulis, batik cap, busana siap pakai, dan aksesori karya pengrajin Trusmi, Cirebon. Dibangun menggunakan **Laravel 11**, **Inertia.js (React 18)**, dan **Filament v3** dengan bahasa desain **Modern Editorial Minimalism** yang bersih, hangat, dan responsif di desktop maupun perangkat mobile.

---

## Fitur Utama

### Storefront Pembeli
- **Hero Campaign Slider & Overlay Navbar:** Carousel editorial 16:9 penuh dengan autoplay, gestur swipe mobile, dukungan keyboard, serta header transparan di beranda yang otomatis berubah menjadi bar solid saat halaman digulir.
- **Beranda Editorial & Label Produk:** Mosaik kategori, *Pilihan Terlaris* dengan navigasi slide, *Koleksi Pilihan*, *Koleksi Terbaru*, label minimalis (*Best Seller* & *Baru*), serta layanan konsultasi pesanan batik custom via WhatsApp.
- **Katalog Lengkap & Filter Real-Time:** Filter kategori, 5 motif khas Cirebon (*Mega Mendung, Kawung, Parang, Truntum, Wadasan*), rentang harga, ketersediaan stok, pencarian kata kunci, pengurutan, drawer filter mobile, dan paginasi server-side.
- **Detail Produk, Panduan Ukuran & Ulasan Pembeli:** Pemilihan ukuran (`S / M / L / XL`) untuk kategori pakaian, modal panduan ukuran, status stok real-time, serta ulasan bintang (1–5) terverifikasi dari pembeli.
- **Keranjang & Checkout Aman:** Slide-over cart drawer dengan sinkronisasi harga otomatis dari server (`/api/cart-sync`), mendukung *guest checkout* maupun akun pelanggan, penguncian stok atomik (*row-level locking*), dan instruksi pembayaran langsung (Transfer Bank, QRIS / E-Wallet, atau COD).
- **Pelacakan, Konfirmasi Selesai & Rating (`/pesanan`):** Pelacakan pesanan memakai kode unik (`ORD-XXXXXX`), konfirmasi pesanan selesai oleh pembeli, dan formulir bintang serta ulasan.
- **Dwi-Bahasa (ID / EN):** Pergantian bahasa Indonesia & Inggris secara instan.
- **Fallback Motif SVG Inline:** 5 motif batik SVG bawaan tampil otomatis bila produk belum memiliki foto, dan langsung tergantikan saat foto produk diunggah dari panel admin.

### Panel Admin Pengrajin (`/admin`)
- **Sesi Autentikasi Terpisah:** Guard `admin` terpisah dari sesi login pelanggan (`web`), dengan validasi `FilamentUser` (`is_admin = true`).
- **Dashboard Ringkas & Tambah Produk Cepat:** Statistik toko, widget *Tambah Produk Cepat*, dan tabel *Kelola Cepat Harga & Produk* langsung di halaman utama admin.
- **Manajemen Produk & Kategori (`ProductResource`, `CategoryResource`):** CRUD produk & kategori katalog dinamis, unggah foto ke disk `public`, ubah harga/stok 1-klik, serta toggle instan *Tayang di Web*, *Best Seller*, dan *Baru* yang langsung tersinkron ke storefront.
- **Manajemen Visual & Pembayaran (`HeroSlideResource`, `ManageStoreVisuals`, `ManagePaymentSettings`):** Ganti gambar hero slider, banner *Koleksi Pilihan*, foto kategori, rekening bank, gambar QRIS, catatan COD, serta nomor WhatsApp toko langsung dari dashboard.
- **Manajemen Pesanan (`OrderResource`):** Verifikasi pesanan 1-klik, perbarui status (*Baru → Diproses → Dikirim → Selesai / Batal*), input ongkir dengan perhitungan ulang `grand_total` otomatis, pantau rating pembeli, dan tindak lanjut ke pelanggan via template WhatsApp.

---

## Arsitektur & Teknologi

| Lapisan | Teknologi | Keterangan |
| :--- | :--- | :--- |
| **Backend** | Laravel 11 (PHP 8.2+) | Routing, Eloquent ORM, transaksi database, validasi |
| **Frontend** | Inertia.js v2 + React 18 + Vite | SPA responsif dengan server-driven routing |
| **Design System** | Modern Editorial Minimalism | Palet warm ivory (`#F7F5F0`), charcoal (`#24231F`), olive (`#69715B`), bronze (`#A27B55`); tipografi Playfair Display + Inter |
| **Admin Panel** | Filament v3 (`/admin`) | Dashboard manajemen katalog, visual, pembayaran, dan pesanan |
| **Database** | SQLite (Lokal) / PostgreSQL & MySQL (Produksi) | Migrasi terstruktur dan seeder idempoten |
| **Autentikasi** | Laravel Breeze (`web`) + Filament (`admin`) | Sesi pelanggan dan admin terpisah penuh |

---

## Struktur Direktori Utama

```text
app/
├── Filament/
│   ├── Pages/                       # Pengaturan tampilan toko & pembayaran
│   ├── Resources/                   # Produk, Kategori, Hero Slides, Pesanan
│   └── Widgets/                     # Statistik, Tambah Produk Cepat, Kelola Cepat
├── Http/
│   ├── Controllers/ShopController.php
│   └── Middleware/HandleInertiaRequests.php
└── Models/                          # Category, Product, HeroSlide, Order, OrderItem, Review, SiteSetting, User

database/
├── migrations/                      # Skema tabel & migrasi idempoten
└── seeders/                         # Seeder katalog awal & tampilan default

resources/
├── css/index.css                    # Design tokens, layout editorial & responsif mobile
├── views/                           # Shell Inertia & tema kustom Filament
└── js/
    ├── Layouts/ShopLayout.jsx       # Overlay/sticky header, pencarian, drawer, footer
    ├── Components/                  # HeroCarousel, ProductCard, CartDrawer, PaymentInstructions, Motif, dll.
    ├── Pages/Shop/                  # Home, Catalog, ProductDetail, Cart, Checkout, Orders, OrderDetail, Help
    ├── Pages/Auth/ & Profile/       # Halaman autentikasi & akun pelanggan
    ├── Data/i18n.js                 # Kamus dwi-bahasa (ID / EN)
    └── Lib/                         # cart.js, format.js, i18n.js, whatsapp.js
```

---

## Instalasi & Menjalankan Lokal

### Prasyarat
- PHP **8.2+** dengan ekstensi aktif: `pdo_sqlite` / `pdo_pgsql` / `pdo_mysql`, `mbstring`, `fileinfo`, `intl`, `openssl`
- Composer 2+
- Node.js **18+** & npm

### Langkah Setup
```bash
git clone <url-repositori>
cd batik-cirebon-laravel

# 1. Pasang dependensi backend & siapkan environment
composer install
cp .env.example .env
php artisan key:generate

# 2. Siapkan database lokal (SQLite) & jalankan migrasi
touch database/database.sqlite
php artisan migrate
php artisan storage:link

# 3. Pasang dependensi frontend & kompilasi aset
npm install
npm run build        # atau `npm run dev` untuk mode pengembangan

# 4. Jalankan server lokal
php artisan serve    # buka http://127.0.0.1:8000
```

### Manajemen Akun Admin
Panel admin dapat diakses melalui `/admin`. Untuk membuat akun admin baru secara aman pada lingkungan lokal atau produksi, jalankan:

```bash
php artisan make:filament-user
```

Pastikan pengguna yang dibuat memiliki atribut `is_admin = true` pada tabel `users`, dan selalu gunakan kata sandi yang kuat untuk lingkungan produksi.

---

## Pengujian Otomatis

```bash
php artisan test
```

Suite pengujian menggunakan database SQLite `:memory:` (`phpunit.xml`) sehingga tidak memengaruhi data lokal. Pengujian fitur (`tests/Feature/ShopTest.php`) mencakup:
- Normalisasi kartu produk, kategori, dan URL foto `/storage/`
- Validasi & pengurangan stok atomik saat checkout
- Pelacakan kode pesanan di `/pesanan`
- Perhitungan ulang `grand_total` saat ongkir diubah
- Proteksi akses panel Filament & pemisahan sesi `web`/`admin`
- Filter harga, stok, pengurutan, sinkronisasi harga, dan label *Best Seller* / *Baru*
- Konfirmasi pesanan selesai serta penyimpanan ulasan bintang (1–5)
- Idempotensi seeder untuk deployment produksi

---

## Panduan Deployment

### Opsi A — Laravel Cloud / PaaS
1. Hubungkan repositori Git dan atur environment variable produksi (`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, serta koneksi database).
2. Pastikan perintah build & deploy menjalankan:
   ```bash
   npm ci && npm run build
   php artisan migrate --force
   php artisan storage:link
   ```
3. Untuk penyimpanan permanen foto produk yang diunggah lewat panel admin pada kontainer *ephemeral*, disarankan menghubungkan **Object Storage (S3/R2)**.

### Opsi B — Cloud VPS (Ubuntu / Nginx)
```bash
git clone <url-repositori>
cd batik-cirebon-laravel
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```
Arahkan `root` Nginx ke direktori `public/` dan aktifkan HTTPS melalui SSL/Certbot.

### Opsi C — Shared Hosting (cPanel)
1. Jalankan `npm run build` secara lokal, lalu atur `.env` produksi (`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, dan kredensial database hosting).
2. Pisahkan isi direktori `public/` ke `public_html/` dan sesuaikan path `autoload.php` serta `bootstrap/app.php` pada `public_html/index.php`.
3. Jalankan `php artisan migrate --force`, `php artisan storage:link`, dan `php artisan optimize`, lalu aktifkan SSL.

---

## Konfigurasi Toko Sebelum Go-Live

Setelah deploy, sesuaikan data operasional toko langsung melalui panel `/admin`:
- **Tampilan Toko (`/admin/tampilan-toko`) & Hero Slider (`/admin/hero-slides`):** Unggah foto banner kampanye dan kategori.
- **Pengaturan Pembayaran (`/admin/pengaturan-pembayaran`):** Masukkan detail rekening bank, gambar QRIS, catatan COD, serta nomor WhatsApp resmi toko.
- **Produk (`/admin/products`):** Unggah foto asli, sesuaikan harga/stok, dan pilih produk *Best Seller* serta *Baru*.
- **Footer (`resources/js/Layouts/ShopLayout.jsx`):** Perbarui tautan media sosial dan alamat email publik toko.

---

## Lisensi

Proyek ini dikembangkan untuk Program Pengabdian Masyarakat — **Batik Daffa (Trusmi, Cirebon)**.
