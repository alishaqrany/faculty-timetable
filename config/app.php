<?php

$env = static function (string $key, $default = null) {
    if (array_key_exists($key, $_SERVER) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }

    $value = getenv($key);
    return ($value !== false && $value !== '') ? $value : $default;
};

$normalizePath = static function (?string $path): string {
    $path = trim((string) $path);
    if ($path === '' || $path === '.' || $path === '/') {
        return '';
    }

    $path = '/' . trim(str_replace('\\', '/', $path), '/');
    return $path === '/' ? '' : $path;
};

$scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php');
$scriptDir = $normalizePath(dirname($scriptName));

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = is_string($requestPath) && $requestPath !== '' ? $requestPath : '/';

$publicSuffix = '/public';
$basePath = $scriptDir;
if ($basePath !== '' && substr($basePath, -strlen($publicSuffix)) === $publicSuffix) {
    $hasPrefix = $requestPath === $basePath || strpos($requestPath, $basePath . '/') === 0;
    if (!$hasPrefix) {
        $basePath = substr($basePath, 0, -strlen($publicSuffix));
    }
}

$basePathOverride = $normalizePath($env('APP_BASE_PATH'));
$publicBasePathOverride = $normalizePath($env('APP_PUBLIC_BASE_PATH'));

$configuredUrl = trim((string) $env('APP_URL', ''));
if ($configuredUrl !== '' && $basePathOverride === '') {
    $configuredPath = parse_url($configuredUrl, PHP_URL_PATH);
    if (is_string($configuredPath)) {
        $basePathOverride = $normalizePath($configuredPath);
    }
}

$basePath = $basePathOverride !== '' ? $basePathOverride : $basePath;
$publicBasePath = $publicBasePathOverride !== '' ? $publicBasePathOverride : $scriptDir;

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    || (strtolower((string) $env('APP_FORCE_SCHEME', '')) === 'https');

$host = (string) ($_SERVER['HTTP_HOST'] ?? $env('APP_HOST', 'localhost'));
$detectedUrl = ($https ? 'https' : 'http') . '://' . $host . $basePath;

return [
    'name'      => 'نظام إدارة الجداول الدراسية',
    'name_en'   => 'Timetable Management System',
    'version'   => '2.0.0',
    'url'       => $configuredUrl !== '' ? rtrim($configuredUrl, '/') : rtrim($detectedUrl, '/'),
    'base_path' => $basePath,
    'public_base_path' => $publicBasePath,
    'timezone'  => 'Africa/Cairo',
    'debug'     => true,
    'lang'      => 'ar',
    'charset'   => 'UTF-8',
    'per_page'  => 15,
];
