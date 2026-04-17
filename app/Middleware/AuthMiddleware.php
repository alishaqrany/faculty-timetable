<?php

namespace App\Middleware;

class AuthMiddleware
{
    public function handle(array $params = []): void
    {
        $session = \Session::getInstance();
        if (!$session->isLoggedIn()) {
            $session->flash('error', 'يجب تسجيل الدخول أولاً');
            header('Location: ' . url('/login'));
            exit();
        }
    }
}
