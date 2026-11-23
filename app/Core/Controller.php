<?php

namespace App\Core;

/**
 * Controller dasar.
 */
abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/app'): void
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $path): void
    {
        redirect($path);
    }

    protected function json($data, int $status = 200): void
    {
        (new Response())->json($data, $status);
    }

    protected function back(): void
    {
        (new Response())->back();
    }
}
