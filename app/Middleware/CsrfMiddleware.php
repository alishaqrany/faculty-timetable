<?php

namespace App\Middleware;

class CsrfMiddleware
{
    public function handle(array $params = []): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (!in_array($method, ['POST', 'PUT', 'DELETE'])) {
            return;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        $session = \Session::getInstance();

        if (!$session->csrfValidate($token)) {
            $session->flash('error', 'رمز الأمان غير صالح. يرجى تحديث الصفحة والمحاولة مرة أخرى.');
            $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
            header('Location: ' . $referer);
            exit();
        }
    }
}
