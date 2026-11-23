# PROGRES — Website Warung Grosir Bhakti Karya

> Terakhir diperbarui: 23 November 2026
> Stack: PHP Native (MVC) · MySQL · Tailwind (CDN) · Laragon

---

## Ringkasan

| Kategori | Selesai | Belum |
|---|---|---|
| Fondasi (MVC core, DB, auth) | ✅ 100% | — |
| Fitur pelanggan | ✅ 90% | QRIS gateway nyata |
| Fitur admin | ✅ 85% | Laporan lanjutan |
| Loyalitas | ✅ 100% | — |
| Total file PHP | 77 | lint 0 error |

---

## ✅ SELESAI

### 1. Analisis & Perencanaan
- [x] Analisis `Database_Bhaktikarya.xlsx` (22 sheet, 100 produk)
- [x] PRD lengkap (`PRD_Bhakti_Karya.md`)
- [x] Skema SQL 22 tabel + seed (`database/schema_bhaktikarya.sql`, `seed_bhaktikarya.sql`)
- [x] DB produksi: `bhaktikarya_grosir` di MySQL 8.0.30 (port 3306)

### 2. Core MVC (PHP Native)
- [x] Router (param `{id}`, grup prefix, middleware, injection Request via reflection)
- [x] Database PDO wrapper, Model dasar (CRUD generik)
- [x] View + 3 layout (app, admin, auth)
- [x] Session (dot-notation, flash, CSRF), Auth admin, AuthPelanggan (login via HP)
- [x] Validator, Cart (session), Storage (upload gambar)
- [x] Helpers: `url()`, `e()`, `rupiah()`, `csrf_field()`, `old()`, `flash()`, `product_image()`

### 3. Fitur Pelanggan
- [x] Beranda: kategori, favorit, terlaris, status buka/tutup
- [x] Katalog: pencarian, filter kategori, urutkan, pagination
- [x] Detail produk + produk serupa + stok real-time
- [x] Keranjang (tambah/ubah/hapus) — session-based
- [x] Checkout: data kirim, antar/ambil, zona ongkir, QRIS/COD/Transfer, jadwal & catatan
- [x] **Ongkir otomatis** — zona kelurahan, gratis (≤2 km atau ≥Rp100rb), `OngkirService`
- [x] Pembuatan pesanan transaksional: pesanan + detail + stok log + transaksi, nota unik
- [x] Pelacakan pesanan `/pesanan/{nota}` + timeline 7 tahap, `/lacak`
- [x] Fallback gambar: placeholder lokal + `onerror` otomatis (13 URL rusak dibersihkan)

### 4. Loyalitas (end-to-end, teruji)
- [x] Poin otomatis saat checkout (1 poin / Rp10.000) → `poin_log`
- [x] Login pelanggan via nomor HP (auto-daftar)
- [x] Halaman `/akun`: poin, riwayat pesanan
- [x] Tukar poin → voucher (kode unik, masa berlaku), daftar voucher milik pelanggan
- [x] Harga reseller tersedia di skema (UI segmen reseller menyusul)

### 5. Panel Admin
- [x] Dashboard: omzet harian, pesanan, produk, pelanggan, **piutang**, stok menipis
- [x] Produk: CRUD + upload gambar file/URL + preview
- [x] Pesanan: daftar, filter status, ubah status (timeline otomatis)
- [x] Stok: penyesuaian masuk/keluar + stok_log + daftar stok + status menipis
- [x] Pelanggan: daftar + segmen tipe + poin
- [x] **Bon/Piutang**: buat bon (dari pesanan atau manual), cicilan bayar, status lunas/sebagian, detail riwayat pembayaran

### 6. Rutinitas & Notifikasi Stok
- [x] Rutinitas belanja: simpan keranjang sebagai template, pesan ulang 1 klik, hapus
- [x] Stok alert: form "Ingatkan Saya" saat stok habis, daftar permintaan di admin

### 7. Infrastruktur & Tooling
- [x] ETL `database/etl/import_produk.php` — 100 produk dari Excel (tanpa dependensi, idempoten)
- [x] Vhost `bhaktikarya.test` + junction `www/bhaktikarya`
- [x] Live preview server (`server.php`) → `http://localhost:8080`
- [x] Perbaikan environment: `extension_dir` php.ini 8.1, `session.save_path` fallback
- [x] README.md (struktur, cara jalan, konvensi)

---

## 🔲 BELUM / RENCANA BERIKUTNYA

### Prioritas Tinggi
- [ ] **QRIS gateway nyata** (Midtrans/Xendit) — saat ini mode simulasi + COD/transfer
- [ ] **Harga reseller di UI** — kolom siap, perlu segmen & tampilan harga khusus
- [ ] **Voucher di checkout** — pemakaian voucher hasil tukar poin saat membayar
- [ ] **Kode promo di checkout** — validasi HEMAT10 / ONGKIR8RB

### Prioritas Menengah
- [ ] Laporan penjualan (harian/bulanan, produk terlaris, grafik)
- [ ] Notifikasi WhatsApp (status pesanan, stok alert) — via API (Fonnte/Wablas)
- [ ] Halaman riwayat pesanan pelanggan yang lebih kaya (filter, ulang pesanan)
- [ ] Bon otomatis saat checkout reseller pilih tempo
- [ ] Backup otomatis DB (tabel `backup_log` sudah siap)

### Prioritas Rendah / Polish
- [ ] Tailwind build lokal (ganti CDN) + Vite
- [ ] Tes otomatis (PHPUnit/Pest)
- [ ] Multi foto produk, review/rating
- [ ] PWA / notifikasi browser
- [ ] Optimasi SEO (meta, sitemap)

---

## 🐛 BUG YANG SUDAH DIPERBAIKI
| Bug | Perbaikan |
|---|---|
| Router tidak inject `Request` → fatal error | `Router::resolveArgs()` via reflection |
| `session.save_path` invalid (php.ini Laragon) | Fallback ke `storage/sessions` |
| `extension_dir` php.ini 8.1 salah path | Diperbaiki (backup `php.ini.bak-bhaktikarya`) |
| Folder proyek bernama spasi → Apache 404 | Junction `www/bhaktikarya` + vhost |
| 13 URL gambar produk rusak | Placeholder + `onerror` fallback |
| Blok notifikasi stok nyasar di form admin stok | Ditulis ulang, div balance 12/12 |

---

## ▶️ CARA MENJALANKAN
```bash
# 1. Import DB (buat bhaktikarya_grosir)
mysql -u root < database/schema_bhaktikarya.sql
mysql -u root < database/seed_bhaktikarya.sql

# 2. Impor produk dari Excel
php database/etl/import_produk.php

# 3. Live preview
php -S 127.0.0.1:8080 -t public server.php

# 4. Atau via Laragon (vhost) → http://bhaktikarya.test
```
**Login admin:** `admin` / `adminpassword`
**Login pelanggan:** nomor HP (auto-daftar)
