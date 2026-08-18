<?php

namespace Modules\Core\Infra\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Infra\Models\InfraRedirect;

class HandleDomainRedirects
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $host = $request->getHost();

        // Find redirect for this domain
        $redirect = InfraRedirect::where('from_domain', $host)
            ->where('is_active', true)
            ->first();

        if ($redirect) {
            $targetUrl = $this->buildTargetUrl($request, $redirect);

            return redirect()->to($targetUrl, $redirect->status_code);
        }

        return $next($request);
    }

    /**
     * Build the target URL for redirection.
     */
    protected function buildTargetUrl(Request $request, InfraRedirect $redirect): string
    {
        $scheme = $request->getScheme();
        $domain = $redirect->to_domain;
        $path = $redirect->keep_path ? $request->getPathInfo() : ($redirect->target_path ?? '/');
        $query = $request->getQueryString();

        $url = "{$scheme}://{$domain}".($path === '/' ? '' : $path);

        if ($query) {
            $url .= "?{$query}";
        }

        return $url;
    }
}
