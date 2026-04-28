<?php
namespace App\Controllers\Api\V1;

require_once APP_ROOT . '/app/Models/User.php';

use App\Models\User;

class AuthController extends BaseApiController
{
    public function login(): void
    {
        $username = trim((string)$this->request->input('username', ''));
        $password = (string)$this->request->input('password', '');

        if ($username === '' || $password === '') {
            $this->fail('اسم المستخدم وكلمة المرور مطلوبان', 422, [
                'username' => ['required'],
                'password' => ['required'],
            ]);
        }

        $user = User::findByUsername($username);
        if (!$user || !password_verify($password, $user['password'])) {
            $this->fail('بيانات الدخول غير صحيحة', 401);
        }
        if ((int)$user['is_active'] !== 1) {
            $this->fail('الحساب معطل', 403);
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        \Database::getInstance()->insert('api_tokens', [
            'user_id' => (int)$user['id'],
            'token' => $tokenHash,
            'name' => 'mobile_v1',
            'expires_at' => $expiresAt,
        ]);

        $fullUser = \Database::getInstance()->fetch(
            "SELECT u.id, u.username, u.email, u.role_id, u.member_id, r.role_slug, r.role_name
             FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE u.id = ? LIMIT 1",
            [(int)$user['id']]
        );

        $this->ok([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
            'user' => $fullUser,
        ], 'Login successful');
    }

    public function logout(): void
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            $tokenHash = hash('sha256', trim($matches[1]));
            \Database::getInstance()->delete('api_tokens', ['token' => $tokenHash]);
        }

        $this->ok(null, 'Logged out');
    }

    public function me(): void
    {
        $apiUser = $this->apiUser();
        if (!$apiUser) {
            $this->fail('مصادقة مطلوبة', 401);
        }

        $user = \Database::getInstance()->fetch(
            "SELECT u.id, u.username, u.email, u.role_id, u.member_id, r.role_slug, r.role_name
             FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE u.id = ? LIMIT 1",
            [(int)$apiUser['user_id']]
        );

        if (!$user) {
            $this->fail('المستخدم غير موجود', 404);
        }

        $this->ok($user);
    }
}
