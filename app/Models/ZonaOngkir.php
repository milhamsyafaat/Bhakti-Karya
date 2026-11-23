<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class ZonaOngkir extends Model
{
    protected static string $table = 'zona_ongkir';
    protected static string $primaryKey = 'id';

    public static function aktif(): array
    {
        return Database::select('SELECT * FROM `zona_ongkir` WHERE `aktif` = 1 ORDER BY `jarak_km` ASC');
    }
}
