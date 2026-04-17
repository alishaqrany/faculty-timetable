<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class Semester extends \Model
{
    protected static string $table = 'semesters';
    protected static string $primaryKey = 'id';
    protected static array $fillable = ['semester_name', 'academic_year_id', 'start_date', 'end_date', 'is_current', 'is_active'];

    public static function current(): ?array
    {
        return static::findWhere(['is_current' => 1]);
    }

    public static function forYear(int $yearId): array
    {
        return static::where(['academic_year_id' => $yearId], 'start_date ASC');
    }

    public static function setCurrent(int $id): void
    {
        $db = \Database::getInstance();
        $db->execute("UPDATE semesters SET is_current = 0");
        $db->execute("UPDATE semesters SET is_current = 1 WHERE id = ?", [$id]);
    }

    public static function allWithYear(): array
    {
        return static::query(
            "SELECT s.*, ay.year_name
             FROM semesters s
             JOIN academic_years ay ON s.academic_year_id = ay.id
             ORDER BY ay.start_date DESC, s.start_date ASC"
        );
    }
}
