<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class AcademicYear extends \Model
{
    protected static string $table = 'academic_years';
    protected static string $primaryKey = 'id';
    protected static array $fillable = ['year_name', 'start_date', 'end_date', 'is_current', 'is_active'];

    public static function current(): ?array
    {
        return static::findWhere(['is_current' => 1]);
    }

    public static function setCurrent(int $id): void
    {
        $db = \Database::getInstance();
        $db->execute("UPDATE academic_years SET is_current = 0");
        $db->execute("UPDATE academic_years SET is_current = 1 WHERE id = ?", [$id]);
    }
}
