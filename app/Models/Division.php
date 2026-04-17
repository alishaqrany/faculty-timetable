<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

/**
 * Division Model (الشعب)
 * 
 * الشعبة هي تقسيم للفرقة - قد تكون عامة (فرقة واحدة) أو متعددة (شعبة أ، شعبة ب)
 * كل شعبة تنتمي لقسم وفرقة معينة
 */
class Division extends \Model
{
    protected static string $table = 'divisions';
    protected static string $primaryKey = 'division_id';
    protected static array $fillable = ['division_name', 'department_id', 'level_id', 'capacity', 'is_active'];

    /**
     * Get all divisions with department and level details
     */
    public static function allWithDetails(string $orderBy = 'd.department_name, l.sort_order, dv.division_name'): array
    {
        return static::query(
              "SELECT dv.*, d.department_name, l.level_name
               FROM divisions dv
               JOIN departments d ON dv.department_id = d.department_id
               JOIN levels l ON dv.level_id = l.level_id
             ORDER BY $orderBy"
        );
    }

    /**
     * Get divisions by department
     */
    public static function byDepartment(int $departmentId): array
    {
        return static::query(
            "SELECT dv.*, l.level_name
             FROM divisions dv
             JOIN levels l ON dv.level_id = l.level_id
             WHERE dv.department_id = ?
             ORDER BY l.sort_order, dv.division_name",
            [$departmentId]
        );
    }

    /**
     * Get divisions by department and level
     */
    public static function byDepartmentAndLevel(int $departmentId, int $levelId): array
    {
        return static::query(
            "SELECT * FROM divisions 
             WHERE department_id = ? AND level_id = ? AND is_active = 1
             ORDER BY division_name",
            [$departmentId, $levelId]
        );
    }

    /**
     * Get single division with details
     */
    public static function findWithDetails(int $id): ?array
    {
        return static::queryOne(
            "SELECT dv.*, d.department_name, l.level_name
             FROM divisions dv
             JOIN departments d ON dv.department_id = d.department_id
             JOIN levels l ON dv.level_id = l.level_id
             WHERE dv.division_id = ?",
            [$id]
        );
    }

    /**
     * Get all sections belonging to this division
     */
    public static function getSections(int $divisionId): array
    {
        return static::query(
            "SELECT * FROM sections WHERE division_id = ? ORDER BY section_name",
            [$divisionId]
        );
    }

    /**
     * Get sections count for a division
     */
    public static function getSectionsCount(int $divisionId): int
    {
        $result = static::queryOne(
            "SELECT COUNT(*) as cnt FROM sections WHERE division_id = ?",
            [$divisionId]
        );
        return (int)($result['cnt'] ?? 0);
    }

    /**
     * Get active divisions for dropdown
     */
    public static function active(): array
    {
        return static::query(
              "SELECT dv.*, d.department_name, l.level_name
               FROM divisions dv
               JOIN departments d ON dv.department_id = d.department_id
               JOIN levels l ON dv.level_id = l.level_id
               WHERE dv.is_active = 1
               ORDER BY d.department_name, l.sort_order, dv.division_name"
        );
    }

    /**
     * Get divisions grouped by department and level
     */
    public static function allGroupedByDepartmentLevel(): array
    {
        $divisions = static::allWithDetails();
        $grouped = [];
        
        foreach ($divisions as $div) {
            $deptId = $div['department_id'];
            $levelId = $div['level_id'];
            
            if (!isset($grouped[$deptId])) {
                $grouped[$deptId] = [
                    'department_id' => $deptId,
                    'department_name' => $div['department_name'],
                    'levels' => []
                ];
            }
            
            if (!isset($grouped[$deptId]['levels'][$levelId])) {
                $grouped[$deptId]['levels'][$levelId] = [
                    'level_id' => $levelId,
                    'level_name' => $div['level_name'],
                    'divisions' => []
                ];
            }
            
            $grouped[$deptId]['levels'][$levelId]['divisions'][] = $div;
        }
        
        return $grouped;
    }
}
