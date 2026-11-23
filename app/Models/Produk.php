<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Produk extends Model
{
    protected static string $table = 'produk';
    protected static string $primaryKey = 'id';

    /**
     * Query katalog dengan filter opsional.
     */
    public static function filter(array $opts = []): array
    {
        [$where, $params] = self::buildWhere($opts);
        $sql = 'SELECT * FROM `produk`' . $where;

        $sort = $opts['sort'] ?? 'terbaru';
        $sql .= match ($sort) {
            'termurah'  => ' ORDER BY harga ASC',
            'termahal'  => ' ORDER BY harga DESC',
            'terlaris'  => ' ORDER BY penjualan DESC',
            default     => ' ORDER BY id ASC',
        };

        $limit = max(1, (int) ($opts['limit'] ?? 24));
        $offset = max(0, (int) ($opts['offset'] ?? 0));
        $sql .= " LIMIT $limit OFFSET $offset";

        return Database::select($sql, $params);
    }

    public static function countFilter(array $opts = []): int
    {
        [$where, $params] = self::buildWhere($opts);
        $row = Database::first('SELECT COUNT(*) AS c FROM `produk`' . $where, $params);
        return (int) ($row['c'] ?? 0);
    }

    private static function buildWhere(array $opts): array
    {
        $clauses = ['aktif = 1'];
        $params = [];

        if (!empty($opts['kategori_id'])) {
            $clauses[] = 'kategori_id = ?';
            $params[] = (int) $opts['kategori_id'];
        }
        if (!empty($opts['q'])) {
            $clauses[] = '(nama_produk LIKE ? OR alias LIKE ?)';
            $like = '%' . $opts['q'] . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if (!empty($opts['badge'])) {
            $clauses[] = 'badge_status = ?';
            $params[] = $opts['badge'];
        }

        return [' WHERE ' . implode(' AND ', $clauses), $params];
    }

    public static function favorit(int $limit = 8): array
    {
        return Database::select(
            'SELECT * FROM `produk` WHERE aktif = 1 AND is_favorit = 1 ORDER BY penjualan DESC LIMIT ' . max(1, $limit)
        );
    }

    public static function terlaris(int $limit = 8): array
    {
        return Database::select(
            'SELECT * FROM `produk` WHERE aktif = 1 ORDER BY penjualan DESC, id ASC LIMIT ' . max(1, $limit)
        );
    }

    public static function related(int $kategoriId, int $excludeId, int $limit = 4): array
    {
        return Database::select(
            'SELECT * FROM `produk` WHERE aktif = 1 AND kategori_id = ? AND id <> ? ORDER BY penjualan DESC LIMIT ' . max(1, $limit),
            [$kategoriId, $excludeId]
        );
    }
}
