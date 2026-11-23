<?php

namespace App\Core;

/**
 * Autentikasi berbasis session untuk admin/kasir (tabel `users`).
 */
class Auth
{
    public function attempt(string $username, string $password): bool
    {
        $user = Database::first(
            'SELECT * FROM `users` WHERE `username` = ? LIMIT 1',
            [$username]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        Session::set('auth_user', [
            'id'    => $user['id'],
            'nama'  => $user['nama_lengkap'],
            'role'  => $user['role'],
            'email' => $user['email'],
        ]);
        Session::regenerate();

        return true;
    }

    public function check(): bool
    {
        return Session::has('auth_user');
    }

    public function user(): ?array
    {
        return Session::get('auth_user');
    }

    public function id()
    {
        return Session::get('auth_user.id');
    }

    public function isAdmin(): bool
    {
        return Session::get('auth_user.role') === 'admin';
    }

    public function logout(): void
    {
        Session::forget('auth_user');
        Session::regenerate();
    }
}
