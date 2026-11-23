<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Pesanan;

class PesananController extends Controller
{
    public function index(Request $request): void
    {
        $status = (string) $request->input('status', '');
        $sql = 'SELECT * FROM `pesanan`';
        $params = [];
        if ($status !== '') {
            $sql .= ' WHERE `status` = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY `id` DESC LIMIT 50';

        $this->view('admin/pesanan/index', [
            'title'   => 'Kelola Pesanan',
            'pesanan' => Database::select($sql, $params),
            'status'  => $status,
        ], 'layouts/admin');
    }

    public function show(Request $request, string $nota): void
    {
        $pesanan = Pesanan::findByNota($nota);
        if (!$pesanan) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Pesanan Tidak Ditemukan']);
            return;
        }

        $this->view('admin/pesanan/show', [
            'title'   => 'Pesanan ' . $nota,
            'pesanan' => $pesanan,
            'detail'  => Pesanan::detail($nota),
        ], 'layouts/admin');
    }

    public function updateStatus(Request $request, string $nota): void
    {
        $allowed = ['Menunggu Pembayaran', 'Diproses', 'Dikemas', 'Dikirim', 'Tiba', 'Diterima', 'Selesai', 'Dibatalkan'];
        $status = (string) $request->input('status', '');
        if (!in_array($status, $allowed, true)) {
            flash('error', 'Status tidak valid.');
            $this->redirect('/admin/pesanan/' . $nota);
        }

        $data = ['status' => $status];
        $map = ['Diterima' => 'diterima_at', 'Dikemas' => 'dikemas_at', 'Dikirim' => 'dikirim_at', 'Tiba' => 'tiba_at'];
        if (isset($map[$status])) {
            $data[$map[$status]] = date('Y-m-d H:i:s');
        }

        Pesanan::update(Pesanan::findByNota($nota)['id'], $data);
        flash('success', 'Status pesanan diperbarui menjadi ' . $status . '.');
        $this->redirect('/admin/pesanan/' . $nota);
    }
}
