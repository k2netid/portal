<?php

declare(strict_types=1);

namespace Modules\Member\Support;

use Modules\Member\Models\Member;

/**
 * Canonical public reader profile shape for API + portal payloads.
 */
final class MemberPublicProfile
{
    /**
     * @return array{
     *   id: string,
     *   name: string,
     *   email: string,
     *   phone: string|null,
     *   avatar: string|null,
     *   bio: string|null,
     *   locale: string|null,
     *   timezone: string|null,
     *   status: string,
     *   email_verified: bool,
     *   pending_email: string|null,
     *   last_login_at: string|null,
     *   created_at: string|null
     * }
     */
    public static function serialize(Member $member): array
    {
        return [
            'id' => (string) $member->id,
            'name' => (string) $member->name,
            'email' => (string) $member->email,
            'phone' => self::nullableString($member->phone),
            'avatar' => self::nullableString($member->avatar),
            'bio' => self::nullableString($member->bio),
            'locale' => self::nullableString($member->locale),
            'timezone' => self::nullableString($member->timezone),
            'status' => (string) $member->status,
            'email_verified' => $member->email_verified_at !== null,
            'pending_email' => is_string($member->pending_email) ? $member->pending_email : null,
            'last_login_at' => $member->last_login_at?->toIso8601String(),
            'created_at' => $member->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function profileValidationRules(Member $member): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[\d\s+\-().#extxEXT]*$/u'],
            'avatar' => 'nullable|string|max:512',
            'bio' => 'nullable|string|max:500',
            'locale' => ['nullable', 'string', 'max:10', 'regex:/^[a-z]{2}([_-][A-Za-z]{2})?$/'],
            'timezone' => 'nullable|string|max:64|timezone:all',
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function profileFillAttributes(array $validated): array
    {
        $out = [
            'name' => trim((string) $validated['name']),
        ];

        foreach (['phone', 'avatar', 'bio', 'locale', 'timezone'] as $key) {
            if (! array_key_exists($key, $validated)) {
                continue;
            }
            $value = $validated[$key];
            if ($value === null || (is_string($value) && trim($value) === '')) {
                $out[$key] = null;
                continue;
            }
            $out[$key] = is_string($value) ? trim($value) : $value;
        }

        return $out;
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
