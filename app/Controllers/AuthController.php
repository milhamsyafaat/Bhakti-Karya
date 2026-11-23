<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Session::has('auth_user')) {
            $this->redirect('/admin');
        }
        $this->view('auth/login', ['title' => 'Masuk Admin'], 'layouts/auth');
    }

    public function login(Request $request): void
    {
        $username = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', '');

        if ((new Auth())->attempt($username, $password)) {
            flash('success', 'Selamat datang, ' . Session::get('auth_user.nama') . '.');
            $this->redirect('/admin');
        }

        Session::set('_old', ['username' => $username]);
        flash('error', 'Username atau password salah.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        (new Auth())->logout();
        $this->redirect('/');
    }
}
