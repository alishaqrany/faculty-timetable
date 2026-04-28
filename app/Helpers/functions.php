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

function app_base_path(): string
{
    static $basePath = null;
    if ($basePath !== null) return $basePath;

    $basePath = rtrim((string) config('app.base_path', ''), '/');
    if ($basePath === '.' || $basePath === '/') {
        $basePath = '';
    }

    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $requestPath = is_string($requestPath) && $requestPath !== '' ? $requestPath : '/';

    $publicSuffix = '/public';
    if ($basePath !== '' && substr($basePath, -strlen($publicSuffix)) === $publicSuffix) {
        $hasPrefix = $requestPath === $basePath || strpos($requestPath, $basePath . '/') === 0;
        if (!$hasPrefix) {
            $basePath = substr($basePath, 0, -strlen($publicSuffix));
        }
    }

    return $basePath;
}

function app_base_url(): string
{
    static $baseUrl = null;
    if ($baseUrl !== null) return $baseUrl;

    $basePath = app_base_path();
    $host = $_SERVER['HTTP_HOST'] ?? '';

    if ($host !== '') {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        $scheme = $https ? 'https' : 'http';
        $baseUrl = $scheme . '://' . $host . $basePath;
        return $baseUrl;
    }

    $configuredUrl = rtrim((string) config('app.url', ''), '/');
    if ($configuredUrl === '') {
        $baseUrl = $basePath;
        return $baseUrl;
    }

    $parts = parse_url($configuredUrl);
    if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
        $baseUrl = $configuredUrl;
        return $baseUrl;
    }

    $userInfo = '';
    if (isset($parts['user'])) {
        $userInfo = $parts['user'];
        if (isset($parts['pass'])) {
            $userInfo .= ':' . $parts['pass'];
        }
        $userInfo .= '@';
    }

    $authority = $parts['host'];
    if (isset($parts['port'])) {
        $authority .= ':' . $parts['port'];
    }

    $baseUrl = $parts['scheme'] . '://' . $userInfo . $authority . $basePath;
    return $baseUrl;
}

/**
 * Generate a full URL from a path.
 */
function url(string $path = ''): string
{
    $base = rtrim(app_base_url(), '/');
    if ($path === '' || $path === '/') return $base . '/';
    return $base . '/' . ltrim($path, '/');
}

/**
 * Generate asset URL with cache buster.
 */
function asset(string $path): string
{
    $base = rtrim(app_base_url(), '/');
    $basePath = app_base_path();
    $normalizedPath = ltrim($path, '/');
    $publicPrefix = (substr($basePath, -7) === '/public') ? '' : '/public';
    $filePath = APP_ROOT . '/public/' . $normalizedPath;
    $version = file_exists($filePath) ? filemtime($filePath) : time();
    return $base . $publicPrefix . '/' . $normalizedPath . '?v=' . $version;
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
 * Translate audit action to a human-readable Arabic label.
 */
function audit_action_label(string $action): string
{
    static $labels = [
        'CREATE' => 'إنشاء',
        'UPDATE' => 'تحديث',
        'DELETE' => 'حذف',
        'LOGIN'  => 'تسجيل دخول',
        'LOGOUT' => 'تسجيل خروج',
        'PASS_ROLE' => 'تمرير دور',
        'IMPORT' => 'استيراد',
        'EXPORT' => 'تصدير',
    ];

    return $labels[strtoupper($action)] ?? $action;
}

/**
 * Bootstrap badge class for audit actions.
 */
function audit_action_badge_class(string $action): string
{
    return match (strtoupper($action)) {
        'CREATE' => 'success',
        'UPDATE' => 'warning',
        'DELETE' => 'danger',
        'LOGIN', 'LOGOUT' => 'primary',
        'IMPORT', 'EXPORT', 'PASS_ROLE' => 'info',
        default => 'secondary',
    };
}

/**
 * Translate audit module to a human-readable Arabic label.
 */
function audit_module_label(string $module): string
{
    static $labels = [
        'auth' => 'المصادقة',
        'departments' => 'الأقسام',
        'levels' => 'الفرق',
        'members' => 'أعضاء هيئة التدريس',
        'subjects' => 'المقررات',
        'divisions' => 'الشُعب',
        'sections' => 'السكاشن',
        'classrooms' => 'القاعات',
        'sessions' => 'الفترات الزمنية',
        'academic_years' => 'السنوات الأكاديمية',
        'semesters' => 'الفصول الدراسية',
        'member_courses' => 'تكليفات التدريس',
        'scheduling' => 'الجدولة',
        'timetable' => 'الجدول الدراسي',
        'users' => 'المستخدمون',
        'notifications' => 'الإشعارات',
        'data_transfer' => 'نقل البيانات',
    ];

    return $labels[$module] ?? $module;
}

/**
 * Decode audit JSON payload safely.
 */
function audit_decode_values($raw): array
{
    if (is_array($raw)) {
        return $raw;
    }
    if (!is_string($raw) || trim($raw) === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Try to extract a human-friendly entity name from audit payload.
 */
function audit_entity_name(array $log): ?string
{
    $newValues = audit_decode_values($log['new_values'] ?? null);
    $oldValues = audit_decode_values($log['old_values'] ?? null);
    $data = !empty($newValues) ? $newValues : $oldValues;

    if (empty($data)) {
        return null;
    }

    $preferredKeys = [
        'department_name',
        'level_name',
        'member_name',
        'subject_name',
        'division_name',
        'section_name',
        'classroom_name',
        'session_name',
        'academic_year_name',
        'semester_name',
        'username',
        'role_name',
        'setting_key',
        'title',
        'name',
    ];

    foreach ($preferredKeys as $key) {
        if (isset($data[$key]) && is_scalar($data[$key])) {
            $value = trim((string)$data[$key]);
            if ($value !== '') {
                return $value;
            }
        }
    }

    foreach ($data as $key => $value) {
        if (!is_scalar($value)) {
            continue;
        }
        if (str_ends_with((string)$key, '_name')) {
            $text = trim((string)$value);
            if ($text !== '') {
                return $text;
            }
        }
    }

    return null;
}

/**
 * Human readable audit summary.
 */
function audit_log_summary(array $log): string
{
    $action = strtoupper((string)($log['action'] ?? ''));
    $module = (string)($log['module'] ?? '');
    $moduleLabel = audit_module_label($module);
    $recordId = !empty($log['record_id']) ? ' #' . $log['record_id'] : '';
    $entityName = audit_entity_name($log);
    $entityPart = $entityName ? ': ' . $entityName : '';

    return match ($action) {
        'CREATE' => 'تم إنشاء ' . $moduleLabel . $recordId . $entityPart,
        'UPDATE' => 'تم تحديث ' . $moduleLabel . $recordId . $entityPart,
        'DELETE' => 'تم حذف ' . $moduleLabel . $recordId . $entityPart,
        'LOGIN'  => 'تم تسجيل دخول المستخدم',
        'LOGOUT' => 'تم تسجيل خروج المستخدم',
        'PASS_ROLE' => 'تم تمرير دور الجدولة',
        'IMPORT' => 'تم استيراد بيانات ' . $moduleLabel . $entityPart,
        'EXPORT' => 'تم تصدير بيانات ' . $moduleLabel . $entityPart,
        default => trim(audit_action_label($action) . ' ' . $moduleLabel . $recordId . $entityPart),
    };
}

/**
 * Generate pagination HTML.
 */
function pagination_html(array $pagination, string $baseUrl, array $query = []): string
{
    if ($pagination['last_page'] <= 1) return '';

    $separator = str_contains($baseUrl, '?') ? '&' : '?';
    $queryString = $query ? http_build_query($query) : '';
    if ($queryString !== '') {
        $baseUrl .= ($separator . $queryString);
        $separator = '&';
    }

    $html = '<nav dir="ltr"><ul class="pagination pagination-sm justify-content-center m-0">';

    // Previous
    if ($pagination['current_page'] > 1) {
        $prev = $pagination['current_page'] - 1;
        $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl) . $separator . 'page=' . $prev . '">&laquo;</a></li>';
    }

    // Pages
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['last_page'], $pagination['current_page'] + 2);

    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $pagination['current_page'] ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . e($baseUrl) . $separator . 'page=' . $i . '">' . $i . '</a></li>';
    }

    // Next
    if ($pagination['current_page'] < $pagination['last_page']) {
        $next = $pagination['current_page'] + 1;
        $html .= '<li class="page-item"><a class="page-link" href="' . e($baseUrl) . $separator . 'page=' . $next . '">&raquo;</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}
