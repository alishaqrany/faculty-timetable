<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Services/DataTransferService.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Services\DataTransferService;
use App\Services\AuditService;

class DataTransferController extends \Controller
{
    public function exportSql(): void
    {
        $this->authorize('settings.manage');
        AuditService::log('UPDATE', 'data_transfer');
        DataTransferService::exportSql('timetable_backup_' . date('Y-m-d_H-i') . '.sql');
    }

    public function exportExcel(): void
    {
        $this->authorize('settings.manage');
        AuditService::log('UPDATE', 'data_transfer');
        DataTransferService::exportExcel('timetable_backup_' . date('Y-m-d_H-i') . '.xls');
    }

    public function importSql(): void
    {
        $this->authorize('settings.manage');
        $this->validateCsrf();

        $file = $this->request->file('sql_file');
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->redirect('/settings', 'اختر ملف SQL صالحاً للاستيراد.', 'error');
            return;
        }

        if (strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION)) !== 'sql') {
            $this->redirect('/settings', 'صيغة الملف غير مدعومة. الرجاء رفع ملف .sql', 'error');
            return;
        }

        $content = @file_get_contents($file['tmp_name']);
        if ($content === false) {
            $this->redirect('/settings', 'تعذر قراءة ملف SQL المرفوع.', 'error');
            return;
        }

        try {
            DataTransferService::importSql($content);
            AuditService::log('UPDATE', 'data_transfer');
            $this->redirect('/settings', 'تم استيراد بيانات SQL بنجاح ✓');
        } catch (\Throwable $e) {
            error_log('DataTransfer importSql failed: ' . $e->getMessage());
            $this->redirect('/settings', 'فشل استيراد SQL. راجع سجل النظام للتحقق من التفاصيل.', 'error');
        }
    }

    public function importExcel(): void
    {
        $this->authorize('settings.manage');
        $this->validateCsrf();

        $file = $this->request->file('excel_file');
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->redirect('/settings', 'اختر ملف Excel صالحاً للاستيراد.', 'error');
            return;
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, ['xls', 'xml'], true)) {
            $this->redirect('/settings', 'صيغة الملف غير مدعومة. الرجاء رفع ملف .xls أو .xml المصدر من النظام.', 'error');
            return;
        }

        $content = @file_get_contents($file['tmp_name']);
        if ($content === false) {
            $this->redirect('/settings', 'تعذر قراءة ملف Excel المرفوع.', 'error');
            return;
        }

        try {
            $result = DataTransferService::importExcelXml($content);
            AuditService::log('UPDATE', 'data_transfer');
            $tablesCount = count($result['tables'] ?? []);
            $rowsCount = (int)($result['rows'] ?? 0);
            $this->redirect('/settings', "تم استيراد ملف Excel بنجاح ✓ (الجداول: {$tablesCount}، الصفوف: {$rowsCount})");
        } catch (\Throwable $e) {
            error_log('DataTransfer importExcel failed: ' . $e->getMessage());
            $this->redirect('/settings', 'فشل استيراد Excel. راجع سجل النظام للتحقق من التفاصيل.', 'error');
        }
    }

    public function downloadSampleSql(): void
    {
        $this->authorize('settings.manage');

        $path = APP_ROOT . '/storage/import_samples/sample_dummy_data.sql';
        if (!file_exists($path)) {
            $this->redirect('/settings', 'ملف البيانات التجريبي غير موجود.', 'error');
            return;
        }

        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="sample_dummy_data.sql"');
        readfile($path);
        exit;
    }
}
