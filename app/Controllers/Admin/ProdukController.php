<?php

namespace App\Controllers\Admin;

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
            'limit'       => 20,
            'offset'      => max(0, (int) $request->input('page', 1) - 1) * 20,
        ];

        $this->view('admin/produk/index', [
            'title'    => 'Kelola Produk',
            'produk'   => Produk::filter($opts),
            'kategori' => Kategori::ordered(),
            'opts'     => $opts,
            'total'    => Produk::countFilter($opts),
        ], 'layouts/admin');
    }

    public function create(): void
    {
        $this->view('admin/produk/form', [
            'title'    => 'Tambah Produk',
            'produk'   => null,
            'kategori' => Kategori::ordered(),
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        $data = $this->payload($request);
        if ($data === null) {
            $this->redirect('/admin/produk/create');
        }
        Produk::insert($data);
        flash('success', 'Produk berhasil ditambahkan.');
        $this->redirect('/admin/produk');
    }

    public function edit(Request $request, string $id): void
    {
        $produk = Produk::find((int) $id);
        if (!$produk) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Produk Tidak Ditemukan']);
            return;
        }
        $this->view('admin/produk/form', [
            'title'    => 'Edit Produk',
            'produk'   => $produk,
            'kategori' => Kategori::ordered(),
        ], 'layouts/admin');
    }

    public function update(Request $request, string $id): void
    {
        $data = $this->payload($request);
        if ($data === null) {
            $this->redirect('/admin/produk/' . $id . '/edit');
        }
        Produk::update((int) $id, $data);
        flash('success', 'Produk berhasil diperbarui.');
        $this->redirect('/admin/produk');
    }

    public function destroy(Request $request, string $id): void
    {
        Produk::update((int) $id, ['aktif' => 0]);
        flash('success', 'Produk dinonaktifkan.');
        $this->redirect('/admin/produk');
    }

    private function payload(Request $request): ?array
    {
        $nama = trim((string) $request->input('nama_produk', ''));
        if ($nama === '') {
            flash('error', 'Nama produk wajib diisi.');
            return null;
        }

        // Gambar: prioritaskan file upload, fallback ke URL teks
        $gambar = trim((string) $request->input('gambar', ''));
        $file = $request->file('gambar_file');
        if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $stored = \App\Core\Storage::putUpload($file, 'produk');
            if ($stored !== null) {
                $gambar = $stored;
            } else {
                flash('error', 'Gagal upload gambar (format harus jpg/png/webp/gif, maks 2 MB).');
            }
        }

        return [
            'nama_produk'    => $nama,
            'kategori_id'    => (int) $request->input('kategori_id', 0) ?: null,
            'harga'          => (float) $request->input('harga', 0),
            'harga_lama'     => (float) $request->input('harga_lama', 0) ?: null,
            'stok'           => (int) $request->input('stok', 0),
            'badge_status'   => (string) $request->input('badge_status', 'Standar'),
            'deskripsi'      => (string) $request->input('deskripsi', ''),
            'gambar'         => $gambar,
            'is_favorit'     => (int) ((bool) $request->input('is_favorit')) ,
            'harga_reseller' => (float) $request->input('harga_reseller', 0),
            'satuan'         => (string) $request->input('satuan', 'pcs'),
            'min_order'      => (int) $request->input('min_order', 1),
            'aktif'          => 1,
        ];
    }
}
