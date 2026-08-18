<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Modules\Core\Security\Models\SecurityLog;
use Modules\Core\Security\Rules\StrongPassword;
use Modules\Core\Security\Services\SecurityService;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\ActivityLog;
use Modules\Core\System\Models\LoginHistory;
use Modules\Core\System\Models\Role;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Core\System\Notifications\ResetPassword;
use Modules\Core\System\Services\CaptchaService;
use Modules\Core\System\Services\SessionManager;
use PragmaRX\Google2FA\Google2FA;

/**
 * @OA\Tag(name="Authentication")
 */
class AuthController extends BaseApiController
{
    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     tags={"Authentication"},
     *     summary="User login",
     *     description="Authenticate user and return access token",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="token", type="string", example="1|xxxxxxxxxxxx")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Email not verified",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     * User login.
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $emailRaw = $request->input('email');
        $email = is_string($emailRaw) ? $emailRaw : '';
        $passwordRaw = $request->input('password');
        $password = is_string($passwordRaw) ? $passwordRaw : '';

        // Verify Captcha - Skip if two_factor_code is present (step 2 of login)
        // This avoids 422 errors because the captcha token is consumed during the first attempt.
        if (Setting::get('enable_captcha', false) &&
            Setting::get('captcha_on_login', true) &&
            ! $request->has('two_factor_code')) {
            $captchaService = new CaptchaService;

            $request->validate([
                'captcha_token' => 'required|string',
                'captcha_answer' => 'required|string',
            ]);

            $captchaTokenRaw = $request->input('captcha_token');
            $captchaAnswerRaw = $request->input('captcha_answer');
            $captchaToken = is_string($captchaTokenRaw) ? $captchaTokenRaw : '';
            $captchaAnswer = is_string($captchaAnswerRaw) ? $captchaAnswerRaw : '';

            if (! $captchaService->verify($captchaToken, $captchaAnswer)) {
                return $this->validationError(['captcha' => ['Invalid captcha verification. Please try again.']]);
            }
        }

        $securityService = app(SecurityService::class);

        // Use IpHelper to get real client IP (handles proxies/CDN properly)
        $ipAddress = IpHelper::getClientIp($request);

        // Check if IP is blocked (auto-expires via cache TTL)
        if ($securityService->isIpBlocked($ipAddress)) {
            $remainingSeconds = $securityService->getRemainingBlockTime($ipAddress);
            $remainingMinutes = max(1, (int) ceil($remainingSeconds / 60));

            return response()->json([
                'success' => false,
                'message' => "Your IP address has been temporarily blocked. Please try again in {$remainingMinutes} minute(s).",
                'retry_after' => $remainingSeconds,
                'errors' => [
                    'email' => ["Too many failed login attempts. Please try again in {$remainingMinutes} minute(s)."],
                ],
            ], 429);
        }

        // Check if account is locked (auto-expires via cache TTL)
        if ($securityService->isAccountLocked($email)) {
            $remainingSeconds = $securityService->getAccountLockoutRemaining($email);
            $remainingMinutes = max(1, (int) ceil($remainingSeconds / 60));

            return response()->json([
                'success' => false,
                'message' => "Account temporarily locked. Please try again in {$remainingMinutes} minute(s).",
                'retry_after' => $remainingSeconds,
                'errors' => [
                    'email' => ["Account temporarily locked due to too many failed login attempts. Please try again in {$remainingMinutes} minute(s)."],
                ],
            ], 429);
        }

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, (string) $user->password)) {
            // Record failed login (may trigger progressive blocking)
            $result = $securityService->recordFailedLogin($email, $ipAddress);

            // Record failed login history if user exists
            if ($user) {
                LoginHistory::create([
                    'user_id' => $user->id,
                    'ip_address' => $ipAddress,
                    'user_agent' => is_string($request->userAgent()) ? $request->userAgent() : '',
                    'login_at' => now(),
                    'status' => 'failed',
                    'failure_reason' => 'Invalid password',
                ]);
            }

            // If IP was just blocked, return 429 with retry_after
            if ($result['ip_blocked']) {
                $blockDuration = $result['block_duration'];

                return response()->json([
                    'success' => false,
                    'message' => 'Too many failed attempts. Your IP has been temporarily blocked.',
                    'retry_after' => $blockDuration,
                    'errors' => [
                        'email' => ['Too many failed login attempts. Please try again later.'],
                    ],
                ], 429);
            }

            return $this->validationError([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check verification
        if (! $user->hasVerifiedEmail()) {
            return $this->error('Please verify your email address before logging in.', 403);
        }

        // Record successful login
        $securityService->recordSuccessfulLogin($user, $ipAddress);

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);

        // Record login history
        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $ipAddress,
            'user_agent' => is_string($request->userAgent()) ? $request->userAgent() : '',
            'login_at' => now(),
            'status' => 'success',
        ]);

        // Check if 2FA is enabled
        if ($user->hasTwoFactorEnabled()) {
            // Check if 2FA code is provided
            if (! $request->has('two_factor_code')) {
                return $this->success([
                    'requires_two_factor' => true,
                    'user_id' => $user->id,
                ], 'Two-factor authentication code required', 200);
            }

            // Verify 2FA code
            $twoFactorAuth = $user->twoFactorAuth;
            $twoFactorCodeRaw = $request->input('two_factor_code');
            $twoFactorCode = is_string($twoFactorCodeRaw) ? $twoFactorCodeRaw : '';

            $secret = $twoFactorAuth ? $twoFactorAuth->getDecryptedSecret() : null;

            $google2fa = new Google2FA;
            $valid = false;
            if ($secret) {
                $valid = $google2fa->verifyKey($secret, $twoFactorCode, 2);
            }

            // If TOTP fails, try backup code
            if (! $valid && $twoFactorAuth) {
                $valid = $twoFactorAuth->verifyBackupCode($twoFactorCode);
            }

            if (! $valid) {
                // Record failed login
                $securityService->recordFailedLogin($email, $ipAddress);

                LoginHistory::create([
                    'user_id' => $user->id,
                    'ip_address' => $ipAddress,
                    'user_agent' => is_string($request->userAgent()) ? $request->userAgent() : '',
                    'login_at' => now(),
                    'status' => 'failed',
                    'failure_reason' => 'Invalid 2FA code',
                ]);

                return $this->validationError([
                    'two_factor_code' => ['Invalid two-factor authentication code'],
                ]);
            }
        }

        // Log activity (optional - skip if ActivityLog doesn't exist)
        try {
            if (class_exists(ActivityLog::class)) {
                ActivityLog::log('login', null, [], $user, 'User logged in');
            }
        } catch (\Exception $e) {
            // ActivityLog not available, skip
        }

        // Handle concurrent login control
        $singleSession = Setting::get('single_session_enabled', false);
        $maxSessionsRaw = Setting::get('max_concurrent_sessions', 0);
        $maxSessions = is_numeric($maxSessionsRaw) ? (int) $maxSessionsRaw : 0;

        if ($singleSession) {
            // Revoke all previous tokens (Sanctum)
            $tokens = $user->tokens();
            $revokedCount = $tokens->count();

            Log::info("Single Session: User {$user->email} has {$revokedCount} tokens. Revoking...");

            if ($revokedCount > 0) {
                $tokens->delete();

                SecurityLog::log(
                    'session_invalidated',
                    $user,
                    $ipAddress,
                    "Previous {$revokedCount} session(s) invalidated due to new login (single session mode)"
                );
            }

            // Also invalidate previous sessions if using stateful session guards
            // Note: This requires the AuthenticateSession middleware to be active
            try {
                // Create web session for Sanctum SPA (stateful)
                Auth::login($user, $request->boolean('remember'));
                if ($request->hasSession()) {
                    $request->session()->regenerate();
                }

                // Set tiered session lifetime based on user role
                SessionManager::setLifetimeForUser($user);

                Auth::logoutOtherDevices($password);
                Log::info("Single Session: logoutOtherDevices called for {$user->email}");
            } catch (\Exception $e) {
                Log::error('Single Session Error: '.$e->getMessage());
            }
        } elseif ($maxSessions > 0) {
            // Limit concurrent sessions (token based)
            $activeTokens = $user->tokens()->orderBy('created_at', 'asc')->get();

            if ($activeTokens->count() >= $maxSessions) {
                // Remove oldest tokens to make room for new one
                $tokensToRemove = $activeTokens->count() - $maxSessions + 1;
                $oldestTokenIds = $activeTokens->take($tokensToRemove)->pluck('id');
                $user->tokens()->whereIn('id', $oldestTokenIds)->delete();

                SecurityLog::log(
                    'session_limit_reached',
                    $user,
                    $ipAddress,
                    "Oldest {$tokensToRemove} session(s) removed due to max session limit ({$maxSessions})"
                );
            }
        }

        $user->load('roles');
        $user->setRelation('permissions', $user->getAllPermissions());

        // Ensure session is started if it wasn't handled by single-session logic above
        // Only regenerate session if one exists (supports both SPA and pure API clients)
        if (! Auth::check()) {
            Auth::login($user, $request->boolean('remember'));
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }
        }

        // Determine target domain for redirection
        $baseUrl = config('app.url');
        $targetUrl = (is_string($baseUrl) ? rtrim($baseUrl, '/') : '').'/' . \Modules\Core\System\Models\Setting::resolveConsoleDashboardSlug();

        $user->load('roles');

        // Pure session-based auth - no token needed
        return $this->success([
            'user' => $user,
            'redirect_to' => $targetUrl,
        ], 'Login successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     tags={"Authentication"},
     *     summary="User registration",
     *     description="Register a new user account",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Registration successful",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     * User registration.
     */
    public function register(Request $request): JsonResponse
    {
        // Check if registration is enabled in settings
        $registrationEnabled = Setting::get('enable_registration', true);
        if (! $registrationEnabled) {
            return $this->error('Registration is currently disabled.', 403, [], 'REGISTRATION_DISABLED');
        }

        try {
            // Verify Captcha
            if (Setting::get('enable_captcha', false) && Setting::get('captcha_on_register', true)) {
                $captchaService = new CaptchaService;

                $request->validate([
                    'captcha_token' => 'required|string',
                    'captcha_answer' => 'required|string',
                ]);

                $captchaTokenRaw = $request->input('captcha_token');
                $captchaAnswerRaw = $request->input('captcha_answer');
                $captchaToken = is_string($captchaTokenRaw) ? $captchaTokenRaw : '';
                $captchaAnswer = is_string($captchaAnswerRaw) ? $captchaAnswerRaw : '';

                if (! $captchaService->verify($captchaToken, $captchaAnswer)) {
                    throw ValidationException::withMessages([
                        'captcha' => ['Invalid captcha verification. Please try again.'],
                    ]);
                }
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')],
                'password' => ['required', 'confirmed', 'min:8', new StrongPassword],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $passwordRaw = $validated['password'];
        $password = is_string($passwordRaw) ? $passwordRaw : '';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
        ]);

        // Assign default role (member)
        $memberRole = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        $user->assignRole($memberRole);

        // Send email verification
        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => $user->load('roles'),
            'token' => $token,
        ], 'Registration successful. Please verify your email address.', 201);
    }

    /**
     * User logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */

        // Log activity
        if ($user) {
            ActivityLog::log('logout', null, [], $user, 'User logged out');
        }

        // Pure session-based logout - invalidate session only
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Logout from web guard if authenticated
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $user->load('roles');
        $user->setRelation('permissions', $user->getAllPermissions());

        return $this->success($user, 'User retrieved successfully');
    }

    /**
     * Resend verification email.
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if ($user->hasVerifiedEmail()) {
            return $this->validationError(['email' => ['Email already verified']], 'Email already verified');
        }

        $user->sendEmailVerificationNotification();

        return $this->success(null, 'Verification email sent');
    }

    /**
     * Verify email address.
     *
     * @param  int|string  $id
     * @param  string  $hash
     */
    public function verifyEmail(Request $request, $id, $hash): JsonResponse
    {
        $user = User::findOrFail($id);
        /** @var User $user */
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->validationError(['hash' => ['Invalid verification link']], 'Invalid verification link');
        }

        if ($user->hasVerifiedEmail()) {
            return $this->success(null, 'Email already verified');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->success(null, 'Email verified successfully');
    }

    /**
     * Verify email address (API version).
     */
    public function verifyEmailApi(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'token' => 'required|string',
                'email' => 'required|email',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $emailRaw = $request->input('email');
        $email = is_string($emailRaw) ? $emailRaw : '';

        $user = User::where('email', $email)->first();

        if (! $user) {
            return $this->error('User not found', 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->success(null, 'Email already verified');
        }

        // Verify token: must match the HMAC signature of the user's email
        $tokenRaw = $request->input('token');
        $token = is_string($tokenRaw) ? $tokenRaw : '';
        $appKey = config('app.key');
        $expectedHash = hash_hmac('sha256', (string) $user->getEmailForVerification(), is_string($appKey) ? $appKey : '');

        if (! hash_equals($expectedHash, $token)) {
            return $this->error('Invalid or expired verification token', 400, [], 'INVALID_TOKEN');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));

            return $this->success(null, 'Email verified successfully');
        }

        return $this->error('Verification failed', 400);
    }

    /**
     * Resend verification email (API version).
     */
    public function resendVerificationEmailApi(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $emailRaw = $request->input('email');
        $email = is_string($emailRaw) ? $emailRaw : '';

        $user = User::where('email', $email)->first();

        if (! $user) {
            // Don't reveal if user exists (security best practice)
            return $this->success(null, 'If the email exists, a verification link has been sent');
        }

        if ($user->hasVerifiedEmail()) {
            return $this->validationError(['email' => ['Email already verified']], 'Email already verified');
        }

        $user->sendEmailVerificationNotification();

        return $this->success(null, 'Verification email sent');
    }

    /**
     * Handle forgot password request.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $request->validate(['email' => 'required|email']);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $emailRaw = $request->input('email');
        $email = is_string($emailRaw) ? $emailRaw : '';

        $user = User::where('email', $email)->first();

        if (! $user) {
            // Don't reveal if user exists (security best practice)
            return $this->success(null, 'If the email exists, a password reset link has been sent');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $user->notify(new ResetPassword((string) $token));

        return $this->success(null, 'If the email exists, a password reset link has been sent');
    }

    /**
     * Handle password reset request.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => ['required', 'confirmed', 'min:8', new StrongPassword],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $emailRaw = $request->input('email');
        $email = is_string($emailRaw) ? $emailRaw : '';
        $tokenRaw = $request->input('token');
        $token = is_string($tokenRaw) ? $tokenRaw : '';
        $passwordRaw = $request->input('password');
        $password = is_string($passwordRaw) ? $passwordRaw : '';

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (! $passwordReset || ! Hash::check($token, (string) $passwordReset->token)) {
            return $this->error('Invalid or expired reset token', 400, [], 'INVALID_TOKEN');
        }

        // Check if token is expired (60 minutes)
        if (Carbon::parse((string) $passwordReset->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return $this->error('Reset token has expired', 400, [], 'TOKEN_EXPIRED');
        }

        $user = User::where('email', $email)->firstOrFail();
        /** @var User $user */
        $user->update(['password' => Hash::make($password)]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Log activity
        ActivityLog::log('password_reset', null, [], $user, 'Password reset via email');

        return $this->success(null, 'Password reset successfully');
    }
}
