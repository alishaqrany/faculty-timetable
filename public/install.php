<?php
/**
 * Install Wizard for Timetable Management System v2
 *
 * 6-step installer:
 *   1. Requirements check
 *   2. Database configuration
 *   3. Run migrations & seeds
 *   4. Create admin account
 *   5. System settings (app name, timezone, base URL)
 *   6. Completion
 */
session_start();

define('APP_ROOT', dirname(__DIR__));

function detect_app_base_path(): string
{
    $scriptDir = detect_script_dir();

    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $requestPath = is_string($requestPath) && $requestPath !== '' ? $requestPath : '/';

    $publicSuffix = '/public';
    if ($scriptDir !== '' && substr($scriptDir, -strlen($publicSuffix)) === $publicSuffix) {
        $hasPrefix = $requestPath === $scriptDir || strpos($requestPath, $scriptDir . '/') === 0;
        if (!$hasPrefix) {
            $scriptDir = substr($scriptDir, 0, -strlen($publicSuffix));
        }
    }

    return $scriptDir;
}

function detect_script_dir(): string
{
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    if ($scriptDir === '.' || $scriptDir === '/') {
        return '';
    }

    return '/' . trim(str_replace('\\', '/', $scriptDir), '/');
}

function detect_app_url(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . detect_app_base_path();
}

function detect_public_base_path(): string
{
    return detect_script_dir();
}

function build_app_config_content(string $appName, string $timezone, bool $debug = false): string
{
    return <<<'PHP'
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

$basePathOverride = $normalizePath($env('APP_BASE_PATH', __APP_BASE_PATH__));
$publicBasePathOverride = $normalizePath($env('APP_PUBLIC_BASE_PATH'));

$configuredUrl = trim((string) $env('APP_URL', __APP_URL__));
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
    'name'      => __APP_NAME__,
    'name_en'   => 'Timetable Management System',
    'version'   => '2.0.0',
    'url'       => $configuredUrl !== '' ? rtrim($configuredUrl, '/') : rtrim($detectedUrl, '/'),
    'base_path' => $basePath,
    'public_base_path' => $publicBasePath,
    'timezone'  => __TIMEZONE__,
    'debug'     => __DEBUG__,
    'lang'      => 'ar',
    'charset'   => 'UTF-8',
    'per_page'  => 15,
];
PHP;
}

if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>إصدار PHP غير مدعوم</title></head><body style="font-family:Tahoma,Arial,sans-serif;padding:24px;line-height:1.8"><h2>إصدار PHP غير مدعوم</h2><p>لا يمكن تشغيل معالج التثبيت لأن النظام يحتاج إلى PHP 8.0 أو أحدث.</p><p>الإصدار الحالي على الخادم: ' . htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8') . '</p></body></html>';
    exit;
}

// Load core Database class
require_once APP_ROOT . '/core/Database.php';

$step    = $_GET['step'] ?? '1';
$error   = '';
$success = '';
$dbCfg   = require APP_ROOT . '/config/database.php';
$totalSteps = 6;

// ─── Step Handlers ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === '1') {
        header('Location: install.php?step=2');
        exit;
    }

    if ($step === '2') {
        $host = trim($_POST['host'] ?? 'localhost');
        $user = trim($_POST['username'] ?? 'root');
        $pass = $_POST['password'] ?? '';
        $name = trim($_POST['database'] ?? 'timetable_v2');

        if (!$host || !$user || !$name) {
            $error = 'يرجى ملء جميع الحقول المطلوبة.';
        } else {
            try {
                $testConn = new mysqli($host, $user, $pass);
                if ($testConn->connect_error) {
                    throw new Exception($testConn->connect_error);
                }
                $testConn->query("CREATE DATABASE IF NOT EXISTS `" . $testConn->real_escape_string($name) . "`
                    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $testConn->close();

                $configContent = "<?php\n\nreturn [\n"
                    . "    'host'     => " . var_export($host, true) . ",\n"
                    . "    'username' => " . var_export($user, true) . ",\n"
                    . "    'password' => " . var_export($pass, true) . ",\n"
                    . "    'database' => " . var_export($name, true) . ",\n"
                    . "    'charset'  => 'utf8mb4',\n"
                    . "];\n";
                file_put_contents(APP_ROOT . '/config/database.php', $configContent);

                header('Location: install.php?step=3');
                exit;
            } catch (Exception $e) {
                $error = 'فشل الاتصال بقاعدة البيانات: ' . htmlspecialchars($e->getMessage());
            }
        }
    }

    if ($step === '3') {
        try {
            $dbCfg = require APP_ROOT . '/config/database.php';
            $db = Database::connectWith($dbCfg['host'], $dbCfg['username'], $dbCfg['password'], $dbCfg['database']);

            $db->raw("CREATE TABLE IF NOT EXISTS `migrations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `migration` VARCHAR(255) NOT NULL,
                `ran_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $migrationDir = APP_ROOT . '/database/migrations';
            $files = glob($migrationDir . '/*.php');
            sort($files);

            foreach ($files as $file) {
                $filename = basename($file);
                $exists = $db->fetch("SELECT id FROM migrations WHERE migration = ?", [$filename]);
                if (!$exists) {
                    $migration = require $file;
                    if (is_callable($migration)) {
                        $migration($db);
                    }
                    $db->insert('migrations', ['migration' => $filename]);
                }
            }

            $seedDir = APP_ROOT . '/database/seeds';
            $seedFiles = glob($seedDir . '/*.php');
            sort($seedFiles);

            foreach ($seedFiles as $file) {
                $seed = require $file;
                if (is_callable($seed)) {
                    $seed($db);
                }
            }

            header('Location: install.php?step=4');
            exit;
        } catch (Exception $e) {
            $error = 'خطأ أثناء تنفيذ التهيئة: ' . htmlspecialchars($e->getMessage());
        }
    }

    if ($step === '4') {
        // Create custom admin account
        $adminUser = trim($_POST['admin_username'] ?? '');
        $adminPass = $_POST['admin_password'] ?? '';
        $adminPass2 = $_POST['admin_password2'] ?? '';
        $adminName = trim($_POST['admin_name'] ?? 'مدير النظام');

        if (!$adminUser || !$adminPass) {
            $error = 'اسم المستخدم وكلمة المرور مطلوبان.';
        } elseif (strlen($adminPass) < 6) {
            $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.';
        } elseif ($adminPass !== $adminPass2) {
            $error = 'كلمتا المرور غير متطابقتين.';
        } else {
            try {
                $dbCfg = require APP_ROOT . '/config/database.php';
                $db = Database::connectWith($dbCfg['host'], $dbCfg['username'], $dbCfg['password'], $dbCfg['database']);

                // Get admin role
                $adminRole = $db->fetch("SELECT id FROM roles WHERE role_slug = 'admin' LIMIT 1");
                $roleId = $adminRole ? (int)$adminRole['id'] : 1;

                // Check if admin user already exists (from seed)
                $existing = $db->fetch("SELECT id FROM users WHERE username = ?", [$adminUser]);

                if ($existing) {
                    // Update the seeded admin account
                    $db->raw("UPDATE users SET password = '" . $db->getConnection()->real_escape_string(password_hash($adminPass, PASSWORD_DEFAULT)) . "' WHERE id = " . (int)$existing['id']);
                    // Update member name
                    $member = $db->fetch("SELECT member_id FROM users WHERE id = ?", [(int)$existing['id']]);
                    if ($member && $member['member_id']) {
                        $db->update('faculty_members', ['member_name' => $adminName], ['member_id' => (int)$member['member_id']]);
                    }
                } else {
                    // Create new admin member
                    $memberId = $db->insert('faculty_members', [
                        'member_name' => $adminName,
                        'degree'      => 'غير محدد',
                        'join_date'   => date('Y-m-d'),
                        'is_active'   => 1,
                    ]);
                    // Create new admin user
                    $db->insert('users', [
                        'username'            => $adminUser,
                        'password'            => password_hash($adminPass, PASSWORD_DEFAULT),
                        'member_id'           => $memberId,
                        'role_id'             => $roleId,
                        'registration_status' => 1,
                        'is_active'           => 1,
                    ]);
                }

                // Store username in session for display on completion page
                $_SESSION['install_admin_user'] = $adminUser;

                header('Location: install.php?step=5');
                exit;
            } catch (Exception $e) {
                $error = 'خطأ أثناء إنشاء حساب المدير: ' . htmlspecialchars($e->getMessage());
            }
        }
    }

    if ($step === '5') {
        // Save system settings
            $appName  = trim($_POST['app_name'] ?? 'نظام إدارة الجداول الدراسية');
            $appUrl   = trim($_POST['app_url'] ?? '');
            $basePath = trim($_POST['base_path'] ?? '');
            $timezone = trim($_POST['timezone'] ?? 'Asia/Riyadh');

        try {
            // Auto-detect URL if empty
            if (!$appUrl) {
                $appUrl = detect_app_url();
            }
            if (!$basePath) {
                $basePath = detect_app_base_path();
            }

            // Write a portable config/app.php that can adapt to different hosts and paths.
            $appConfig = build_app_config_content($appName, $timezone, false);
            $appConfig = str_replace('__APP_NAME__', var_export($appName, true), $appConfig);
            $appConfig = str_replace('__TIMEZONE__', var_export($timezone, true), $appConfig);
            $appConfig = str_replace('__DEBUG__', 'false', $appConfig);
            $appConfig = str_replace('__APP_URL__', var_export(rtrim($appUrl, '/'), true), $appConfig);

            $normalizedBasePath = '';
            if ($basePath !== '') {
                $normalizedBasePath = '/' . trim(str_replace('\\', '/', $basePath), '/');
                if ($normalizedBasePath === '/') {
                    $normalizedBasePath = '';
                }
            }

            $appConfig = str_replace('__APP_BASE_PATH__', var_export($normalizedBasePath, true), $appConfig);

            file_put_contents(APP_ROOT . '/config/app.php', $appConfig);

            // Save institution name in settings table
            $dbCfg = require APP_ROOT . '/config/database.php';
            $db = Database::connectWith($dbCfg['host'], $dbCfg['username'], $dbCfg['password'], $dbCfg['database']);
            $existing = $db->fetch("SELECT id FROM settings WHERE setting_key = 'institution_name'");
            if ($existing) {
                $db->update('settings', ['setting_value' => $appName], ['id' => (int)$existing['id']]);
            } else {
                $db->insert('settings', ['setting_key' => 'institution_name', 'setting_value' => $appName]);
            }

            // Mark installed
            if (!is_dir(APP_ROOT . '/storage')) {
                @mkdir(APP_ROOT . '/storage', 0755, true);
            }
            file_put_contents(APP_ROOT . '/storage/installed.lock', json_encode([
                'installed_at' => date('Y-m-d H:i:s'),
                'php_version'  => PHP_VERSION,
                'app_version'  => '2.0.0',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            header('Location: install.php?step=6');
            exit;
        } catch (Exception $e) {
            $error = 'خطأ أثناء حفظ الإعدادات: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// ─── Requirements Check Data ────────────────────────────────────────────────────
$requirements = [
    ['name' => 'PHP >= 8.0',            'ok' => version_compare(PHP_VERSION, '8.0.0', '>=')],
    ['name' => 'MySQLi Extension',       'ok' => extension_loaded('mysqli')],
    ['name' => 'Session Extension',      'ok' => extension_loaded('session')],
    ['name' => 'JSON Extension',         'ok' => extension_loaded('json')],
    ['name' => 'MBString Extension',     'ok' => extension_loaded('mbstring')],
    ['name' => 'config/ قابل للكتابة',   'ok' => is_writable(APP_ROOT . '/config')],
    ['name' => 'storage/ موجود وقابل للكتابة', 'ok' => is_dir(APP_ROOT . '/storage') && is_writable(APP_ROOT . '/storage')],
];
$allPassed = !in_array(false, array_column($requirements, 'ok'), true);

// Check if already installed
$isInstalled = file_exists(APP_ROOT . '/storage/installed.lock');

// Auto-detect base URL for step 5
$autoUrl  = '';
$autoBase = '';
if ($step === '5') {
    $autoUrl  = detect_app_url();
    $autoBase = detect_app_base_path();
}

// Read current app config for step 5 defaults
$appCfg = @include(APP_ROOT . '/config/app.php');
if (!is_array($appCfg)) $appCfg = [];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تثبيت النظام - نظام إدارة الجداول الدراسية</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <style>
        body { font-family: 'Tajawal', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .install-card { max-width: 700px; margin: 40px auto; border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
        .install-header { background: linear-gradient(135deg, #1e3c72, #2a5298); color: #fff; border-radius: 15px 15px 0 0; padding: 30px; text-align: center; }
        .install-header h2 { margin: 0; font-weight: 700; }
        .install-header p { margin: 10px 0 0; opacity: .85; }
        .step-indicator { display: flex; justify-content: center; gap: 8px; margin-top: 15px; }
        .step-dot { width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,.3); transition: .3s; }
        .step-dot.active { background: #fff; transform: scale(1.3); }
        .step-dot.done { background: #28a745; }
        .install-body { padding: 30px; background: #fff; border-radius: 0 0 15px 15px; }
        .req-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee; }
        .req-item:last-child { border: none; }
        .badge-ok { background: #28a745; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: .85rem; }
        .badge-fail { background: #dc3545; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: .85rem; }
        .step-label { font-size: .7rem; margin-top: 4px; color: rgba(255,255,255,.6); }
        .step-col { text-align: center; }
        .step-col.active .step-label { color: #fff; }
    </style>
</head>
<body>
<div class="container">
<div class="install-card">
    <div class="install-header">
        <h2><i class="fas fa-calendar-alt ml-2"></i>نظام إدارة الجداول الدراسية</h2>
        <p>معالج التثبيت — الإصدار 2.0</p>
        <div class="step-indicator">
            <?php for ($i = 1; $i <= $totalSteps; $i++): ?>
                <div class="step-col <?= $step == $i ? 'active' : '' ?>">
                    <div class="step-dot <?= $step > $i ? 'done' : ($step == $i ? 'active' : '') ?>"></div>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="install-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle ml-1"></i><?= $error ?></div>
        <?php endif; ?>

        <?php if ($isInstalled && !in_array($step, ['6'])): ?>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle ml-1"></i>
                النظام مثبت بالفعل. إذا كنت تريد إعادة التثبيت، احذف الملف <code>storage/installed.lock</code> أولاً.
            </div>
            <a href="index.php" class="btn btn-primary btn-block">
                <i class="fas fa-home ml-1"></i> الذهاب للنظام
            </a>

        <?php elseif ($step === '1'): ?>
            <!-- Step 1: Requirements -->
            <h4 class="mb-4"><i class="fas fa-clipboard-check ml-2 text-primary"></i>الخطوة 1: فحص المتطلبات</h4>
            <?php foreach ($requirements as $req): ?>
                <div class="req-item">
                    <span><?= $req['name'] ?></span>
                    <?php if ($req['ok']): ?>
                        <span class="badge-ok"><i class="fas fa-check ml-1"></i>متوفر</span>
                    <?php else: ?>
                        <span class="badge-fail"><i class="fas fa-times ml-1"></i>غير متوفر</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <form method="POST" class="mt-4">
                <button type="submit" class="btn btn-primary btn-lg btn-block" <?= $allPassed ? '' : 'disabled' ?>>
                    <?php if ($allPassed): ?>
                        <i class="fas fa-arrow-left ml-1"></i> التالي — إعداد قاعدة البيانات
                    <?php else: ?>
                        <i class="fas fa-exclamation-triangle ml-1"></i> يرجى توفير جميع المتطلبات أولاً
                    <?php endif; ?>
                </button>
            </form>

        <?php elseif ($step === '2'): ?>
            <!-- Step 2: Database Config -->
            <h4 class="mb-4"><i class="fas fa-database ml-2 text-primary"></i>الخطوة 2: إعداد قاعدة البيانات</h4>
            <form method="POST">
                <div class="form-group">
                    <label>خادم قاعدة البيانات <span class="text-danger">*</span></label>
                    <input type="text" name="host" class="form-control" value="<?= htmlspecialchars($dbCfg['host'] ?? 'localhost') ?>" required>
                </div>
                <div class="form-group">
                    <label>اسم المستخدم <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($dbCfg['username'] ?? 'root') ?>" required>
                </div>
                <div class="form-group">
                    <label>كلمة المرور</label>
                    <input type="password" name="password" class="form-control" value="">
                </div>
                <div class="form-group">
                    <label>اسم قاعدة البيانات <span class="text-danger">*</span></label>
                    <input type="text" name="database" class="form-control" value="<?= htmlspecialchars($dbCfg['database'] ?? 'timetable_v2') ?>" required>
                    <small class="form-text text-muted">سيتم إنشاء قاعدة البيانات تلقائيًا إذا لم تكن موجودة.</small>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <a href="install.php?step=1" class="btn btn-secondary">
                        <i class="fas fa-arrow-right mr-1"></i> السابق
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-arrow-left ml-1"></i> التالي — تهيئة النظام
                    </button>
                </div>
            </form>

        <?php elseif ($step === '3'): ?>
            <!-- Step 3: Run Setup -->
            <h4 class="mb-4"><i class="fas fa-cogs ml-2 text-primary"></i>الخطوة 3: تهيئة قاعدة البيانات</h4>
            <p>سيتم الآن:</p>
            <ul class="pr-4">
                <li>إنشاء جداول قاعدة البيانات (20 جدول)</li>
                <li>إضافة البيانات الأساسية (الأدوار، الصلاحيات، الدرجات العلمية)</li>
            </ul>
            <form method="POST">
                <div class="d-flex justify-content-between mt-4">
                    <a href="install.php?step=2" class="btn btn-secondary">
                        <i class="fas fa-arrow-right mr-1"></i> السابق
                    </a>
                    <button type="submit" class="btn btn-success btn-lg" onclick="this.disabled=true;this.innerHTML='<i class=\'fas fa-spinner fa-spin ml-1\'></i> جارٍ التثبيت...';this.form.submit();">
                        <i class="fas fa-play ml-1"></i> بدء التثبيت
                    </button>
                </div>
            </form>

        <?php elseif ($step === '4'): ?>
            <!-- Step 4: Create Admin Account -->
            <h4 class="mb-4"><i class="fas fa-user-shield ml-2 text-primary"></i>الخطوة 4: إنشاء حساب المدير</h4>
            <p class="text-muted mb-3">أنشئ حساب المدير الرئيسي للنظام.</p>
            <form method="POST">
                <div class="form-group">
                    <label>الاسم الكامل <span class="text-danger">*</span></label>
                    <input type="text" name="admin_name" class="form-control" value="<?= htmlspecialchars($_POST['admin_name'] ?? 'مدير النظام') ?>" required>
                </div>
                <div class="form-group">
                    <label>اسم المستخدم <span class="text-danger">*</span></label>
                    <input type="text" name="admin_username" class="form-control" value="<?= htmlspecialchars($_POST['admin_username'] ?? 'admin') ?>" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>كلمة المرور <span class="text-danger">*</span></label>
                    <input type="password" name="admin_password" class="form-control" required autocomplete="new-password" minlength="6">
                    <small class="form-text text-muted">6 أحرف على الأقل</small>
                </div>
                <div class="form-group">
                    <label>تأكيد كلمة المرور <span class="text-danger">*</span></label>
                    <input type="password" name="admin_password2" class="form-control" required autocomplete="new-password" minlength="6">
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <a href="install.php?step=3" class="btn btn-secondary">
                        <i class="fas fa-arrow-right mr-1"></i> السابق
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-arrow-left ml-1"></i> التالي — إعدادات النظام
                    </button>
                </div>
            </form>

        <?php elseif ($step === '5'): ?>
            <!-- Step 5: System Settings -->
            <h4 class="mb-4"><i class="fas fa-sliders-h ml-2 text-primary"></i>الخطوة 5: إعدادات النظام</h4>
            <form method="POST">
                <div class="form-group">
                    <label>اسم المؤسسة / النظام</label>
                    <input type="text" name="app_name" class="form-control" value="<?= htmlspecialchars($appCfg['name'] ?? 'نظام إدارة الجداول الدراسية') ?>">
                </div>
                <div class="form-group">
                    <label>رابط التطبيق (URL)</label>
                    <input type="text" name="app_url" class="form-control text-left" dir="ltr" value="<?= htmlspecialchars($appCfg['url'] ?? $autoUrl) ?>" placeholder="<?= htmlspecialchars($autoUrl) ?>">
                    <small class="form-text text-muted">الأفضل تركه فارغًا ليبقى النقل بين الخوادم مرنًا. مثال: <code dir="ltr"><?= htmlspecialchars($autoUrl) ?></code></small>
                </div>
                <div class="form-group">
                    <label>المسار الأساسي (Base Path)</label>
                    <input type="text" name="base_path" class="form-control text-left" dir="ltr" value="<?= htmlspecialchars($appCfg['base_path'] ?? $autoBase) ?>" placeholder="<?= htmlspecialchars($autoBase) ?>">
                    <small class="form-text text-muted">الأفضل تركه فارغًا ليُكتشف تلقائيًا حسب مكان النشر. مثال: <code dir="ltr"><?= htmlspecialchars($autoBase) ?></code></small>
                </div>
                <div class="form-group">
                    <label>المنطقة الزمنية</label>
                    <select name="timezone" class="form-control">
                        <?php
                        $zones = ['Asia/Riyadh', 'Asia/Dubai', 'Africa/Cairo', 'Asia/Baghdad', 'Asia/Kuwait', 'Asia/Muscat', 'Asia/Aden', 'Asia/Bahrain', 'Asia/Qatar', 'UTC'];
                        $current = $appCfg['timezone'] ?? 'Asia/Riyadh';
                        foreach ($zones as $tz): ?>
                            <option value="<?= $tz ?>" <?= $tz === $current ? 'selected' : '' ?>><?= $tz ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <a href="install.php?step=4" class="btn btn-secondary">
                        <i class="fas fa-arrow-right mr-1"></i> السابق
                    </a>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-check ml-1"></i> إكمال التثبيت
                    </button>
                </div>
            </form>

        <?php elseif ($step === '6'): ?>
            <!-- Step 6: Done -->
            <div class="text-center">
                <div style="font-size: 4rem; color: #28a745; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="mb-3">تم التثبيت بنجاح!</h3>
                <p class="text-muted mb-4">النظام جاهز للاستخدام. يمكنك الآن تسجيل الدخول باستخدام حساب المدير الذي أنشأته.</p>
                <?php if (!empty($_SESSION['install_admin_user'])): ?>
                <div class="card card-body bg-light mb-4">
                    <strong>اسم المستخدم:</strong> <code><?= htmlspecialchars($_SESSION['install_admin_user']) ?></code>
                </div>
                <?php unset($_SESSION['install_admin_user']); ?>
                <?php endif; ?>
                <div class="alert alert-info text-right">
                    <i class="fas fa-lightbulb ml-1"></i>
                    <strong>نصائح البدء:</strong>
                    <ul class="mb-0 mt-2 pr-3">
                        <li>أضف الأقسام والمستويات والقاعات أولاً من قائمة "البيانات الأساسية"</li>
                        <li>أضف أعضاء هيئة التدريس ثم المقررات والشُعب</li>
                        <li>أنشئ الجدول الدراسي من قائمة "الجدولة"</li>
                        <li>صدّر الجدول كـ PDF أو Excel من صفحة "الجدول الدراسي"</li>
                    </ul>
                </div>
                <a href="index.php" class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-sign-in-alt ml-1"></i> الانتقال لتسجيل الدخول
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
