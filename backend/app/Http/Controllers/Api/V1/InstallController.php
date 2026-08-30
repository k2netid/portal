<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Modules\Core\Security\Models\SecurityLog;
use Modules\Core\System\Models\User;

class InstallController extends Controller
{
    public function getStatus(): JsonResponse
    {
        $postResetPath = storage_path('app/.post_reset');

        return response()->json([
            'is_installed' => config('app.installed', false),
            'is_post_reset' => File::exists($postResetPath),
            'setup_token' => File::exists($postResetPath)
                ? trim((string) File::get($postResetPath))
                : null,
            'requirements' => $this->checkRequirements(),
            'os' => $this->detectOS(),
        ]);
    }

    /**
     * @return array{family: string, distro: string}
     */
    protected function detectOS(): array
    {
        $family = PHP_OS_FAMILY;
        $distro = 'unknown';

        if ($family === 'Linux' && File::exists('/etc/os-release')) {
            $osRelease = File::get('/etc/os-release');
            if (str_contains($osRelease, 'ubuntu') || str_contains($osRelease, 'debian')) {
                $distro = 'debian';
            } elseif (str_contains($osRelease, 'centos') || str_contains($osRelease, 'almalinux') || str_contains($osRelease, 'rhel') || str_contains($osRelease, 'fedora')) {
                $distro = 'rhel';
            }
        }

        return [
            'family' => $family,
            'distro' => $distro,
        ];
    }

    public function install(Request $request): JsonResponse
    {
        if (config('app.installed')) {
            return response()->json(['message' => 'Already installed.'], 403);
        }

        $validated = $request->validate([
            'app_name' => 'required|string',
            'app_url' => 'required|url',
            'db_connection' => 'required|in:mysql,pgsql,sqlite',
            'db_host' => 'required_unless:db_connection,sqlite',
            'db_port' => 'required_unless:db_connection,sqlite',
            'db_database' => 'required',
            'db_username' => 'required_unless:db_connection,sqlite',
            'db_password' => 'nullable',
            // Add other fields as needed
        ]);

        try {
            // 1. Update Environment
            $host = parse_url((string) $validated['app_url'], PHP_URL_HOST);
            $hostString = is_string($host) ? $host : '';

            $this->updateEnv([
                'APP_NAME' => "\"{$validated['app_name']}\"",
                'APP_URL' => $validated['app_url'],
                'DB_CONNECTION' => $validated['db_connection'],
                'DB_HOST' => $validated['db_host'] ?? '',
                'DB_PORT' => $validated['db_port'] ?? '',
                'DB_DATABASE' => $validated['db_database'],
                'DB_USERNAME' => $validated['db_username'] ?? '',
                'DB_PASSWORD' => $validated['db_password'] ?? '',
                'VITE_APP_NAME' => "\"{$validated['app_name']}\"",
                'VITE_API_URL' => $validated['app_url'],
                'VITE_ROOT_DOMAIN' => $hostString,
                'VITE_PORTAL_URL' => $validated['app_url'],
                'APP_ROOT_DOMAIN' => $hostString,
            ]);

            // 2. Generate Key if empty
            $appKey = config('app.key');
            if (! is_string($appKey) || $appKey === '') {
                Artisan::call('key:generate', ['--force' => true]);
            }

            // 3. Run Migrations
            Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);

            // 4. Create Default Super User
            $this->createSuperUser();

            // 5. Finalize
            File::put(storage_path('installed'), 'Web installation completed at: '.now());
            $this->updateEnv(['APP_INSTALLED' => 'true']);

            return response()->json([
                'message' => 'Installation successful!',
                'redirect_url' => url('/'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Installation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function postResetSetup(Request $request): JsonResponse
    {
        if (! File::exists(storage_path('app/.post_reset'))) {
            if (class_exists('\Modules\Core\Security\Models\SecurityLog')) {
                SecurityLog::log('suspicious_activity', null, request()->ip(), 'Unauthorized setup access attempt (no active reset flag)');
            }
            if (class_exists('\Modules\Core\Security\Services\SecurityService')) {
                app('\Modules\Core\Security\Services\SecurityService')->blockIpTemporarily((string) request()->ip(), 'Unauthorized setup probe (no flag)');
            }

            return response()->json(['message' => 'No active reset flag found.'], 403);
        }

        $expectedToken = trim(File::get(storage_path('app/.post_reset')));

        $validated = $request->validate([
            'setup_token' => 'required|string',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:srv_auth_users',
            'email' => 'required|email|unique:srv_auth_users',
            'password' => 'required|string|min:8',
        ]);

        if (! hash_equals($expectedToken, $validated['setup_token'])) {
            if (class_exists('\Modules\Core\Security\Models\SecurityLog')) {
                SecurityLog::log('suspicious_activity', null, request()->ip(), 'Unauthorized setup access attempt (invalid token)');
            }
            if (class_exists('\Modules\Core\Security\Services\SecurityService')) {
                app('\Modules\Core\Security\Services\SecurityService')->blockIpTemporarily((string) request()->ip(), 'Unauthorized setup probe (invalid token)');
            }

            return response()->json(['message' => 'Invalid or expired setup token.'], 403);
        }

        try {
            $userClass = '\Modules\Core\System\Models\User';
            if (! class_exists($userClass)) {
                $userClass = '\Illuminate\Database\Eloquent\Model';
            }

            /** @var User|Model $user */
            $user = $userClass::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => \Hash::make($validated['password']),
            ]);

            if (is_object($user) && method_exists($user, 'markEmailAsVerified')) {
                $user->markEmailAsVerified();
            }

            /** @var User $user */
            // Assign Super Admin role if Spatie Permission is used
            if (is_object($user) && method_exists($user, 'assignRole')) {
                try {
                    $user->assignRole('super');
                } catch (\Exception $e) {
                    $roleClass = '\Modules\Core\System\Models\Role';
                    if (class_exists($roleClass)) {
                        $roleClass::firstOrCreate(['name' => 'super', 'guard_name' => 'web']);
                        $user->assignRole('super');
                    } else {
                        $fallbackRoleClass = '\Spatie\Permission\Models\Role';
                        if (class_exists($fallbackRoleClass)) {
                            $fallbackRoleClass::firstOrCreate(['name' => 'super', 'guard_name' => 'web']);
                            $user->assignRole('super');
                        }
                    }
                }
            }

            File::delete(storage_path('app/.post_reset'));
            File::put(storage_path('app/.post_reset_welcome'), 'true');

            // Optionally log them in immediately (Sanctum)
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Admin created successfully.',
                'token' => $token,
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Setup failed: '.$e->getMessage()], 500);
        }
    }

    /**
     * @return array<string, bool|string>
     */
    protected function checkRequirements(): array
    {
        $extensions = [
            'bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'gd',
            'intl', 'json', 'mbstring', 'openssl', 'pdo',
            'tokenizer', 'xml', 'zip',
        ];

        $results = [
            'php_version' => PHP_VERSION,
            'php_supported' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'writable_env' => is_writable(base_path('.env')) || is_writable(base_path()),
            'writable_storage' => is_writable(storage_path()) && is_writable(base_path('bootstrap/cache')),
            'pdo_enabled' => extension_loaded('pdo') && (extension_loaded('pdo_mysql') || extension_loaded('pdo_pgsql') || extension_loaded('pdo_sqlite')),
        ];

        foreach ($extensions as $ext) {
            $results["ext_$ext"] = extension_loaded($ext);
        }

        return $results;
    }

    protected function createSuperUser(): void
    {
        try {
            $emailRaw = config('app.super_admin_email');
            $email = is_scalar($emailRaw) && (string) $emailRaw !== '' ? (string) $emailRaw : 'super@jejakawan.com';
            $passwordRaw = config('app.super_admin_password');
            $password = is_scalar($passwordRaw) && (string) $passwordRaw !== ''
                ? (string) $passwordRaw
                : 'password';

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'username' => 'super',
                    'name' => 'Super Administrator',
                    'password' => \Hash::make($password),
                    'email_verified_at' => now(),
                ]
            );
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('super');
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create super user: '.$e->getMessage());
        }
    }

    /**
     * @param  array<string, string>  $data
     */
    protected function updateEnv(array $data): void
    {
        $path = base_path('.env');
        if (! File::exists($path)) {
            File::copy(base_path('.env.example'), $path);
        }

        $content = File::get($path);
        foreach ($data as $key => $value) {
            if (str_contains($content, "{$key}=")) {
                $updated = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
                if ($updated !== null) {
                    $content = $updated;
                }
            } else {
                $content .= "\n{$key}={$value}";
            }
        }
        File::put($path, $content);
    }
}
