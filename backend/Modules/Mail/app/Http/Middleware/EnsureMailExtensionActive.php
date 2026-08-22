<?php

declare(strict_types=1);

namespace Modules\Mail\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\System\Models\Extension;
use Symfony\Component\HttpFoundation\Response;

class EnsureMailExtensionActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $active = Extension::query()
            ->where('slug', 'mail')
            ->where('status', 'active')
            ->exists();

        if (! $active) {
            return response()->json([
                'success' => false,
                'message' => 'JA-Mail extension is not active. Enable it from App Store.',
                'error_code' => 'MAIL_EXTENSION_INACTIVE',
            ], 403);
        }

        return $next($request);
    }
}
