-- =====================================================================
--  SEED DATA — WARUNG GROSIR BHAKTI KARYA
--  File    : seed_bhaktikarya.sql  (DML)
--  Jalankan SETELAH schema_bhaktikarya.sql
-- =====================================================================

SET NAMES utf8mb4;
USE `bhaktikarya_grosir`;

-- ---------------------------------------------------------------------
-- USERS  (password di-hash bcrypt)
--   admin / adminpassword
--   user1 / userpassword
-- ---------------------------------------------------------------------
INSERT INTO `users` (`id`,`username`,`password`,`role`,`nama_lengkap`,`telepon`,`alamat`,`email`) VALUES
(1,'admin','$2y$10$ZUhCA7ZPAstr2iuccKNsAuhLeCBNkU69McIotYfj7CoPFr.6Eqhg.','admin','Owner Bhakti Karya','08515550912','Cilangkap, Tapos, Depok','admin@bhaktikarya.com'),
(2,'user1','$2y$10$x7pNVrTmMQNLT/DF9KsQ3.BsplI2YA/ySpEvIJdnJ7UnQTB2vfqnS','user','Budi Santoso','081234567890','Cilangkap, Tapos, Depok','budi@gmail.com')
ON DUPLICATE KEY UPDATE `username`=VALUES(`username`);

-- ---------------------------------------------------------------------
-- KATEGORI  (8 kategori, menggabungkan sheet `kategori` + data produk)
-- ---------------------------------------------------------------------
INSERT INTO `kategori` (`id`,`kode`,`nama_kategori`,`ikon`,`urutan`) VALUES
(1,'K01','Sembako','🌾',1),
(2,'K02','Minuman','🥤',2),
(3,'K03','Snack & Biskuit','🍪',3),
(4,'K04','Bumbu & Dapur','🧂',4),
(5,'K05','Sabun & Kebersihan','🧼',5),
(6,'K06','Perawatan Diri','🧴',6),
(7,'K07','Perlengkapan Rumah','🏠',7),
(8,'K08','Mie Instan','🍜',8)
ON DUPLICATE KEY UPDATE `nama_kategori`=VALUES(`nama_kategori`);

-- ---------------------------------------------------------------------
-- ZONA ONGKIR
-- ---------------------------------------------------------------------
INSERT INTO `zona_ongkir` (`id`,`kelurahan`,`kecamatan`,`jarak_km`,`tarif`,`aktif`) VALUES
('Z01','Cilangkap','Tapos',1.20,0.00,1),
('Z02','Tapos','Tapos',2.80,12000.00,1),
('Z03','Sukamaju Baru','Tapos',3.40,15000.00,1),
('Z04','Jatijajar','Tapos',4.20,18000.00,1),
('Z05','Cimpaeun','Tapos',5.40,22000.00,1),
('Z06','Kalisuren','Tapos',6.10,25000.00,1),
('Z07','Lainnya','Tapos',8.00,30000.00,1)
ON DUPLICATE KEY UPDATE `tarif`=VALUES(`tarif`);

-- ---------------------------------------------------------------------
-- PENGATURAN
-- ---------------------------------------------------------------------
INSERT INTO `pengaturan` (`setting_key`,`setting_value`) VALUES
('toko_lat','-6.4025'),
('toko_lng','106.864'),
('radius_gratis_km','2.0'),
('min_belanja_gratis_ongkir','100000'),
('faktor_jalan','1.3'),
('maks_jarak_antar_km','5.0'),
('tarif_dalam_radius','8000'),
('tarif_per_km','4000'),
('toko_buka','1'),
('jam_buka','07:00:00'),
('jam_tutup','21:00:00'),
('poin_per_rupiah','10000'),
('low_stock','10')
ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);

-- ---------------------------------------------------------------------
-- META
-- ---------------------------------------------------------------------
INSERT INTO `meta` (`meta_key`,`meta_value`) VALUES
('created_at','2026-10-31T03:38:38.305Z'),
('versi_stok','2'),
('stok_diperbarui','2026-11-02T03:34:16.383Z'),
('version','4')
ON DUPLICATE KEY UPDATE `meta_value`=VALUES(`meta_value`);

-- ---------------------------------------------------------------------
-- VOUCHER KATALOG (tukar poin)
-- ---------------------------------------------------------------------
INSERT INTO `voucher_katalog` (`id`,`nama`,`poin_dibutuhkan`,`tipe`,`nilai`,`min_belanja`,`masa_berlaku_hari`,`aktif`) VALUES
('V01','Voucher Belanja Rp10.000',100,'nominal',10000.00,100000.00,30,1),
('V02','Voucher Belanja Rp30.000',250,'nominal',30000.00,250000.00,30,1),
('V03','Voucher Belanja Rp75.000',500,'nominal',75000.00,500000.00,30,1)
ON DUPLICATE KEY UPDATE `nama`=VALUES(`nama`);

-- ---------------------------------------------------------------------
-- KODE PROMO
-- ---------------------------------------------------------------------
INSERT INTO `kode_promo` (`id`,`kode`,`tipe`,`nilai`,`min_belanja`,`mulai`,`selesai`,`kuota`,`terpakai`,`aktif`) VALUES
('KP01','HEMAT10','persen',10.00,50000.00,'2026-10-21 03:38:37','2026-11-30 03:38:37',100,0,1),
('KP02','ONGKIR8RB','nominal',8000.00,0.00,'2026-10-21 03:38:37','2026-11-30 03:38:37',0,0,1)
ON DUPLICATE KEY UPDATE `kode`=VALUES(`kode`);

-- ---------------------------------------------------------------------
-- PELANGGAN (contoh)
-- ---------------------------------------------------------------------
INSERT INTO `pelanggan` (`id`,`nama_lengkap`,`telepon`,`alamat`,`kecamatan`,`kelurahan`,`kota`,`tipe`,`poin`,`total_belanja`) VALUES
('C001','Coki','085124563524','Antah berantah','Tapos','Sukamaju Baru','Kota Depok','umum',0,83000.00)
ON DUPLICATE KEY UPDATE `nama_lengkap`=VALUES(`nama_lengkap`);

-- ---------------------------------------------------------------------
-- CATATAN PRODUK
--   100 produk tidak disertakan di seed ini. Impor dari file Excel via
--   script ETL, dengan pemetaan:
--     Produk.kategori_id (1..8) -> kategori.id
--     Produk.gambar             -> URL dari sheet
--   Contoh baris tunggal:
-- ---------------------------------------------------------------------
INSERT INTO `produk`
  (`id`,`nama_produk`,`kategori_id`,`harga`,`harga_lama`,`stok`,`badge_status`,`deskripsi`,`gambar`,`is_favorit`,`satuan`,`min_order`,`aktif`)
VALUES
  (1,'Beras Ramos 5kg',1,68000.00,72000.00,24,'Komoditas','Beras pulen kualitas super 5kg',
   'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full//catalog-image/MTA-48503252/no_brand_beras_ramos_bandung_asli_pulen_5kg_murah_full01_410af56b.jpeg',
   1,'pcs',1,1)
ON DUPLICATE KEY UPDATE `nama_produk`=VALUES(`nama_produk`);
