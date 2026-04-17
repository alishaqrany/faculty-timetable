<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class Session extends \Model
{
    protected static string $table = 'sessions';
    protected static string $primaryKey = 'session_id';
    protected static array $fillable = ['day', 'session_name', 'start_time', 'end_time', 'duration', 'is_active'];

    public static function active(): array
    {
        return static::where(['is_active' => 1], 'FIELD(day,"الأحد","الاثنين","الثلاثاء","الأربعاء","الخميس"), start_time ASC');
    }

    public static function grouped(): array
    {
        $sessions = static::active();
        $grouped = [];
        foreach ($sessions as $s) {
            $grouped[$s['day']][] = $s;
        }
        return $grouped;
    }
}
