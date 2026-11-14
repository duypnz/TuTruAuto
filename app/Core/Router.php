<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        foreach ($this->routes[$method] ?? [] as $path => $handler) {
            $pattern = preg_replace_callback('/\{([a-zA-Z]+)\}/', static fn (array $m): string => '(?P<' . $m[1] . '>[^/]+)', $path);
            if (preg_match('#^' . $pattern . '/?$#', rtrim($uri, '/') ?: '/', $matches)) {
                [$class, $action] = $handler;
                (new $class())->$action($matches);
                return;
            }
        }
        http_response_code(404);
        echo 'Trang bạn tìm không tồn tại.';
    }
}
