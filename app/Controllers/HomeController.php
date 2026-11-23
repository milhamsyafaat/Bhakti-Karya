<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Kategori;
use App\Models\Pengaturan;
use App\Models\Produk;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'title'     => 'Beranda',
            'kategori'  => Kategori::ordered(),
            'favorit'   => Produk::favorit(8),
            'terlaris'  => Produk::terlaris(8),
            'tokoBuka'  => (string) Pengaturan::get('toko_buka', '1') === '1',
            'jamBuka'   => Pengaturan::get('jam_buka', '07:00:00'),
            'jamTutup'  => Pengaturan::get('jam_tutup', '21:00:00'),
        ]);
    }
}
