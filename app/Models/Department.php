<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class Department extends \Model
{
    protected static string $table = 'departments';
    protected static string $primaryKey = 'department_id';
    protected static array $fillable = ['department_name', 'department_code', 'description', 'is_active'];

    public static function active(): array
    {
        return static::where(['is_active' => 1], 'department_name ASC');
    }
}
