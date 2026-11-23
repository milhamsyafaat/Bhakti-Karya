<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

class PelangganController extends Controller
{
    public function index(): void
    {
        $this->view('admin/pelanggan/index', [
            'title'     => 'Data Pelanggan',
            'pelanggan' => Database::select('SELECT * FROM `pelanggan` ORDER BY `total_belanja` DESC LIMIT 100'),
        ], 'layouts/admin');
    }
}
