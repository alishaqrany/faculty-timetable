<?php
/**
 * Front Controller — all requests are routed through here.
 */

define('APP_ROOT', dirname(__DIR__));

if (version_compare(PHP_VERSION, '8.0.0', '<')) {
	http_response_code(503);
	header('Content-Type: text/html; charset=UTF-8');
	echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>إصدار PHP غير مدعوم</title></head><body style="font-family:Tahoma,Arial,sans-serif;padding:24px;line-height:1.8"><h2>إصدار PHP غير مدعوم</h2><p>هذا النظام يحتاج إلى PHP 8.0 أو أحدث.</p><p>الإصدار الحالي على الخادم: ' . htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8') . '</p></body></html>';
	exit;
}

// Load core
require_once APP_ROOT . '/core/Application.php';

// Boot the application
$app = new Application();
$app->run();
