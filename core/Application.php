<?php

class Application
{
    private Router $router;
    private static ?Application $instance = null;

    public function __construct()
    {
        self::$instance = $this;

        // Define root path
        if (!defined('APP_ROOT')) {
            define('APP_ROOT', dirname(__DIR__));
        }

        // Load core classes
        $this->loadCore();

        // Start session
        Session::getInstance()->start();

        // Load config
        $this->loadConfig();

        // Load helpers
        require_once APP_ROOT . '/app/Helpers/functions.php';

        // Initialize router
        $this->router = new Router();

        // Load routes
        $router = $this->router;
        require APP_ROOT . '/config/routes.php';
    }

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        try {
            $request = new Request();
            $method = $request->method();
            $uri = $request->uri();

            $this->router->dispatch($method, $uri);
        } catch (\Throwable $exception) {
            $this->handleException($exception);
        }
    }

    private function loadCore(): void
    {
        $coreFiles = [
            'Database', 'Router', 'Request', 'Session',
            'View', 'Model', 'Controller',
        ];
        foreach ($coreFiles as $file) {
            require_once APP_ROOT . "/core/$file.php";
        }
    }

    private function loadConfig(): void
    {
        // Make config globally accessible
        $GLOBALS['__app_config'] = [];

        $configFiles = ['app', 'database', 'permissions'];
        foreach ($configFiles as $file) {
            $path = APP_ROOT . "/config/$file.php";
            if (file_exists($path)) {
                $GLOBALS['__app_config'][$file] = require $path;
            }
        }
    }

    private function handleException(\Throwable $exception): void
    {
        $errorReference = $this->generateErrorReference();
        error_log('[' . $errorReference . '] ' . (string) $exception);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $isDatabaseIssue = Database::isConnectionException($exception);
        $statusCode = $isDatabaseIssue ? 503 : 500;

        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: text/html; charset=UTF-8');
        }

        $view = new View();
        echo $view->render($isDatabaseIssue ? 'errors.database-unavailable' : 'errors.server-error', [
            'statusCode' => $statusCode,
            'homeUrl' => url('/'),
            'retryUrl' => url($_SERVER['REQUEST_URI'] ?? '/'),
            'installUrl' => url('/install.php'),
            'errorReference' => $errorReference,
        ]);
    }

    private function generateErrorReference(): string
    {
        try {
            $suffix = strtoupper(bin2hex(random_bytes(3)));
        } catch (\Throwable $exception) {
            $suffix = strtoupper(substr(md5(uniqid('', true)), 0, 6));
        }

        return 'ERR-' . date('Ymd-His') . '-' . $suffix;
    }
}
