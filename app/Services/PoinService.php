<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Pengaturan;

/**
 * Logika poin loyalitas & penukaran voucher.
 */
class PoinService
{
    /**
     * Hitung poin dari nominal belanja (1 poin per poin_per_rupiah).
     */
    public static function hitungPoin(float $totalBayar): int
    {
        $rate = (float) Pengaturan::get('poin_per_rupiah', 10000);
        if ($rate <= 0) {
            return 0;
        }
        return (int) floor($totalBayar / $rate);
    }

    /**
     * Tambah poin ke pelanggan + catat ledger.
     */
    public static function tambah(string $pelangganId, int $jumlah, string $keterangan, ?string $referensi = null): void
    {
        if ($jumlah <= 0) {
            return;
        }
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $row = Database::first('SELECT `poin` FROM `pelanggan` WHERE `id` = ? FOR UPDATE', [$pelangganId]);
            if (!$row) {
                $pdo->rollBack();
                return;
            }
            $sebelum = (int) $row['poin'];
            $sesudah = $sebelum + $jumlah;

            Database::query('UPDATE `pelanggan` SET `poin` = ? WHERE `id` = ?', [$sesudah, $pelangganId]);
            Database::query(
                'INSERT INTO `poin_log` (`pelanggan_id`,`jenis`,`jumlah`,`saldo_sebelum`,`saldo_sesudah`,`keterangan`,`referensi`,`tanggal`)
                 VALUES (?,?,?,?,?,?,?,NOW())',
                [$pelangganId, 'masuk', $jumlah, $sebelum, $sesudah, $keterangan, $referensi]
            );
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Kurangi poin (penukaran voucher).
     */
    public static function kurangi(string $pelangganId, int $jumlah, string $keterangan, ?string $referensi = null): bool
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $row = Database::first('SELECT `poin` FROM `pelanggan` WHERE `id` = ? FOR UPDATE', [$pelangganId]);
            if (!$row || (int) $row['poin'] < $jumlah) {
                $pdo->rollBack();
                return false;
            }
            $sebelum = (int) $row['poin'];
            $sesudah = $sebelum - $jumlah;
            Database::query('UPDATE `pelanggan` SET `poin` = ? WHERE `id` = ?', [$sesudah, $pelangganId]);
            Database::query(
                'INSERT INTO `poin_log` (`pelanggan_id`,`jenis`,`jumlah`,`saldo_sebelum`,`saldo_sesudah`,`keterangan`,`referensi`,`tanggal`)
                 VALUES (?,?,?,?,?,?,?,NOW())',
                [$pelangganId, 'keluar', $jumlah, $sebelum, $sesudah, $keterangan, $referensi]
            );
            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Tukar poin dengan voucher dari katalog.
     * @return array{ok:bool, kode?:string, pesan:string}
     */
    public static function tukarVoucher(string $pelangganId, string $katalogId): array
    {
        $katalog = Database::first('SELECT * FROM `voucher_katalog` WHERE `id` = ? AND `aktif` = 1', [$katalogId]);
        if (!$katalog) {
            return ['ok' => false, 'pesan' => 'Voucher tidak tersedia.'];
        }

        $poinDibutuhkan = (int) $katalog['poin_dibutuhkan'];

        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $row = Database::first('SELECT `poin` FROM `pelanggan` WHERE `id` = ? FOR UPDATE', [$pelangganId]);
            if (!$row) {
                $pdo->rollBack();
                return ['ok' => false, 'pesan' => 'Pelanggan tidak ditemukan.'];
            }
            if ((int) $row['poin'] < $poinDibutuhkan) {
                $pdo->rollBack();
                return ['ok' => false, 'pesan' => 'Poin tidak cukup.'];
            }

            $sebelum = (int) $row['poin'];
            $sesudah = $sebelum - $poinDibutuhkan;
            Database::query('UPDATE `pelanggan` SET `poin` = ? WHERE `id` = ?', [$sesudah, $pelangganId]);
            Database::query(
                'INSERT INTO `poin_log` (`pelanggan_id`,`jenis`,`jumlah`,`saldo_sebelum`,`saldo_sesudah`,`keterangan`,`referensi`,`tanggal`)
                 VALUES (?,?,?,?,?,?,?,NOW())',
                [$pelangganId, 'keluar', $poinDibutuhkan, $sebelum, $sesudah, 'Tukar: ' . $katalog['nama'], $katalogId]
            );

            $kode = 'VC' . strtoupper(bin2hex(random_bytes(4)));
            $kedaluwarsa = date('Y-m-d H:i:s', strtotime('+' . (int) $katalog['masa_berlaku_hari'] . ' days'));
            Database::query(
                'INSERT INTO `voucher_pelanggan`
                 (`id`,`kode`,`pelanggan_id`,`katalog_id`,`status`,`tgl_tukar`,`tgl_kedaluwarsa`)
                 VALUES (?,?,?,?,?,NOW(),?)',
                ['VP-' . $kode, $kode, $pelangganId, $katalogId, 'aktif', $kedaluwarsa]
            );

            $pdo->commit();
            return ['ok' => true, 'kode' => $kode, 'pesan' => 'Voucher berhasil ditukar.'];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'pesan' => 'Gagal menukar voucher: ' . $e->getMessage()];
        }
    }
}
