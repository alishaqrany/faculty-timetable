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
        $request = new Request();
        $method = $request->method();
        $uri = $request->uri();

        $this->router->dispatch($method, $uri);
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
}
