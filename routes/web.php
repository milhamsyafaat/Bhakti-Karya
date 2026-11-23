<?php
/**
 * Definisi route aplikasi.
 * Variabel tersedia: $router (\App\Core\Router), $app (\App\Core\App)
 */

use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

/** @var \App\Core\Router $router */

// ---------------------------------------------------------------------
// Publik
// ---------------------------------------------------------------------
$router->get('/', 'HomeController@index');
$router->get('/produk', 'ProdukController@index');
$router->get('/produk/{id}', 'ProdukController@show');
$router->get('/kategori/{id}', 'ProdukController@index');

// Cek ongkir (AJAX)
$router->get('/ongkir', 'CheckoutController@cekOngkir');

// ---------------------------------------------------------------------
// Keranjang
// ---------------------------------------------------------------------
$router->get('/keranjang', 'KeranjangController@index');
$router->post('/keranjang/tambah', 'KeranjangController@add');
$router->post('/keranjang/update', 'KeranjangController@update');
$router->post('/keranjang/hapus', 'KeranjangController@remove');

// ---------------------------------------------------------------------
// Checkout & Pesanan
// ---------------------------------------------------------------------
$router->get('/checkout', 'CheckoutController@index');
$router->post('/checkout', 'CheckoutController@store');
$router->get('/pesanan/{nota}', 'PesananController@show');
$router->get('/lacak', 'PesananController@track');

// ---------------------------------------------------------------------
// Autentikasi (admin/kasir)
// ---------------------------------------------------------------------
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->post('/logout', 'AuthController@logout');

// ---------------------------------------------------------------------
// Akun pelanggan (login via nomor HP)
// ---------------------------------------------------------------------
$router->get('/masuk', 'AkunController@showMasuk');
$router->post('/masuk', 'AkunController@masuk');
$router->post('/keluar', 'AkunController@keluar');
$router->get('/akun', 'AkunController@index');
$router->get('/akun/poin', 'AkunController@poin');
$router->get('/akun/voucher', 'AkunController@voucher');
$router->post('/akun/tukar-voucher', 'AkunController@tukarVoucher');

// ---------------------------------------------------------------------
// Rutinitas belanja (pesanan langganan)
// ---------------------------------------------------------------------
$router->get('/rutinitas', 'RutinitasController@index');
$router->post('/rutinitas/simpan', 'RutinitasController@simpan');
$router->post('/rutinitas/{id}/pesan', 'RutinitasController@pesan');
$router->post('/rutinitas/{id}/hapus', 'RutinitasController@hapus');

// ---------------------------------------------------------------------
// Notifikasi stok (stok_alert)
// ---------------------------------------------------------------------
$router->post('/stok-alert', 'StokAlertController@simpan');

// ---------------------------------------------------------------------
// Panel admin
// ---------------------------------------------------------------------
$router->group(['prefix' => 'admin', 'middleware' => [AuthMiddleware::class, AdminMiddleware::class]], function ($r) {
    $r->get('/', 'Admin/DashboardController@index');

    $r->get('/produk', 'Admin/ProdukController@index');
    $r->get('/produk/create', 'Admin/ProdukController@create');
    $r->post('/produk', 'Admin/ProdukController@store');
    $r->get('/produk/{id}/edit', 'Admin/ProdukController@edit');
    $r->post('/produk/{id}', 'Admin/ProdukController@update');
    $r->post('/produk/{id}/hapus', 'Admin/ProdukController@destroy');

    $r->get('/pesanan', 'Admin/PesananController@index');
    $r->get('/pesanan/{nota}', 'Admin/PesananController@show');
    $r->post('/pesanan/{nota}/status', 'Admin/PesananController@updateStatus');

    $r->get('/stok', 'Admin/StokController@index');
    $r->post('/stok', 'Admin/StokController@store');

    $r->get('/pelanggan', 'Admin/PelangganController@index');

    $r->get('/bon', 'Admin/BonController@index');
    $r->post('/bon', 'Admin/BonController@store');
    $r->get('/bon/{id}', 'Admin/BonController@show');
    $r->post('/bon/{id}/bayar', 'Admin/BonController@bayar');
});
