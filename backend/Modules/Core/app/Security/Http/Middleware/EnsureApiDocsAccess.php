<?php

declare(strict_types=1);

namespace Modules\Core\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate Scramble docs UI/JSON behind viewApiDocs (admin+) in every environment.
 *
 * Scramble's RestrictedDocsAccess opens /docs/api when APP_ENV=local; that is too
 * loose on shared LAN hosts. Prefer this middleware in config/scramble.php.
 */
final class EnsureApiDocsAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Gate::allows('viewApiDocs')) {
            return $next($request);
        }

        if ($request->user() === null) {
            if ($this->wantsDocumentPayload($request)) {
                abort(403, 'API documentation requires authentication.');
            }

            return redirect()->guest('/auth/console-sign-in');
        }

        abort(403, 'API documentation access requires an admin role.');
    }

    private function wantsDocumentPayload(Request $request): bool
    {
        return $request->expectsJson()
            || $request->wantsJson()
            || str_ends_with($request->path(), '.json');
    }
}
