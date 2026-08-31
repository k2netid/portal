<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Member\Models\Member;
use Modules\Member\Services\MemberAccountService;
use Modules\Member\Services\MemberEmailChange;
use Modules\Member\Support\MemberPublicProfile;

class ProfileController extends BaseApiController
{
    public function update(Request $request): JsonResponse
    {
        $member = $request->user('member');
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        try {
            $validated = $request->validate(MemberPublicProfile::profileValidationRules($member));
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $member->update(MemberPublicProfile::profileFillAttributes($validated));

        return $this->success($member->fresh()?->toPublicProfile() ?? MemberPublicProfile::serialize($member), 'Profile updated');
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $member = $request->user('member');
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        try {
            $validated = $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        if (! Hash::check($validated['current_password'], (string) $member->password)) {
            return $this->validationError([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $member->update(['password' => $validated['password']]);

        return $this->success(null, 'Password updated');
    }

    public function requestEmailChange(Request $request): JsonResponse
    {
        $member = $request->user('member');
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        try {
            $validated = $request->validate([
                'email' => 'required|email|max:255|unique:mem_members,email,'.$member->id,
                'current_password' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        if (! Hash::check($validated['current_password'], (string) $member->password)) {
            return $this->validationError([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        if (strcasecmp($validated['email'], (string) $member->email) === 0) {
            return $this->validationError([
                'email' => ['New email must be different from your current email.'],
            ]);
        }

        try {
            app(MemberEmailChange::class)->request($member, $validated['email']);
        } catch (\Throwable) {
            return $this->error('Could not send confirmation email', 503);
        }

        return $this->success([
            'pending_email' => $validated['email'],
        ], 'Confirmation email sent to the new address');
    }

    public function confirmEmailChange(Request $request, string $id, string $hash): JsonResponse|RedirectResponse
    {
        $change = app(MemberEmailChange::class);
        $member = Member::query()->find($id);
        $status = 'invalid';

        if ($member instanceof Member) {
            $status = $change->confirm($member, $hash);
            if ($status === 'mismatch') {
                $status = 'invalid';
            }
        }

        if ($request->expectsJson()) {
            if ($status !== 'ok') {
                return $this->error('Invalid email change link', 403);
            }

            return $this->success(['email_verified' => true], 'Email updated');
        }

        return redirect()->away($change->frontendResultUrl($status));
    }

    public function destroy(Request $request): JsonResponse
    {
        $member = $request->user('member');
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        try {
            $validated = $request->validate([
                'current_password' => 'required|string',
                'confirm' => 'required|in:DELETE',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        if (! Hash::check($validated['current_password'], (string) $member->password)) {
            return $this->validationError([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        app(MemberAccountService::class)->delete($member);

        return $this->success(null, 'Account deleted');
    }
}
