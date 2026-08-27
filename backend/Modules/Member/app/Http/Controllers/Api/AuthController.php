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
use Modules\Member\Services\MemberEmailVerification;

class AuthController extends BaseApiController
{
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:mem_members,email',
                'password' => 'required|string|min:8|confirmed',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $member = Member::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => 'active',
        ]);

        $token = $member->createToken('member')->plainTextToken;

        try {
            app(MemberEmailVerification::class)->send($member);
        } catch (\Throwable) {
            // Registration still succeeds if outbound mail is not configured.
        }

        return $this->success([
            'member' => $this->publicMember($member),
            'token' => $token,
        ], 'Member registered', 201);
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $member = Member::query()->where('email', $validated['email'])->first();
        if ($member === null || ! Hash::check($validated['password'], (string) $member->password)) {
            return $this->error('Invalid member credentials', 401);
        }

        if ($member->status !== 'active') {
            return $this->error('Member account is not active', 403);
        }

        $token = $member->createToken('member')->plainTextToken;

        return $this->success([
            'member' => $this->publicMember($member),
            'token' => $token,
        ], 'Member logged in');
    }

    public function me(Request $request): JsonResponse
    {
        $member = $request->user('member');
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        return $this->success($this->publicMember($member), 'Member profile');
    }

    public function logout(Request $request): JsonResponse
    {
        $member = $request->user('member');
        if ($member instanceof Member) {
            $member->currentAccessToken()?->delete();
        }

        return $this->success(null, 'Member logged out');
    }

    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse|RedirectResponse
    {
        $verification = app(MemberEmailVerification::class);
        $member = Member::query()->find($id);
        $status = 'invalid';

        if ($member instanceof Member && $verification->isValidHash($member, $hash)) {
            if ($member->email_verified_at !== null) {
                $status = 'already';
            } else {
                $member->forceFill(['email_verified_at' => now()])->save();
                $status = 'ok';
            }
        }

        if ($request->expectsJson()) {
            if ($status === 'invalid') {
                return $this->error('Invalid verification link', 403);
            }

            return $this->success(['email_verified' => true], $status === 'already' ? 'Email already verified' : 'Email verified');
        }

        return redirect()->away($verification->frontendResultUrl($status));
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $member = $request->user('member');
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        if ($member->email_verified_at !== null) {
            return $this->success(['email_verified' => true], 'Email already verified');
        }

        try {
            app(MemberEmailVerification::class)->send($member);
        } catch (\Throwable) {
            return $this->error('Could not send verification email', 503);
        }

        return $this->success(null, 'Verification email sent');
    }

    /**
     * @return array{id: string, name: string, email: string, status: string, email_verified: bool}
     */
    private function publicMember(Member $member): array
    {
        return [
            'id' => (string) $member->id,
            'name' => (string) $member->name,
            'email' => (string) $member->email,
            'status' => (string) $member->status,
            'email_verified' => $member->email_verified_at !== null,
        ];
    }
}
