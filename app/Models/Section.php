<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class Section extends \Model
{
    protected static string $table = 'sections';
    protected static string $primaryKey = 'section_id';
    protected static array $fillable = ['section_name', 'department_id', 'level_id', 'capacity', 'is_active'];

    public static function allWithDetails(string $orderBy = 'sec.section_name ASC'): array
    {
        return static::query(
            "SELECT sec.*, d.department_name, l.level_name
             FROM sections sec
             JOIN departments d ON sec.department_id = d.department_id
             JOIN levels l ON sec.level_id = l.level_id
             ORDER BY $orderBy"
        );
    }

    public static function findWithDetails(int $id): ?array
    {
        return static::queryOne(
            "SELECT sec.*, d.department_name, l.level_name
             FROM sections sec
             JOIN departments d ON sec.department_id = d.department_id
             JOIN levels l ON sec.level_id = l.level_id
             WHERE sec.section_id = ?",
            [$id]
        );
    }
}
