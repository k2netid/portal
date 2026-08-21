<?php

declare(strict_types=1);

namespace Modules\Core\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Database\Seeders\System\ConsoleBrandingSettingsSeeder;
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
            $superPassword = 'password';
        } else {
            $superPassword = (string) $passwordRaw;
        }

        $superAdmin = User::firstOrCreate(
            ['email' => $superEmail],
            [
                'username' => 'super',
                'name' => 'Super Admin',
                'password' => Hash::make($superPassword),
                'email_verified_at' => now(),
            ]
        );

        if (empty($superAdmin->username)) {
            $superAdmin->update(['username' => 'super']);
        }

        $superAdmin->assignRole('super');

        $this->call(InfrastructureSeeder::class);
        $this->call([
            ConsoleBrandingSettingsSeeder::class,
        ]);
    }
}
