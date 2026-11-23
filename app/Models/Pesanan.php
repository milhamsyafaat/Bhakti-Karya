<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Pesanan extends Model
{
    protected static string $table = 'pesanan';
    protected static string $primaryKey = 'id';

    public static function findByNota(string $nota): ?array
    {
        return self::firstWhere('nota_id', $nota);
    }

    public static function detail(string $nota): array
    {
        return Database::select('SELECT * FROM `pesanan_detail` WHERE `nota_id` = ?', [$nota]);
    }

    public static function terbaru(int $limit = 10): array
    {
        return Database::select('SELECT * FROM `pesanan` ORDER BY `id` DESC LIMIT ' . max(1, $limit));
    }

    public static function statistikHariIni(): array
    {
        return Database::first(
            "SELECT COUNT(*) AS jumlah, COALESCE(SUM(total_bayar),0) AS omzet
             FROM `pesanan` WHERE DATE(`tanggal`) = CURDATE()"
        ) ?? ['jumlah' => 0, 'omzet' => 0];
    }
}
