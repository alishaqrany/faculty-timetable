<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class AuditLog extends \Model
{
    protected static string $table = 'audit_logs';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [
        'user_id', 'action', 'module', 'record_id',
        'old_values', 'new_values', 'ip_address', 'user_agent',
    ];

    public static function allWithUser(int $limit = 50, int $offset = 0, array $filters = []): array
    {
        $sql = "SELECT al.*, u.username, fm.member_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                LEFT JOIN faculty_members fm ON u.member_id = fm.member_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['module'])) {
            $sql .= " AND al.module = ?";
            $params[] = $filters['module'];
        }
        if (!empty($filters['action'])) {
            $sql .= " AND al.action = ?";
            $params[] = $filters['action'];
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return static::query($sql, $params);
    }

    public static function findWithUser(int $id): ?array
    {
        return static::queryOne(
            "SELECT al.*, u.username, fm.member_name
             FROM audit_logs al
             LEFT JOIN users u ON al.user_id = u.id
             LEFT JOIN faculty_members fm ON u.member_id = fm.member_id
             WHERE al.id = ?",
            [$id]
        );
    }
}
