<?php

namespace Modules\Core\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\User;

class InfrastructureSeeder extends Seeder
{
    /**
     * Run the infrastructure seeds.
     */
    public function run(): void
    {
        $adminEmail = config('app.super_admin_email');
        if (! is_string($adminEmail) || $adminEmail === '') {
            $adminEmail = 'super@jejakawan.com';
        }

        $admin = User::where('email', $adminEmail)->first();
        if (! $admin) {
            return;
        }
    }
}
