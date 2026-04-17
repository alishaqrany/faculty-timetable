<?php
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once APP_ROOT . '/db_config.php';
require_once APP_ROOT . '/core/auth.php';
require_once APP_ROOT . '/core/flash.php';
require_once APP_ROOT . '/core/csrf.php';
