<?php

declare(strict_types=1);

namespace Modules\Member\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Modules\Core\System\Contracts\PasswordPolicyPortInterface;
use Modules\Core\System\Support\SqlLikeEscape;
use Modules\Member\Models\Member;

final class MemberDirectorySupport
{
    /**
     * @return array<string, mixed>
     */
    public static function serialize(Member $member, bool $detailed = false): array
    {
        $data = [
            'id' => (string) $member->id,
            'name' => $member->name,
            'email' => (string) $member->email,
            'phone' => $member->phone,
            'status' => (string) $member->status,
            'email_verified_at' => $member->email_verified_at,
            'last_login_at' => $member->last_login_at,
            'created_at' => $member->created_at,
            'deleted_at' => $member->deleted_at,
        ];

        if (! $detailed) {
            return $data;
        }

        $data['avatar'] = $member->avatar;
        $data['bio'] = $member->bio;
        $data['locale'] = $member->locale;
        $data['timezone'] = $member->timezone;
        $data['pending_email'] = $member->pending_email;
        $data['activity'] = self::activityCounts($member);

        return $data;
    }

    /**
     * @return Builder<Member>
     */
    public static function filteredQuery(Request $request): Builder
    {
        $query = Member::query()->orderByDesc('created_at');

        $search = trim((string) $request->input('search', $request->input('q', '')));
        if ($search !== '') {
            SqlLikeEscape::whereContainsAny($query, ['email', 'name', 'phone'], $search);
        }

        $status = (string) $request->input('status', '');
        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        $verified = (string) $request->input('verified', '');
        if ($verified === '1' || $verified === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($verified === '0' || $verified === 'unverified') {
            $query->whereNull('email_verified_at');
        }

        $trashed = (string) $request->input('trashed', 'without');
        if ($trashed === 'only') {
            $query->onlyTrashed();
        } elseif ($trashed === 'with') {
            $query->withTrashed();
        }

        $stat = (string) $request->input('stat', '');
        if ($stat === 'recent') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($stat === 'active') {
            $query->whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subDays(30));
        }

        return $query;
    }

    /**
     * @return array{bookmarks: int, comments: int, submissions: int, newsletter_subscribed: bool|null}
     */
    public static function activityCounts(Member $member): array
    {
        $bookmarks = 0;
        if (Schema::hasTable('mem_bookmarks')) {
            $bookmarks = (int) DB::table('mem_bookmarks')->where('member_id', $member->id)->count();
        }

        $comments = 0;
        if (Schema::hasTable('pub_comments') && Schema::hasColumn('pub_comments', 'member_id')) {
            $comments = (int) DB::table('pub_comments')->where('member_id', $member->id)->count();
        }

        $submissions = 0;
        if (Schema::hasTable('frm_form_submissions') && Schema::hasColumn('frm_form_submissions', 'member_id')) {
            $submissions = (int) DB::table('frm_form_submissions')->where('member_id', $member->id)->count();
        }

        $newsletterSubscribed = null;
        if (Schema::hasTable('nwl_subscribers')) {
            $row = DB::table('nwl_subscribers')
                ->where('email', $member->email)
                ->whereNull('deleted_at')
                ->first(['status']);
            if ($row !== null) {
                $newsletterSubscribed = ((string) ($row->status ?? '')) === 'subscribed';
            }
        }

        return [
            'bookmarks' => $bookmarks,
            'comments' => $comments,
            'submissions' => $submissions,
            'newsletter_subscribed' => $newsletterSubscribed,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function adminStoreRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:mem_members,email',
            'password' => ['required', 'string', app(PasswordPolicyPortInterface::class)->rule()],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[\d\s+\-().#extxEXT]*$/u'],
            'avatar' => 'nullable|string|max:512',
            'bio' => 'nullable|string|max:500',
            'locale' => ['nullable', 'string', 'max:10', 'regex:/^[a-z]{2}([_-][A-Za-z]{2})?$/'],
            'timezone' => 'nullable|string|max:64|timezone:all',
            'status' => 'sometimes|in:active,inactive',
            'verify_email' => 'sometimes|boolean',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function adminUpdateRules(Member $member): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('mem_members', 'email')->ignore($member->id)],
            'password' => ['sometimes', 'nullable', 'string', app(PasswordPolicyPortInterface::class)->rule()],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[\d\s+\-().#extxEXT]*$/u'],
            'avatar' => 'nullable|string|max:512',
            'bio' => 'nullable|string|max:500',
            'locale' => ['nullable', 'string', 'max:10', 'regex:/^[a-z]{2}([_-][A-Za-z]{2})?$/'],
            'timezone' => 'nullable|string|max:64|timezone:all',
            'status' => 'sometimes|in:active,inactive',
            'verify_email' => 'sometimes|boolean',
        ];
    }
}
