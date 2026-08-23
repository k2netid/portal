<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\System\Models\Extension;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate console/API routes on Module Registry product status.
 *
 * Usage: middleware('extension.active:mail')
 */
class EnsureExtensionActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            return response()->json([
                'success' => false,
                'message' => 'Extension slug is required for extension.active middleware.',
                'error_code' => 'EXTENSION_SLUG_REQUIRED',
            ], 500);
        }

        $active = Extension::query()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->exists();

        if (! $active) {
            $code = strtoupper(str_replace('-', '_', $slug)).'_EXTENSION_INACTIVE';

            return response()->json([
                'success' => false,
                'message' => "Extension '{$slug}' is not active. Enable it from Module Registry & App Store.",
                'error_code' => $code,
            ], 403);
        }

        return $next($request);
    }
}
