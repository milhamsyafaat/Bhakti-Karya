<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class StokAlertController extends Controller
{
    /**
     * Pelanggan meminta notifikasi ketika stok produk tersedia kembali.
     */
    public function simpan(Request $request): void
    {
        $produkId = (int) $request->input('produk_id', 0);
        $produk = \App\Models\Produk::find($produkId);
        if (!$produk) {
            flash('error', 'Produk tidak ditemukan.');
            $this->redirect('/produk');
        }

        $telepon = trim((string) $request->input('telepon', ''));
        $nama = trim((string) $request->input('nama', ''));
        $pelangganId = null;

        // Prioritas data pelanggan dari sesi
        if (pelanggan_auth()->check()) {
            $u = pelanggan_auth()->user();
            $telepon = $telepon ?: (string) $u['telepon'];
            $nama = $nama ?: (string) $u['nama'];
            $pelangganId = $u['id'];
        }

        if ($telepon === '') {
            flash('error', 'Nomor HP wajib diisi untuk notifikasi.');
            $this->redirect('/produk/' . $produkId);
        }

        // Hindari duplikat aktif
        $ada = Database::first(
            'SELECT `id` FROM `stok_alert` WHERE `produk_id` = ? AND `telepon` = ? AND `status` = "menunggu"',
            [$produkId, $telepon]
        );
        if ($ada) {
            flash('success', 'Anda sudah terdaftar untuk notifikasi produk ini.');
            $this->redirect('/produk/' . $produkId);
        }

        Database::query(
            'INSERT INTO `stok_alert` (`pelanggan_id`,`produk_id`,`telepon`,`nama`,`status`) VALUES (?,?,?,?,?)',
            [$pelangganId, $produkId, $telepon, $nama ?: null, 'menunggu']
        );
        flash('success', 'Kami akan mengabari Anda saat stok ' . $produk['nama_produk'] . ' tersedia.');
        $this->redirect('/produk/' . $produkId);
    }
}
