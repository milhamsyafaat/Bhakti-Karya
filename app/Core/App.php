<?php

namespace App\Core;

/**
 * Kernel aplikasi: menyusun router, memuat route, lalu dispatch.
 */
class App
{
    private Router $router;
    private Request $request;

    public function __construct()
    {
        $this->router = new Router();
        $this->request = new Request();
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function request(): Request
    {
        return $this->request;
    }

    public function run(): void
    {
        // CSRF guard untuk semua request POST
        if ($this->request->isPost() && !$this->request->csrfValid()) {
            http_response_code(419);
            View::render('errors/419', ['title' => 'Sesi Kedaluwarsa']);
            return;
        }

        $routesFile = BASE_PATH . '/routes/web.php';
        if (is_file($routesFile)) {
            $app = $this;
            $router = $this->router;
            require $routesFile;
        }

        $this->router->dispatch($this->request);
    }
}
