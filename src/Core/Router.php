<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes[] = ['GET', $path, $handler];
    }

    public function post(string $path, array $handler): void
    {
        $this->routes[] = ['POST', $path, $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = strtok($uri, '?') ?: '/';

        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            if ($method !== $routeMethod) {
                continue;
            }

            $pattern = $this->toRegex($routePath);

            if (!preg_match($pattern, $uri, $matches)) {
                continue;
            }

            $params = array_values(
                array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY)
            );

            [$controllerClass, $action] = $handler;
            (new $controllerClass())->$action(...$params);
            return;
        }

        http_response_code(404);
        require __DIR__ . '/../../views/errors/404.php';
    }

    private function toRegex(string $path): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}
