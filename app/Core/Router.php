<?php

namespace App\Core;

/**
 * Router sederhana: mendukung GET/POST, parameter {id}, prefix grup,
 * dan middleware per-route.
 */
class Router
{
    /** @var array<string, array<int, array{pattern:string, action:mixed, middleware:array}>> */
    private array $routes = ['GET' => [], 'POST' => []];

    private string $prefix = '';
    private array $groupMiddleware = [];

    public function get(string $uri, $action, array $middleware = []): void
    {
        $this->add('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, $action, array $middleware = []): void
    {
        $this->add('POST', $uri, $action, $middleware);
    }

    public function group(array $options, callable $callback): void
    {
        $prevPrefix = $this->prefix;
        $prevMw = $this->groupMiddleware;

        if (!empty($options['prefix'])) {
            $this->prefix = trim($prevPrefix . '/' . trim($options['prefix'], '/'), '/');
        }
        if (!empty($options['middleware'])) {
            $this->groupMiddleware = array_merge($prevMw, (array) $options['middleware']);
        }

        $callback($this);

        $this->prefix = $prevPrefix;
        $this->groupMiddleware = $prevMw;
    }

    private function add(string $method, string $uri, $action, array $middleware): void
    {
        $full = '/' . trim($this->prefix . '/' . trim($uri, '/'), '/');
        $full = $full === '/' ? '/' : rtrim($full, '/');
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $full);

        $this->routes[$method][] = [
            'pattern'    => '#^' . $pattern . '$#',
            'action'     => $action,
            'middleware' => array_merge($this->groupMiddleware, $middleware),
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                foreach ($route['middleware'] as $mw) {
                    $instance = new $mw();
                    if (method_exists($instance, 'handle') && $instance->handle($request) === false) {
                        return;
                    }
                }

                $this->invoke($route['action'], $params, $request);
                return;
            }
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Halaman Tidak Ditemukan']);
    }

    private function invoke($action, array $params, Request $request): void
    {
        if (is_callable($action)) {
            call_user_func($action, $request, ...array_values($params));
            return;
        }

        if (is_string($action) && strpos($action, '@') !== false) {
            [$class, $method] = explode('@', $action);
            $class = 'App\\Controllers\\' . str_replace('/', '\\', $class);
            if (class_exists($class)) {
                $controller = new $class();
                if (method_exists($controller, $method)) {
                    call_user_func_array([$controller, $method], $this->resolveArgs($controller, $method, $params, $request));
                    return;
                }
            }
        }

        http_response_code(500);
        echo 'Handler route tidak valid.';
    }

    /**
     * Menyusun argumen method controller: parameter route by-name,
     * sisanya diisi Request (atau default) sesuai signature.
     */
    private function resolveArgs(object $controller, string $method, array $params, Request $request): array
    {
        try {
            $ref = new \ReflectionMethod($controller, $method);
        } catch (\ReflectionException $e) {
            return array_values($params);
        }

        $args = [];
        foreach ($ref->getParameters() as $param) {
            $type = $param->getType();
            $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : '';

            if ($typeName === Request::class || strtolower($typeName) === 'request') {
                $args[] = $request;
                continue;
            }

            $name = $param->getName();
            if (array_key_exists($name, $params)) {
                $args[] = $params[$name];
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }

            // Param tak dikenal & wajib: lewatkan request bila tipenya class lain
            if ($typeName !== '' && class_exists($typeName)) {
                $args[] = new $typeName();
            } else {
                $args[] = null;
            }
        }
        return $args;
    }
}
