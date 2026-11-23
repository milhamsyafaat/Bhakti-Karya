<?php

namespace App\Core;

use App\Models\Pelanggan;

/**
 * Autentikasi pelanggan toko (berbasis nomor HP).
 */
class AuthPelanggan
{
    public function attempt(string $telepon, ?string $nama = null): bool
    {
        $telepon = trim($telepon);
        if ($telepon === '') {
            return false;
        }

        $pelanggan = Pelanggan::firstWhere('telepon', $telepon);

        if (!$pelanggan) {
            if (!$nama) {
                return false;
            }
            $maxId = Database::first("SELECT MAX(CAST(SUBSTRING(`id`,2) AS UNSIGNED)) AS m FROM `pelanggan`");
            $nextNum = (int) ($maxId['m'] ?? 0) + 1;
            $id = 'C' . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
            Pelanggan::insert([
                'id' => $id, 'nama_lengkap' => $nama, 'telepon' => $telepon,
                'kota' => 'Kota Depok', 'tipe' => 'umum',
            ]);
            $pelanggan = Pelanggan::find($id);
        }

        Session::set('pelanggan', [
            'id'    => $pelanggan['id'],
            'nama'  => $pelanggan['nama_lengkap'],
            'telepon' => $pelanggan['telepon'],
            'tipe'  => $pelanggan['tipe'],
            'poin'  => (int) $pelanggan['poin'],
        ]);
        Session::regenerate();
        return true;
    }

    public function check(): bool
    {
        return Session::has('pelanggan');
    }

    public function user(): ?array
    {
        return Session::get('pelanggan');
    }

    public function id(): ?string
    {
        return Session::get('pelanggan.id');
    }

    public function refresh(): void
    {
        $id = $this->id();
        if (!$id) {
            return;
        }
        $p = Pelanggan::find($id);
        if ($p) {
            Session::set('pelanggan.poin', (int) $p['poin']);
            Session::set('pelanggan.tipe', $p['tipe']);
        }
    }

    public function logout(): void
    {
        Session::forget('pelanggan');
        Session::regenerate();
    }
}
