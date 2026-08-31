<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Member\Services\MemberPasswordReset;

class PasswordResetController extends BaseApiController
{
    public function forgot(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|max:255',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        try {
            app(MemberPasswordReset::class)->sendResetLink($validated['email']);
        } catch (\Throwable) {
            // Still return generic success — do not leak mail failures to attackers.
        }

        return $this->success(null, 'If the email exists, a password reset link has been sent');
    }

    public function reset(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|max:255',
                'token' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $ok = app(MemberPasswordReset::class)->reset(
            $validated['email'],
            $validated['token'],
            $validated['password'],
        );

        if (! $ok) {
            return $this->error('Invalid or expired reset token', 400, [], 'INVALID_TOKEN');
        }

        return $this->success(null, 'Password reset successfully');
    }
}
