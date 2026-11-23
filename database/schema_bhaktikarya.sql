-- =====================================================================
--  SKEMA DATABASE — WARUNG GROSIR BHAKTI KARYA
--  Engine  : MySQL 5.7+ / MariaDB 10.4+  (teruji di MySQL 5.7.39)
--  Charset : utf8mb4
--  File    : schema_bhaktikarya.sql  (DDL)
--  Catatan : Jalankan di database kosong, lalu eksekusi seed_bhaktikarya.sql
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `bhaktikarya_grosir`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bhaktikarya_grosir`;

-- ---------------------------------------------------------------------
-- 1. USERS (admin / kasir / staff)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(50)  NOT NULL,
  `password`      VARCHAR(255) NOT NULL COMMENT 'hash bcrypt/argon2 — JANGAN plaintext',
  `role`          ENUM('admin','user','kasir') NOT NULL DEFAULT 'user',
  `nama_lengkap`  VARCHAR(150) NOT NULL,
  `telepon`       VARCHAR(20)  DEFAULT NULL,
  `alamat`        TEXT         DEFAULT NULL,
  `email`         VARCHAR(150) DEFAULT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. KATEGORI
--    Master 8 kategori (menggabungkan sheet `kategori` + `Produk.nama_kategori`)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `kategori`;
CREATE TABLE `kategori` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode`          VARCHAR(10)  NOT NULL COMMENT 'K01..K08',
  `nama_kategori` VARCHAR(50)  NOT NULL,
  `ikon`          VARCHAR(10)  DEFAULT NULL COMMENT 'emoji',
  `urutan`        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kategori_kode` (`kode`),
  UNIQUE KEY `uq_kategori_nama` (`nama_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. ZONA ONGKIR (per kelurahan)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `zona_ongkir`;
CREATE TABLE `zona_ongkir` (
  `id`        VARCHAR(10)  NOT NULL COMMENT 'Z01..',
  `kelurahan` VARCHAR(80)  NOT NULL,
  `kecamatan` VARCHAR(80)  NOT NULL,
  `jarak_km`  DECIMAL(6,2) NOT NULL DEFAULT 0,
  `tarif`     DECIMAL(12,2) NOT NULL DEFAULT 0,
  `aktif`     TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_zona_kelurahan` (`kelurahan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. PENGATURAN (key-value konfigurasi toko)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `pengaturan`;
CREATE TABLE `pengaturan` (
  `setting_key`   VARCHAR(60)  NOT NULL,
  `setting_value` VARCHAR(255) NOT NULL,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. META (metadata versi/audit internal)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `meta`;
CREATE TABLE `meta` (
  `meta_key`   VARCHAR(60)  NOT NULL,
  `meta_value` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`meta_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. PELANGGAN
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `pelanggan`;
CREATE TABLE `pelanggan` (
  `id`            VARCHAR(20)  NOT NULL COMMENT 'C001..',
  `nama_lengkap`  VARCHAR(150) NOT NULL,
  `telepon`       VARCHAR(20)  NOT NULL,
  `alamat`        TEXT         DEFAULT NULL,
  `kecamatan`     VARCHAR(80)  DEFAULT NULL,
  `kelurahan`     VARCHAR(80)  DEFAULT NULL,
  `kota`          VARCHAR(80)  DEFAULT 'Kota Depok',
  `tipe`          ENUM('umum','reseller') NOT NULL DEFAULT 'umum',
  `poin`          INT          NOT NULL DEFAULT 0,
  `total_belanja` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pelanggan_telepon` (`telepon`),
  KEY `idx_pelanggan_tipe` (`tipe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. PRODUK
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `produk`;
CREATE TABLE `produk` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_produk`    VARCHAR(150) NOT NULL,
  `kategori_id`    INT UNSIGNED DEFAULT NULL,
  `harga`          DECIMAL(12,2) NOT NULL DEFAULT 0,
  `harga_lama`     DECIMAL(12,2) DEFAULT NULL COMMENT 'harga coret',
  `stok`           INT          NOT NULL DEFAULT 0,
  `badge_status`   ENUM('Standar','Terlaris','Komoditas','Promo') NOT NULL DEFAULT 'Standar',
  `deskripsi`      TEXT         DEFAULT NULL,
  `gambar`         VARCHAR(500) DEFAULT NULL,
  `is_favorit`     TINYINT(1)   NOT NULL DEFAULT 0,
  `harga_beli`     DECIMAL(12,2) NOT NULL DEFAULT 0,
  `harga_reseller` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `penjualan`      INT          NOT NULL DEFAULT 0 COMMENT 'counter terjual',
  `alias`          VARCHAR(150) DEFAULT NULL COMMENT 'kata kunci pencarian',
  `satuan`         VARCHAR(20)  NOT NULL DEFAULT 'pcs',
  `min_order`      INT          NOT NULL DEFAULT 1,
  `aktif`          TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_produk_kategori` (`kategori_id`),
  KEY `idx_produk_aktif` (`aktif`),
  KEY `idx_produk_favorit` (`is_favorit`),
  KEY `idx_produk_badge` (`badge_status`),
  FULLTEXT KEY `ft_produk` (`nama_produk`,`alias`,`deskripsi`),
  CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`kategori_id`)
    REFERENCES `kategori` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. PESANAN (tabel utama — lifecycle lengkap)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `pesanan`;
CREATE TABLE `pesanan` (
  `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nota_id`               VARCHAR(32)  NOT NULL COMMENT 'WBK-YYYYMMDD-XXXX / WGK-...',
  `request_id`            VARCHAR(40)  DEFAULT NULL COMMENT 'idempotency key',
  `user_id`               BIGINT UNSIGNED DEFAULT NULL COMMENT 'kasir/staff pembuat (opsional)',
  `pelanggan_id`          VARCHAR(20)  DEFAULT NULL,
  `nama_pelanggan`        VARCHAR(150) DEFAULT NULL,
  `no_hp`                 VARCHAR(20)  DEFAULT NULL,
  `alamat`                TEXT         DEFAULT NULL,
  `subtotal`              DECIMAL(12,2) NOT NULL DEFAULT 0,
  `diskon`                DECIMAL(12,2) NOT NULL DEFAULT 0,
  `jarak_km`              DECIMAL(6,2) DEFAULT NULL,
  `ongkir`                DECIMAL(12,2) NOT NULL DEFAULT 0,
  `metode_pembayaran`     VARCHAR(30)  DEFAULT NULL COMMENT 'QRIS/COD/Transfer',
  `detail_pembayaran`     VARCHAR(100) DEFAULT NULL,
  `jadwal`                VARCHAR(100) DEFAULT NULL COMMENT 'slot pengiriman',
  `catatan`               TEXT         DEFAULT NULL,
  `total_harga`           DECIMAL(12,2) NOT NULL DEFAULT 0,
  `status`                ENUM('Menunggu Pembayaran','Diproses','Dikemas','Dikirim','Tiba','Diterima','Selesai','Dibatalkan')
                            NOT NULL DEFAULT 'Menunggu Pembayaran',
  `tanggal`               DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `metode_kirim`          ENUM('antar','ambil') NOT NULL DEFAULT 'antar',
  `metode_bayar`          ENUM('qris','cod','transfer') NOT NULL DEFAULT 'qris',
  `sumber`                ENUM('online','kasir') NOT NULL DEFAULT 'online',
  `ongkir_gratis`         TINYINT(1)   NOT NULL DEFAULT 0,
  `zona_id`               VARCHAR(10)  DEFAULT NULL,
  `kurang_belanja`        DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'selisih menuju gratis ongkir',
  `gratis_potensi`        TINYINT(1)   NOT NULL DEFAULT 0,
  `diskon_promo`          DECIMAL(12,2) NOT NULL DEFAULT 0,
  `diskon_reseller`       DECIMAL(12,2) NOT NULL DEFAULT 0,
  `subtotal_setelah_promo` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `voucher_kode`          VARCHAR(30)  DEFAULT NULL,
  `voucher_diskon`        DECIMAL(12,2) NOT NULL DEFAULT 0,
  `kode_promo`            VARCHAR(30)  DEFAULT NULL,
  `diskon_kode`           DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_bayar`           DECIMAL(12,2) NOT NULL DEFAULT 0,
  `poin_didapat`          INT          NOT NULL DEFAULT 0,
  `poin_diberikan`        TINYINT(1)   NOT NULL DEFAULT 0,
  `poin_dibalikkan`       TINYINT(1)   NOT NULL DEFAULT 0,
  `stock_restored`        TINYINT(1)   NOT NULL DEFAULT 0,
  `toko_tutup`            TINYINT(1)   NOT NULL DEFAULT 0,
  `toko_pesan`            VARCHAR(255) DEFAULT NULL,
  `pembayaran_status`     VARCHAR(30)  DEFAULT 'Menunggu Pembayaran',
  `qr_payload`            TEXT         DEFAULT NULL,
  `diterima_at`           DATETIME     DEFAULT NULL,
  `dikemas_at`            DATETIME     DEFAULT NULL,
  `dikirim_at`            DATETIME     DEFAULT NULL,
  `tiba_at`               DATETIME     DEFAULT NULL,
  `is_new_customer`       TINYINT(1)   NOT NULL DEFAULT 0,
  `alasan_batal`          TEXT         DEFAULT NULL,
  `created_at`            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pesanan_nota` (`nota_id`),
  UNIQUE KEY `uq_pesanan_request` (`request_id`),
  KEY `idx_pesanan_status` (`status`),
  KEY `idx_pesanan_tanggal` (`tanggal`),
  KEY `idx_pesanan_pelanggan` (`pelanggan_id`),
  KEY `idx_pesanan_zona` (`zona_id`),
  CONSTRAINT `fk_pesanan_pelanggan` FOREIGN KEY (`pelanggan_id`)
    REFERENCES `pelanggan` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_pesanan_zona` FOREIGN KEY (`zona_id`)
    REFERENCES `zona_ongkir` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_pesanan_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. PESANAN DETAIL (item)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `pesanan_detail`;
CREATE TABLE `pesanan_detail` (
  `id`            VARCHAR(40)  NOT NULL COMMENT 'DTL-<nota>-<idx>',
  `nota_id`       VARCHAR(32)  NOT NULL,
  `pelanggan_id`  VARCHAR(20)  DEFAULT NULL,
  `produk_id`     BIGINT UNSIGNED NOT NULL,
  `nama_produk`   VARCHAR(150) NOT NULL,
  `jumlah`        INT          NOT NULL DEFAULT 1,
  `harga_normal`  DECIMAL(12,2) NOT NULL DEFAULT 0,
  `harga_satuan`  DECIMAL(12,2) NOT NULL DEFAULT 0,
  `diskon_promo`  DECIMAL(12,2) NOT NULL DEFAULT 0,
  `subtotal`      DECIMAL(12,2) NOT NULL DEFAULT 0,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_detail_nota` (`nota_id`),
  KEY `idx_detail_produk` (`produk_id`),
  CONSTRAINT `fk_detail_nota` FOREIGN KEY (`nota_id`)
    REFERENCES `pesanan` (`nota_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_detail_produk` FOREIGN KEY (`produk_id`)
    REFERENCES `produk` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 10. TRANSAKSI (ringkasan pembayaran)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `transaksi`;
CREATE TABLE `transaksi` (
  `id`            VARCHAR(30)  NOT NULL COMMENT 'TRX-...',
  `nota_id`       VARCHAR(32)  NOT NULL,
  `tanggal`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_bayar`   DECIMAL(12,2) NOT NULL DEFAULT 0,
  `status`        VARCHAR(30)  NOT NULL DEFAULT 'Diterima',
  `metode_bayar`  ENUM('qris','cod','transfer') NOT NULL DEFAULT 'qris',
  `metode_kirim`  ENUM('antar','ambil') NOT NULL DEFAULT 'antar',
  `ongkir`        DECIMAL(12,2) NOT NULL DEFAULT 0,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_transaksi_nota` (`nota_id`),
  CONSTRAINT `fk_transaksi_pesanan` FOREIGN KEY (`nota_id`)
    REFERENCES `pesanan` (`nota_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 11. PROMO (diskon per produk)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `promo`;
CREATE TABLE `promo` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `produk_id`   BIGINT UNSIGNED NOT NULL,
  `tipe`        ENUM('persen','nominal') NOT NULL DEFAULT 'persen',
  `nilai`       DECIMAL(12,2) NOT NULL DEFAULT 0,
  `mulai`       DATETIME     DEFAULT NULL,
  `selesai`     DATETIME     DEFAULT NULL,
  `aktif`       TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_promo_produk` (`produk_id`),
  KEY `idx_promo_aktif` (`aktif`),
  CONSTRAINT `fk_promo_produk` FOREIGN KEY (`produk_id`)
    REFERENCES `produk` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 12. KODE PROMO
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `kode_promo`;
CREATE TABLE `kode_promo` (
  `id`          VARCHAR(20)  NOT NULL COMMENT 'KP01..',
  `kode`        VARCHAR(30)  NOT NULL,
  `tipe`        ENUM('persen','nominal') NOT NULL DEFAULT 'persen',
  `nilai`       DECIMAL(12,2) NOT NULL DEFAULT 0,
  `min_belanja` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `mulai`       DATETIME     DEFAULT NULL,
  `selesai`     DATETIME     DEFAULT NULL,
  `kuota`       INT          NOT NULL DEFAULT 0 COMMENT '0 = tanpa batas',
  `terpakai`    INT          NOT NULL DEFAULT 0,
  `aktif`       TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kode_promo_kode` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 13. VOUCHER KATALOG (tukar poin)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `voucher_katalog`;
CREATE TABLE `voucher_katalog` (
  `id`                 VARCHAR(10)  NOT NULL COMMENT 'V01..',
  `nama`               VARCHAR(100) NOT NULL,
  `poin_dibutuhkan`    INT          NOT NULL DEFAULT 0,
  `tipe`               ENUM('nominal','persen') NOT NULL DEFAULT 'nominal',
  `nilai`              DECIMAL(12,2) NOT NULL DEFAULT 0,
  `min_belanja`        DECIMAL(12,2) NOT NULL DEFAULT 0,
  `masa_berlaku_hari`  INT          NOT NULL DEFAULT 30,
  `aktif`              TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 14. VOUCHER PELANGGAN (hasil tukar poin)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `voucher_pelanggan`;
CREATE TABLE `voucher_pelanggan` (
  `id`               VARCHAR(30)  NOT NULL,
  `kode`             VARCHAR(30)  NOT NULL,
  `pelanggan_id`     VARCHAR(20)  NOT NULL,
  `katalog_id`       VARCHAR(10)  NOT NULL,
  `status`           ENUM('aktif','terpakai','kedaluwarsa') NOT NULL DEFAULT 'aktif',
  `tgl_tukar`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tgl_kedaluwarsa`  DATETIME     DEFAULT NULL,
  `nota_pakai`       VARCHAR(32)  DEFAULT NULL,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_voucher_pel_kode` (`kode`),
  KEY `idx_voucher_pel_pelanggan` (`pelanggan_id`),
  CONSTRAINT `fk_voucher_pel_pelanggan` FOREIGN KEY (`pelanggan_id`)
    REFERENCES `pelanggan` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_voucher_pel_katalog` FOREIGN KEY (`katalog_id`)
    REFERENCES `voucher_katalog` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 15. POIN LOG (ledger poin)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `poin_log`;
CREATE TABLE `poin_log` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pelanggan_id`   VARCHAR(20)  NOT NULL,
  `jenis`          ENUM('masuk','keluar') NOT NULL,
  `jumlah`         INT          NOT NULL DEFAULT 0,
  `saldo_sebelum`  INT          NOT NULL DEFAULT 0,
  `saldo_sesudah`  INT          NOT NULL DEFAULT 0,
  `keterangan`     VARCHAR(255) DEFAULT NULL,
  `referensi`      VARCHAR(40)  DEFAULT NULL COMMENT 'nota_id / voucher',
  `tanggal`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_poin_pelanggan` (`pelanggan_id`),
  CONSTRAINT `fk_poin_pelanggan` FOREIGN KEY (`pelanggan_id`)
    REFERENCES `pelanggan` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 16. STOK LOG (mutasi stok)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `stok_log`;
CREATE TABLE `stok_log` (
  `id`            VARCHAR(40)  NOT NULL COMMENT 'LOG-YYYYMMDDHHMMSS-xxx',
  `produk_id`     BIGINT UNSIGNED NOT NULL,
  `nama_produk`   VARCHAR(150) DEFAULT NULL,
  `jenis`         ENUM('masuk','keluar','koreksi') NOT NULL,
  `jumlah`        INT          NOT NULL DEFAULT 0,
  `keterangan`    VARCHAR(255) DEFAULT NULL,
  `tanggal`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stok_sebelum`  INT          NOT NULL DEFAULT 0,
  `stok_sesudah`  INT          NOT NULL DEFAULT 0,
  `referensi`     VARCHAR(40)  DEFAULT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stoklog_produk` (`produk_id`),
  KEY `idx_stoklog_tanggal` (`tanggal`),
  CONSTRAINT `fk_stoklog_produk` FOREIGN KEY (`produk_id`)
    REFERENCES `produk` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 17. STOK HARIAN (snapshot)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `stok_harian`;
CREATE TABLE `stok_harian` (
  `id`              VARCHAR(40)  NOT NULL COMMENT 'SH-YYYY-MM-DD-<produk>',
  `tanggal`         DATE         NOT NULL,
  `produk_id`       BIGINT UNSIGNED NOT NULL,
  `stok_awal_hari`  INT          NOT NULL DEFAULT 0,
  `masuk_hari`      INT          NOT NULL DEFAULT 0,
  `keluar_hari`     INT          NOT NULL DEFAULT 0,
  `sisa`            INT          NOT NULL DEFAULT 0,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stokharian_tanggal_produk` (`tanggal`,`produk_id`),
  KEY `idx_stokharian_produk` (`produk_id`),
  CONSTRAINT `fk_stokharian_produk` FOREIGN KEY (`produk_id`)
    REFERENCES `produk` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 18. STOK ALERT (notifikasi stok habis)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `stok_alert`;
CREATE TABLE `stok_alert` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pelanggan_id`  VARCHAR(20)  DEFAULT NULL,
  `produk_id`     BIGINT UNSIGNED NOT NULL,
  `telepon`       VARCHAR(20)  NOT NULL,
  `nama`          VARCHAR(150) DEFAULT NULL,
  `status`        ENUM('menunggu','dikirim','selesai') NOT NULL DEFAULT 'menunggu',
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_alert_produk` (`produk_id`),
  KEY `idx_alert_status` (`status`),
  CONSTRAINT `fk_alert_produk` FOREIGN KEY (`produk_id`)
    REFERENCES `produk` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_alert_pelanggan` FOREIGN KEY (`pelanggan_id`)
    REFERENCES `pelanggan` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 19. BON (piutang / tempo)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `bon`;
CREATE TABLE `bon` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pelanggan_id`  VARCHAR(20)  NOT NULL,
  `nota_id`       VARCHAR(32)  DEFAULT NULL,
  `total`         DECIMAL(12,2) NOT NULL DEFAULT 0,
  `sisa`          DECIMAL(12,2) NOT NULL DEFAULT 0,
  `status`        ENUM('belum_lunas','sebagian','lunas') NOT NULL DEFAULT 'belum_lunas',
  `jatuh_tempo`   DATE         DEFAULT NULL,
  `keterangan`    VARCHAR(255) DEFAULT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bon_pelanggan` (`pelanggan_id`),
  KEY `idx_bon_status` (`status`),
  CONSTRAINT `fk_bon_pelanggan` FOREIGN KEY (`pelanggan_id`)
    REFERENCES `pelanggan` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_bon_pesanan` FOREIGN KEY (`nota_id`)
    REFERENCES `pesanan` (`nota_id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 20. BON BAYAR (cicilan)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `bon_bayar`;
CREATE TABLE `bon_bayar` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bon_id`      BIGINT UNSIGNED NOT NULL,
  `jumlah`      DECIMAL(12,2) NOT NULL DEFAULT 0,
  `tanggal`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `keterangan`  VARCHAR(255) DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bonbayar_bon` (`bon_id`),
  CONSTRAINT `fk_bonbayar_bon` FOREIGN KEY (`bon_id`)
    REFERENCES `bon` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 21. RUTINITAS (pesanan langganan)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `rutinitas`;
CREATE TABLE `rutinitas` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pelanggan_id`  VARCHAR(20)  NOT NULL,
  `nama`          VARCHAR(150) NOT NULL,
  `items_json`    JSON         NOT NULL COMMENT '[{produk_id, jumlah}]',
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rutinitas_pelanggan` (`pelanggan_id`),
  CONSTRAINT `fk_rutinitas_pelanggan` FOREIGN KEY (`pelanggan_id`)
    REFERENCES `pelanggan` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 22. BACKUP LOG
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `backup_log`;
CREATE TABLE `backup_log` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `waktu`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nama`         VARCHAR(150) NOT NULL,
  `jumlah_json`  JSON         DEFAULT NULL COMMENT 'ringkasan jumlah baris per tabel',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
