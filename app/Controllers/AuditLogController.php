<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/AuditLog.php';

use App\Models\AuditLog;

class AuditLogController extends \Controller
{
    public function index(): void
    {
        $this->authorize('audit.view');

        $filters = [
            'module'  => $this->request->input('module'),
            'action'  => $this->request->input('action'),
            'user_id' => $this->request->input('user_id'),
        ];

        $page = current_page();
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $logs = AuditLog::allWithUser($perPage, $offset, array_filter($filters));
        $total = AuditLog::count(array_filter($filters));

        $this->render('audit-logs.index', [
            'logs'    => $logs,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'last_page'    => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function show(string $id): void
    {
        $this->authorize('audit.view');

        $log = AuditLog::findWithUser((int)$id);
        if (!$log) $this->redirect('/audit-logs', 'السجل غير موجود', 'error');

        $this->render('audit-logs.show', ['log' => $log]);
    }
}
