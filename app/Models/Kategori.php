<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Kategori extends Model
{
    protected static string $table = 'kategori';
    protected static string $primaryKey = 'id';

    public static function ordered(): array
    {
        return Database::select('SELECT * FROM `kategori` ORDER BY `urutan` ASC, `id` ASC');
    }
}
