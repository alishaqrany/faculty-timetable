<?php

class Controller
{
    protected View $view;
    protected Request $request;
    protected Session $session;

    public function __construct()
    {
        $this->view = new View();
        $this->request = new Request();
        $this->session = Session::getInstance();
    }

    /**
     * Render a view and send it to the browser.
     */
    protected function render(string $template, array $data = []): void
    {
        // Inject common data
        $data['auth'] = $this->authUser();
        $data['csrf_token'] = $this->session->csrfToken();
        $data['flash_success'] = $this->session->getFlash('success');
        $data['flash_error'] = $this->session->getFlash('error');
        $data['flash_errors'] = $this->session->getFlash('errors', []);
        $data['old'] = $this->session->getFlash('old', []);
        $data['notifications_count'] = $this->getUnreadNotificationCount();

        echo $this->view->render($template, $data);
    }

    /**
     * Redirect to a URL.
     */
    protected function redirect(string $url, ?string $message = null, string $type = 'success'): void
    {
        if ($message) {
            $this->session->flash($type, $message);
        }
        header('Location: ' . url($url));
        exit();
    }

    /**
     * Send a JSON response.
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * Check CSRF token on POST requests.
     */
    protected function validateCsrf(): void
    {
        if ($this->request->isPost()) {
            $token = $this->request->input('csrf_token')
                  ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
            if (!$this->session->csrfValidate($token)) {
                $this->session->flash('error', 'رمز الأمان غير صالح. حاول مرة أخرى.');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('/')));
                exit();
            }
        }
    }

    /**
     * Check if user has a permission.
     */
    protected function authorize(string $permission): void
    {
        if (!can($permission)) {
            http_response_code(403);
            $this->render('errors.403');
            exit();
        }
    }

    /**
     * Get current authenticated user data.
     */
    protected function authUser(): ?array
    {
        static $user = null;
        if ($user === null && $this->session->isLoggedIn()) {
            $user = Database::getInstance()->fetch(
                "SELECT u.*, r.role_name, r.role_slug, fm.member_name, fm.department_id
                 FROM users u
                 LEFT JOIN roles r ON u.role_id = r.id
                 LEFT JOIN faculty_members fm ON u.member_id = fm.member_id
                 WHERE u.id = ? LIMIT 1",
                [$this->session->userId()]
            );
        }
        return $user;
    }

    /**
     * Get unread notifications count for navbar badge.
     */
    private function getUnreadNotificationCount(): int
    {
        if (!$this->session->isLoggedIn()) return 0;
        try {
            $row = Database::getInstance()->fetch(
                "SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0",
                [$this->session->userId()]
            );
            return (int)($row['cnt'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
