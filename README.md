# Batik Cirebon — Laravel 11 + Inertia (React) + Filament

Migrasi dari prototype React/Vite (`../src`) ke stack Laravel penuh. Tampilan, CSS Hallmark, dan motif SVG batik dipertahankan apa adanya.

## Stack

| Lapisan | Teknologi |
|---|---|
| Backend | Laravel 11.57 (PHP 8.2+) |
| Frontend | Inertia.js + React 18 (Vite) |
| Admin | Filament v3 (`/admin`) |
| Database | SQLite (dev) / MySQL (produksi) |
| Auth | Laravel Breeze |

## Struktur penting

```
app/Http/Controllers/ShopController.php   semua halaman toko + proses checkout
app/Models/                                Category, Product, Order, OrderItem
database/migrations/                       4 tabel inti
database/seeders/ProductSeeder.php         26 produk batik (dari src/data/products.js)
resources/css/index.css                    CSS Hallmark — jangan diubah tanpa alasan
resources/js/Layouts/ShopLayout.jsx        header + footer + nav
resources/js/Pages/Shop/                   7 halaman toko
resources/js/Components/Motif.jsx          SVG Mega Mendung / Kawung / Parang / Truntum
resources/js/Lib/                          cart, i18n, format harga, WhatsApp
routes/web.php                             route toko + auth
```

## Jalankan lokal

Butuh PHP 8.2+, Composer, Node 18+.

```bash
composer install
cp .env.example .env
php artisan key:generate

# SQLite (paling cepat, tanpa server DB)
touch database/database.sqlite
php artisan migrate --seed

npm install
npm run build        # atau `npm run dev` untuk hot reload

php artisan serve    # http://127.0.0.1:8000
```

## Alur belanja

1. `/` beranda — hero, kategori, unggulan, terbaru
2. `/katalog` — filter kategori + motif + cari + urut + paginasi
3. `/produk/{slug}` — detail, varian ukuran (pakaian), jumlah, tombol WhatsApp
4. `/keranjang` — keranjang lokal (`localStorage: batik_cart`)
5. `/checkout` — form penerima + pembayaran; **harga dihitung ulang di server**
6. `/pesanan/{order_code}` — ringkasan + tombol konfirmasi WhatsApp

Keranjang sengaja tetap di `localStorage` (bukan session server) supaya pengunjung bisa mengumpulkan tanpa login. Yang masuk database hanya pesanan final.

## Admin Filament

```bash
php artisan make:filament-user      # buat akun admin pertama
```

Panel di `/admin`. Resource: Produk (dengan upload foto), Pesanan (ubah status baru → diproses → dikirim → selesai).

## Data yang masih placeholder

Ganti sebelum rilis ke `.com`:

| Lokasi | Isi |
|---|---|
| `resources/js/Lib/whatsapp.js` | `WA_NUMBER = 'xxxxxxxxxxxx'` → nomor asli format `628xxx` |
| `resources/js/Lib/format.js` | `PRICE_PLACEHOLDER = true` → `false` setelah harga final |
| `resources/js/Data/i18n.js` | `announce` (nominal gratis ongkir), `address`, `footer_about` |
| `resources/js/Layouts/ShopLayout.jsx` | link Instagram/Facebook, email, alamat |

## Deploy ke hosting cPanel (domain .com)

### 1. Build aset

```bash
npm run build          # hasil di public/build/
```

### 2. `.env` produksi

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domainmu.com
APP_KEY=              # php artisan key:generate --show

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_db
DB_USERNAME=user_db
DB_PASSWORD=password_db
```

### 3. Upload

- Seluruh proyek **kecuali** `public/` → `/home/USER/batik-backend/`
- Isi `public/` (termasuk `build/`, `.htaccess`, `index.php`) → `/home/USER/public_html/`

Sesuaikan `public_html/index.php`:

```php
require __DIR__.'/../batik-backend/vendor/autoload.php';
$app = require_once __DIR__.'/../batik-backend/bootstrap/app.php';
```

### 4. Setelah upload

```bash
php artisan migrate --force --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Import database lewat phpMyAdmin bila migrasi tidak bisa dijalankan dari terminal hosting.

### 5. Domain & SSL

- Arahkan nameserver domain ke hosting
- Aktifkan **AutoSSL / Let's Encrypt** di cPanel
- Paksa HTTPS (`.htaccess`: `RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]`)

## Deploy ke VPS (alternatif, lebih mudah)

```bash
git clone <repo> && cd batik-cirebon
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force --seed
php artisan storage:link
```

Nginx root diarahkan ke `public/`. Pakai `php artisan serve` hanya untuk uji lokal.

## Catatan teknis

- `ShopController::processCheckout()` memakai `DB::transaction` dan menghitung ulang `subtotal`/`line_total` dari harga di database — klien tidak bisa memanipulasi total.
- `Product::booted()` membuat slug otomatis dari nama; `uniqueSlug()` menangani duplikat.
- `HandleInertiaRequests` membagikan `auth.user` ke semua halaman.
- I18n (ID/EN) di `resources/js/Data/i18n.js` + `Lib/i18n.js`, disimpan di `localStorage: batik_lang`.
