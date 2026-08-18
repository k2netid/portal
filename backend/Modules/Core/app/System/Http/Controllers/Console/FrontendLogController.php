<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Http\Controllers\BaseApiController;

class FrontendLogController extends BaseApiController
{
    /**
     * Handle incoming frontend log entries.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'stack' => 'nullable|string',
            'url' => 'nullable|string', // Changed from url to string for flexibility
            'user_agent' => 'nullable|string',
            'user_id' => 'nullable|integer',
            'data' => 'nullable|array',
            'level' => 'nullable|string|in:debug,info,warning,error,critical',
        ]);

        $stack = $validated['stack'] ?? null;
        if ($stack && strlen((string) $stack) > 3000) {
            $stack = substr((string) $stack, 0, 3000)."\n... (truncated by backend)";
        }

        $authenticatedUserId = $request->user()?->id;
        $reportedUserId = isset($validated['user_id']) && is_numeric($validated['user_id'])
            ? (int) $validated['user_id']
            : null;
        // Prefer server-side identity; only accept body user_id for unauthenticated clients.
        $userIdForLog = $authenticatedUserId ?? $reportedUserId;

        $context = [
            'url' => $validated['url'] ?? null,
            'user_id' => $userIdForLog,
            'user_agent' => $validated['user_agent'] ?? $request->userAgent(),
            'stack' => $stack,
            'data' => $validated['data'] ?? [],
            'ip' => IpHelper::getClientIp($request),
        ];

        $level = $validated['level'] ?? 'error';
        $message = $validated['message'];

        // Log to specific frontend channel
        Log::channel('frontend')->log($level, "Frontend Error: {$message}", $context);

        return $this->success(null, 'logged');
    }
}
