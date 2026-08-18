<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('srv_auth_users', function (Blueprint $table) {
            $table->integer('username_changes_count')->default(0)->after('username');
        });

        // Seed existing users with a generated username if they don't have one
        DB::table('srv_auth_users')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                if ($user->username) {
                    continue;
                } // Skip if already has username

                $baseUsername = explode('@', $user->email)[0];
                // Clean up username to be alphanumeric only
                $baseUsername = preg_replace('/[^a-zA-Z0-9]/', '', $baseUsername);
                if (empty($baseUsername)) {
                    $baseUsername = 'user';
                }

                $username = $baseUsername;
                $counter = 1;
                while (DB::table('srv_auth_users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                    $username = $baseUsername.$counter;
                    $counter++;
                }
                DB::table('srv_auth_users')->where('id', $user->id)->update(['username' => strtolower($username)]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('srv_auth_users', function (Blueprint $table) {
            $table->dropColumn(['username_changes_count']);
        });
    }
};
