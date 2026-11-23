# Product Requirements Document (PRD)
## Website Warung Grosir Bhakti Karya

| Field | Keterangan |
|---|---|
| **Nama Produk** | Bhakti Karya — Platform Belanja Online Warung Grosir |
| **Versi Dokumen** | 1.0 |
| **Tanggal** | 23 November 2026 |
| **Sumber Acuan** | `Database_Bhaktikarya.xlsx` (22 sheet, 100 produk, meta versi 4) |
| **Status** | Draft untuk pengembangan |

---

## 1. Ringkasan Eksekutif

Bhakti Karya adalah **warung grosir** yang berlokasi di Kecamatan Tapos, Kota Depok, yang saat ini melayani penjualan secara langsung maupun online. Website ini akan menjadi kanal digital resmi untuk:

- Menampilkan katalog produk grosir (sembako, minuman, snack, bumbu, kebersihan, perawatan diri, perlengkapan rumah, mie instan).
- Menerima pesanan online dengan **antar-ke-rumah** dan **ambil di toko**.
- Menghitung **ongkos kirim otomatis** berbasis zona/jarak wilayah kelurahan di Kecamatan Tapos.
- Mengelola **stok harian**, **promo**, **poin loyalitas**, **voucher**, **harga reseller**, dan **bon (hutang pelanggan)**.

Struktur database yang ada menjadi **single source of truth** untuk kebutuhan fungsional dan model data.

---

## 2. Latar Belakang & Tujuan

### 2.1 Masalah
- Pencatatan pesanan, stok, dan piutang (bon) masih manual / semi-digital, rawan selisih dan sulit dilacak.
- Pelanggan tidak punya kanal untuk melihat harga, ketersediaan stok, atau memesan ulang dengan cepat.
- Promo, poin, dan harga khusus reseller sulit dikelola konsisten.

### 2.2 Tujuan
1. Menyediakan katalog online yang selalu sinkron dengan stok nyata.
2. Mengotomatisasi perhitungan ongkir, promo, poin, dan total bayar.
3. Memberi pelanggan pengalaman belanja ulang cepat (rutinitas langganan).
4. Menyediakan dashboard admin untuk pesanan, stok, keuangan, dan pelanggan.

### 2.3 Non-Tujuan (Fase 1)
- Multi-toko / multi-cabang.
- Integrasi kurir pihak ketiga (hanya kurir internal).
- Marketplace multi-penjual.

---

## 3. Target Pengguna & Persona

| Persona | Deskripsi | Kebutuhan Utama |
|---|---|---|
| **Pelanggan Umum** | Warga lokal Tapos & sekitarnya | Harga grosir, antar cepat, COD/QRIS, poin |
| **Pelanggan Reseller** | Warung/toko kecil yang kulak ulang | Harga reseller, beli jumlah besar, bon/tempo |
| **Admin/Owner** | Pengelola toko (`User.role = admin`) | Kelola produk, stok, pesanan, laporan |
| **Kasir/Staff** | Operator harian (`User.role = user`) | Proses pesanan, konfirmasi bayar, stok |

---

## 4. Ruang Lingkup

### 4.1 In Scope
- Situs web responsif (mobile-first) untuk pelanggan.
- Panel admin.
- Integrasi pembayaran **QRIS** (mode simulasi → produksi).
- Manajemen stok, promo, poin, voucher, bon, langganan.

### 4.2 Out of Scope (awal)
- Aplikasi native iOS/Android.
- Notifikasi push (mulai dengan WhatsApp/email bila memungkinkan).

---

## 5. Arsitektur Data (Berdasarkan Database)

> Database berisi 22 tabel. Berikut pemetaan tabel → domain fungsional.

### 5.1 Domain Produk & Kategori
**`Produk`** (100 baris)
`id, nama_produk, kategori_id, nama_kategori, harga, harga_lama, stok, badge_status, deskripsi, gambar, is_favorit, harga_beli, harga_reseller, penjualan, alias, satuan, min_order, aktif, created_at, updated_at`
- `badge_status`: `Terlaris` (40), `Standar` (42), `Komoditas` (9), `Promo` (9).
- `harga_lama` dipakai untuk menampilkan harga coret (diskon visual).
- `gambar` berupa URL gambar produk.
- Harga produk berkisar **Rp2.500 – Rp68.000**.

**`kategori`** — `id (K01..K06), nama_kategori, ikon (emoji), urutan, created_at`.
> ⚠️ **Catatan inkonsistensi:** sheet `kategori` berisi 6 kategori (Sembako, Minuman, Snack, Tisu & Kebersihan, Bumbu Dapur, Perawatan), sedangkan `Produk.nama_kategori` memuat **8 kategori** (termasuk *Snack & Biskuit, Bumbu & Dapur, Sabun & Kebersihan, Perawatan Diri, Perlengkapan Rumah, Mie Instan*). **Perlu keputusan:** jadikan `kategori` sebagai master dan migrasikan produk, atau pakai kategori dari produk.

### 5.2 Domain Pesanan
**`Pesanan`** (tabel utama, ±50 kolom) — lifecycle pesanan lengkap:
`id, nota_id (WBK/WGK-…), user_id, nama_pelanggan, no_hp, alamat, subtotal, diskon, jarak_km, ongkir, metode_pembayaran, detail_pembayaran, jadwal, catatan, total_harga, status, pelanggan_id, metode_kirim, metode_bayar, sumber (online/kasir), ongkir_gratis, zona_id, kurang_belanja, gratis_potensi, diskon_promo, diskon_reseller, subtotal_setelah_promo, voucher_kode, voucher_diskon, kode_promo, diskon_kode, total_bayar, poin_didapat, poin_diberikan, poin_dibalikkan, stock_restored, toko_tutup, toko_pesan, pembayaran_status, qr_payload, diterima_at, dikemas_at, dikirim_at, tiba_at, is_new_customer, alasan_batal`

**`transaksi`** — ringkasan pembayaran: `id, nota_id, tanggal, total_bayar, status, metode_bayar, metode_kirim, ongkir`.

**`pesanan_detail`** — baris item: `id, nota_id, pelanggan_id, produk_id, nama_produk, jumlah, harga_normal, harga_satuan, diskon_promo, subtotal`.

### 5.3 Domain Pelanggan & Loyalitas
**`pelanggan`** — `id (C001…), nama_lengkap, telepon, alamat, kecamatan, kelurahan, kota, tipe (umum/reseller), poin, total_belanja, created_at, updated_at`.

**`poin_log`** — ledger poin: `id, pelanggan_id, jenis (masuk/keluar), jumlah, saldo_sebelum, saldo_sesudah, keterangan, referensi, tanggal`.

**`voucher_katalog`** — voucher yang bisa ditukar poin:
`V01 Rp10.000 (100 poin, min Rp100.000)`, `V02 Rp30.000 (250 poin, min Rp250.000)`, `V03 Rp75.000 (500 poin, min Rp500.000)`; `masa_berlaku_hari = 30`.

**`voucher_pelanggan`** — voucher yang dimiliki pelanggan: `id, kode, pelanggan_id, katalog_id, status, tgl_tukar, tgl_kedaluwarsa, nota_pakai`.

### 5.4 Domain Logistik & Ongkir
**`zona_ongkir`** — tarif per kelurahan:
| ID | Kelurahan | Jarak | Tarif |
|---|---|---|---|
| Z01 | Cilangkap | 1.2 km | Rp0 |
| Z02 | Tapos | 2.8 km | Rp12.000 |
| Z03 | Sukamaju Baru | 3.4 km | Rp15.000 |
| Z04 | Jatijajar | 4.2 km | Rp18.000 |
| Z05 | Cimpaeun | 5.4 km | Rp22.000 |
| Z06 | Kalisuren | 6.1 km | Rp25.000 |
| Z07 | Lainnya | 8.0 km | Rp30.000 |

**`pengaturan`** — parameter operasional:
| Key | Nilai | Arti |
|---|---|---|
| toko_lat / toko_lng | -6.4025 / 106.864 | Koordinat toko |
| radius_gratis_km | 2.0 | Radius gratis ongkir |
| min_belanja_gratis_ongkir | 100000 | Min belanja gratis ongkir |
| faktor_jalan | 1.3 | Faktor jarak jalan vs lurus |
| maks_jarak_antar_km | 5.0 | Batas jarak antar |
| tarif_dalam_radius | 8000 | Tarif dasar dalam radius |
| tarif_per_km | 4000 | Tarif per km di luar radius |
| toko_buka | 1 | Status toko |
| jam_buka / jam_tutup | 07:00 / 21:00 | Jam operasional |
| poin_per_rupiah | 10000 | 1 poin per Rp10.000 |
| low_stock | 10 | Ambang stok menipis |

### 5.5 Domain Promo, Stok, Bon, Lainnya
- **`promo`** — promo per produk: `id, produk_id, tipe, nilai, mulai, selesai, aktif`.
- **`kode_promo`** — `HEMAT10` (persen 10%, min Rp50.000, kuota 100), `ONGKIR8RB` (nominal Rp8.000).
- **`stok_log`** — mutasi stok: `jenis (masuk/keluar), jumlah, stok_sebelum, stok_sesudah, referensi`.
- **`stok_harian`** — snapshot harian: `stok_awal_hari, masuk_hari, keluar_hari, sisa`.
- **`stok_alert`** — permintaan notifikasi stok habis: `pelanggan_id, produk_id, telepon, nama, status`.
- **`bon`** — piutang: `pelanggan_id, nota_id, total, sisa, status, jatuh_tempo`.
- **`bon_bayar`** — cicilan bayar bon: `bon_id, jumlah, tanggal`.
- **`rutinitas`** — pesanan langganan: `pelanggan_id, nama, items_json`.
- **`backup_log` / `meta`** — `versi_stok=2`, `version=4`.

---

## 6. Fitur Fungsional

### 6.1 Sisi Pelanggan

**F1. Beranda & Katalog**
- Banner promo, kategori (dengan ikon emoji), produk favorit/terlaris.
- Menampilkan badge: Terlaris / Komoditas / Promo / Standar.
- Harga coret bila `harga_lama > harga`.
- Pencarian (nama, alias) + filter kategori + urut harga/terlaris.

**F2. Detail Produk**
- Gambar, deskripsi, satuan, `min_order`, status stok real-time.
- Indikator jika stok ≤ `low_stock` (stok menipis) atau habis → tombol **"Ingatkan saya"** (`stok_alert`).

**F3. Keranjang & Checkout**
- Keranjang sesi/cookie (guest) + tersinkron untuk user login.
- Input alamat → pilih kecamatan & kelurahan → auto `zona_ongkir` → hitung `jarak_km` & `ongkir`.
- Pilih `metode_kirim`: **antar** / **ambil di toko** (ongkir 0 untuk ambil/radius gratis).
- Pilih `jadwal` (slot pengiriman) + `catatan`.
- Pilih `metode_bayar`: **QRIS**, **COD**, **Transfer**.
- Ringkasan: subtotal, diskon_promo, diskon_reseller, diskon_kode, voucher_diskon, ongkir, total_bayar.

**F4. Ongkos Kirim Otomatis**
- Bila `jarak_km ≤ radius_gratis_km` **atau** belanja ≥ `min_belanja_gratis_ongkir` → ongkir gratis (`ongkir_gratis`, `gratis_potensi`, `kurang_belanja` untuk info).
- Jika melebihi `maks_jarak_antar_km` → tampilkan pesan / fallback zona "Lainnya".
- Formula jarak memakai `faktor_jalan` (jarak jalan ≈ jarak lurus × 1.3).

**F5. Pembayaran**
- QRIS: generate `qr_payload` (format `QRIS.<mode>|Warung Grosir Bhakti Karya|<nota>|<total>|<ts>`), status bayar `Menunggu Pembayaran` → `Dibayar`.
- COD: bayar saat terima.
- Transfer: unggah bukti / konfirmasi admin.

**F6. Pelacakan Pesanan**
- Status: `Menunggu Pembayaran → Diproses → Dikemas → Dikirim → Tiba/Diterima → Selesai`.
- Timeline memakai `diterima_at, dikemas_at, dikirim_at, tiba_at`.
- Bisa dibatalkan dengan `alasan_batal` sebelum tahap tertentu; `stock_restored` mengembalikan stok.

**F7. Akun & Loyalitas**
- Registrasi/login pelanggan (berbasis `pelanggan` + `telepon`).
- Riwayat pesanan, saldo poin, dan riwayat poin (`poin_log`).
- Tukar poin → voucher (`voucher_katalog` → `voucher_pelanggan`), cek masa berlaku.
- Penanda pelanggan baru (`is_new_customer`).

**F8. Promo & Kode Promo**
- Terapkan `kode_promo` (persen/nominal) dengan validasi `min_belanja`, `mulai`–`selesai`, sisa `kuota`.
- Voucher dari poin diterapkan sebagai potongan.

**F9. Harga Reseller**
- Pelanggan `tipe=reseller` melihat `harga_reseller` (menggantikan `harga`).
- Hitung `diskon_reseller` di pesanan.

**F10. Bon (Tempo) & Cicilan**
- Untuk reseller/pelanggan terdaftar: buat bon dengan `jatuh_tempo`, bayar sebagian (`bon_bayar`), update `sisa` & `status`.

**F11. Rutinitas / Langganan**
- Simpan template pesanan berulang (`items_json`) → tombol "Pesan lagi" sekali klik.

### 6.2 Panel Admin

**A1. Dashboard** — ringkasan penjualan, pesanan baru, stok menipis, piutang jatuh tempo.

**A2. Manajemen Produk** — CRUD produk, upload gambar, atur `kategori`, `badge_status`, `harga`, `harga_lama`, `harga_reseller`, `harga_beli`, `min_order`, `aktif`, `is_favorit`.

**A3. Manajemen Stok**
- Input stok masuk/keluar → catat `stok_log` + update `Produk.stok`.
- Snapshot harian `stok_harian`.
- Notifikasi `low_stock` & daftar permintaan `stok_alert`.

**A4. Manajemen Pesanan** — ubah status, konfirmasi bayar, input nomor resi/kurir internal, batalkan pesanan (dengan alasan).

**A5. Promo & Voucher** — CRUD `promo`, `kode_promo`, `voucher_katalog`.

**A6. Pelanggan & Poin** — daftar pelanggan, segmen reseller, koreksi poin, kelola voucher.

**A7. Bon & Piutang** — daftar bon, catat cicilan, lihat jatuh tempo.

**A8. Pengaturan** — kelola `pengaturan` (jam buka, tarif, radius, poin rate, low stock) & `zona_ongkir`.

**A9. Laporan** — penjualan (harian/bulanan), produk terlaris, mutasi stok, piutang.

**A10. Audit & Backup** — lihat `backup_log`, `meta` versi.

---

## 7. Aturan Bisnis (Business Rules)

1. **Poin:** `poin_didapat = floor(total_bayar / poin_per_rupiah)` (default 1 poin / Rp10.000).
2. **Gratis ongkir** jika (jarak ≤ 2 km) **atau** (subtotal ≥ Rp100.000).
3. **Ongkir** = tarif zona; di luar radius = `tarif_dalam_radius` + `tarif_per_km × (km − radius)`.
4. **Stok** berkurang saat pesanan dikonfirmasi; dikembalikan bila dibatalkan (`stock_restored`).
5. **Toko tutup:** pesanan masuk ditandai `toko_tutup` dan diproses jam buka berikutnya.
6. **Kode promo** hanya berlaku dalam rentang tanggal, min belanja, dan selama `kuota > terpakai`.
7. **Voucher poin** kedaluwarsa sesuai `masa_berlaku_hari`.
8. **Harga reseller** hanya untuk `pelanggan.tipe = reseller`.

---

## 8. Persyaratan Non-Fungsional

| Aspek | Target |
|---|---|
| Responsif | Mobile-first, breakpoint 360/768/1024/1440 |
| Performa | LCP < 2.5s, halaman katalog < 2s |
| Keamanan | Hash password, HTTPS, proteksi CSRF, validasi input; **jangan simpan password plaintext** (perbaiki data seed `admin/adminpassword`) |
| Ketersediaan | 99% uptime |
| Skalabilitas | Mendukung ≥ 1.000 SKU, ≥ 500 pesanan/hari |
| Observabilitas | Log transaksi, mutasi stok, backup terjadwal |
| Bahasa | Indonesia |
| Mata Uang | Rupiah (IDR), format `Rp37.500` |

---

## 9. Rencana Rilis (Fase)

**Fase 1 — MVP (Inti)**
Katalog, detail produk, keranjang, checkout, ongkir zona, QRIS/COD, pelacakan status, panel admin produk & pesanan, stok dasar.

**Fase 2 — Loyalitas & Promo**
Poin, voucher, kode promo, promo produk, harga reseller, favorit.

**Fase 3 — Lanjutan**
Bon/piutang + cicilan, rutinitas langganan, stok_alert, laporan lanjutan, snapshot stok harian, backup otomatis.

---

## 10. Metrik Keberhasilan

- ≥ **70%** pesanan online diproses otomatis (tanpa input ulang manual).
- **0 selisih** stok antara sistem dan fisik setelah 1 siklus opname.
- ≥ **40%** pelanggan kembali dalam 30 hari (repeat order).
- Waktu checkout rata-rata < **3 menit**.

---

## 11. Asumsi & Pertanyaan Terbuka

1. **Master kategori** mana yang dipakai — 6 kategori sheet `kategori` atau 8 kategori di `Produk`? (Perlu normalisasi.)
2. `maks_jarak_antar_km = 5 km`, tetapi `zona_ongkir` Z07 = 8 km → mana yang menang? (Rekomendasi: zona jadi acuan, jarak hanya untuk radius gratis.)
3. Apakah pembayaran QRIS memakai payment gateway nyata (mis. Midtrans/Xendit) saat produksi?
4. Apakah login pelanggan berbasis nomor HP + OTP atau email + password?
5. Kebutuhan cetak struk/nota fisik?
6. Apakah ada integrasi WhatsApp untuk notifikasi pesanan?

---

## 12. Lampiran — Kamus Data Ringkas

| Tabel | Fungsi | Kunci Relasi |
|---|---|---|
| Produk | Katalog | kategori_id → kategori |
| kategori | Master kategori | — |
| pesanan_detail | Item pesanan | nota_id → Pesanan/transaksi; produk_id → Produk |
| Pesanan | Pesanan (lifecycle) | pelanggan_id → pelanggan |
| transaksi | Ringkasan bayar | nota_id → Pesanan |
| pelanggan | Data pelanggan | — |
| poin_log | Ledger poin | pelanggan_id → pelanggan |
| voucher_katalog | Voucher tukar poin | — |
| voucher_pelanggan | Voucher dimiliki | pelanggan_id, katalog_id |
| zona_ongkir | Tarif kelurahan | — |
| pengaturan | Konfigurasi | — |
| promo | Promo produk | produk_id → Produk |
| kode_promo | Kode diskon | — |
| stok_log | Mutasi stok | produk_id → Produk |
| stok_harian | Snapshot stok | produk_id → Produk |
| stok_alert | Notifikasi stok | pelanggan_id, produk_id |
| bon | Piutang | pelanggan_id, nota_id |
| bon_bayar | Cicilan bon | bon_id → bon |
| rutinitas | Pesanan langganan | pelanggan_id |
| backup_log / meta | Audit & versi | — |
| User | Akun admin/staff | — |
