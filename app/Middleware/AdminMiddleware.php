<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

class AdminMiddleware
{
    public function handle(Request $request): bool
    {
        if (Session::get('auth_user.role') !== 'admin') {
            http_response_code(403);
            \App\Core\View::render('errors/403', ['title' => 'Akses Ditolak']);
            return false;
        }
        return true;
    }
}
