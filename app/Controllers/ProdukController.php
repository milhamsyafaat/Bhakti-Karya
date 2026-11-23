<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Kategori;
use App\Models\Produk;

class ProdukController extends Controller
{
    public function index(Request $request): void
    {
        $opts = [
            'q'           => trim((string) $request->input('q', '')),
            'kategori_id' => (int) $request->input('kategori_id', 0),
            'sort'        => (string) $request->input('sort', 'terbaru'),
            'limit'       => 24,
            'offset'      => max(0, (int) $request->input('page', 1) - 1) * 24,
        ];

        $total = Produk::countFilter($opts);

        $this->view('produk/index', [
            'title'    => 'Katalog Produk',
            'produk'   => Produk::filter($opts),
            'kategori' => Kategori::ordered(),
            'opts'     => $opts,
            'total'    => $total,
        ]);
    }

    public function show(Request $request, string $id): void
    {
        $produk = Produk::find((int) $id);
        if (!$produk) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Produk Tidak Ditemukan']);
            return;
        }

        $this->view('produk/show', [
            'title'   => $produk['nama_produk'],
            'produk'  => $produk,
            'related' => Produk::related((int) $produk['kategori_id'], (int) $produk['id'], 4),
        ]);
    }
}
