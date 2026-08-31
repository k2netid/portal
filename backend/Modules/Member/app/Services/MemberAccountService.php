<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Member\Models\Member;

class MemberAccountService
{
    public function delete(Member $member): void
    {
        DB::transaction(function () use ($member): void {
            $member->tokens()->delete();

            if (Schema::hasTable('mem_bookmarks')) {
                DB::table('mem_bookmarks')->where('member_id', $member->id)->delete();
            }

            if (Schema::hasTable('pub_comments') && Schema::hasColumn('pub_comments', 'member_id')) {
                DB::table('pub_comments')->where('member_id', $member->id)->update(['member_id' => null]);
            }

            if (Schema::hasTable('frm_form_submissions') && Schema::hasColumn('frm_form_submissions', 'member_id')) {
                DB::table('frm_form_submissions')->where('member_id', $member->id)->update(['member_id' => null]);
            }

            if (Schema::hasTable('mem_password_reset_tokens')) {
                DB::table('mem_password_reset_tokens')->where('email', $member->email)->delete();
            }

            $member->delete();
        });
    }
}
