<?php
namespace App\Controllers;

require_once APP_ROOT . '/app/Models/User.php';
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Models\User;
use App\Services\AuditService;

class AuthController extends \Controller
{
    public function login(): void
    {
        if ($this->session->isLoggedIn()) {
            $this->redirect('/');
        }
        $this->render('auth.login');
    }

    public function doLogin(): void
    {
        $this->validateCsrf();

        $username = trim($this->request->input('username', ''));
        $password = $this->request->input('password', '');

        if ($username === '' || $password === '') {
            $this->redirect('/login', 'يرجى إدخال اسم المستخدم وكلمة المرور', 'error');
        }

        $user = User::findByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->redirect('/login', 'اسم المستخدم أو كلمة المرور غير صحيحة', 'error');
        }

        if (!$user['is_active']) {
            $this->redirect('/login', 'الحساب معطّل. تواصل مع مدير النظام.', 'error');
        }

        // Set session
        $this->session->regenerate();
        $this->session->set('user_id', $user['id']);
        $this->session->set('member_id', $user['member_id']);
        $this->session->set('role_id', $user['role_id']);

        // Update last login
        User::updateById($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        AuditService::log('LOGIN', 'auth', $user['id']);

        $this->redirect('/', 'مرحباً بك في النظام');
    }

    public function logout(): void
    {
        AuditService::log('LOGOUT', 'auth');
        $this->session->destroy();
        session_start();
        $this->redirect('/login', 'تم تسجيل الخروج بنجاح');
    }

    public function profile(): void
    {
        $user = $this->authUser();
        if (!$user) $this->redirect('/login');

        $this->render('auth.profile', ['user' => $user]);
    }

    public function updateProfile(): void
    {
        $this->validateCsrf();
        $user = $this->authUser();
        if (!$user) $this->redirect('/login');

        $email = trim($this->request->input('email', ''));
        $currentPassword = $this->request->input('current_password', '');
        $newPassword = $this->request->input('new_password', '');

        $data = [];
        if ($email !== '') {
            $data['email'] = $email;
        }

        if ($newPassword !== '') {
            if (!password_verify($currentPassword, $user['password'])) {
                $this->redirect('/profile', 'كلمة المرور الحالية غير صحيحة', 'error');
            }
            if (mb_strlen($newPassword) < 6) {
                $this->redirect('/profile', 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل', 'error');
            }
            $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        if (!empty($data)) {
            User::updateById($user['id'], $data);
            AuditService::log('UPDATE_PROFILE', 'auth', $user['id']);
        }

        $this->redirect('/profile', 'تم تحديث الملف الشخصي بنجاح');
    }
}
