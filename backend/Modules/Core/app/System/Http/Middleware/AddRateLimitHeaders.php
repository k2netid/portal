<?php

namespace Modules\Core\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Add Rate Limit Headers Middleware
 * Adds rate limit information to API responses.
 */
class AddRateLimitHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Get rate limit info from throttle middleware
        $rateLimitInfo = $this->getRateLimitInfo($request);

        if ($rateLimitInfo) {
            $response->headers->set('X-RateLimit-Limit', (string) $rateLimitInfo['limit']);
            $response->headers->set('X-RateLimit-Remaining', (string) $rateLimitInfo['remaining']);
            $response->headers->set('X-RateLimit-Reset', (string) $rateLimitInfo['reset']);
        }

        return $response;
    }

    /**
     * Get rate limit information from request
     *
     * @return array{limit: int, remaining: int, reset: int}|null
     */
    protected function getRateLimitInfo(Request $request): ?array
    {
        // Laravel's throttle middleware stores rate limit info in response headers
        // We'll extract it if available
        $limit = $request->header('X-RateLimit-Limit');
        $remaining = $request->header('X-RateLimit-Remaining');
        $reset = $request->header('X-RateLimit-Reset');

        if ($limit !== null) {
            return [
                'limit' => (int) $limit,
                'remaining' => (int) ($remaining ?? 0),
                'reset' => (int) ($reset ?? time() + 60),
            ];
        }

        return null;
    }
}
