<?php
namespace App\Controllers\Api;

require_once APP_ROOT . '/app/Models/User.php';

use App\Models\User;

class AuthController extends \Controller
{
    public function login(): void
    {
        $username = $this->request->input('username', '');
        $password = $this->request->input('password', '');

        if (!$username || !$password) {
            $this->json(['error' => 'اسم المستخدم وكلمة المرور مطلوبة'], 400);
        }

        $user = User::findByUsername($username);
        if (!$user || !password_verify($password, $user['password'])) {
            $this->json(['error' => 'بيانات الدخول غير صحيحة'], 401);
        }

        if (!$user['is_active']) {
            $this->json(['error' => 'الحساب معطّل'], 403);
        }

        // Generate API token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        \Database::getInstance()->insert('api_tokens', [
            'user_id'    => $user['id'],
            'token'      => $token,
            'name'       => 'api_login',
            'expires_at' => $expiresAt,
        ]);

        $this->json([
            'token'      => $token,
            'expires_at' => $expiresAt,
            'user'       => [
                'id'       => $user['id'],
                'username' => $user['username'],
            ],
        ]);
    }
}
