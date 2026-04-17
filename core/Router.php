<?php

class Router
{
    private array $routes = [];
    private array $groupMiddleware = [];
    private string $groupPrefix = '';

    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function resource(string $path, string $controller, array $middleware = []): void
    {
        $this->get($path, "$controller@index", $middleware);
        $this->get("$path/create", "$controller@create", $middleware);
        $this->post($path, "$controller@store", $middleware);
        $this->get("$path/{id}/edit", "$controller@edit", $middleware);
        $this->post("$path/{id}", "$controller@update", $middleware);
        $this->post("$path/{id}/delete", "$controller@destroy", $middleware);
    }

    public function group(string $prefix, array $middleware, callable $callback): void
    {
        $prevPrefix = $this->groupPrefix;
        $prevMiddleware = $this->groupMiddleware;
        $this->groupPrefix = $prevPrefix . $prefix;
        $this->groupMiddleware = array_merge($prevMiddleware, $middleware);
        $callback($this);
        $this->groupPrefix = $prevPrefix;
        $this->groupMiddleware = $prevMiddleware;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = '/' . trim($uri, '/');
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $pattern = $this->buildPattern($route['path']);
            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware
                foreach ($route['middleware'] as $mw) {
                    $mwClass = "App\\Middleware\\{$mw}";
                    $file = APP_ROOT . '/app/Middleware/' . $mw . '.php';
                    if (file_exists($file)) {
                        require_once $file;
                    }
                    if (class_exists($mwClass)) {
                        $instance = new $mwClass();
                        $instance->handle($params);
                    }
                }

                // Parse handler
                [$controllerName, $action] = explode('@', $route['handler']);

                // Support namespaced controllers (e.g., Api\AuthController)
                $controllerFile = APP_ROOT . '/app/Controllers/' . str_replace('\\', '/', $controllerName) . '.php';
                require_once $controllerFile;

                $fullClass = "App\\Controllers\\$controllerName";
                $controller = new $fullClass();
                $controller->$action(...array_values($params));
                return;
            }
        }

        // No route matched → 404
        http_response_code(404);
        $view = new View();
        echo $view->render('errors.404', [
            'auth' => null,
            'csrf_token' => Session::getInstance()->csrfToken(),
            'flash_success' => null,
            'flash_error' => null,
            'flash_errors' => [],
            'old' => [],
            'notifications_count' => 0,
        ]);
    }

    private function addRoute(string $method, string $path, string $handler, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $method,
            'path'       => $this->groupPrefix . $path,
            'handler'    => $handler,
            'middleware'  => array_merge($this->groupMiddleware, $middleware),
        ];
    }

    private function buildPattern(string $path): string
    {
        // Replace {param} with named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
