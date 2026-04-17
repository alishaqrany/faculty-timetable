<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

/**
 * Section Model (السكاشن/مجموعات العملي)
 * 
 * السكشن هو مجموعة عملي داخل الشعبة
 * كل سكشن ينتمي لشعبة معينة (والشعبة تحدد القسم والفرقة)
 */
class Section extends \Model
{
    protected static string $table = 'sections';
    protected static string $primaryKey = 'section_id';
    protected static array $fillable = ['section_name', 'division_id', 'department_id', 'level_id', 'capacity', 'is_active'];

    /**
     * Get all sections with division, department and level details
     */
    public static function allWithDetails(string $orderBy = 'd.department_name, l.sort_order, dv.division_name, sec.section_name'): array
    {
        return static::query(
              "SELECT sec.*, dv.division_name, d.department_name, l.level_name
             FROM sections sec
               LEFT JOIN divisions dv ON sec.division_id = dv.division_id
               LEFT JOIN departments d ON COALESCE(dv.department_id, sec.department_id) = d.department_id
               LEFT JOIN levels l ON COALESCE(dv.level_id, sec.level_id) = l.level_id
             ORDER BY $orderBy"
        );
    }

    /**
     * Get sections by division
     */
    public static function byDivision(int $divisionId): array
    {
        return static::query(
            "SELECT * FROM sections WHERE division_id = ? AND is_active = 1 ORDER BY section_name",
            [$divisionId]
        );
    }

    /**
     * Get single section with details
     */
    public static function findWithDetails(int $id): ?array
    {
        return static::queryOne(
            "SELECT sec.*, dv.division_name, dv.department_id as div_dept_id, dv.level_id as div_level_id,
                    d.department_name, l.level_name
             FROM sections sec
             LEFT JOIN divisions dv ON sec.division_id = dv.division_id
             LEFT JOIN departments d ON COALESCE(dv.department_id, sec.department_id) = d.department_id
             LEFT JOIN levels l ON COALESCE(dv.level_id, sec.level_id) = l.level_id
             WHERE sec.section_id = ?",
            [$id]
        );
    }

    /**
     * Get parent division
     */
    public static function getDivision(int $sectionId): ?array
    {
        return static::queryOne(
            "SELECT dv.*, d.department_name, l.level_name
             FROM sections sec
             JOIN divisions dv ON sec.division_id = dv.division_id
             JOIN departments d ON dv.department_id = d.department_id
             JOIN levels l ON dv.level_id = l.level_id
             WHERE sec.section_id = ?",
            [$sectionId]
        );
    }

    /**
     * Get active sections for dropdown
     */
    public static function active(): array
    {
        return static::query(
              "SELECT sec.*, dv.division_name, d.department_name, l.level_name
             FROM sections sec
               LEFT JOIN divisions dv ON sec.division_id = dv.division_id
               LEFT JOIN departments d ON COALESCE(dv.department_id, sec.department_id) = d.department_id
               LEFT JOIN levels l ON COALESCE(dv.level_id, sec.level_id) = l.level_id
             WHERE sec.is_active = 1
               ORDER BY d.department_name, l.sort_order, dv.division_name, sec.section_name"
        );
    }

    /**
     * Get sections grouped by division
     */
    public static function allGroupedByDivision(): array
    {
        $sections = static::allWithDetails();
        $grouped = [];
        
        foreach ($sections as $sec) {
            $divId = $sec['division_id'] ?? 0;
            
            if (!isset($grouped[$divId])) {
                $grouped[$divId] = [
                    'division_id' => $divId,
                    'division_name' => $sec['division_name'] ?? 'بدون شعبة',
                    'department_name' => $sec['department_name'],
                    'level_name' => $sec['level_name'],
                    'sections' => []
                ];
            }
            
            $grouped[$divId]['sections'][] = $sec;
        }
        
        return $grouped;
    }
}
