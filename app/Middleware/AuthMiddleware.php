<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

class AuthMiddleware
{
    public function handle(Request $request): bool
    {
        if (!Session::has('auth_user')) {
            Session::flash('error', 'Silakan login terlebih dahulu.');
            redirect('/login');
            return false;
        }
        return true;
    }
}
