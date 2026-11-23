<?php

namespace App\Core;

/**
 * Keranjang belanja berbasis session.
 * Struktur: [produk_id => qty]
 */
class Cart
{
    public static function items(): array
    {
        return Session::get('cart', []) ?: [];
    }

    public static function add(int $produkId, int $qty = 1): void
    {
        $items = self::items();
        $items[$produkId] = ($items[$produkId] ?? 0) + max(1, $qty);
        Session::set('cart', $items);
    }

    public static function update(int $produkId, int $qty): void
    {
        $items = self::items();
        if ($qty <= 0) {
            unset($items[$produkId]);
        } else {
            $items[$produkId] = $qty;
        }
        Session::set('cart', $items);
    }

    public static function remove(int $produkId): void
    {
        $items = self::items();
        unset($items[$produkId]);
        Session::set('cart', $items);
    }

    public static function clear(): void
    {
        Session::forget('cart');
    }

    public static function count(): int
    {
        return array_sum(self::items());
    }

    /**
     * Baris keranjang yang sudah digabung dengan data produk.
     * @return array<int, array<string,mixed>>
     */
    public static function detailed(): array
    {
        $items = self::items();
        if (empty($items)) {
            return [];
        }
        $ids = array_map('intval', array_keys($items));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = Database::select("SELECT * FROM `produk` WHERE `id` IN ($placeholders)", $ids);

        $out = [];
        foreach ($rows as $row) {
            $qty = (int) $items[$row['id']];
            $harga = (float) $row['harga'];
            $out[] = [
                'produk'   => $row,
                'qty'      => $qty,
                'harga'    => $harga,
                'subtotal' => $harga * $qty,
            ];
        }
        return $out;
    }

    public static function subtotal(): float
    {
        $total = 0.0;
        foreach (self::detailed() as $line) {
            $total += $line['subtotal'];
        }
        return $total;
    }
}
