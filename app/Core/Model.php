<?php

namespace App\Core;

/**
 * Model dasar berbasis PDO. Child cukup set $table & $primaryKey.
 */
abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    public static function table(): string
    {
        return static::$table;
    }

    public static function all(): array
    {
        return Database::select('SELECT * FROM `' . static::$table . '`');
    }

    public static function find($id): ?array
    {
        return Database::first(
            'SELECT * FROM `' . static::$table . '` WHERE `' . static::$primaryKey . '` = ? LIMIT 1',
            [$id]
        );
    }

    public static function where(string $column, $value): array
    {
        return Database::select(
            'SELECT * FROM `' . static::$table . '` WHERE `' . $column . '` = ?',
            [$value]
        );
    }

    public static function firstWhere(string $column, $value): ?array
    {
        return Database::first(
            'SELECT * FROM `' . static::$table . '` WHERE `' . $column . '` = ? LIMIT 1',
            [$value]
        );
    }

    public static function insert(array $data)
    {
        $columns = array_keys($data);
        $fields = '`' . implode('`,`', $columns) . '`';
        $holders = implode(',', array_fill(0, count($columns), '?'));
        Database::query(
            'INSERT INTO `' . static::$table . "` ($fields) VALUES ($holders)",
            array_values($data)
        );
        return Database::lastInsertId();
    }

    public static function update($id, array $data): int
    {
        $sets = implode(',', array_map(fn ($c) => "`$c` = ?", array_keys($data)));
        $params = array_values($data);
        $params[] = $id;
        return Database::execute(
            'UPDATE `' . static::$table . "` SET $sets WHERE `" . static::$primaryKey . '` = ?',
            $params
        );
    }

    public static function delete($id): int
    {
        return Database::execute(
            'DELETE FROM `' . static::$table . '` WHERE `' . static::$primaryKey . '` = ?',
            [$id]
        );
    }

    public static function count(string $where = '', array $params = []): int
    {
        $sql = 'SELECT COUNT(*) AS c FROM `' . static::$table . '`';
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }
        $row = Database::first($sql, $params);
        return (int) ($row['c'] ?? 0);
    }
}
