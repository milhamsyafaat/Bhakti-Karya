<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class BonController extends Controller
{
    public function index(): void
    {
        $bons = Database::select(
            'SELECT b.*, p.nama_lengkap, p.telepon
             FROM `bon` b
             LEFT JOIN `pelanggan` p ON p.id = b.pelanggan_id
             ORDER BY b.`status` <> "lunas" DESC, b.`jatuh_tempo` ASC, b.`id` DESC'
        );

        $pelanggan = Database::select('SELECT `id`,`nama_lengkap`,`telepon`,`tipe` FROM `pelanggan` ORDER BY `nama_lengkap`');
        $pesananBelumBon = Database::select(
            "SELECT p.`nota_id`, p.`pelanggan_id`, p.`nama_pelanggan`, p.`total_bayar`
             FROM `pesanan` p
             LEFT JOIN `bon` b ON b.`nota_id` = p.`nota_id`
             WHERE b.`id` IS NULL AND p.`pelanggan_id` IS NOT NULL AND p.`status` <> 'Dibatalkan'
             ORDER BY p.`id` DESC LIMIT 50"
        );

        $ringkasan = Database::first(
            'SELECT COALESCE(SUM(`total`),0) AS total, COALESCE(SUM(`sisa`),0) AS sisa,
                    SUM(`status` <> "lunas") AS belum_lunas
             FROM `bon`'
        ) ?? ['total' => 0, 'sisa' => 0, 'belum_lunas' => 0];

        $this->view('admin/bon/index', [
            'title'    => 'Bon / Piutang',
            'bons'     => $bons,
            'pelanggan' => $pelanggan,
            'pesananBelumBon' => $pesananBelumBon,
            'ringkasan' => $ringkasan,
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        $pelangganId = trim((string) $request->input('pelanggan_id', ''));
        $total = (float) $request->input('total', 0);
        $notaId = trim((string) $request->input('nota_id', '')) ?: null;
        $jatuhTempo = trim((string) $request->input('jatuh_tempo', '')) ?: null;
        $keterangan = trim((string) $request->input('keterangan', ''));

        if ($pelangganId === '' || $total <= 0) {
            flash('error', 'Pelanggan dan total bon (lebih dari 0) wajib diisi.');
            $this->redirect('/admin/bon');
        }

        // Cegah bon ganda untuk nota yang sama
        if ($notaId !== null) {
            $ada = Database::first('SELECT `id` FROM `bon` WHERE `nota_id` = ?', [$notaId]);
            if ($ada) {
                flash('error', 'Pesanan ini sudah memiliki bon.');
                $this->redirect('/admin/bon');
            }
        }

        Database::query(
            'INSERT INTO `bon` (`pelanggan_id`,`nota_id`,`total`,`sisa`,`status`,`jatuh_tempo`,`keterangan`)
             VALUES (?,?,?,?,?,?,?)',
            [$pelangganId, $notaId, $total, $total, 'belum_lunas', $jatuhTempo, $keterangan]
        );
        flash('success', 'Bon baru dibuat.');
        $this->redirect('/admin/bon');
    }

    public function bayar(Request $request, string $id): void
    {
        $bon = Database::first('SELECT * FROM `bon` WHERE `id` = ?', [$id]);
        if (!$bon) {
            flash('error', 'Bon tidak ditemukan.');
            $this->redirect('/admin/bon');
        }

        $jumlah = (float) $request->input('jumlah', 0);
        if ($jumlah <= 0) {
            flash('error', 'Jumlah bayar harus lebih dari 0.');
            $this->redirect('/admin/bon');
        }
        $jumlah = min($jumlah, (float) $bon['sisa']);

        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            Database::query(
                'INSERT INTO `bon_bayar` (`bon_id`,`jumlah`,`tanggal`,`keterangan`) VALUES (?,?,NOW(),?)',
                [$id, $jumlah, (string) $request->input('keterangan', '')]
            );
            $sisa = (float) $bon['sisa'] - $jumlah;
            $status = $sisa <= 0 ? 'lunas' : 'sebagian';
            Database::query('UPDATE `bon` SET `sisa` = ?, `status` = ? WHERE `id` = ?', [$sisa, $status, $id]);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
            $this->redirect('/admin/bon');
        }

        flash('success', 'Pembayaran bon dicatat.');
        $this->redirect('/admin/bon');
    }

    public function show(Request $request, string $id): void
    {
        $bon = Database::first(
            'SELECT b.*, p.nama_lengkap, p.telepon FROM `bon` b LEFT JOIN `pelanggan` p ON p.id=b.pelanggan_id WHERE b.id = ?',
            [$id]
        );
        if (!$bon) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Bon Tidak Ditemukan']);
            return;
        }
        $bayar = Database::select('SELECT * FROM `bon_bayar` WHERE `bon_id` = ? ORDER BY `tanggal`', [$id]);

        $this->view('admin/bon/show', [
            'title' => 'Bon #' . $id,
            'bon'   => $bon,
            'bayar' => $bayar,
        ], 'layouts/admin');
    }
}
