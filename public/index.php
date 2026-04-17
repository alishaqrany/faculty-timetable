<?php
/**
 * Front Controller — all requests are routed through here.
 */

define('APP_ROOT', dirname(__DIR__));

// Load core
require_once APP_ROOT . '/core/Application.php';

// Boot the application
$app = new Application();
$app->run();
