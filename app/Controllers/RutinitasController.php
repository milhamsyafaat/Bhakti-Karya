<?php

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class RutinitasController extends Controller
{
    public function index(): void
    {
        $a = pelanggan_auth();
        if (!$a->check()) {
            flash('error', 'Silakan masuk terlebih dahulu.');
            $this->redirect('/masuk');
        }

        $this->view('rutinitas/index', [
            'title'    => 'Rutinitas Belanja',
            'rutinitas' => $this->listWithItems($a->id()),
        ]);
    }

    public function simpan(Request $request): void
    {
        $a = pelanggan_auth();
        if (!$a->check()) {
            flash('error', 'Silakan masuk terlebih dahulu.');
            $this->redirect('/masuk');
        }

        $nama = trim((string) $request->input('nama', ''));
        $lines = Cart::detailed();
        if ($nama === '' || empty($lines)) {
            flash('error', 'Nama rutinitas wajib diisi dan keranjang tidak boleh kosong.');
            $this->redirect('/keranjang');
        }

        $items = [];
        foreach ($lines as $l) {
            $items[] = ['produk_id' => (int) $l['produk']['id'], 'jumlah' => (int) $l['qty']];
        }

        Database::query(
            'INSERT INTO `rutinitas` (`pelanggan_id`,`nama`,`items_json`) VALUES (?,?,?)',
            [$a->id(), $nama, json_encode($items, JSON_UNESCAPED_UNICODE)]
        );
        flash('success', 'Rutinitas "' . $nama . '" disimpan.');
        $this->redirect('/rutinitas');
    }

    public function pesan(Request $request, string $id): void
    {
        $a = pelanggan_auth();
        if (!$a->check()) {
            $this->redirect('/masuk');
        }

        $row = Database::first('SELECT * FROM `rutinitas` WHERE `id` = ? AND `pelanggan_id` = ?', [$id, $a->id()]);
        if (!$row) {
            flash('error', 'Rutinitas tidak ditemukan.');
            $this->redirect('/rutinitas');
        }

        $items = json_decode($row['items_json'], true) ?: [];
        Cart::clear();
        $ditambahkan = 0;
        foreach ($items as $it) {
            $p = \App\Models\Produk::find((int) $it['produk_id']);
            if ($p && (int) $p['aktif'] === 1 && (int) $p['stok'] > 0) {
                Cart::add((int) $p['id'], max(1, (int) $it['jumlah']));
                $ditambahkan++;
            }
        }

        if ($ditambahkan === 0) {
            flash('error', 'Semua produk pada rutinitas ini sedang tidak tersedia.');
            $this->redirect('/rutinitas');
        }
        flash('success', 'Items rutinitas ditambahkan ke keranjang.');
        $this->redirect('/keranjang');
    }

    public function hapus(Request $request, string $id): void
    {
        $a = pelanggan_auth();
        if (!$a->check()) {
            $this->redirect('/masuk');
        }
        Database::query('DELETE FROM `rutinitas` WHERE `id` = ? AND `pelanggan_id` = ?', [$id, $a->id()]);
        flash('success', 'Rutinitas dihapus.');
        $this->redirect('/rutinitas');
    }

    private function listWithItems(string $pelangganId): array
    {
        $rows = Database::select('SELECT * FROM `rutinitas` WHERE `pelanggan_id` = ? ORDER BY `id` DESC', [$pelangganId]);
        foreach ($rows as &$r) {
            $items = json_decode($r['items_json'], true) ?: [];
            $detail = [];
            foreach ($items as $it) {
                $p = \App\Models\Produk::find((int) $it['produk_id']);
                if ($p) {
                    $detail[] = [
                        'nama' => $p['nama_produk'],
                        'jumlah' => (int) $it['jumlah'],
                        'harga' => (float) $p['harga'],
                        'stok' => (int) $p['stok'],
                    ];
                }
            }
            $r['detail'] = $detail;
            $r['total'] = array_sum(array_map(fn ($d) => $d['harga'] * $d['jumlah'], $detail));
        }
        return $rows;
    }
}
