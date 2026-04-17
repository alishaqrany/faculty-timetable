<?php
namespace App\Services\Cloud;

/**
 * GoogleDriveService - التكامل مع Google Drive API
 */
class GoogleDriveService
{
    private static ?array $config = null;
    private const SCOPES = ['https://www.googleapis.com/auth/drive.file'];
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const API_URL = 'https://www.googleapis.com/drive/v3';
    private const UPLOAD_URL = 'https://www.googleapis.com/upload/drive/v3';

    /**
     * تحميل الإعدادات
     */
    private static function loadConfig(): array
    {
        if (self::$config === null) {
            $cloud = require APP_ROOT . '/config/cloud.php';
            self::$config = $cloud['google_drive'] ?? [];
        }
        return self::$config;
    }

    /**
     * هل الخدمة مفعلة
     */
    public static function isEnabled(): bool
    {
        $config = self::loadConfig();
        return !empty($config['enabled']) && !empty($config['client_id']);
    }

    /**
     * هل تم المصادقة
     */
    public static function isAuthenticated(): bool
    {
        $config = self::loadConfig();
        return !empty($config['access_token']);
    }

    /**
     * الحصول على رابط المصادقة
     */
    public static function getAuthUrl(): string
    {
        $config = self::loadConfig();
        
        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'] ?: url('/backups/google-drive/callback'),
            'response_type' => 'code',
            'scope' => implode(' ', self::SCOPES),
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];
        
        return self::AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * معالجة Callback من Google
     */
    public static function handleCallback(string $code): array
    {
        $config = self::loadConfig();
        
        $response = self::httpPost(self::TOKEN_URL, [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code,
            'redirect_uri' => $config['redirect_uri'] ?: url('/backups/google-drive/callback'),
            'grant_type' => 'authorization_code',
        ]);
        
        if (!isset($response['access_token'])) {
            return ['success' => false, 'error' => 'فشل الحصول على Access Token'];
        }
        
        // حفظ التوكنات
        self::saveTokens($response['access_token'], $response['refresh_token'] ?? '');
        
        return ['success' => true];
    }

    /**
     * تجديد Access Token
     */
    public static function refreshToken(): bool
    {
        $config = self::loadConfig();
        
        if (empty($config['refresh_token'])) {
            return false;
        }
        
        $response = self::httpPost(self::TOKEN_URL, [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'refresh_token' => $config['refresh_token'],
            'grant_type' => 'refresh_token',
        ]);
        
        if (!isset($response['access_token'])) {
            return false;
        }
        
        self::saveTokens($response['access_token'], $config['refresh_token']);
        
        return true;
    }

    /**
     * رفع ملف إلى Google Drive
     */
    public static function uploadFile(string $localPath, ?string $remoteName = null): array
    {
        $config = self::loadConfig();
        
        if (!self::isAuthenticated()) {
            return ['success' => false, 'error' => 'غير مصادق عليه'];
        }
        
        if (!file_exists($localPath)) {
            return ['success' => false, 'error' => 'الملف غير موجود'];
        }
        
        $filename = $remoteName ?? basename($localPath);
        $content = file_get_contents($localPath);
        $mimeType = self::getMimeType($localPath);
        
        // إنشاء metadata
        $metadata = [
            'name' => $filename,
        ];
        
        if (!empty($config['folder_id'])) {
            $metadata['parents'] = [$config['folder_id']];
        }
        
        // Multipart upload
        $boundary = '-------' . uniqid();
        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $body .= json_encode($metadata) . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: {$mimeType}\r\n\r\n";
        $body .= $content . "\r\n";
        $body .= "--{$boundary}--";
        
        $response = self::httpRequest(
            'POST',
            self::UPLOAD_URL . '/files?uploadType=multipart',
            $body,
            [
                'Authorization: Bearer ' . $config['access_token'],
                'Content-Type: multipart/related; boundary=' . $boundary,
            ]
        );
        
        if (isset($response['error'])) {
            // محاولة تجديد التوكن
            if (self::refreshToken()) {
                return self::uploadFile($localPath, $remoteName);
            }
            return ['success' => false, 'error' => $response['error']['message'] ?? 'فشل الرفع'];
        }
        
        return [
            'success' => true,
            'file_id' => $response['id'] ?? '',
            'name' => $response['name'] ?? $filename,
        ];
    }

    /**
     * الحصول على قائمة الملفات
     */
    public static function listFiles(): array
    {
        $config = self::loadConfig();
        
        if (!self::isAuthenticated()) {
            return ['success' => false, 'error' => 'غير مصادق عليه', 'files' => []];
        }
        
        $query = "mimeType != 'application/vnd.google-apps.folder'";
        if (!empty($config['folder_id'])) {
            $query .= " and '{$config['folder_id']}' in parents";
        }
        
        $params = [
            'q' => $query,
            'fields' => 'files(id,name,size,createdTime,mimeType)',
            'orderBy' => 'createdTime desc',
            'pageSize' => 50,
        ];
        
        $response = self::httpRequest(
            'GET',
            self::API_URL . '/files?' . http_build_query($params),
            null,
            ['Authorization: Bearer ' . $config['access_token']]
        );
        
        if (isset($response['error'])) {
            if (self::refreshToken()) {
                return self::listFiles();
            }
            return ['success' => false, 'error' => $response['error']['message'] ?? 'فشل جلب الملفات', 'files' => []];
        }
        
        return [
            'success' => true,
            'files' => $response['files'] ?? [],
        ];
    }

    /**
     * تنزيل ملف من Google Drive
     */
    public static function downloadFile(string $fileId, string $localPath): array
    {
        $config = self::loadConfig();
        
        if (!self::isAuthenticated()) {
            return ['success' => false, 'error' => 'غير مصادق عليه'];
        }
        
        $content = self::httpRequest(
            'GET',
            self::API_URL . '/files/' . $fileId . '?alt=media',
            null,
            ['Authorization: Bearer ' . $config['access_token']],
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
     * حذف ملف من Google Drive
     */
    public static function deleteFile(string $fileId): array
    {
        $config = self::loadConfig();
        
        if (!self::isAuthenticated()) {
            return ['success' => false, 'error' => 'غير مصادق عليه'];
        }
        
        $response = self::httpRequest(
            'DELETE',
            self::API_URL . '/files/' . $fileId,
            null,
            ['Authorization: Bearer ' . $config['access_token']]
        );
        
        return ['success' => true];
    }

    /**
     * حفظ التوكنات في الإعدادات
     */
    private static function saveTokens(string $accessToken, string $refreshToken): void
    {
        $configPath = APP_ROOT . '/config/cloud.php';
        $config = require $configPath;
        
        $config['google_drive']['access_token'] = $accessToken;
        if ($refreshToken) {
            $config['google_drive']['refresh_token'] = $refreshToken;
        }
        
        $content = "<?php\n/**\n * Cloud Services Configuration\n */\n\nreturn " . var_export($config, true) . ";\n";
        file_put_contents($configPath, $content);
        
        self::$config = null; // Reset cache
    }

    /**
     * طلب HTTP
     */
    private static function httpRequest(string $method, string $url, $body = null, array $headers = [], bool $raw = false)
    {
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
     * طلب POST
     */
    private static function httpPost(string $url, array $data): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
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
