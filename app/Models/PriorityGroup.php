<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class PriorityGroup extends \Model
{
    protected static string $table = 'priority_groups';
    protected static string $primaryKey = 'id';
    protected static array $fillable = ['category_id', 'group_name', 'sort_order', 'is_active'];

    public static function forCategory(int $categoryId): array
    {
        return static::query(
            "SELECT pg.*, 
                    (SELECT COUNT(*) FROM priority_group_members pgm WHERE pgm.group_id = pg.id) as member_count
             FROM priority_groups pg
             WHERE pg.category_id = ?
             ORDER BY pg.sort_order ASC",
            [$categoryId]
        );
    }

    public static function activeForCategory(int $categoryId): array
    {
        return static::query(
            "SELECT pg.*, 
                    (SELECT COUNT(*) FROM priority_group_members pgm WHERE pgm.group_id = pg.id) as member_count
             FROM priority_groups pg
             WHERE pg.category_id = ? AND pg.is_active = 1
             ORDER BY pg.sort_order ASC",
            [$categoryId]
        );
    }

    public static function withMembers(int $groupId): array
    {
        return static::query(
            "SELECT fm.member_id, fm.member_name, fm.email, d.department_name, ad.degree_name, pgm.id as pivot_id
             FROM priority_group_members pgm
             JOIN faculty_members fm ON pgm.member_id = fm.member_id
             LEFT JOIN departments d ON fm.department_id = d.department_id
             LEFT JOIN academic_degrees ad ON fm.degree_id = ad.id
             WHERE pgm.group_id = ?
             ORDER BY fm.member_name ASC",
            [$groupId]
        );
    }

    public static function findGroupsForMember(int $memberId): array
    {
        return static::query(
            "SELECT pg.*, pc.category_name, pgm.id as pivot_id
             FROM priority_group_members pgm
             JOIN priority_groups pg ON pgm.group_id = pg.id
             JOIN priority_categories pc ON pg.category_id = pc.id
             WHERE pgm.member_id = ?
             ORDER BY pc.sort_order ASC, pg.sort_order ASC",
            [$memberId]
        );
    }

    public static function isMemberInGroup(int $groupId, int $memberId): bool
    {
        $row = \Database::getInstance()->fetch(
            "SELECT id FROM priority_group_members WHERE group_id = ? AND member_id = ?",
            [$groupId, $memberId]
        );
        return $row !== null;
    }

    public static function addMember(int $groupId, int $memberId): int
    {
        return \Database::getInstance()->insert('priority_group_members', [
            'group_id' => $groupId,
            'member_id' => $memberId,
        ]);
    }

    public static function removeMember(int $pivotId): int
    {
        return \Database::getInstance()->delete('priority_group_members', ['id' => $pivotId]);
    }

    /**
     * Get next group by sort_order in same category.
     */
    public static function nextInCategory(int $categoryId, int $currentSortOrder): ?array
    {
        return static::queryOne(
            "SELECT * FROM priority_groups
             WHERE category_id = ? AND sort_order > ? AND is_active = 1
             ORDER BY sort_order ASC LIMIT 1",
            [$categoryId, $currentSortOrder]
        );
    }

    /**
     * Get the first group in a category.
     */
    public static function firstInCategory(int $categoryId): ?array
    {
        return static::queryOne(
            "SELECT * FROM priority_groups
             WHERE category_id = ? AND is_active = 1
             ORDER BY sort_order ASC LIMIT 1",
            [$categoryId]
        );
    }
}
