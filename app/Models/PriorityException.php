<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class PriorityException extends \Model
{
    protected static string $table = 'priority_exceptions';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [
        'exception_type', 'member_id', 'group_id',
        'granted_by', 'reason', 'is_active', 'expires_at',
    ];

    /**
     * Check if a member has an active exception (individual or group-based).
     */
    public static function activeForMember(int $memberId): ?array
    {
        // Check direct member exception
        $direct = static::queryOne(
            "SELECT * FROM priority_exceptions
             WHERE exception_type = 'member' AND member_id = ? AND is_active = 1
               AND (expires_at IS NULL OR expires_at > NOW())
             LIMIT 1",
            [$memberId]
        );
        if ($direct) return $direct;

        // Check group-based exception
        $groupException = static::queryOne(
            "SELECT pe.* FROM priority_exceptions pe
             JOIN priority_group_members pgm ON pe.group_id = pgm.group_id
             WHERE pe.exception_type = 'group' AND pgm.member_id = ? AND pe.is_active = 1
               AND (pe.expires_at IS NULL OR pe.expires_at > NOW())
             LIMIT 1",
            [$memberId]
        );
        return $groupException;
    }

    /**
     * Get all active exceptions.
     */
    public static function allActive(): array
    {
        return static::query(
            "SELECT pe.*,
                    fm.member_name,
                    pg.group_name,
                    u.username as granted_by_name
             FROM priority_exceptions pe
             LEFT JOIN faculty_members fm ON pe.member_id = fm.member_id
             LEFT JOIN priority_groups pg ON pe.group_id = pg.id
             LEFT JOIN users u ON pe.granted_by = u.id
             WHERE pe.is_active = 1
               AND (pe.expires_at IS NULL OR pe.expires_at > NOW())
             ORDER BY pe.created_at DESC"
        );
    }

    /**
     * Get all exceptions (including expired).
     */
    public static function allWithDetails(): array
    {
        return static::query(
            "SELECT pe.*,
                    fm.member_name,
                    pg.group_name,
                    u.username as granted_by_name
             FROM priority_exceptions pe
             LEFT JOIN faculty_members fm ON pe.member_id = fm.member_id
             LEFT JOIN priority_groups pg ON pe.group_id = pg.id
             LEFT JOIN users u ON pe.granted_by = u.id
             ORDER BY pe.is_active DESC, pe.created_at DESC"
        );
    }

    /**
     * Revoke an exception.
     */
    public static function revoke(int $id): int
    {
        return static::updateById($id, ['is_active' => 0]);
    }
}
