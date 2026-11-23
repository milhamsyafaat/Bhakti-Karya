# Bhakti Karya — Website Warung Grosir

Aplikasi web **PHP Native (MVC)** untuk Warung Grosir Bhakti Karya: katalog, keranjang, checkout dengan ongkir zona, pelacakan pesanan, dan panel admin.

## Struktur Project

```
Bhakti Karya/
├── app/
│   ├── Controllers/          # Controller publik + Admin/
│   ├── Core/                 # Kernel (Router, Database, Model, View, Auth, dll)
│   ├── Middleware/           # AuthMiddleware, AdminMiddleware
│   ├── Models/               # Produk, Kategori, Pesanan, dsb
│   ├── Services/             # OngkirService (logika bisnis)
│   └── Views/                # Blade-like plain PHP views
│       ├── layouts/          # app, admin, auth
│       ├── partials/         # produk-card
│       ├── home/ produk/ keranjang/ checkout/ pesanan/ akun/ auth/
│       ├── admin/            # dashboard, produk, pesanan, stok, pelanggan
│       └── errors/           # 404, 403, 419
├── bootstrap/app.php         # Autoloader, .env, config, session
├── config/                   # app.php, database.php
├── database/                 # schema + seed SQL + etl/import_produk.php
├── public/                   # DOCUMENT ROOT (index.php, assets)
├── routes/web.php            # Definisi route
└── storage/                  # logs, uploads
```

## Kebutuhan

- PHP 7.4+ (teruji 8.1)
- MySQL 5.7+ / MariaDB (import `database/schema_bhaktikarya.sql`)

## Cara Menjalankan (Laragon)

1. Pastikan MySQL server Laragon berjalan.
2. Import database (membuat DB `bhaktikarya_grosir`):
   ```
   mysql -u root < database/schema_bhaktikarya.sql
   mysql -u root < database/seed_bhaktikarya.sql
   ```
3. Impor 100 produk dari Excel (ETL):
   ```
   php database/etl/import_produk.php
   ```
4. Sesuaikan `.env` (DB, APP_URL).
5. Akses `http://bhaktikarya.test` (lihat catatan di bawah).

Login admin default: **admin / adminpassword** (ganti di produksi).

### Catatan environment (yang sudah disetel)

- Apache Laragon memakai **PHP 8.3**, `extension_dir` sudah benar; `pdo_mysql` & `mbstring` termuat.
- `extension_dir` di **php.ini 8.1** sebelumnya salah (`D:/laragon/laragon/...`) dan sudah diperbaiki (backup: `php.ini.bak-bhaktikarya`).
- Proyek berada di folder bernama **"Bhakti Karya"** (ada spasi) sehingga Apache gagal resolve berkas. Dibuat **junction** tanpa spasi:
  ```
  mklink /J "D:\laragon\www\bhaktikarya" "D:\laragon\www\SEMESTER 5\Bhakti Karya"
  ```
  Vhost: `D:\laragon\etc\apache2\sites-enabled\auto.bhaktikarya.test.conf`
  (DocumentRoot → `www/bhaktikarya/public`, `AllowOverride All`).
- Agar `bhaktikarya.test` ter-resolve: **restart Laragon** (menambah entri hosts otomatis) atau tambahkan manual di `C:\Windows\System32\drivers\etc\hosts`:
  ```
  127.0.0.1 bhaktikarya.test
  ```


## Alur Arsitektur

- `public/index.php` → `bootstrap/app.php` → `App::run()` → `routes/web.php` → Router → Controller → View.
- Model mewarisi `App\Core\Model` (PDO). Query kompleks pakai `App\Core\Database`.
- Validasi CSRF otomatis untuk semua POST (lihat `App::run()`).
- Ongkir: `App\Services\OngkirService` (gratis ongkir radius/min belanja).
- Keranjang disimpan di session (`App\Core\Cart`).

## Route Utama

| Method | URI | Controller |
|---|---|---|
| GET | `/` | HomeController@index |
| GET | `/produk`, `/produk/{id}` | ProdukController |
| GET/POST | `/keranjang*` | KeranjangController |
| GET/POST | `/checkout`, `/ongkir` | CheckoutController |
| GET | `/pesanan/{nota}`, `/lacak` | PesananController |
| GET/POST | `/login`, `/logout` | AuthController |
| GET | `/admin/*` | Admin\* (butuh login + role admin) |

## Konvensi

- Namespace `App\`, PSR-4 ke `app/`.
- View dirender dengan `$this->view('folder/file', $data, 'layouts/xxx')`.
- Akses konfigurasi: `config('app.name')`, `config('database')`.
- Helper: `url()`, `asset()`, `e()`, `rupiah()`, `csrf_field()`, `old()`, `flash()`.

## Status Implementasi

- [x] Core MVC (router, PDO, view, auth, CSRF, session)
- [x] Katalog + filter + detail produk
- [x] Keranjang + checkout + ongkir zona + buat pesanan
- [x] Panel admin: dashboard, produk, pesanan, stok, pelanggan
- [x] ETL impor 100 produk dari Excel
- [x] Upload gambar produk (file + URL, fallback placeholder)
- [x] Login pelanggan via nomor HP + poin & tukar voucher end-to-end
- [ ] Pembayaran QRIS gateway nyata
- [ ] Bon / piutang, rutinitas langganan
- [ ] Kirim notifikasi WhatsApp (stok_alert, status pesanan)
```
