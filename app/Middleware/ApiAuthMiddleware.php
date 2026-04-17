<?php

namespace App\Middleware;

class ApiAuthMiddleware
{
    public function handle(array $params = []): void
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
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
            $apiToken = $db->fetch(
                "SELECT at.*, u.id as user_id, u.username, u.role_id, u.member_id, u.is_active
                 FROM api_tokens at
                 JOIN users u ON at.user_id = u.id
                 WHERE at.token = ? AND at.expires_at > NOW() AND u.is_active = 1
                 LIMIT 1",
                [$token]
            );

            if (!$apiToken) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'رمز غير صالح', 'message' => 'Invalid or expired token'], JSON_UNESCAPED_UNICODE);
                exit();
            }

            // Update last used
            $db->update('api_tokens', ['last_used_at' => date('Y-m-d H:i:s')], ['id' => $apiToken['id']]);

            // Set session data for the API user
            $session = \Session::getInstance();
            $session->set('user_id', $apiToken['user_id']);
            $session->set('role_id', $apiToken['role_id']);
            $session->set('member_id', $apiToken['member_id']);
        } catch (\Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'خطأ في المصادقة'], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }
}
