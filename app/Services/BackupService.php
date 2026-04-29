<?php
namespace App\Services;

/**
 * BackupService - إدارة النسخ الاحتياطي المحلي والسحابي
 */
class BackupService
{
    private const TABLES = [
        'roles',
        'permissions',
        'role_permissions',
        'academic_degrees',
        'departments',
        'levels',
        'academic_years',
        'semesters',
        'priority_categories',
        'priority_groups',
        'faculty_members',
        'users',
        'priority_group_members',
        'priority_exceptions',
        'subjects',
        'divisions',
        'sections',
        'classrooms',
        'sessions',
        'member_courses',
        'member_course_shared_divisions',
        'timetable',
        'department_priority_order',
        'settings',
        'notifications',
        'api_tokens',
        'audit_logs',
        'migrations',
    ];

    /**
     * إنشاء مجلد النسخ الاحتياطية إذا لم يكن موجوداً
     */
    public static function ensureBackupDir(): string
    {
        $config = require APP_ROOT . '/config/cloud.php';
        $path = $config['backup']['local_path'] ?? APP_ROOT . '/storage/backups';
        
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        return $path;
    }

    /**
     * الحصول على قائمة النسخ الاحتياطية المحلية
     */
    public static function getLocalBackups(): array
    {
        $path = self::ensureBackupDir();
        $backups = [];
        
        $files = glob($path . '/*.{sql,xls,zip}', GLOB_BRACE);
        
        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file),
                'path' => $file,
                'size' => filesize($file),
                'size_formatted' => self::formatBytes(filesize($file)),
                'type' => pathinfo($file, PATHINFO_EXTENSION),
                'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                'timestamp' => filemtime($file),
            ];
        }
        
        // ترتيب حسب الأحدث
        usort($backups, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
        
        return $backups;
    }

    /**
     * إنشاء نسخة احتياطية SQL وحفظها محلياً
     */
    public static function createSqlBackup(?string $filename = null): array
    {
        $db = \Database::getInstance();
        $path = self::ensureBackupDir();
        
        $filename = $filename ?? 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $path . '/' . $filename;
        
        $content = self::generateSqlContent();
        
        file_put_contents($filepath, $content);
        
        // تنظيف النسخ القديمة
        self::cleanupOldBackups();
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $filepath,
            'size' => filesize($filepath),
            'size_formatted' => self::formatBytes(filesize($filepath)),
        ];
    }

    /**
     * إنشاء نسخة احتياطية Excel وحفظها محلياً
     */
    public static function createExcelBackup(?string $filename = null): array
    {
        $db = \Database::getInstance();
        $path = self::ensureBackupDir();
        
        $filename = $filename ?? 'backup_' . date('Y-m-d_H-i-s') . '.xls';
        $filepath = $path . '/' . $filename;
        
        $content = self::generateExcelContent();
        
        file_put_contents($filepath, $content);
        
        // تنظيف النسخ القديمة
        self::cleanupOldBackups();
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $filepath,
            'size' => filesize($filepath),
            'size_formatted' => self::formatBytes(filesize($filepath)),
        ];
    }

    /**
     * توليد محتوى SQL
     */
    public static function generateSqlContent(): string
    {
        $db = \Database::getInstance();
        
        $content = "-- Timetable Database Backup\n";
        $content .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
        $content .= "-- Application: " . config('app.name', 'نظام الجداول') . "\n\n";
        $content .= "SET NAMES utf8mb4;\n";
        $content .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach (self::TABLES as $table) {
            try {
                $columnsRows = $db->fetchAll("SHOW COLUMNS FROM `{$table}`");
                if (empty($columnsRows)) {
                    continue;
                }

                $columns = array_map(static fn(array $row) => $row['Field'], $columnsRows);
                $quotedColumns = implode(', ', array_map(static fn(string $col) => "`{$col}`", $columns));

                $content .= "-- Table: {$table}\n";
                $content .= "TRUNCATE TABLE `{$table}`;\n";

                $rows = $db->fetchAll("SELECT * FROM `{$table}`");
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($columns as $col) {
                        $value = $row[$col] ?? null;
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . $db->escape((string)$value) . "'";
                        }
                    }

                    $content .= "INSERT INTO `{$table}` ({$quotedColumns}) VALUES (" . implode(', ', $values) . ");\n";
                }

                $content .= "\n";
            } catch (\Throwable $e) {
                $content .= "-- Error exporting table {$table}: " . $e->getMessage() . "\n\n";
            }
        }

        $content .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        
        return $content;
    }

    /**
     * توليد محتوى Excel XML
     */
    public static function generateExcelContent(): string
    {
        $db = \Database::getInstance();
        
        $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $content .= "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\">\n";

        foreach (self::TABLES as $table) {
            try {
                $sheetName = mb_substr($table, 0, 31);
                $columnsRows = $db->fetchAll("SHOW COLUMNS FROM `{$table}`");
                if (empty($columnsRows)) {
                    continue;
                }

                $columns = array_map(static fn(array $row) => $row['Field'], $columnsRows);

                $content .= '<Worksheet ss:Name="' . htmlspecialchars($sheetName) . '"><Table>';
                $content .= '<Row>';
                foreach ($columns as $col) {
                    $content .= '<Cell><Data ss:Type="String">' . htmlspecialchars($col) . '</Data></Cell>';
                }
                $content .= '</Row>';

                $rows = $db->fetchAll("SELECT * FROM `{$table}`");
                foreach ($rows as $row) {
                    $content .= '<Row>';
                    foreach ($columns as $col) {
                        $value = $row[$col] ?? '';
                        $content .= '<Cell><Data ss:Type="String">' . htmlspecialchars((string)$value) . '</Data></Cell>';
                    }
                    $content .= '</Row>';
                }

                $content .= '</Table></Worksheet>';
            } catch (\Throwable $e) {
                // تخطي الجداول التي بها مشاكل
            }
        }

        $content .= '</Workbook>';
        
        return $content;
    }

    /**
     * حذف نسخة احتياطية محلية
     */
    public static function deleteLocalBackup(string $filename): bool
    {
        $path = self::ensureBackupDir();
        $filepath = $path . '/' . basename($filename);
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }

    /**
     * استعادة من ملف SQL محلي
     */
    public static function restoreFromSql(string $filename): array
    {
        $path = self::ensureBackupDir();
        $filepath = $path . '/' . basename($filename);
        
        if (!file_exists($filepath)) {
            return ['success' => false, 'error' => 'الملف غير موجود'];
        }
        
        $content = file_get_contents($filepath);
        if ($content === false) {
            return ['success' => false, 'error' => 'تعذر قراءة الملف'];
        }
        
        try {
            DataTransferService::importSql($content);
            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * تنزيل نسخة احتياطية
     */
    public static function downloadBackup(string $filename): void
    {
        $path = self::ensureBackupDir();
        $filepath = $path . '/' . basename($filename);
        
        if (!file_exists($filepath)) {
            http_response_code(404);
            exit('File not found');
        }
        
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeTypes = [
            'sql' => 'application/sql',
            'xls' => 'application/vnd.ms-excel',
            'zip' => 'application/zip',
        ];
        
        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filepath));
        
        readfile($filepath);
        exit;
    }

    /**
     * تنظيف النسخ الاحتياطية القديمة
     */
    public static function cleanupOldBackups(): int
    {
        $config = require APP_ROOT . '/config/cloud.php';
        $maxBackups = $config['backup']['max_local_backups'] ?? 10;
        
        $backups = self::getLocalBackups();
        $deleted = 0;
        
        if (count($backups) > $maxBackups) {
            $toDelete = array_slice($backups, $maxBackups);
            foreach ($toDelete as $backup) {
                if (unlink($backup['path'])) {
                    $deleted++;
                }
            }
        }
        
        return $deleted;
    }

    /**
     * الحصول على إحصائيات النسخ الاحتياطية
     */
    public static function getBackupStats(): array
    {
        $backups = self::getLocalBackups();
        $totalSize = array_sum(array_column($backups, 'size'));
        
        $sqlCount = count(array_filter($backups, fn($b) => $b['type'] === 'sql'));
        $excelCount = count(array_filter($backups, fn($b) => $b['type'] === 'xls'));
        
        return [
            'total_count' => count($backups),
            'sql_count' => $sqlCount,
            'excel_count' => $excelCount,
            'total_size' => $totalSize,
            'total_size_formatted' => self::formatBytes($totalSize),
            'last_backup' => $backups[0] ?? null,
        ];
    }

    /**
     * تنسيق حجم الملف
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * الحصول على إعدادات السحابة
     */
    public static function getCloudConfig(): array
    {
        return require APP_ROOT . '/config/cloud.php';
    }

    /**
     * تحديث إعدادات السحابة
     */
    public static function updateCloudConfig(array $settings): bool
    {
        $configPath = APP_ROOT . '/config/cloud.php';
        $config = require $configPath;
        
        // دمج الإعدادات الجديدة
        foreach ($settings as $section => $values) {
            if (isset($config[$section]) && is_array($values)) {
                $config[$section] = array_merge($config[$section], $values);
            }
        }
        
        // توليد محتوى الملف
        $content = "<?php\n/**\n * Cloud Services Configuration\n */\n\nreturn " . var_export($config, true) . ";\n";
        
        return file_put_contents($configPath, $content) !== false;
    }
}
