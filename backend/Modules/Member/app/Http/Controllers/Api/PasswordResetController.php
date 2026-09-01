<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Core\System\Contracts\PasswordPolicyPortInterface;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Member\Contracts\MemberSecurityAuditPortInterface;
use Modules\Member\Models\Member;
use Modules\Member\Services\MemberPasswordReset;
use Modules\Member\Support\MemberCaptchaGuard;

class PasswordResetController extends BaseApiController
{
    public function forgot(Request $request): JsonResponse
    {
        try {
            MemberCaptchaGuard::assert($request, 'forgot-password');
            $validated = $request->validate([
                'email' => 'required|email|max:255',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $email = $validated['email'];
        $member = Member::query()->where('email', $email)->first();

        try {
            app(MemberPasswordReset::class)->sendResetLink($email);
        } catch (\Throwable) {
            // Still return generic success — do not leak mail failures to attackers.
        }

        app(MemberSecurityAuditPortInterface::class)->record(
            'member_password_reset_requested',
            $member instanceof Member ? $member : null,
            "Member password reset requested for: {$email}",
            ['email' => $email],
        );

        return $this->success(null, 'If the email exists, a password reset link has been sent');
    }

    public function reset(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|max:255',
                'token' => 'required|string',
                'password' => ['required', 'string', 'confirmed', app(PasswordPolicyPortInterface::class)->rule()],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $member = Member::query()->where('email', $validated['email'])->first();

        $ok = app(MemberPasswordReset::class)->reset(
            $validated['email'],
            $validated['token'],
            $validated['password'],
        );

        if (! $ok) {
            return $this->error('Invalid or expired reset token', 400, [], 'INVALID_TOKEN');
        }

        app(MemberSecurityAuditPortInterface::class)->record(
            'member_password_reset',
            $member instanceof Member ? $member : null,
            "Member password reset completed for: {$validated['email']}",
            ['email' => $validated['email']],
        );

        return $this->success(null, 'Password reset successfully');
    }
}
