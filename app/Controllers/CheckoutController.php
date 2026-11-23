<?php

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Pelanggan;
use App\Models\ZonaOngkir;
use App\Services\OngkirService;

class CheckoutController extends Controller
{
    public function index(): void
    {
        $lines = Cart::detailed();
        if (empty($lines)) {
            $this->redirect('/keranjang');
        }
        $subtotal = Cart::subtotal();

        $this->view('checkout/index', [
            'title'    => 'Checkout',
            'lines'    => $lines,
            'subtotal' => $subtotal,
            'zona'     => ZonaOngkir::aktif(),
        ]);
    }

    public function cekOngkir(Request $request): void
    {
        $subtotal = Cart::subtotal();
        $metode = (string) $request->input('metode_kirim', 'antar');
        $hasil = OngkirService::hitung($subtotal, (string) $request->input('zona_id', ''), $metode);

        (new Response())->json([
            'ongkir'    => $hasil['ongkir'],
            'gratis'    => $hasil['gratis'],
            'kurang'    => $hasil['kurang'],
            'jarak_km'  => $hasil['jarak_km'],
            'ongkir_rp' => rupiah($hasil['ongkir']),
        ]);
    }

    public function store(Request $request): void
    {
        $lines = Cart::detailed();
        if (empty($lines)) {
            $this->redirect('/keranjang');
        }

        $nama = trim((string) $request->input('nama_pelanggan', ''));
        $hp = trim((string) $request->input('no_hp', ''));
        $alamat = trim((string) $request->input('alamat', ''));

        if ($nama === '' || $hp === '') {
            flash('error', 'Nama dan nomor HP wajib diisi.');
            $this->redirect('/checkout');
        }

        $metodeKirim = $request->input('metode_kirim', 'antar') === 'ambil' ? 'ambil' : 'antar';
        $metodeBayar = in_array($request->input('metode_bayar'), ['qris', 'cod', 'transfer'], true)
            ? $request->input('metode_bayar') : 'qris';
        $zonaId = $metodeKirim === 'ambil' ? null : (string) $request->input('zona_id', 'Z07');

        $subtotal = Cart::subtotal();
        $ongkir = OngkirService::hitung($subtotal, $zonaId, $metodeKirim);
        $totalBayar = $subtotal + $ongkir['ongkir'];
        $notaId = 'WBK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));

        // Cari/buat pelanggan berdasarkan no HP
        $pelanggan = Pelanggan::firstWhere('telepon', $hp);
        if (!$pelanggan) {
            $maxId = Database::first("SELECT MAX(CAST(SUBSTRING(`id`,2) AS UNSIGNED)) AS m FROM `pelanggan`");
            $nextNum = (int) ($maxId['m'] ?? 0) + 1;
            $pelangganId = 'C' . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
            Pelanggan::insert([
                'id' => $pelangganId, 'nama_lengkap' => $nama, 'telepon' => $hp,
                'alamat' => $alamat, 'kota' => 'Kota Depok', 'tipe' => 'umum',
            ]);
        } else {
            $pelangganId = $pelanggan['id'];
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            Database::query(
                "INSERT INTO `pesanan`
                  (`nota_id`,`nama_pelanggan`,`no_hp`,`alamat`,`subtotal`,`jarak_km`,`ongkir`,
                   `metode_kirim`,`metode_bayar`,`metode_pembayaran`,`sumber`,`ongkir_gratis`,`zona_id`,
                   `kurang_belanja`,`diskon_promo`,`diskon_reseller`,`subtotal_setelah_promo`,
                   `diskon_kode`,`voucher_diskon`,`total_bayar`,`status`,`pembayaran_status`,
                   `pelanggan_id`,`is_new_customer`,`tanggal`,`jadwal`,`catatan`)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?,?)",
                [
                    $notaId, $nama, $hp, $alamat, $subtotal, $ongkir['jarak_km'], $ongkir['ongkir'],
                    $metodeKirim, $metodeBayar, strtoupper($metodeBayar), 'online',
                    $ongkir['gratis'] ? 1 : 0, $zonaId,
                    $ongkir['kurang'], 0, 0, $subtotal,
                    0, 0, $totalBayar, 'Menunggu Pembayaran', 'Menunggu Pembayaran',
                    $pelangganId, $pelanggan ? 0 : 1,
                    (string) $request->input('jadwal', ''), (string) $request->input('catatan', ''),
                ]
            );

            $idx = 0;
            foreach ($lines as $line) {
                $p = $line['produk'];
                $qty = (int) $line['qty'];
                $detailId = 'DTL-' . $notaId . '-' . $idx++;

                Database::query(
                    "INSERT INTO `pesanan_detail`
                      (`id`,`nota_id`,`pelanggan_id`,`produk_id`,`nama_produk`,`jumlah`,
                       `harga_normal`,`harga_satuan`,`diskon_promo`,`subtotal`)
                     VALUES (?,?,?,?,?,?,?,?,?,?)",
                    [$detailId, $notaId, $pelangganId, $p['id'], $p['nama_produk'], $qty,
                     $p['harga'], $p['harga'], 0, $line['subtotal']]
                );

                // kurangi stok + catat log
                $stokSebelum = (int) $p['stok'];
                $stokSesudah = $stokSebelum - $qty;
                Database::query('UPDATE `produk` SET `stok` = ?, `penjualan` = `penjualan` + ? WHERE `id` = ?',
                    [$stokSesudah, $qty, $p['id']]);

                $logId = 'LOG-' . date('YmdHis') . '-' . random_int(100, 999);
                Database::query(
                    "INSERT INTO `stok_log`
                      (`id`,`produk_id`,`nama_produk`,`jenis`,`jumlah`,`keterangan`,`stok_sebelum`,`stok_sesudah`,`referensi`)
                     VALUES (?,?,?,?,?,?,?,?,?)",
                    [$logId, $p['id'], $p['nama_produk'], 'keluar', $qty,
                     'Penjualan online ' . $notaId, $stokSebelum, $stokSesudah, $notaId]
                );
            }

            $trxId = 'TRX-' . strtoupper(bin2hex(random_bytes(4)));
            Database::query(
                "INSERT INTO `transaksi` (`id`,`nota_id`,`total_bayar`,`status`,`metode_bayar`,`metode_kirim`,`ongkir`)
                 VALUES (?,?,?,?,?,?,?)",
                [$trxId, $notaId, $totalBayar, 'Menunggu Pembayaran', $metodeBayar, $metodeKirim, $ongkir['ongkir']]
            );

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash('error', 'Gagal membuat pesanan: ' . $e->getMessage());
            $this->redirect('/checkout');
        }

        // Beri poin ke pelanggan
        try {
            $poin = \App\Services\PoinService::hitungPoin($totalBayar);
            if ($poin > 0) {
                \App\Services\PoinService::tambah($pelangganId, $poin, 'Poin belanja ' . $notaId, $notaId);
                Database::query('UPDATE `pesanan` SET `poin_didapat` = ?, `poin_diberikan` = 1 WHERE `nota_id` = ?', [$poin, $notaId]);
                Database::query('UPDATE `pelanggan` SET `total_belanja` = `total_belanja` + ? WHERE `id` = ?', [$totalBayar, $pelangganId]);
            }
            // Simpan sesi pelanggan agar bisa akses akun
            $pl = \App\Models\Pelanggan::find($pelangganId);
            if ($pl && !pelanggan_auth()->check()) {
                \App\Core\Session::set('pelanggan', [
                    'id' => $pl['id'], 'nama' => $pl['nama_lengkap'], 'telepon' => $pl['telepon'],
                    'tipe' => $pl['tipe'], 'poin' => (int) $pl['poin'],
                ]);
            }
        } catch (\Throwable $e) {
            // Poin gagal tidak membatalkan pesanan
        }

        Cart::clear();
        $this->redirect('/pesanan/' . $notaId);
    }
}
