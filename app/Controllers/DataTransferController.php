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
            $this->session->remove('sample_demo_meta');
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
            $this->session->remove('sample_demo_meta');
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
        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . DataTransferService::sampleSqlFilename() . '"');
        echo DataTransferService::generateSampleSqlContent();
        exit;
    }

    public function importSampleSql(): void
    {
        $this->authorize('settings.manage');
        $this->validateCsrf();

        try {
            DataTransferService::importSql(DataTransferService::generateSampleSqlContent());

            $this->session->set('sample_demo_meta', [
                'imported' => true,
                'imported_at' => date('Y-m-d H:i:s'),
                'accounts_count' => count(DataTransferService::sampleDemoAccounts()),
                'summary' => DataTransferService::sampleDataSummary(),
            ]);

            $this->session->flash('success', 'تم استيراد البيانات التجريبية الجاهزة بنجاح ✓');
            AuditService::log('UPDATE', 'data_transfer');

            header('Location: ' . url('/settings/data-transfer/sample-accounts'));
            exit;
        } catch (\Throwable $e) {
            error_log('DataTransfer importSampleSql failed: ' . $e->getMessage());
            $this->redirect('/settings', 'فشل استيراد البيانات التجريبية. راجع سجل النظام للتحقق من التفاصيل.', 'error');
        }
    }

    public function sampleAccounts(): void
    {
        $this->authorize('settings.manage');

        $meta = $this->session->get('sample_demo_meta', []);

        $this->render('settings.sample-accounts', [
            'sampleAccounts' => DataTransferService::sampleDemoAccounts(),
            'sampleSummary' => DataTransferService::sampleDataSummary(),
            'samplePassword' => DataTransferService::sampleDemoPassword(),
            'sampleImported' => !empty($meta['imported']),
            'sampleImportedAt' => $meta['imported_at'] ?? null,
        ]);
    }

    public function resetSystem(): void
    {
        $this->authorize('settings.manage');
        $this->validateCsrf();

        try {
            DataTransferService::resetSystem();
            $this->session->remove('sample_demo_meta');
            AuditService::log('DELETE', 'data_transfer_reset');
            $this->redirect('/settings', 'تمت إعادة تهيئة النظام ومسح البيانات التشغيلية مع الإبقاء على حسابات المدير والإعدادات الأساسية.');
        } catch (\Throwable $e) {
            error_log('DataTransfer resetSystem failed: ' . $e->getMessage());
            $this->redirect('/settings', 'فشلت إعادة التهيئة. راجع سجل النظام للتحقق من التفاصيل.', 'error');
        }

        exit;
    }
}
