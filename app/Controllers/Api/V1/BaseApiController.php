<?php
namespace App\Controllers\Api\V1;

class BaseApiController extends \Controller
{
    protected function ok($data = null, string $message = 'OK', array $meta = []): void
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }
        $this->json($payload, 200);
    }

    protected function created($data = null, string $message = 'Created'): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], 201);
    }

    protected function fail(string $message, int $status = 400, array $errors = []): void
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];
        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }
        $this->json($payload, $status);
    }

    protected function apiUser(): ?array
    {
        return $GLOBALS['api_user'] ?? null;
    }

    protected function paginationMeta(int $total, int $page, int $perPage): array
    {
        return [
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => max(1, (int)ceil($total / max(1, $perPage))),
        ];
    }
}
