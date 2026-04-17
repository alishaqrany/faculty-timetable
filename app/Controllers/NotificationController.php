<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Notification.php';

use App\Models\Notification;

class NotificationController extends \Controller
{
    public function index(): void
    {
        $notifications = Notification::forUser($this->session->userId(), 100);
        $this->render('notifications.index', ['notifications' => $notifications]);
    }

    public function markRead(string $id): void
    {
        $this->validateCsrf();
        Notification::updateById((int)$id, ['is_read' => 1]);
        $this->redirect('/notifications');
    }

    public function markAllRead(): void
    {
        $this->validateCsrf();
        Notification::markAllRead($this->session->userId());
        $this->redirect('/notifications', 'تم تعليم جميع الإشعارات كمقروءة ✓');
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();
        Notification::destroy((int)$id);
        $this->redirect('/notifications');
    }
}
