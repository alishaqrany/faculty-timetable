<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class Subject extends \Model
{
    protected static string $table = 'subjects';
    protected static string $primaryKey = 'subject_id';
    protected static array $fillable = [
        'subject_name', 'subject_code', 'department_id', 'level_id',
        'hours', 'credit_hours', 'subject_type', 'is_active',
    ];

    public static function allWithDetails(string $orderBy = 's.subject_name ASC'): array
    {
        return static::query(
            "SELECT s.*, d.department_name, l.level_name
             FROM subjects s
             JOIN departments d ON s.department_id = d.department_id
             JOIN levels l ON s.level_id = l.level_id
             ORDER BY $orderBy"
        );
    }

    public static function findWithDetails(int $id): ?array
    {
        return static::queryOne(
            "SELECT s.*, d.department_name, l.level_name
             FROM subjects s
             JOIN departments d ON s.department_id = d.department_id
             JOIN levels l ON s.level_id = l.level_id
             WHERE s.subject_id = ?",
            [$id]
        );
    }
}
