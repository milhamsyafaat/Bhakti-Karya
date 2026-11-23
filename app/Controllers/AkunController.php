<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\PoinService;
class AkunController extends Controller
{
    public function index(): void
    {
        $a = pelanggan_auth();
        if (!$a->check()) {
            flash('error', 'Silakan masuk dengan nomor HP.');
            $this->redirect('/masuk');
        }
        $a->refresh();
        $u = $a->user();

        $pesanan = Database::select(
            'SELECT `nota_id`,`total_bayar`,`status`,`tanggal` FROM `pesanan`
             WHERE `pelanggan_id` = ? ORDER BY `id` DESC LIMIT 20',
            [$u['id']]
        );

        $this->view('akun/index', [
            'title'   => 'Akun Saya',
            'user'    => $u,
            'pesanan' => $pesanan,
        ]);
    }

    public function poin(): void
    {
        $a = pelanggan_auth();
        if (!$a->check()) {
            $this->redirect('/masuk');
        }
        $a->refresh();
        $u = $a->user();

        $log = Database::select(
            'SELECT * FROM `poin_log` WHERE `pelanggan_id` = ? ORDER BY `tanggal` DESC LIMIT 50',
            [$u['id']]
        );

        $this->view('akun/poin', [
            'title' => 'Riwayat Poin',
            'user'  => $u,
            'log'   => $log,
        ]);
    }

    public function voucher(): void
    {
        $a = pelanggan_auth();
        if (!$a->check()) {
            $this->redirect('/masuk');
        }
        $a->refresh();
        $u = $a->user();

        $katalog = Database::select('SELECT * FROM `voucher_katalog` WHERE `aktif` = 1 ORDER BY `poin_dibutuhkan`');
        $punya = Database::select(
            'SELECT vp.*, vk.nama AS nama_voucher, vk.nilai, vk.min_belanja
             FROM `voucher_pelanggan` vp
             JOIN `voucher_katalog` vk ON vk.id = vp.katalog_id
             WHERE vp.`pelanggan_id` = ? ORDER BY vp.`tgl_tukar` DESC',
            [$u['id']]
        );

        $this->view('akun/voucher', [
            'title'   => 'Tukar Poin',
            'user'    => $u,
            'katalog' => $katalog,
            'punya'   => $punya,
        ]);
    }

    public function tukarVoucher(Request $request): void
    {
        $a = pelanggan_auth();
        if (!$a->check()) {
            flash('error', 'Silakan masuk terlebih dahulu.');
            $this->redirect('/masuk');
        }

        $hasil = PoinService::tukarVoucher($a->id(), (string) $request->input('katalog_id', ''));
        flash($hasil['ok'] ? 'success' : 'error', $hasil['pesan']);
        if ($hasil['ok']) {
            flash('kode_voucher', $hasil['kode']);
        }
        $this->redirect('/akun/voucher');
    }

    public function showMasuk(): void
    {
        if (pelanggan_auth()->check()) {
            $this->redirect('/akun');
        }
        $this->view('auth/masuk', ['title' => 'Masuk Pelanggan']);
    }

    public function masuk(Request $request): void
    {
        $telepon = trim((string) $request->input('telepon', ''));
        $nama = trim((string) $request->input('nama', ''));

        if ($telepon === '') {
            flash('error', 'Nomor HP wajib diisi.');
            $this->redirect('/masuk');
        }

        if (pelanggan_auth()->attempt($telepon, $nama ?: null)) {
            flash('success', 'Selamat datang, ' . Session::get('pelanggan.nama') . '!');
            $this->redirect('/akun');
        }

        flash('error', 'Nomor HP belum terdaftar. Isi nama untuk mendaftar otomatis.');
        Session::set('_old', ['telepon' => $telepon]);
        $this->redirect('/masuk');
    }

    public function keluar(): void
    {
        pelanggan_auth()->logout();
        $this->redirect('/');
    }
}
