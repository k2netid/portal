<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKycLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  $level  (e.g. level_1, level_2)
     */
    public function handle(Request $request, Closure $next, string $level = 'level_1'): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Compare KYC levels (simple string comparison or custom logic)
        $currentLevel = $user->kyc_level ?? 'level_0';

        $levelMap = [
            'level_0' => 0,
            'level_1' => 1,
            'level_2' => 2,
            'level_3' => 3,
        ];

        $currentVal = $levelMap[$currentLevel] ?? 0;
        $requiredVal = $levelMap[$level] ?? 1;

        if ($currentVal < $requiredVal) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient KYC level. Requires '.$level,
                'required_level' => $level,
                'current_level' => $currentLevel,
                'onboarding_step' => $user->onboarding_step ?? 0,
            ], 403);
        }

        return $next($request);
    }
}
