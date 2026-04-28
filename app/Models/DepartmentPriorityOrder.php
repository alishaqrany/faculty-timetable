<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class DepartmentPriorityOrder extends \Model
{
    protected static string $table = 'department_priority_order';
    protected static string $primaryKey = 'id';
    protected static array $fillable = ['department_id', 'sort_order', 'is_completed', 'current_group_id'];

    /**
     * Get department ordering with department names.
     */
    public static function allWithDetails(): array
    {
        return static::query(
            "SELECT dpo.*, d.department_name, d.department_code
             FROM department_priority_order dpo
             JOIN departments d ON dpo.department_id = d.department_id
             WHERE d.is_active = 1
             ORDER BY dpo.sort_order ASC"
        );
    }

    /**
     * Get the current (first non-completed) department.
     */
    public static function currentDepartment(): ?array
    {
        return static::queryOne(
            "SELECT dpo.*, d.department_name
             FROM department_priority_order dpo
             JOIN departments d ON dpo.department_id = d.department_id
             WHERE dpo.is_completed = 0 AND d.is_active = 1
             ORDER BY dpo.sort_order ASC LIMIT 1"
        );
    }

    /**
     * Get next department after the current one.
     */
    public static function nextDepartment(int $currentSortOrder): ?array
    {
        return static::queryOne(
            "SELECT dpo.*, d.department_name
             FROM department_priority_order dpo
             JOIN departments d ON dpo.department_id = d.department_id
             WHERE dpo.sort_order > ? AND dpo.is_completed = 0 AND d.is_active = 1
             ORDER BY dpo.sort_order ASC LIMIT 1",
            [$currentSortOrder]
        );
    }

    /**
     * Mark a department as completed.
     */
    public static function markCompleted(int $departmentId): int
    {
        $row = static::findWhere(['department_id' => $departmentId]);
        if ($row) {
            return static::updateById($row['id'], ['is_completed' => 1]);
        }
        return 0;
    }

    /**
     * Reset all departments to not completed and clear per-dept group tracking.
     */
    public static function resetAll(): void
    {
        \Database::getInstance()->raw(
            "UPDATE department_priority_order SET is_completed = 0, current_group_id = NULL"
        );
    }

    /**
     * Get the current group ID for a specific department (parallel mode).
     * Falls back to null if not set.
     */
    public static function getDeptCurrentGroupId(int $departmentId): ?int
    {
        $row = static::findWhere(['department_id' => $departmentId]);
        if ($row && !empty($row['current_group_id'])) {
            return (int) $row['current_group_id'];
        }
        return null;
    }

    /**
     * Set the current group for a specific department (parallel mode).
     */
    public static function setDeptCurrentGroupId(int $departmentId, ?int $groupId): void
    {
        $row = static::findWhere(['department_id' => $departmentId]);
        if ($row) {
            static::updateById($row['id'], ['current_group_id' => $groupId]);
        }
    }

    /**
     * Initialize all departments with the first group of the given category.
     */
    public static function initAllDeptGroups(int $firstGroupId): void
    {
        \Database::getInstance()->execute(
            "UPDATE department_priority_order SET current_group_id = ?, is_completed = 0",
            [$firstGroupId]
        );
    }

    /**
     * Sync department ordering (insert missing, remove deleted).
     */
    public static function syncWithDepartments(?int $initialGroupId = null): void
    {
        $db = \Database::getInstance();
        
        // Get all active departments
        $departments = $db->fetchAll("SELECT department_id FROM departments WHERE is_active = 1");
        $deptIds = array_column($departments, 'department_id');
        
        // Get existing order entries
        $existing = $db->fetchAll("SELECT department_id FROM department_priority_order");
        $existingIds = array_column($existing, 'department_id');
        
        // Add missing departments
        $maxOrder = (int) $db->fetchColumn("SELECT MAX(sort_order) FROM department_priority_order");
        foreach ($deptIds as $deptId) {
            if (!in_array($deptId, $existingIds)) {
                $maxOrder++;
                $insertData = [
                    'department_id' => $deptId,
                    'sort_order' => $maxOrder,
                    'is_completed' => 0,
                ];

                if ($initialGroupId) {
                    $insertData['current_group_id'] = $initialGroupId;
                }

                $db->insert('department_priority_order', $insertData);
            }
        }

        if ($initialGroupId) {
            $db->execute(
                "UPDATE department_priority_order SET current_group_id = ? WHERE current_group_id IS NULL",
                [$initialGroupId]
            );
        }
        
        // Remove entries for deleted/inactive departments
        foreach ($existingIds as $existingId) {
            if (!in_array($existingId, $deptIds)) {
                $db->delete('department_priority_order', ['department_id' => $existingId]);
            }
        }
    }
}
