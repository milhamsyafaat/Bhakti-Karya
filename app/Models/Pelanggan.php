<?php

namespace App\Models;

use App\Core\Model;

class Pelanggan extends Model
{
    protected static string $table = 'pelanggan';
    protected static string $primaryKey = 'id';

    public static function reseller(): array
    {
        return self::where('tipe', 'reseller');
    }
}
