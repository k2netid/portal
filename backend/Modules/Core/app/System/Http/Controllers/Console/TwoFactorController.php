<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\TwoFactorAuth;
use Modules\Core\System\Models\User;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends BaseApiController
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA;
    }

    /**
     * Generate QR code and secret for 2FA setup
     */
    public function generate(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Check global 2FA setting
        if (! Setting::get('enable_2fa', false)) {
            return $this->error(
                'Two-factor authentication is globally disabled.',
                400,
                [],
                '2FA_DISABLED'
            );
        }

        // Check if 2FA is already enabled
        if ($user->hasTwoFactorEnabled()) {
            return $this->error(
                'Two-factor authentication is already enabled. Please disable it first to generate a new secret.',
                400,
                [],
                '2FA_ALREADY_ENABLED'
            );
        }

        // Generate secret
        $secret = $this->google2fa->generateSecretKey();

        // Get or create 2FA record
        /** @var TwoFactorAuth $twoFactorAuth */
        $twoFactorAuth = $user->twoFactorAuth ?? new TwoFactorAuth;
        if (! $twoFactorAuth->exists) {
            $twoFactorAuth->user_id = $user->id;
        }
        $twoFactorAuth->setSecret($secret);
        $twoFactorAuth->enabled = false;
        $twoFactorAuth->save();

        // Generate QR code URL
        $appName = config('app.name');
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            is_string($appName) ? $appName : 'App',
            (string) $user->email,
            $secret
        );

        // Generate backup codes
        $backupCodes = $this->generateBackupCodes();
        $twoFactorAuth->setBackupCodes($backupCodes);
        $twoFactorAuth->save();

        return $this->success([
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
            'backup_codes' => $backupCodes, // Only show once during setup
        ], '2FA secret generated successfully. Please scan the QR code and verify with a code.');
    }

    /**
     * Verify TOTP code and enable 2FA
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        if (! Setting::get('enable_2fa', false)) {
            return $this->error(
                'Two-factor authentication is globally disabled.',
                400,
                [],
                '2FA_DISABLED'
            );
        }

        /** @var User $user */
        $user = $request->user();
        /** @var TwoFactorAuth|null $twoFactorAuth */
        $twoFactorAuth = $user->twoFactorAuth()->first();

        if (! $twoFactorAuth || $twoFactorAuth->enabled) {
            return $this->error(
                '2FA is not set up or already enabled',
                400,
                [],
                '2FA_INVALID_STATE'
            );
        }

        $secret = $twoFactorAuth->getDecryptedSecret();
        if (! $secret) {
            return $this->error(
                'Invalid 2FA secret. Please generate a new one.',
                400,
                [],
                'INVALID_SECRET'
            );
        }

        $code = is_string($request->input('code')) ? $request->input('code') : '';

        // Verify code
        $valid = $this->google2fa->verifyKey($secret, $code, 2); // 2 = 2 time windows tolerance

        if (! $valid) {
            return $this->error(
                'Invalid verification code. Please try again.',
                422,
                [],
                'INVALID_CODE'
            );
        }

        // Enable 2FA
        $twoFactorAuth->enabled = true;
        $twoFactorAuth->enabled_at = now();
        $twoFactorAuth->save();

        return $this->success([
            'enabled' => true,
            'enabled_at' => $twoFactorAuth->enabled_at,
            'backup_codes_count' => $twoFactorAuth->getRemainingBackupCodesCount(),
        ], 'Two-factor authentication enabled successfully');
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        /** @var User $user */
        $user = $request->user();

        $password = is_string($request->input('password')) ? $request->input('password') : '';

        // Verify password
        if (! Hash::check($password, (string) $user->password)) {
            return $this->validationError([
                'password' => ['Invalid password'],
            ]);
        }

        // Check if 2FA is required (admin users)
        if ($user->requiresTwoFactor()) {
            return $this->error(
                'Two-factor authentication is required for admin users and cannot be disabled.',
                403,
                [],
                '2FA_REQUIRED'
            );
        }

        /** @var TwoFactorAuth|null $twoFactorAuth */
        $twoFactorAuth = $user->twoFactorAuth()->first();
        if ($twoFactorAuth) {
            $twoFactorAuth->enabled = false;
            $twoFactorAuth->secret = null;
            $twoFactorAuth->backup_codes = null;
            $twoFactorAuth->save();
        }

        return $this->success(null, 'Two-factor authentication disabled successfully');
    }

    /**
     * Regenerate backup codes
     */
    public function regenerateBackupCodes(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        /** @var User $user */
        $user = $request->user();

        $password = is_string($request->input('password')) ? $request->input('password') : '';

        // Verify password
        if (! Hash::check($password, (string) $user->password)) {
            return $this->validationError([
                'password' => ['Invalid password'],
            ]);
        }

        /** @var TwoFactorAuth|null $twoFactorAuth */
        $twoFactorAuth = $user->twoFactorAuth()->first();
        if (! $twoFactorAuth || ! $twoFactorAuth->enabled) {
            return $this->error(
                '2FA is not enabled',
                400,
                [],
                '2FA_NOT_ENABLED'
            );
        }

        // Generate new backup codes
        $backupCodes = $this->generateBackupCodes();
        $twoFactorAuth->setBackupCodes($backupCodes);
        $twoFactorAuth->save();

        return $this->success([
            'backup_codes' => $backupCodes, // Only show once
        ], 'Backup codes regenerated successfully. Please save them in a safe place.');
    }

    /**
     * Verify TOTP code (for login)
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', Rule::exists(User::class, 'id')],
            'code' => 'required|string|size:6',
        ]);

        /** @var User $user */
        $user = User::findOrFail($request->input('user_id'));
        /** @var TwoFactorAuth|null $twoFactorAuth */
        $twoFactorAuth = $user->twoFactorAuth;

        if (! $twoFactorAuth || ! $twoFactorAuth->enabled) {
            return $this->error(
                '2FA is not enabled for this user',
                400,
                [],
                '2FA_NOT_ENABLED'
            );
        }

        $secret = $twoFactorAuth->getDecryptedSecret();
        if (! $secret) {
            return $this->error(
                'Invalid 2FA secret',
                400,
                [],
                'INVALID_SECRET'
            );
        }

        $code = is_string($request->input('code')) ? $request->input('code') : '';

        // Try TOTP code first
        $valid = $this->google2fa->verifyKey($secret, $code, 2);

        // If TOTP fails, try backup code
        if (! $valid) {
            $valid = $twoFactorAuth->verifyBackupCode($code);
        }

        if (! $valid) {
            return $this->error(
                'Invalid verification code',
                422,
                [],
                'INVALID_CODE'
            );
        }

        return $this->success([
            'verified' => true,
            'remaining_backup_codes' => $twoFactorAuth->getRemainingBackupCodesCount(),
        ], 'Code verified successfully');
    }

    /**
     * Get 2FA status
     */
    public function status(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var TwoFactorAuth|null $twoFactorAuth */
        $twoFactorAuth = $user->twoFactorAuth()->first();

        return $this->success([
            'enabled' => $user->hasTwoFactorEnabled(),
            'required' => $user->requiresTwoFactor(),
            'backup_codes_count' => $twoFactorAuth ? $twoFactorAuth->getRemainingBackupCodesCount() : 0,
            'enabled_at' => $twoFactorAuth?->enabled_at,
            'global_enabled' => (bool) Setting::get('enable_2fa', false),
        ], '2FA status retrieved successfully');
    }

    /**
     * Generate backup codes
     *
     * @return array<string>
     */
    private function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(8));
        }

        return $codes;
    }
}
