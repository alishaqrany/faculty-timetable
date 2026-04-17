<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class Classroom extends \Model
{
    protected static string $table = 'classrooms';
    protected static string $primaryKey = 'classroom_id';
    protected static array $fillable = ['classroom_name', 'capacity', 'classroom_type', 'building', 'floor', 'is_active'];

    public static function active(): array
    {
        return static::where(['is_active' => 1], 'classroom_name ASC');
    }
}
