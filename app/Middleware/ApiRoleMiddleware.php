<?php

namespace App\Middleware;

class ApiRoleMiddleware
{
    public function handle(array $params = []): void
    {
        $apiUser = $GLOBALS['api_user'] ?? null;
        if (!$apiUser) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required',
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $user = \Database::getInstance()->fetch(
            "SELECT u.id, r.role_slug
             FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE u.id = ? LIMIT 1",
            [(int)$apiUser['user_id']]
        );

        $role = (string)($user['role_slug'] ?? '');
        if (!in_array($role, ['admin', 'dept_head'], true)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Not authorized for admin mobile endpoints',
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }
}
