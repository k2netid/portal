<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScimAuth
{
    /**
     * Handle an incoming request for SCIM API.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('services.scim.token');
        $expectedToken = is_string($expectedToken) ? $expectedToken : null;
        $bearerToken = $request->bearerToken();

        if (is_string($expectedToken) && $expectedToken !== '') {
            if (! is_string($bearerToken) || ! hash_equals($expectedToken, $bearerToken)) {
                return response()->json([
                    'schemas' => ['urn:ietf:params:scim:api:messages:2.0:Error'],
                    'detail' => 'Unauthorized or invalid SCIM token',
                    'status' => '401',
                ], 401);
            }
        }

        return $next($request);
    }
}
