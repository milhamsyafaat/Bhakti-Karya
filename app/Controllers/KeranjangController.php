<?php

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Core\Request;

class KeranjangController extends Controller
{
    public function index(): void
    {
        $this->view('keranjang/index', [
            'title' => 'Keranjang Belanja',
            'lines' => Cart::detailed(),
            'subtotal' => Cart::subtotal(),
        ]);
    }

    public function add(Request $request): void
    {
        $id = (int) $request->input('produk_id', 0);
        $qty = max(1, (int) $request->input('qty', 1));
        if ($id > 0) {
            Cart::add($id, $qty);
            flash('success', 'Produk ditambahkan ke keranjang.');
        }
        $this->redirect('/keranjang');
    }

    public function update(Request $request): void
    {
        $id = (int) $request->input('produk_id', 0);
        $qty = (int) $request->input('qty', 0);
        if ($id > 0) {
            Cart::update($id, $qty);
        }
        $this->redirect('/keranjang');
    }

    public function remove(Request $request): void
    {
        $id = (int) $request->input('produk_id', 0);
        if ($id > 0) {
            Cart::remove($id);
        }
        $this->redirect('/keranjang');
    }
}
