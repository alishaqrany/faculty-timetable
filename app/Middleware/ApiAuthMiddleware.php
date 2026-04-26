<?php

namespace App\Middleware;

class ApiAuthMiddleware
{
    public function handle(array $params = []): void
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if ($header === '' && function_exists('getallheaders')) {
            $headers = getallheaders();
            if (is_array($headers)) {
                $header = $headers['Authorization']
                    ?? $headers['authorization']
                    ?? '';
            }
        }

        $token = null;

        if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'مصادقة مطلوبة', 'message' => 'Authentication required'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        try {
            $db = \Database::getInstance();
            $tokenHash = hash('sha256', $token);
            $apiToken = $db->fetch(
                "SELECT at.*, u.id as user_id, u.username, u.role_id, u.member_id, u.is_active
                 FROM api_tokens at
                 JOIN users u ON at.user_id = u.id
                 WHERE (at.token = ? OR at.token = ?) AND at.expires_at > NOW() AND u.is_active = 1
                 LIMIT 1",
                [$tokenHash, $token]
            );

            if (!$apiToken) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'رمز غير صالح', 'message' => 'Invalid or expired token'], JSON_UNESCAPED_UNICODE);
                exit();
            }

            // Update last used
            $db->update('api_tokens', ['last_used_at' => date('Y-m-d H:i:s')], ['id' => $apiToken['id']]);

            // Backward-compatible migration: if token was stored in plaintext, upgrade to hashed format.
            if (hash_equals((string)$apiToken['token'], (string)$token)) {
                $db->update('api_tokens', ['token' => $tokenHash], ['id' => $apiToken['id']]);
            }

            // Keep API auth stateless; expose user context without mutating web session.
            $GLOBALS['api_user'] = [
                'user_id' => (int)$apiToken['user_id'],
                'role_id' => (int)$apiToken['role_id'],
                'member_id' => (int)$apiToken['member_id'],
                'username' => (string)$apiToken['username'],
            ];
            $_SERVER['API_USER_ID'] = (string)$apiToken['user_id'];
            $_SERVER['API_ROLE_ID'] = (string)$apiToken['role_id'];
            $_SERVER['API_MEMBER_ID'] = (string)$apiToken['member_id'];
        } catch (\Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'خطأ في المصادقة'], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }
}
