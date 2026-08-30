<?php

namespace Modules\Analytics\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Analytics\Models\AnalyticsSession;
use Modules\Analytics\Models\AnalyticsVisit;
use Modules\Core\System\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class TrackAnalytics
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Only track GET requests and successful responses
        if ($request->method() === 'GET' && $response->getStatusCode() === 200) {
            // Skip tracking for admin/api routes
            if (! $this->shouldTrack($request)) {
                return;
            }

            try {
                $sessionId = session()->getId();

                // Start or get session (this always returns a session)
                $session = AnalyticsSession::start($request, $sessionId);

                // Track visit
                AnalyticsVisit::trackVisit($request);

                // Page views are now incremented inside trackVisit()

            } catch (\Exception $e) {
                // Log error but don't break anything
                \Log::error('Analytics tracking failed: '.$e->getMessage());
            }
        }
    }

    protected function shouldTrack(Request $request): bool
    {
        $path = $request->path();
        $first = (string) $request->segment(1);

        if ($first === 'api') {
            return false;
        }

        // Legacy console URL prefix (pre–console_dashboard_slug); do not pollute analytics.
        if ($first === 'admin') {
            return false;
        }

        try {
            $console = trim((string) Setting::resolveConsoleDashboardSlug());
            if ($console !== '' && strcasecmp($first, $console) === 0) {
                return false;
            }
        } catch (\Throwable) {
            // If settings unavailable, still track (better than silent drop of all traffic).
        }

        // Don't track static assets
        return ! preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path);
    }
}
