<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Services/BackupService.php';
require_once APP_ROOT . '/app/Services/AuditService.php';
require_once APP_ROOT . '/app/Services/Cloud/GoogleDriveService.php';
require_once APP_ROOT . '/app/Services/Cloud/SupabaseService.php';
require_once APP_ROOT . '/app/Services/Cloud/FirebaseService.php';

use App\Services\BackupService;
use App\Services\AuditService;
use App\Services\Cloud\GoogleDriveService;
use App\Services\Cloud\SupabaseService;
use App\Services\Cloud\FirebaseService;

class BackupController extends \Controller
{
    /**
     * صفحة إدارة النسخ الاحتياطية
     */
    public function index(): void
    {
        $this->authorize('backup.view');
        
        $localBackups = BackupService::getLocalBackups();
        $stats = BackupService::getBackupStats();
        $cloudConfig = BackupService::getCloudConfig();
        
        // حالة الخدمات السحابية
        $cloudStatus = [
            'google_drive' => [
                'enabled' => GoogleDriveService::isEnabled(),
                'authenticated' => GoogleDriveService::isAuthenticated(),
            ],
            'supabase' => [
                'enabled' => SupabaseService::isEnabled(),
            ],
            'firebase' => [
                'enabled' => FirebaseService::isEnabled(),
            ],
        ];
        
        $this->render('backups.index', compact('localBackups', 'stats', 'cloudConfig', 'cloudStatus'));
    }

    /**
     * إنشاء نسخة احتياطية SQL
     */
    public function createSqlBackup(): void
    {
        $this->authorize('backup.create');
        $this->validateCsrf();
        
        $result = BackupService::createSqlBackup();
        
        if ($result['success']) {
            AuditService::log('CREATE', 'backup', null, null, ['type' => 'sql', 'filename' => $result['filename']]);
            $this->redirect('/backups', "تم إنشاء نسخة احتياطية SQL بنجاح: {$result['filename']}");
        } else {
            $this->redirect('/backups', 'فشل إنشاء النسخة الاحتياطية', 'error');
        }
    }

    /**
     * إنشاء نسخة احتياطية Excel
     */
    public function createExcelBackup(): void
    {
        $this->authorize('backup.create');
        $this->validateCsrf();
        
        $result = BackupService::createExcelBackup();
        
        if ($result['success']) {
            AuditService::log('CREATE', 'backup', null, null, ['type' => 'excel', 'filename' => $result['filename']]);
            $this->redirect('/backups', "تم إنشاء نسخة احتياطية Excel بنجاح: {$result['filename']}");
        } else {
            $this->redirect('/backups', 'فشل إنشاء النسخة الاحتياطية', 'error');
        }
    }

    /**
     * تنزيل نسخة احتياطية
     */
    public function download(string $filename): void
    {
        $this->authorize('backup.view');
        
        AuditService::log('READ', 'backup', null, null, ['action' => 'download', 'filename' => $filename]);
        BackupService::downloadBackup($filename);
    }

    /**
     * حذف نسخة احتياطية محلية
     */
    public function delete(string $filename): void
    {
        $this->authorize('backup.delete');
        $this->validateCsrf();
        
        if (BackupService::deleteLocalBackup($filename)) {
            AuditService::log('DELETE', 'backup', null, null, ['filename' => $filename]);
            $this->redirect('/backups', 'تم حذف النسخة الاحتياطية بنجاح');
        } else {
            $this->redirect('/backups', 'فشل حذف النسخة الاحتياطية', 'error');
        }
    }

    /**
     * استعادة من نسخة احتياطية
     */
    public function restore(string $filename): void
    {
        $this->authorize('backup.restore');
        $this->validateCsrf();
        
        $result = BackupService::restoreFromSql($filename);
        
        if ($result['success']) {
            AuditService::log('UPDATE', 'backup', null, null, ['action' => 'restore', 'filename' => $filename]);
            $this->redirect('/backups', 'تم استعادة النسخة الاحتياطية بنجاح ✓');
        } else {
            $this->redirect('/backups', 'فشل الاستعادة: ' . ($result['error'] ?? ''), 'error');
        }
    }

    /**
     * رفع نسخة احتياطية إلى جميع الخدمات السحابية المفعلة
     */
    public function uploadToCloud(string $filename): void
    {
        $this->authorize('backup.cloud');
        $this->validateCsrf();
        
        $path = BackupService::ensureBackupDir() . '/' . $filename;
        
        if (!file_exists($path)) {
            $this->redirect('/backups', 'الملف غير موجود', 'error');
            return;
        }
        
        $results = [];
        
        // Google Drive
        if (GoogleDriveService::isEnabled() && GoogleDriveService::isAuthenticated()) {
            $results['google_drive'] = GoogleDriveService::uploadFile($path, $filename);
        }
        
        // Supabase
        if (SupabaseService::isEnabled()) {
            $results['supabase'] = SupabaseService::uploadFile($path, $filename);
        }
        
        // Firebase
        if (FirebaseService::isEnabled()) {
            $results['firebase'] = FirebaseService::uploadFile($path, $filename);
        }
        
        $successCount = count(array_filter($results, fn($r) => $r['success'] ?? false));
        $totalCount = count($results);
        
        if ($totalCount === 0) {
            $this->redirect('/backups', 'لا توجد خدمات سحابية مفعلة', 'warning');
            return;
        }
        
        AuditService::log('CREATE', 'cloud_backup', null, null, ['filename' => $filename, 'results' => $results]);
        
        if ($successCount === $totalCount) {
            $this->redirect('/backups', "تم رفع النسخة الاحتياطية إلى {$successCount} خدمة سحابية بنجاح ✓");
        } elseif ($successCount > 0) {
            $this->redirect('/backups', "تم الرفع إلى {$successCount} من {$totalCount} خدمات", 'warning');
        } else {
            $this->redirect('/backups', 'فشل الرفع إلى جميع الخدمات السحابية', 'error');
        }
    }

    /**
     * مزامنة البيانات مع Supabase
     */
    public function syncSupabase(): void
    {
        $this->authorize('backup.sync');
        $this->validateCsrf();
        
        if (!SupabaseService::isEnabled()) {
            $this->redirect('/backups', 'خدمة Supabase غير مفعلة', 'error');
            return;
        }
        
        $result = SupabaseService::syncAllTables();
        
        AuditService::log('UPDATE', 'sync', null, null, ['service' => 'supabase', 'result' => $result]);
        
        if ($result['success']) {
            $this->redirect('/backups', "تمت مزامنة {$result['synced']} جدول مع Supabase بنجاح ✓");
        } else {
            $this->redirect('/backups', "تمت مزامنة {$result['synced']} من {$result['total']} جدول", 'warning');
        }
    }

    /**
     * مزامنة البيانات مع Firebase
     */
    public function syncFirebase(): void
    {
        $this->authorize('backup.sync');
        $this->validateCsrf();
        
        if (!FirebaseService::isEnabled()) {
            $this->redirect('/backups', 'خدمة Firebase غير مفعلة', 'error');
            return;
        }
        
        $result = FirebaseService::syncAllTables();
        
        AuditService::log('UPDATE', 'sync', null, null, ['service' => 'firebase', 'result' => $result]);
        
        if ($result['success']) {
            $this->redirect('/backups', "تمت مزامنة {$result['synced']} جدول مع Firebase بنجاح ✓");
        } else {
            $this->redirect('/backups', "تمت مزامنة {$result['synced']} من {$result['total']} جدول", 'warning');
        }
    }

    /**
     * صفحة إعدادات السحابة
     */
    public function cloudSettings(): void
    {
        $this->authorize('backup.settings');
        
        $cloudConfig = BackupService::getCloudConfig();
        
        $this->render('backups.cloud-settings', compact('cloudConfig'));
    }

    /**
     * حفظ إعدادات السحابة
     */
    public function saveCloudSettings(): void
    {
        $this->authorize('backup.settings');
        $this->validateCsrf();
        
        $settings = [];
        
        // Google Drive
        if ($this->request->has('google_drive')) {
            $gd = $this->request->input('google_drive', []);
            $settings['google_drive'] = [
                'enabled' => !empty($gd['enabled']),
                'client_id' => $gd['client_id'] ?? '',
                'client_secret' => $gd['client_secret'] ?? '',
                'folder_id' => $gd['folder_id'] ?? '',
            ];
        }
        
        // Supabase
        if ($this->request->has('supabase')) {
            $sb = $this->request->input('supabase', []);
            $settings['supabase'] = [
                'enabled' => !empty($sb['enabled']),
                'url' => $sb['url'] ?? '',
                'anon_key' => $sb['anon_key'] ?? '',
                'service_role_key' => $sb['service_role_key'] ?? '',
                'bucket' => $sb['bucket'] ?? 'backups',
                'sync_tables' => !empty($sb['sync_tables']),
            ];
        }
        
        // Firebase
        if ($this->request->has('firebase')) {
            $fb = $this->request->input('firebase', []);
            $settings['firebase'] = [
                'enabled' => !empty($fb['enabled']),
                'project_id' => $fb['project_id'] ?? '',
                'api_key' => $fb['api_key'] ?? '',
                'storage_bucket' => $fb['storage_bucket'] ?? '',
                'database_url' => $fb['database_url'] ?? '',
                'sync_tables' => !empty($fb['sync_tables']),
            ];
        }
        
        // Backup settings
        if ($this->request->has('backup')) {
            $bk = $this->request->input('backup', []);
            $settings['backup'] = [
                'max_local_backups' => (int)($bk['max_local_backups'] ?? 10),
                'auto_backup' => !empty($bk['auto_backup']),
                'auto_backup_interval' => $bk['auto_backup_interval'] ?? 'daily',
            ];
        }
        
        if (BackupService::updateCloudConfig($settings)) {
            AuditService::log('UPDATE', 'cloud_settings');
            $this->redirect('/backups/cloud-settings', 'تم حفظ الإعدادات بنجاح ✓');
        } else {
            $this->redirect('/backups/cloud-settings', 'فشل حفظ الإعدادات', 'error');
        }
    }

    /**
     * بدء المصادقة مع Google Drive
     */
    public function googleDriveAuth(): void
    {
        $this->authorize('backup.settings');
        
        if (!GoogleDriveService::isEnabled()) {
            $this->redirect('/backups/cloud-settings', 'يرجى تفعيل Google Drive وإدخال بيانات الاعتماد أولاً', 'error');
            return;
        }
        
        $authUrl = GoogleDriveService::getAuthUrl();
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * معالجة Callback من Google Drive
     */
    public function googleDriveCallback(): void
    {
        $code = $this->request->input('code', '');
        
        if (empty($code)) {
            $this->redirect('/backups/cloud-settings', 'فشل المصادقة مع Google Drive', 'error');
            return;
        }
        
        $result = GoogleDriveService::handleCallback($code);
        
        if ($result['success']) {
            AuditService::log('UPDATE', 'google_drive_auth');
            $this->redirect('/backups/cloud-settings', 'تم ربط Google Drive بنجاح ✓');
        } else {
            $this->redirect('/backups/cloud-settings', 'فشل المصادقة: ' . ($result['error'] ?? ''), 'error');
        }
    }

    /**
     * الحصول على ملفات Google Drive
     */
    public function googleDriveFiles(): void
    {
        $this->authorize('backup.view');
        
        $result = GoogleDriveService::listFiles();
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * الحصول على ملفات Supabase
     */
    public function supabaseFiles(): void
    {
        $this->authorize('backup.view');
        
        $result = SupabaseService::listFiles();
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * الحصول على ملفات Firebase
     */
    public function firebaseFiles(): void
    {
        $this->authorize('backup.view');
        
        $result = FirebaseService::listFiles();
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * تنزيل من السحابة إلى المحلي
     */
    public function downloadFromCloud(): void
    {
        $this->authorize('backup.cloud');
        $this->validateCsrf();
        
        $service = $this->request->input('service', '');
        $filename = $this->request->input('filename', '');
        $fileId = $this->request->input('file_id', '');
        
        if (empty($filename)) {
            $this->redirect('/backups', 'اسم الملف مطلوب', 'error');
            return;
        }
        
        $localPath = BackupService::ensureBackupDir() . '/' . basename($filename);
        
        $result = ['success' => false, 'error' => 'خدمة غير معروفة'];
        
        switch ($service) {
            case 'google_drive':
                if ($fileId) {
                    $result = GoogleDriveService::downloadFile($fileId, $localPath);
                }
                break;
            case 'supabase':
                $result = SupabaseService::downloadFile($filename, $localPath);
                break;
            case 'firebase':
                $result = FirebaseService::downloadFile($filename, $localPath);
                break;
        }
        
        if ($result['success']) {
            AuditService::log('CREATE', 'backup', null, null, ['action' => 'download_from_cloud', 'service' => $service, 'filename' => $filename]);
            $this->redirect('/backups', 'تم تنزيل الملف بنجاح ✓');
        } else {
            $this->redirect('/backups', 'فشل التنزيل: ' . ($result['error'] ?? ''), 'error');
        }
    }
}
