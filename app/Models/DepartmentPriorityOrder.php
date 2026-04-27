<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class DepartmentPriorityOrder extends \Model
{
    protected static string $table = 'department_priority_order';
    protected static string $primaryKey = 'id';
    protected static array $fillable = ['department_id', 'sort_order', 'is_completed'];

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
     * Reset all departments to not completed.
     */
    public static function resetAll(): void
    {
        \Database::getInstance()->raw("UPDATE department_priority_order SET is_completed = 0");
    }

    /**
     * Sync department ordering (insert missing, remove deleted).
     */
    public static function syncWithDepartments(): void
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
                $db->insert('department_priority_order', [
                    'department_id' => $deptId,
                    'sort_order' => $maxOrder,
                    'is_completed' => 0,
                ]);
            }
        }
        
        // Remove entries for deleted/inactive departments
        foreach ($existingIds as $existingId) {
            if (!in_array($existingId, $deptIds)) {
                $db->delete('department_priority_order', ['department_id' => $existingId]);
            }
        }
    }
}
