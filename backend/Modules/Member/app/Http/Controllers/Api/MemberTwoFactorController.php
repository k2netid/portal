<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Setting;
use Modules\Member\Contracts\MemberSecurityAuditPortInterface;
use Modules\Member\Models\Member;
use Modules\Member\Models\MemberTwoFactor;
use PragmaRX\Google2FA\Google2FA;

class MemberTwoFactorController extends BaseApiController
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA;
    }

    public function status(Request $request): JsonResponse
    {
        $member = $this->member($request);
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        $twoFactor = $member->twoFactor;

        return $this->success([
            'globally_enabled' => (bool) Setting::get('enable_2fa', false),
            'enabled' => $member->hasTwoFactorEnabled(),
            'enabled_at' => $twoFactor?->enabled_at?->toIso8601String(),
            'backup_codes_count' => $twoFactor?->getRemainingBackupCodesCount() ?? 0,
        ], 'Member 2FA status');
    }

    public function generate(Request $request): JsonResponse
    {
        $member = $this->member($request);
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        if (! Setting::get('enable_2fa', false)) {
            return $this->error('Two-factor authentication is globally disabled.', 400, [], '2FA_DISABLED');
        }

        if ($member->hasTwoFactorEnabled()) {
            return $this->error('Two-factor authentication is already enabled.', 400, [], '2FA_ALREADY_ENABLED');
        }

        $secret = $this->google2fa->generateSecretKey();
        $twoFactor = $member->twoFactor ?? new MemberTwoFactor(['member_id' => $member->id]);
        $twoFactor->member_id = $member->id;
        $twoFactor->setSecret($secret);
        $twoFactor->enabled = false;
        $backupCodes = $this->generateBackupCodes();
        $twoFactor->setBackupCodes($backupCodes);
        $twoFactor->save();

        $appName = config('app.name');
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            is_string($appName) ? $appName : 'App',
            (string) $member->email,
            $secret,
        );

        return $this->success([
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
            'backup_codes' => $backupCodes,
        ], '2FA secret generated');
    }

    public function verify(Request $request): JsonResponse
    {
        $member = $this->member($request);
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        if (! Setting::get('enable_2fa', false)) {
            return $this->error('Two-factor authentication is globally disabled.', 400, [], '2FA_DISABLED');
        }

        try {
            $validated = $request->validate([
                'code' => 'required|string|size:6',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $twoFactor = $member->twoFactor()->first();
        if ($twoFactor === null || $twoFactor->enabled === true) {
            return $this->error('2FA is not set up or already enabled', 400, [], '2FA_INVALID_STATE');
        }

        $secret = $twoFactor->getDecryptedSecret();
        if (! $secret || ! $this->google2fa->verifyKey($secret, $validated['code'], 2)) {
            return $this->error('Invalid verification code.', 422, [], 'INVALID_CODE');
        }

        $twoFactor->enabled = true;
        $twoFactor->enabled_at = now();
        $twoFactor->save();

        app(MemberSecurityAuditPortInterface::class)->record(
            'member_2fa_enabled',
            $member,
            "Member 2FA enabled: {$member->email}",
        );

        return $this->success([
            'enabled' => true,
            'enabled_at' => $twoFactor->enabled_at?->toIso8601String(),
            'backup_codes_count' => $twoFactor->getRemainingBackupCodesCount(),
        ], 'Two-factor authentication enabled');
    }

    public function disable(Request $request): JsonResponse
    {
        $member = $this->member($request);
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        try {
            $validated = $request->validate([
                'password' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        if (! Hash::check($validated['password'], (string) $member->password)) {
            return $this->validationError(['password' => ['Invalid password']]);
        }

        $twoFactor = $member->twoFactor;
        if ($twoFactor) {
            $twoFactor->enabled = false;
            $twoFactor->secret = null;
            $twoFactor->backup_codes = null;
            $twoFactor->enabled_at = null;
            $twoFactor->save();
        }

        app(MemberSecurityAuditPortInterface::class)->record(
            'member_2fa_disabled',
            $member,
            "Member 2FA disabled: {$member->email}",
        );

        return $this->success(null, 'Two-factor authentication disabled');
    }

    public function regenerateBackupCodes(Request $request): JsonResponse
    {
        $member = $this->member($request);
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        try {
            $validated = $request->validate([
                'password' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        if (! Hash::check($validated['password'], (string) $member->password)) {
            return $this->validationError(['password' => ['Invalid password']]);
        }

        $twoFactor = $member->twoFactor()->first();
        if ($twoFactor === null || $twoFactor->enabled !== true) {
            return $this->error('2FA is not enabled', 400, [], '2FA_NOT_ENABLED');
        }

        $backupCodes = $this->generateBackupCodes();
        $twoFactor->setBackupCodes($backupCodes);
        $twoFactor->save();

        app(MemberSecurityAuditPortInterface::class)->record(
            'member_2fa_backup_codes_regenerated',
            $member,
            "Member 2FA backup codes regenerated: {$member->email}",
        );

        return $this->success([
            'backup_codes' => $backupCodes,
            'backup_codes_count' => $twoFactor->getRemainingBackupCodesCount(),
        ], 'Backup codes regenerated successfully. Please save them in a safe place.');
    }

    private function member(Request $request): ?Member
    {
        $member = $request->user('member');

        return $member instanceof Member ? $member : null;
    }

    /**
     * @return list<string>
     */
    private function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(Str::random(4).'-'.Str::random(4));
        }

        return $codes;
    }
}
