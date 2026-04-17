<?php
namespace App\Services\Cloud;

/**
 * FirebaseService - التكامل مع Firebase
 * يدعم:
 * - رفع الملفات إلى Cloud Storage
 * - مزامنة البيانات مع Firestore
 * - مزامنة البيانات مع Realtime Database
 */
class FirebaseService
{
    private static ?array $config = null;
    private static ?string $accessToken = null;

    /**
     * تحميل الإعدادات
     */
    private static function loadConfig(): array
    {
        if (self::$config === null) {
            $cloud = require APP_ROOT . '/config/cloud.php';
            self::$config = $cloud['firebase'] ?? [];
        }
        return self::$config;
    }

    /**
     * هل الخدمة مفعلة
     */
    public static function isEnabled(): bool
    {
        $config = self::loadConfig();
        return !empty($config['enabled']) && !empty($config['project_id']);
    }

    /**
     * الحصول على Access Token من Service Account
     */
    private static function getAccessToken(): ?string
    {
        if (self::$accessToken !== null) {
            return self::$accessToken;
        }
        
        $config = self::loadConfig();
        
        if (empty($config['service_account_json'])) {
            return null;
        }
        
        $saPath = $config['service_account_json'];
        if (!file_exists($saPath)) {
            $saPath = APP_ROOT . '/' . $saPath;
        }
        
        if (!file_exists($saPath)) {
            return null;
        }
        
        $sa = json_decode(file_get_contents($saPath), true);
        if (!$sa) {
            return null;
        }
        
        // إنشاء JWT
        $now = time();
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss' => $sa['client_email'],
            'sub' => $sa['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.database https://www.googleapis.com/auth/datastore https://www.googleapis.com/auth/devstorage.full_control',
        ]));
        
        $signatureInput = $header . '.' . $payload;
        $signature = '';
        
        if (!openssl_sign($signatureInput, $signature, $sa['private_key'], OPENSSL_ALGO_SHA256)) {
            return null;
        }
        
        $jwt = $signatureInput . '.' . base64_encode($signature);
        
        // طلب Access Token
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]));
        
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        self::$accessToken = $response['access_token'] ?? null;
        
        return self::$accessToken;
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
        
        // اختبار الاتصال بـ Realtime Database
        if (!empty($config['database_url'])) {
            $url = rtrim($config['database_url'], '/') . '/.json';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return ['success' => true];
            }
        }
        
        // اختبار باستخدام Service Account
        $token = self::getAccessToken();
        if ($token) {
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'فشل الاتصال بـ Firebase'];
    }

    /**
     * رفع ملف إلى Cloud Storage
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
        $bucket = $config['storage_bucket'] ?? ($config['project_id'] . '.appspot.com');
        $content = file_get_contents($localPath);
        $mimeType = self::getMimeType($localPath);
        
        $token = self::getAccessToken();
        if (!$token && !empty($config['api_key'])) {
            // استخدام API Key للرفع العام (لا يُنصح به في الإنتاج)
            return self::uploadWithApiKey($localPath, $filename);
        }
        
        if (!$token) {
            return ['success' => false, 'error' => 'فشل المصادقة'];
        }
        
        $url = "https://storage.googleapis.com/upload/storage/v1/b/{$bucket}/o?uploadType=media&name=backups/{$filename}";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: ' . $mimeType,
        ]);
        
        $response = json_decode(curl_exec($ch), true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'name' => $response['name'] ?? $filename,
                'url' => $response['mediaLink'] ?? '',
            ];
        }
        
        return ['success' => false, 'error' => $response['error']['message'] ?? 'فشل الرفع'];
    }

    /**
     * الحصول على قائمة الملفات
     */
    public static function listFiles(): array
    {
        $config = self::loadConfig();
        
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'الخدمة غير مفعلة', 'files' => []];
        }
        
        $token = self::getAccessToken();
        if (!$token) {
            return ['success' => false, 'error' => 'فشل المصادقة', 'files' => []];
        }
        
        $bucket = $config['storage_bucket'] ?? ($config['project_id'] . '.appspot.com');
        $url = "https://storage.googleapis.com/storage/v1/b/{$bucket}/o?prefix=backups/";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
        ]);
        
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        $files = [];
        foreach ($response['items'] ?? [] as $item) {
            $files[] = [
                'name' => str_replace('backups/', '', $item['name']),
                'full_name' => $item['name'],
                'size' => (int)($item['size'] ?? 0),
                'created_at' => $item['timeCreated'] ?? '',
                'updated_at' => $item['updated'] ?? '',
            ];
        }
        
        return ['success' => true, 'files' => $files];
    }

    /**
     * تنزيل ملف من Cloud Storage
     */
    public static function downloadFile(string $remoteName, string $localPath): array
    {
        $config = self::loadConfig();
        
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'الخدمة غير مفعلة'];
        }
        
        $token = self::getAccessToken();
        if (!$token) {
            return ['success' => false, 'error' => 'فشل المصادقة'];
        }
        
        $bucket = $config['storage_bucket'] ?? ($config['project_id'] . '.appspot.com');
        $objectName = urlencode("backups/{$remoteName}");
        $url = "https://storage.googleapis.com/storage/v1/b/{$bucket}/o/{$objectName}?alt=media";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
        ]);
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'فشل تنزيل الملف'];
        }
        
        if (file_put_contents($localPath, $content) === false) {
            return ['success' => false, 'error' => 'فشل حفظ الملف'];
        }
        
        return ['success' => true, 'path' => $localPath];
    }

    /**
     * حذف ملف من Cloud Storage
     */
    public static function deleteFile(string $remoteName): array
    {
        $config = self::loadConfig();
        
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'الخدمة غير مفعلة'];
        }
        
        $token = self::getAccessToken();
        if (!$token) {
            return ['success' => false, 'error' => 'فشل المصادقة'];
        }
        
        $bucket = $config['storage_bucket'] ?? ($config['project_id'] . '.appspot.com');
        $objectName = urlencode("backups/{$remoteName}");
        $url = "https://storage.googleapis.com/storage/v1/b/{$bucket}/o/{$objectName}";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
        ]);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ['success' => $httpCode === 204 || $httpCode === 200];
    }

    /**
     * حفظ بيانات في Realtime Database
     */
    public static function saveToDatabase(string $path, array $data): array
    {
        $config = self::loadConfig();
        
        if (!self::isEnabled() || empty($config['database_url'])) {
            return ['success' => false, 'error' => 'Realtime Database غير مفعل'];
        }
        
        $url = rtrim($config['database_url'], '/') . '/' . trim($path, '/') . '.json';
        
        $token = self::getAccessToken();
        $headers = ['Content-Type: application/json'];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = json_decode(curl_exec($ch), true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => $response['error'] ?? 'فشل الحفظ'];
    }

    /**
     * جلب بيانات من Realtime Database
     */
    public static function fetchFromDatabase(string $path): array
    {
        $config = self::loadConfig();
        
        if (!self::isEnabled() || empty($config['database_url'])) {
            return ['success' => false, 'error' => 'Realtime Database غير مفعل', 'data' => null];
        }
        
        $url = rtrim($config['database_url'], '/') . '/' . trim($path, '/') . '.json';
        
        $token = self::getAccessToken();
        $headers = [];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = json_decode(curl_exec($ch), true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'data' => $response];
        }
        
        return ['success' => false, 'error' => 'فشل جلب البيانات', 'data' => null];
    }

    /**
     * مزامنة جدول مع Realtime Database
     */
    public static function syncTable(string $table): array
    {
        $config = self::loadConfig();
        
        if (!self::isEnabled() || empty($config['sync_tables'])) {
            return ['success' => false, 'error' => 'المزامنة غير مفعلة'];
        }
        
        $db = \Database::getInstance();
        
        try {
            $data = $db->fetchAll("SELECT * FROM `{$table}`");
            $indexed = [];
            foreach ($data as $row) {
                $id = $row['id'] ?? uniqid();
                $indexed[$id] = $row;
            }
            
            return self::saveToDatabase("tables/{$table}", $indexed);
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * مزامنة جميع الجداول
     */
    public static function syncAllTables(): array
    {
        $cloudConfig = require APP_ROOT . '/config/cloud.php';
        $tables = $cloudConfig['sync']['tables_to_sync'] ?? [];
        
        $results = [];
        
        foreach ($tables as $table) {
            $results[$table] = self::syncTable($table);
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

    /**
     * رفع باستخدام API Key (بديل بسيط)
     */
    private static function uploadWithApiKey(string $localPath, string $filename): array
    {
        $config = self::loadConfig();
        $bucket = $config['storage_bucket'] ?? ($config['project_id'] . '.appspot.com');
        
        // Firebase Storage REST API with API Key
        $url = "https://firebasestorage.googleapis.com/v0/b/{$bucket}/o?name=backups%2F{$filename}";
        
        $content = file_get_contents($localPath);
        $mimeType = self::getMimeType($localPath);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: ' . $mimeType,
        ]);
        
        $response = json_decode(curl_exec($ch), true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'name' => $response['name'] ?? $filename,
            ];
        }
        
        return ['success' => false, 'error' => $response['error']['message'] ?? 'فشل الرفع'];
    }
}
