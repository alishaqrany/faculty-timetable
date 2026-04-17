<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class Member extends \Model
{
    protected static string $table = 'faculty_members';
    protected static string $primaryKey = 'member_id';
    protected static array $fillable = [
        'member_name', 'email', 'phone', 'department_id', 'degree_id',
        'degree', 'join_date', 'ranking', 'role', 'is_active',
    ];

    public static function allWithDetails(string $orderBy = 'fm.member_name ASC'): array
    {
        return static::query(
            "SELECT fm.*, d.department_name, ad.degree_name
             FROM faculty_members fm
             LEFT JOIN departments d ON fm.department_id = d.department_id
             LEFT JOIN academic_degrees ad ON fm.degree_id = ad.id
             ORDER BY $orderBy"
        );
    }

    public static function findWithDetails(int $id): ?array
    {
        return static::queryOne(
            "SELECT fm.*, d.department_name, ad.degree_name
             FROM faculty_members fm
             LEFT JOIN departments d ON fm.department_id = d.department_id
             LEFT JOIN academic_degrees ad ON fm.degree_id = ad.id
             WHERE fm.member_id = ?",
            [$id]
        );
    }

    public static function active(): array
    {
        return static::where(['is_active' => 1], 'member_name ASC');
    }
}
