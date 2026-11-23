<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Pesanan;

class PesananController extends Controller
{
    public function show(Request $request, string $nota): void
    {
        $pesanan = Pesanan::findByNota($nota);
        if (!$pesanan) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Pesanan Tidak Ditemukan']);
            return;
        }

        $this->view('pesanan/show', [
            'title'   => 'Detail Pesanan ' . $nota,
            'pesanan' => $pesanan,
            'detail'  => Pesanan::detail($nota),
        ]);
    }

    public function track(Request $request): void
    {
        $nota = trim((string) $request->input('nota', ''));
        if ($nota !== '') {
            $this->redirect('/pesanan/' . $nota);
        }
        $this->view('pesanan/track', ['title' => 'Lacak Pesanan']);
    }
}
