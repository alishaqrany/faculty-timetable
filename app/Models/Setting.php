<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class Setting extends \Model
{
    protected static string $table = 'settings';
    protected static string $primaryKey = 'id';
    protected static array $fillable = ['setting_key', 'setting_value', 'setting_group'];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $row = static::findWhere(['setting_key' => $key]);
        return $row ? $row['setting_value'] : $default;
    }

    public static function setValue(string $key, string $value): void
    {
        $existing = static::findWhere(['setting_key' => $key]);
        if ($existing) {
            static::updateById($existing['id'], ['setting_value' => $value]);
        } else {
            static::create(['setting_key' => $key, 'setting_value' => $value]);
        }
    }

    public static function byGroup(string $group): array
    {
        return static::where(['setting_group' => $group], 'setting_key ASC');
    }
}
