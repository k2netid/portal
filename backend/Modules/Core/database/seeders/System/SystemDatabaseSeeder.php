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

        $emailRaw = config('app.super_admin_email', 'super@jejakawan.com');
        $superEmail = is_scalar($emailRaw) ? (string) $emailRaw : 'super@jejakawan.com';
        $passwordRaw = config('app.super_admin_password', 'ChangeMeOnFirstLogin!');
        $superPassword = is_scalar($passwordRaw) ? (string) $passwordRaw : 'ChangeMeOnFirstLogin!';

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
