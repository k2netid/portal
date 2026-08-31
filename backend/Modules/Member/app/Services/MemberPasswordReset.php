<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Member\Models\Member;

class MemberPasswordReset
{
    public function __construct(
        private MemberMailer $mailer,
    ) {}

    public function sendResetLink(string $email): void
    {
        $member = Member::query()->where('email', $email)->first();
        if ($member === null || $member->status !== 'active') {
            return;
        }

        $token = Str::random(64);

        DB::table('mem_password_reset_tokens')->updateOrInsert(
            ['email' => $member->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ],
        );

        $url = $this->mailer->frontendUrl('/member/reset-password', [
            'email' => $member->email,
            'token' => $token,
        ]);

        $subject = 'Reset your reader password';
        $html = '<p>Reset the password for your reader account ('.e($member->email).').</p>'
            .'<p><a href="'.e($url).'">Choose a new password</a></p>'
            .'<p>This link expires in 60 minutes. If you did not request it, ignore this email.</p>';

        $this->mailer->send($member->email, $subject, $html);
    }

    public function reset(string $email, string $token, string $password): bool
    {
        $row = DB::table('mem_password_reset_tokens')->where('email', $email)->first();
        if ($row === null || ! Hash::check($token, (string) $row->token)) {
            return false;
        }

        if ($row->created_at === null || now()->subMinutes(60)->greaterThan($row->created_at)) {
            DB::table('mem_password_reset_tokens')->where('email', $email)->delete();

            return false;
        }

        $member = Member::query()->where('email', $email)->first();
        if ($member === null) {
            DB::table('mem_password_reset_tokens')->where('email', $email)->delete();

            return false;
        }

        $member->update(['password' => $password]);
        $member->tokens()->delete();
        DB::table('mem_password_reset_tokens')->where('email', $email)->delete();

        return true;
    }
}
