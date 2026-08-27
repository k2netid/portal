<?php

declare(strict_types=1);

namespace Modules\Member\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Member\Models\Member;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $member = $request->user('member');
        if (! $member instanceof Member) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error_code' => 'UNAUTHENTICATED',
            ], 401);
        }

        if ($member->email_verified_at === null) {
            return response()->json([
                'success' => false,
                'message' => 'Verify your email before using this feature.',
                'error_code' => 'EMAIL_UNVERIFIED',
            ], 403);
        }

        return $next($request);
    }
}
