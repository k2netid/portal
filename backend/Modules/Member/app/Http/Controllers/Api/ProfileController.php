<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Member\Models\Member;

class ProfileController extends BaseApiController
{
    public function update(Request $request): JsonResponse
    {
        $member = $request->user('member');
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $member->update(['name' => $validated['name']]);

        return $this->success($this->publicMember($member->fresh() ?? $member), 'Profile updated');
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
