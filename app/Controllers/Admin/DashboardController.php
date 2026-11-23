<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Pesanan;
use App\Models\Produk;

class DashboardController extends Controller
{
    public function index(): void
    {
        $lowStock = (int) \App\Models\Pengaturan::get('low_stock', 10);

        $this->view('admin/dashboard', [
            'title'      => 'Dashboard Admin',
            'statPesanan' => Pesanan::statistikHariIni(),
            'totalProduk' => Produk::count('aktif = 1'),
            'totalPelanggan' => (int) (Database::first('SELECT COUNT(*) AS c FROM `pelanggan`')['c'] ?? 0),
            'stokMenipis' => Database::select(
                'SELECT `id`,`nama_produk`,`stok` FROM `produk` WHERE `aktif` = 1 AND `stok` <= ? ORDER BY `stok` ASC LIMIT 10',
                [$lowStock]
            ),
            'pesananTerbaru' => Pesanan::terbaru(8),
            'piutang' => Database::first(
                'SELECT COALESCE(SUM(`sisa`),0) AS sisa, SUM(`status` <> "lunas") AS jumlah FROM `bon`'
            ) ?? ['sisa' => 0, 'jumlah' => 0],
        ], 'layouts/admin');
    }
}
