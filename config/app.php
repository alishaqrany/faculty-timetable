<?php

$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
if ($scriptDir === '.' || $scriptDir === '/') {
    $scriptDir = '';
}

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = is_string($requestPath) && $requestPath !== '' ? $requestPath : '/';

$publicSuffix = '/public';
if ($scriptDir !== '' && substr($scriptDir, -strlen($publicSuffix)) === $publicSuffix) {
    $hasPrefix = $requestPath === $scriptDir || strpos($requestPath, $scriptDir . '/') === 0;
    if (!$hasPrefix) {
        $scriptDir = substr($scriptDir, 0, -strlen($publicSuffix));
    }
}

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

return [
    'name'      => 'نظام إدارة الجداول الدراسية',
    'name_en'   => 'Timetable Management System',
    'version'   => '2.0.0',
    'url'       => ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $scriptDir,
    'base_path' => $scriptDir,
    'timezone'  => 'Africa/Cairo',
    'debug'     => true,
    'lang'      => 'ar',
    'charset'   => 'UTF-8',
    'per_page'  => 15,
];
