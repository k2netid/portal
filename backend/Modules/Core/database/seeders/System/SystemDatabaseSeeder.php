<?php

namespace Modules\Core\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Database\Seeders\System\BeforeFooterPromoPluginSeeder;
use Modules\Core\Database\Seeders\System\ConsoleBrandingSettingsSeeder;
use Modules\Core\Database\Seeders\System\ContentShareBarPluginSeeder;
use Modules\Core\System\Models\User;

class SystemDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(FoundationSeeder::class);

        $emailRaw = config('app.super_admin_email');
        $superEmail = is_scalar($emailRaw) && (string) $emailRaw !== '' ? (string) $emailRaw : 'super@jejakawan.com';
        $passwordRaw = config('app.super_admin_password');
        if (! is_scalar($passwordRaw) || (string) $passwordRaw === '') {
            $superPassword = \Illuminate\Support\Str::random(24);
            $this->command?->warn("⚠ SUPER_ADMIN_PASSWORD not set. Generated random password: {$superPassword}");
            $this->command?->warn('  Set SUPER_ADMIN_PASSWORD in .env to use a specific password.');
        } else {
            $superPassword = (string) $passwordRaw;
        }

        $superAdmin = User::firstOrCreate(
            ['email' => $superEmail],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($superPassword),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('super');

        $this->call(InfrastructureSeeder::class);

        $this->call([
            ConsoleBrandingSettingsSeeder::class,
            ContentShareBarPluginSeeder::class,
            BeforeFooterPromoPluginSeeder::class,
        ]);
    }
}
