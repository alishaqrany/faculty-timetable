<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class Level extends \Model
{
    protected static string $table = 'levels';
    protected static string $primaryKey = 'level_id';
    protected static array $fillable = ['level_name', 'level_code', 'sort_order', 'is_active'];

    public static function active(): array
    {
        return static::where(['is_active' => 1], 'sort_order ASC, level_name ASC');
    }
}
