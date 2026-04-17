<?php

namespace App\Middleware;

/**
 * IP-based rate limiting middleware for API endpoints.
 * Uses file-based storage (no Redis/Memcached dependency).
 */
class RateLimitMiddleware
{
    private int $maxRequests = 60;    // requests per window
    private int $windowSeconds = 60;  // window duration

    public function handle(array $params = []): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = 'rate_' . md5($ip);
        $storageDir = APP_ROOT . '/storage/rate_limits';

        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }

        $file = $storageDir . '/' . $key . '.json';
        $now = time();
        $data = ['requests' => [], 'blocked_until' => 0];

        if (file_exists($file)) {
            $raw = @file_get_contents($file);
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $data = $decoded;
                }
            }
        }

        // If IP is temporarily blocked
        if (isset($data['blocked_until']) && $data['blocked_until'] > $now) {
            $retryAfter = $data['blocked_until'] - $now;
            http_response_code(429);
            header('Content-Type: application/json; charset=utf-8');
            header("Retry-After: {$retryAfter}");
            header("X-RateLimit-Limit: {$this->maxRequests}");
            header('X-RateLimit-Remaining: 0');
            echo json_encode([
                'error' => 'تم تجاوز الحد المسموح',
                'message' => 'Too many requests',
                'retry_after' => $retryAfter
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Filter requests within the current window
        $windowStart = $now - $this->windowSeconds;
        $data['requests'] = array_values(array_filter(
            $data['requests'] ?? [],
            fn($ts) => $ts > $windowStart
        ));

        if (count($data['requests']) >= $this->maxRequests) {
            // Block for the remainder of the window
            $data['blocked_until'] = $now + $this->windowSeconds;
            @file_put_contents($file, json_encode($data), LOCK_EX);

            http_response_code(429);
            header('Content-Type: application/json; charset=utf-8');
            header("Retry-After: {$this->windowSeconds}");
            header("X-RateLimit-Limit: {$this->maxRequests}");
            header('X-RateLimit-Remaining: 0');
            echo json_encode([
                'error' => 'تم تجاوز الحد المسموح',
                'message' => 'Too many requests',
                'retry_after' => $this->windowSeconds
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Record current request
        $data['requests'][] = $now;
        $remaining = $this->maxRequests - count($data['requests']);
        @file_put_contents($file, json_encode($data), LOCK_EX);

        // Set rate limit headers
        header("X-RateLimit-Limit: {$this->maxRequests}");
        header("X-RateLimit-Remaining: {$remaining}");
        header("X-RateLimit-Reset: " . ($now + $this->windowSeconds));
    }
}
