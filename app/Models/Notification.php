<?php
namespace App\Models;
require_once APP_ROOT . '/core/Model.php';

class Notification extends \Model
{
    protected static string $table = 'notifications';
    protected static string $primaryKey = 'id';
    protected static array $fillable = ['user_id', 'title', 'message', 'type', 'link', 'is_read'];

    public static function unreadForUser(int $userId, int $limit = 10): array
    {
        return static::query(
            "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public static function forUser(int $userId, int $limit = 50): array
    {
        return static::query(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public static function markAllRead(int $userId): void
    {
        \Database::getInstance()->execute(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
    }
}
