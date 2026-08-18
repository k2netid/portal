<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LazyExtensionBootMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        if ($route) {
            $action = $route->getAction('controller');
            if (is_string($action)) {
                $actionClean = ltrim($action, '\\');
                if (str_starts_with($actionClean, 'Extensions\\')) {
                    $parts = explode('\\', $actionClean);
                    $studlyName = $parts[1] ?? null;

                    if ($studlyName) {
                        $providerClass = "Extensions\\{$studlyName}\\{$studlyName}ServiceProvider";
                        if (class_exists($providerClass)) {
                            app()->register($providerClass);
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
