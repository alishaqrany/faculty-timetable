<?php

namespace App\Middleware;

class RbacMiddleware
{
    /**
     * Check permissions. Pass permission as route param '_permission'
     * or define in route middleware params.
     */
    public function handle(array $params = []): void
    {
        // The permission to check can be passed via route middleware config
        // For now, individual controllers handle their own permissions via authorize()
    }
}
