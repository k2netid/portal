<?php

declare(strict_types=1);

namespace Modules\Member\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Modules\Member\Models\Member;

/**
 * Opt-in demo reader when INSTALL_SEED_DEMO=true.
 * Credentials: reader@example.com / password12 (verified).
 */
class MemberDemoSeeder extends Seeder
{
    public static function ensure(): void
    {
        (new self)->run();
    }

    public function run(): void
    {
        if (! (bool) config('install.seed_demo', false)) {
            return;
        }

        if (! Schema::hasTable('mem_members')) {
            return;
        }

        Member::query()->updateOrCreate(
            ['email' => 'reader@example.com'],
            [
                'name' => 'Demo Reader',
                'phone' => '+62 812-3456-7890',
                'bio' => 'Demo reader account for exploring the member portal.',
                'locale' => 'id',
                'timezone' => 'Asia/Jakarta',
                'password' => 'password12',
                'status' => 'active',
                'email_verified_at' => now(),
                'pending_email' => null,
            ],
        );
    }
}
