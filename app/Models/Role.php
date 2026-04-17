<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class Role extends \Model
{
    protected static string $table = 'roles';
    protected static string $primaryKey = 'id';
    protected static array $fillable = ['role_name', 'role_slug', 'description', 'is_system'];

    public static function findBySlug(string $slug): ?array
    {
        return static::findWhere(['role_slug' => $slug]);
    }

    public static function getPermissions(int $roleId): array
    {
        return static::query(
            "SELECT p.*
             FROM role_permissions rp
             JOIN permissions p ON rp.permission_id = p.id
             WHERE rp.role_id = ?
             ORDER BY p.module, p.permission_slug",
            [$roleId]
        );
    }
}
