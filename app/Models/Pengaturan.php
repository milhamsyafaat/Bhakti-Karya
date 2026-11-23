<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Pengaturan extends Model
{
    protected static string $table = 'pengaturan';
    protected static string $primaryKey = 'setting_key';

    public static function get(string $key, $default = null)
    {
        $row = Database::first('SELECT `setting_value` FROM `pengaturan` WHERE `setting_key` = ? LIMIT 1', [$key]);
        return $row['setting_value'] ?? $default;
    }

    /**
     * @return array<string,string>
     */
    public static function map(): array
    {
        $out = [];
        foreach (Database::select('SELECT `setting_key`,`setting_value` FROM `pengaturan`') as $row) {
            $out[$row['setting_key']] = $row['setting_value'];
        }
        return $out;
    }
}
