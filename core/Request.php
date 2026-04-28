<?php

class Request
{
    private array $get;
    private array $post;
    private array $server;
    private array $files;

    public function __construct()
    {
        $this->get    = $_GET;
        $this->post   = $_POST;
        $this->server = $_SERVER;
        $this->files  = $_FILES;
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function isAjax(): bool
    {
        return ($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public function input(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function get(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function only(array $keys): array
    {
        $data = $this->all();
        return array_intersect_key($data, array_flip($keys));
    }

    public function has(string $key): bool
    {
        return isset($this->post[$key]) || isset($this->get[$key]);
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function uri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $uri = is_string($uri) ? strtok($uri, '?') : '/';

        $basePath = app_base_path();
        if ($basePath !== '' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . trim((string) $uri, '/');

        if ($uri === '/index.php') {
            return '/';
        }

        if (strpos($uri, '/index.php/') === 0) {
            $uri = substr($uri, strlen('/index.php'));
        }

        return $uri === '' ? '/' : $uri;
    }

    public function queryString(): string
    {
        return $this->server['QUERY_STRING'] ?? '';
    }

    /**
     * Validate input against rules.
     * Returns validated data or sets flash errors and returns false.
     */
    public function validate(array $rules): array|false
    {
        $errors = [];
        $validated = [];

        foreach ($rules as $field => $ruleString) {
            $value = $this->input($field);
            $fieldRules = is_array($ruleString) ? $ruleString : explode('|', $ruleString);

            foreach ($fieldRules as $rule) {
                $param = null;
                if (str_contains($rule, ':')) {
                    [$rule, $param] = explode(':', $rule, 2);
                }

                $error = $this->checkRule($field, $value, $rule, $param);
                if ($error) {
                    $errors[$field][] = $error;
                }
            }

            $validated[$field] = is_string($value) ? trim($value) : $value;
        }

        if ($errors) {
            Session::getInstance()->flash('errors', $errors);
            Session::getInstance()->flash('old', $this->all());
            return false;
        }

        return $validated;
    }

    private function checkRule(string $field, $value, string $rule, ?string $param): ?string
    {
        $labels = [
            'required' => 'حقل مطلوب',
            'integer'  => 'يجب أن يكون رقماً صحيحاً',
            'numeric'  => 'يجب أن يكون رقماً',
            'email'    => 'بريد إلكتروني غير صالح',
            'min'      => "يجب ألا يقل عن $param حرف",
            'max'      => "يجب ألا يزيد عن $param حرف",
            'in'       => 'قيمة غير مسموحة',
            'unique'   => 'القيمة موجودة مسبقاً',
            'date'     => 'تاريخ غير صالح',
        ];

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '') return $labels['required'];
                break;
            case 'integer':
                if ($value !== null && $value !== '' && !ctype_digit((string)$value)) return $labels['integer'];
                break;
            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) return $labels['numeric'];
                break;
            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) return $labels['email'];
                break;
            case 'min':
                if ($value !== null && mb_strlen($value) < (int)$param) return $labels['min'];
                break;
            case 'max':
                if ($value !== null && mb_strlen($value) > (int)$param) return $labels['max'];
                break;
            case 'in':
                $allowed = explode(',', $param);
                if ($value !== null && $value !== '' && !in_array($value, $allowed)) return $labels['in'];
                break;
            case 'date':
                if ($value !== null && $value !== '' && !strtotime($value)) return $labels['date'];
                break;
            case 'unique':
                // param format: table.column or table.column.exceptId
                $parts = explode(',', $param);
                $table = $parts[0];
                $column = $parts[1] ?? $field;
                $exceptId = $parts[2] ?? null;
                $db = Database::getInstance();
                $sql = "SELECT COUNT(*) as cnt FROM `$table` WHERE `$column` = ?";
                $p = [$value];
                if ($exceptId) {
                    $pk = $parts[3] ?? 'id';
                    $sql .= " AND `$pk` != ?";
                    $p[] = $exceptId;
                }
                $row = $db->fetch($sql, $p);
                if ($row && $row['cnt'] > 0) return $labels['unique'];
                break;
        }
        return null;
    }
}
