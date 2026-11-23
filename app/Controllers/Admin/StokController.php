<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class StokController extends Controller
{
    public function index(Request $request): void
    {
        $lowStock = (int) \App\Models\Pengaturan::get('low_stock', 10);

        $this->view('admin/stok/index', [
            'title'   => 'Kelola Stok',
            'produk'  => Database::select('SELECT `id`,`nama_produk`,`stok`,`satuan` FROM `produk` WHERE `aktif` = 1 ORDER BY `nama_produk` ASC LIMIT 100'),
            'logs'    => Database::select('SELECT * FROM `stok_log` ORDER BY `tanggal` DESC LIMIT 20'),
            'lowStock' => $lowStock,
            'alert'   => Database::select(
                'SELECT sa.*, p.nama_produk, p.stok
                 FROM `stok_alert` sa JOIN `produk` p ON p.id = sa.produk_id
                 WHERE sa.status = "menunggu" ORDER BY sa.id DESC LIMIT 50'
            ),
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        $produkId = (int) $request->input('produk_id', 0);
        $jenis = $request->input('jenis', 'masuk') === 'keluar' ? 'keluar' : 'masuk';
        $jumlah = max(1, (int) $request->input('jumlah', 1));

        $produk = \App\Models\Produk::find($produkId);
        if (!$produk) {
            flash('error', 'Produk tidak ditemukan.');
            $this->redirect('/admin/stok');
        }

        $stokSebelum = (int) $produk['stok'];
        $stokSesudah = $jenis === 'masuk' ? $stokSebelum + $jumlah : max(0, $stokSebelum - $jumlah);

        Database::query('UPDATE `produk` SET `stok` = ? WHERE `id` = ?', [$stokSesudah, $produkId]);
        Database::query(
            "INSERT INTO `stok_log` (`id`,`produk_id`,`nama_produk`,`jenis`,`jumlah`,`keterangan`,`stok_sebelum`,`stok_sesudah`,`referensi`)
             VALUES (?,?,?,?,?,?,?,?,?)",
            ['LOG-' . date('YmdHis') . '-' . random_int(100, 999), $produkId, $produk['nama_produk'], $jenis, $jumlah,
             (string) $request->input('keterangan', 'Penyesuaian manual'), $stokSebelum, $stokSesudah, 'MANUAL']
        );

        flash('success', 'Stok ' . $produk['nama_produk'] . ' diperbarui.');
        $this->redirect('/admin/stok');
    }
}
