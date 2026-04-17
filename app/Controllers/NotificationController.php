<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/Notification.php';
require_once APP_ROOT . '/app/Models/Role.php';
require_once APP_ROOT . '/app/Models/User.php';
require_once APP_ROOT . '/app/Services/NotificationService.php';

use App\Models\{Notification, Role, User};
use App\Services\NotificationService;

class NotificationController extends \Controller
{
    public function index(): void
    {
        $this->authorize('notifications.view');
        $notifications = Notification::forUser($this->session->userId(), 100);
        $canSend = can('notifications.send');
        $roles = $canSend ? Role::all('role_name ASC') : [];
        $users = $canSend ? User::allWithDetails('u.username ASC') : [];

        $this->render('notifications.index', [
            'notifications' => $notifications,
            'canSend' => $canSend,
            'roles' => $roles,
            'users' => $users,
        ]);
    }

    public function send(): void
    {
        $this->authorize('notifications.send');
        $this->validateCsrf();

        $data = $this->request->validate([
            'target_type' => 'required|in:all,role,user',
            'target_id'   => 'integer',
            'title'       => 'required|max:200',
            'message'     => 'max:2000',
            'type'        => 'required|in:info,success,warning,danger',
            'link'        => 'max:500',
        ]);

        if ($data === false) {
            $this->redirect('/notifications', 'يرجى تصحيح أخطاء الإرسال', 'error');
        }

        $targetType = $data['target_type'];
        $targetId = (int)($data['target_id'] ?? 0);
        $title = trim($data['title']);
        $message = trim((string)($data['message'] ?? '')) ?: null;
        $type = $data['type'];
        $link = trim((string)($data['link'] ?? '')) ?: null;

        try {
            if ($targetType === 'all') {
                NotificationService::sendToAll($title, $message, $type, $link);
            } elseif ($targetType === 'role') {
                if (!$targetId) {
                    $this->redirect('/notifications', 'يرجى اختيار الدور المستهدف', 'error');
                }
                $role = Role::find($targetId);
                if (!$role) {
                    $this->redirect('/notifications', 'الدور غير موجود', 'error');
                }
                NotificationService::sendToRole($role['role_slug'], $title, $message, $type, $link);
            } else {
                if (!$targetId) {
                    $this->redirect('/notifications', 'يرجى اختيار المستخدم المستهدف', 'error');
                }
                $user = User::find($targetId);
                if (!$user) {
                    $this->redirect('/notifications', 'المستخدم غير موجود', 'error');
                }
                NotificationService::send($targetId, $title, $message, $type, $link);
            }
        } catch (\Throwable $e) {
            error_log('NotificationController send error: ' . $e->getMessage());
            $this->redirect('/notifications', 'تعذر إرسال الإشعار', 'error');
        }

        $this->redirect('/notifications', 'تم إرسال الإشعار بنجاح ✓');
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
