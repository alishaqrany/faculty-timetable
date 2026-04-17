<?php

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
| Global convenience functions available throughout the application.
*/

/**
 * Get a config value using dot notation.
 */
function config(string $key, $default = null)
{
    $parts = explode('.', $key);
    $file = array_shift($parts);
    $config = $GLOBALS['__app_config'][$file] ?? [];
    foreach ($parts as $part) {
        if (!is_array($config) || !array_key_exists($part, $config)) {
            return $default;
        }
        $config = $config[$part];
    }
    return $config;
}

/**
 * Generate a full URL from a path.
 */
function url(string $path = ''): string
{
    $base = rtrim(config('app.url', ''), '/');
    if ($path === '' || $path === '/') return $base . '/';
    return $base . '/' . ltrim($path, '/');
}

/**
 * Generate asset URL with cache buster.
 */
function asset(string $path): string
{
    $base = rtrim(config('app.url', ''), '/');
    $filePath = APP_ROOT . '/public/' . ltrim($path, '/');
    $version = file_exists($filePath) ? filemtime($filePath) : time();
    return $base . '/' . ltrim($path, '/') . '?v=' . $version;
}

/**
 * Generate a CSRF hidden input field.
 */
function csrf_field(): string
{
    $token = Session::getInstance()->csrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Get raw CSRF token.
 */
function csrf_token(): string
{
    return Session::getInstance()->csrfToken();
}

/**
 * Escape output for HTML.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get old form input value.
 */
function old(string $field, string $default = ''): string
{
    $old = Session::getInstance()->getFlash('old', []);
    // Put it back for other fields
    if ($old) {
        Session::getInstance()->flash('old', $old);
    }
    return e($old[$field] ?? $default);
}

/**
 * Get the current authenticated user data.
 */
function auth(): ?array
{
    static $user = null;
    if ($user !== null) return $user ?: null;

    $session = Session::getInstance();
    if (!$session->isLoggedIn()) {
        $user = false;
        return null;
    }

    try {
        $user = Database::getInstance()->fetch(
            "SELECT u.*, r.role_name, r.role_slug, fm.member_name, fm.department_id
             FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             LEFT JOIN faculty_members fm ON u.member_id = fm.member_id
             WHERE u.id = ? LIMIT 1",
            [$session->userId()]
        );
    } catch (\Throwable $e) {
        $user = false;
    }

    return $user ?: null;
}

/**
 * Check if the current user has a specific permission.
 */
function can(string $permission): bool
{
    $user = auth();
    if (!$user) return false;

    // Admin has all permissions
    if (($user['role_slug'] ?? '') === 'admin') return true;

    // Check permission cache in session
    static $permissions = null;
    if ($permissions === null) {
        try {
            $permissions = Database::getInstance()->fetchAll(
                "SELECT p.permission_slug
                 FROM role_permissions rp
                 JOIN permissions p ON rp.permission_id = p.id
                 WHERE rp.role_id = ?",
                [$user['role_id']]
            );
            $permissions = array_column($permissions, 'permission_slug');
        } catch (\Throwable $e) {
            $permissions = [];
        }
    }

    return in_array($permission, $permissions);
}

/**
 * Redirect helper.
 */
function redirect(string $url, ?string $message = null, string $type = 'success'): void
{
    if ($message) {
        Session::getInstance()->flash($type, $message);
    }
    header('Location: ' . url($url));
    exit();
}

/**
 * Flash a message and redirect back.
 */
function back(?string $message = null, string $type = 'success'): void
{
    if ($message) {
        Session::getInstance()->flash($type, $message);
    }
    $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
    header('Location: ' . $referer);
    exit();
}

/**
 * Get current page number from query string.
 */
function current_page(): int
{
    return max(1, (int)($_GET['page'] ?? 1));
}

/**
 * Format a date in Arabic-friendly format.
 */
function format_date(?string $date, string $format = 'Y/m/d'): string
{
    if (!$date) return '-';
    return date($format, strtotime($date));
}

/**
 * Truncate text.
 */
function str_limit(string $text, int $limit = 50): string
{
    if (mb_strlen($text) <= $limit) return $text;
    return mb_substr($text, 0, $limit) . '...';
}

/**
 * Generate pagination HTML.
 */
function pagination_html(array $pagination, string $baseUrl): string
{
    if ($pagination['last_page'] <= 1) return '';

    $html = '<nav dir="ltr"><ul class="pagination pagination-sm justify-content-center m-0">';

    // Previous
    if ($pagination['current_page'] > 1) {
        $prev = $pagination['current_page'] - 1;
        $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl) . '?page=' . $prev . '">&laquo;</a></li>';
    }

    // Pages
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['last_page'], $pagination['current_page'] + 2);

    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $pagination['current_page'] ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . e($baseUrl) . '?page=' . $i . '">' . $i . '</a></li>';
    }

    // Next
    if ($pagination['current_page'] < $pagination['last_page']) {
        $next = $pagination['current_page'] + 1;
        $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl) . '?page=' . $next . '">&raquo;</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}
