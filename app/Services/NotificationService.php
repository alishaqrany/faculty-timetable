<?php
namespace App\Services;

class NotificationService
{
    public static function send(int $userId, string $title, ?string $message = null, string $type = 'info', ?string $link = null): void
    {
        try {
            \Database::getInstance()->insert('notifications', [
                'user_id' => $userId,
                'title'   => $title,
                'message' => $message,
                'type'    => $type,
                'link'    => $link,
            ]);
        } catch (\Throwable $e) {
            error_log("NotificationService error: " . $e->getMessage());
        }
    }

    /**
     * Send notification to all users with a specific role.
     */
    public static function sendToRole(string $roleSlug, string $title, ?string $message = null, string $type = 'info', ?string $link = null): void
    {
        try {
            $users = \Database::getInstance()->fetchAll(
                "SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id WHERE r.role_slug = ? AND u.is_active = 1",
                [$roleSlug]
            );
            foreach ($users as $user) {
                static::send($user['id'], $title, $message, $type, $link);
            }
        } catch (\Throwable $e) {
            error_log("NotificationService sendToRole error: " . $e->getMessage());
        }
    }
}
