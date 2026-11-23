<?php

namespace App\Core;

/**
 * Membungkus respons HTTP.
 */
class Response
{
    public function redirect(string $path): void
    {
        redirect($path);
    }

    public function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function back(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? url('/');
        header('Location: ' . $ref);
        exit;
    }
}
