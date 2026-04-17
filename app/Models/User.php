<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class User extends \Model
{
    protected static string $table = 'users';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [
        'username', 'email', 'password', 'member_id', 'role_id',
        'registration_status', 'is_active', 'last_login_at',
    ];

    public static function findByUsername(string $username): ?array
    {
        return static::findWhere(['username' => $username]);
    }

    public static function withRole(int $id): ?array
    {
        return static::queryOne(
            "SELECT u.*, r.role_name, r.role_slug
             FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE u.id = ?",
            [$id]
        );
    }

    public static function allWithDetails(string $orderBy = 'u.id DESC'): array
    {
        return static::query(
            "SELECT u.*, r.role_name, r.role_slug, fm.member_name
             FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             LEFT JOIN faculty_members fm ON u.member_id = fm.member_id
             ORDER BY $orderBy"
        );
    }
}
