<?php
namespace App\Services\Cloud;

/**
 * SupabaseService - التكامل مع Supabase
 * يدعم:
 * - رفع الملفات إلى Storage
 * - مزامنة الجداول مع قاعدة البيانات
 */
class SupabaseService
{
    private static ?array $config = null;

    /**
     * تحميل الإعدادات
     */
    private static function loadConfig(): array
    {
        if (self::$config === null) {
            $cloud = require APP_ROOT . '/config/cloud.php';
            self::$config = $cloud['supabase'] ?? [];
        }
        return self::$config;
    }

    /**
     * هل الخدمة مفعلة
     */
    public static function isEnabled(): bool
    {
        $config = self::loadConfig();
        return !empty($config['enabled']) && !empty($config['url']) && !empty($config['anon_key']);
    }

    /**
     * اختبار الاتصال
     */
    public static function testConnection(): array
    {
        $config = self::loadConfig();
        
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'الخدمة غير مفعلة'];
        }
        
        $response = self::httpRequest('GET', '/rest/v1/', [], true);
        
        return [
            'success' => $response !== false,
            'error' => $response === false ? 'فشل الاتصال بـ Supabase' : null,
        ];
    }

    /**
     * رفع ملف إلى Storage
     */
    public static function uploadFile(string $localPath, ?string $remoteName = null): array
    {
        $config = self::loadConfig();
        
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'الخدمة غير مفعلة'];
        }
        
        if (!file_exists($localPath)) {
            return ['success' => false, 'error' => 'الملف غير موجود'];
        }
        
        $filename = $remoteName ?? basename($localPath);
        $bucket = $config['bucket'] ?? 'backups';
        $content = file_get_contents($localPath);
        $mimeType = self::getMimeType($localPath);
        
        $response = self::httpRequest(
            'POST',
            "/storage/v1/object/{$bucket}/{$filename}",
            $content,
            false,
            [
                'Content-Type: ' . $mimeType,
                'x-upsert: true',
            ]
        );
        
        if (isset($response['error']) || isset($response['statusCode'])) {
            return ['success' => false, 'error' => $response['error'] ?? $response['message'] ?? 'فشل الرفع'];
        }
        
        return [
            'success' => true,
            'path' => $response['Key'] ?? $filename,
            'url' => $config['url'] . "/storage/v1/object/public/{$bucket}/{$filename}",
        ];
    }

    /**
     * الحصول على قائمة الملفات من Storage
     */
    public static function listFiles(): array
    {
        $config = self::loadConfig();
        
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'الخدمة غير مفعلة', 'files' => []];
        }
        
        $bucket = $config['bucket'] ?? 'backups';
        
        $response = self::httpRequest(
            'POST',
            "/storage/v1/object/list/{$bucket}",
            json_encode(['prefix' => '', 'limit' => 100, 'offset' => 0]),
            false,
            ['Content-Type: application/json']
        );
        
        if (!is_array($response) || isset($response['error'])) {
            return ['success' => false, 'error' => $response['error'] ?? 'فشل جلب الملفات', 'files' => []];
        }
        
        $files = [];
        foreach ($response as $file) {
            if (isset($file['name'])) {
                $files[] = [
                    'name' => $file['name'],
                    'size' => $file['metadata']['size'] ?? 0,
                    'created_at' => $file['created_at'] ?? '',
                    'updated_at' => $file['updated_at'] ?? '',
                ];
            }
        }
        
        return ['success' => true, 'files' => $files];
    }

    /**
     * تنزيل ملف من Storage
     */
    public static function downloadFile(string $remoteName, string $localPath): array
    {
        $config = self::loadConfig();
        
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'الخدمة غير مفعلة'];
        }
        
        $bucket = $config['bucket'] ?? 'backups';
        
        $content = self::httpRequest(
            'GET',
            "/storage/v1/object/{$bucket}/{$remoteName}",
            null,
            true
        );
        
        if ($content === false) {
            return ['success' => false, 'error' => 'فشل تنزيل الملف'];
        }
        
        if (file_put_contents($localPath, $content) === false) {
            return ['success' => false, 'error' => 'فشل حفظ الملف'];
        }
        
        return ['success' => true, 'path' => $localPath];
    }

    /**
     * حذف ملف من Storage
     */
    public static function deleteFile(string $remoteName): array
    {
        $config = self::loadConfig();
        
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'الخدمة غير مفعلة'];
        }
        
        $bucket = $config['bucket'] ?? 'backups';
        
        $response = self::httpRequest(
            'DELETE',
            "/storage/v1/object/{$bucket}",
            json_encode(['prefixes' => [$remoteName]]),
            false,
            ['Content-Type: application/json']
        );
        
        return ['success' => true];
    }

    /**
     * مزامنة جدول معين مع Supabase
     */
    public static function syncTable(string $table, array $data, string $primaryKey = 'id'): array
    {
        $config = self::loadConfig();
        
        if (!self::isEnabled() || empty($config['sync_tables'])) {
            return ['success' => false, 'error' => 'المزامنة غير مفعلة'];
        }
        
        // استخدام upsert
        $response = self::httpRequest(
            'POST',
            "/rest/v1/{$table}",
            json_encode($data),
            false,
            [
                'Content-Type: application/json',
                'Prefer: resolution=merge-duplicates',
            ]
        );
        
        if (isset($response['error']) || isset($response['code'])) {
            return ['success' => false, 'error' => $response['message'] ?? $response['error'] ?? 'فشل المزامنة'];
        }
        
        return ['success' => true, 'synced' => count($data)];
    }

    /**
     * جلب بيانات جدول من Supabase
     */
    public static function fetchTable(string $table): array
    {
        $config = self::loadConfig();
        
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'الخدمة غير مفعلة', 'data' => []];
        }
        
        $response = self::httpRequest(
            'GET',
            "/rest/v1/{$table}?select=*",
            null,
            false,
            ['Content-Type: application/json']
        );
        
        if (isset($response['error']) || isset($response['code'])) {
            return ['success' => false, 'error' => $response['message'] ?? 'فشل جلب البيانات', 'data' => []];
        }
        
        return ['success' => true, 'data' => $response];
    }

    /**
     * مزامنة جميع الجداول المحددة
     */
    public static function syncAllTables(): array
    {
        $cloudConfig = require APP_ROOT . '/config/cloud.php';
        $tables = $cloudConfig['sync']['tables_to_sync'] ?? [];
        
        $db = \Database::getInstance();
        $results = [];
        
        foreach ($tables as $table) {
            try {
                $data = $db->fetchAll("SELECT * FROM `{$table}`");
                $result = self::syncTable($table, $data);
                $results[$table] = $result;
            } catch (\Throwable $e) {
                $results[$table] = ['success' => false, 'error' => $e->getMessage()];
            }
        }
        
        $successCount = count(array_filter($results, fn($r) => $r['success']));
        
        return [
            'success' => $successCount === count($tables),
            'total' => count($tables),
            'synced' => $successCount,
            'details' => $results,
        ];
    }

    /**
     * طلب HTTP
     */
    private static function httpRequest(string $method, string $endpoint, $body = null, bool $raw = false, array $extraHeaders = [])
    {
        $config = self::loadConfig();
        
        $url = rtrim($config['url'], '/') . $endpoint;
        $key = $config['service_role_key'] ?: $config['anon_key'];
        
        $headers = array_merge([
            'apikey: ' . $key,
            'Authorization: Bearer ' . $key,
        ], $extraHeaders);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($raw) {
            return $response;
        }
        
        return json_decode($response, true) ?? [];
    }

    /**
     * الحصول على نوع MIME
     */
    private static function getMimeType(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeTypes = [
            'sql' => 'application/sql',
            'xls' => 'application/vnd.ms-excel',
            'zip' => 'application/zip',
            'json' => 'application/json',
        ];
        
        return $mimeTypes[$ext] ?? 'application/octet-stream';
    }
}
