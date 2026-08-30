<?php

declare(strict_types=1);

namespace Modules\Mail\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Middleware\EnsureExtensionActive;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated Prefer middleware('extension.active:mail'). Kept as alias for existing route groups.
 */
class EnsureMailExtensionActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return app(EnsureExtensionActive::class)->handle($request, $next, 'mail');
    }
}
