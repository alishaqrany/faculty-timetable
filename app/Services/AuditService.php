<?php
namespace App\Services;

class AuditService
{
    /**
     * Log an action to the audit trail.
     */
    public static function log(
        string $action,
        string $module,
        ?int $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        try {
            $session = \Session::getInstance();
            \Database::getInstance()->insert('audit_logs', [
                'user_id'    => $session->userId(),
                'action'     => $action,
                'module'     => $module,
                'record_id'  => $recordId,
                'old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? mb_substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : null,
            ]);
        } catch (\Throwable $e) {
            error_log("AuditService error: " . $e->getMessage());
        }
    }
}
