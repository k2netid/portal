<?php

namespace Modules\Core\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizePaginationParams
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('per_page')) {
            $perPageRaw = $request->input('per_page');
            $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 20;
            $perPage = max(1, min($perPage, 100));
            $request->merge(['per_page' => $perPage]);
        }

        return $next($request);
    }
}
