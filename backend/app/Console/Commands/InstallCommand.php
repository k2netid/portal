<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ja:install {--force : Force the installation even if already installed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Jejakawan (Backend & Frontend) seamlessly';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (File::exists(storage_path('installed')) && ! $this->option('force')) {
            $this->error('❌ Jejakawan is already installed.');
            $this->info('Use --force to reinstall, but BE CAREFUL as it may overwrite your data.');

            return 1;
        }

        $this->info('🚀 Starting Jejakawan Installation...');

        if (! $this->checkRequirements()) {
            return 1;
        }

        $this->setupEnvironment();
        $this->setupDatabase();
        $this->setupRedis();
        $this->setupMail();
        $this->setupFrontend();

        // Finalize Installation
        $this->finalizeInstallation();

        $this->info('✅ Jejakawan has been installed successfully!');

        return 0;
    }

    protected function finalizeInstallation(): void
    {
        $this->comment('🏁 Finalizing installation...');

        // 1. Create Default Super User
        $this->comment('👤 Creating super administrator account...');
        try {
            $userClass = '\App\Models\User';
            if (class_exists($userClass)) {
                $userClass::updateOrCreate(
                    ['username' => 'super'],
                    [
                        'name' => 'Super Administrator',
                        'email' => 'super@jejakawan.com',
                        'password' => \Hash::make('Senja@jejakawan'),
                        'email_verified_at' => now(),
                    ]
                );
            }
        } catch (\Exception $e) {
            $this->error('❌ Failed to create super user: '.$e->getMessage());
        }

        // 2. Create Lock File
        File::put(storage_path('installed'), 'Installation completed at: '.now());

        // 3. Set Installed Flag in Env
        $this->updateEnv(['APP_INSTALLED' => 'true']);

        $this->info('✅ Installation lock created.');

        $this->info("\n".str_repeat('=', 40));
        $this->info('  DEFAULT CREDENTIALS');
        $this->info(str_repeat('=', 40));
        $this->line('  Username : <fg=green>super</>');
        $this->line('  Password : <fg=green>Senja@jejakawan</>');
        $this->info(str_repeat('=', 40)."\n");
    }

    protected function checkRequirements(): bool
    {
        $this->comment('🔍 Checking system requirements...');

        // Check for Node
        $nodeCheck = $this->runExternalCommand('node -v');
        if (! $nodeCheck) {
            $this->error('❌ Node.js is not installed.');

            return false;
        }

        // Check for NPM
        $npmCheck = $this->runExternalCommand('npm -v');
        if (! $npmCheck) {
            $this->error('❌ NPM is not installed.');

            return false;
        }

        $this->info('✅ System requirements met.');

        return true;
    }

    protected function setupEnvironment(): void
    {
        $this->comment('📝 Setting up environment variables...');

        if (! File::exists(base_path('.env'))) {
            File::copy(base_path('.env.example'), base_path('.env'));
            $this->info('✅ Created .env from .example');
        }

        $appNameConfig = config('app.name', 'Jejakawan');
        $appNameDefault = is_string($appNameConfig) ? $appNameConfig : 'Jejakawan';
        $appNameRaw = $this->ask('Application Name', $appNameDefault);
        $appName = is_string($appNameRaw) ? $appNameRaw : $appNameDefault;

        $appUrlConfig = config('app.url', 'http://localhost');
        $appUrlDefault = is_string($appUrlConfig) ? $appUrlConfig : 'http://localhost';
        $appUrlRaw = $this->ask('Application URL (leave as default for auto-detection)', $appUrlDefault);
        $appUrl = is_string($appUrlRaw) ? $appUrlRaw : $appUrlDefault;

        $rootDomainConfig = config('app.root_domain', 'localhost');
        $rootDomainDefault = is_string($rootDomainConfig) ? $rootDomainConfig : 'localhost';
        $rootDomainRaw = $this->ask('Root Domain (leave as default for auto-detection)', $rootDomainDefault);
        $rootDomain = is_string($rootDomainRaw) ? $rootDomainRaw : $rootDomainDefault;

        $this->updateEnv([
            'APP_NAME' => "\"$appName\"",
            'APP_URL' => $appUrl,
            'VITE_APP_NAME' => "\"$appName\"",
            'VITE_API_URL' => $appUrl,
            'VITE_ROOT_DOMAIN' => $rootDomain,
            'VITE_PORTAL_URL' => $appUrl,
            'APP_ROOT_DOMAIN' => $rootDomain,
        ]);

        $this->call('key:generate');
    }

    protected function setupDatabase(): void
    {
        $this->comment('🗄️ Setting up database...');

        $connectionRaw = $this->choice('Database Connection', ['mysql', 'pgsql', 'sqlite'], 'pgsql');
        if (is_string($connectionRaw)) {
            $connection = $connectionRaw;
        } else {
            $first = $connectionRaw[0] ?? 'pgsql';
            $connection = is_string($first) ? $first : 'pgsql';
        }

        if ($connection === 'sqlite') {
            $pathDefault = database_path('database.sqlite');
            $pathRaw = $this->ask('Database Path (absolute)', $pathDefault);
            $path = is_string($pathRaw) ? $pathRaw : $pathDefault;
            if (! File::exists($path)) {
                File::put($path, '');
                $this->info("✅ Created SQLite database at $path");
            }
            $this->updateEnv([
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => $path,
            ]);
        } else {
            $hostConfig = config('database.connections.'.$connection.'.host', '127.0.0.1');
            $hostDefault = is_string($hostConfig) ? $hostConfig : '127.0.0.1';
            $hostRaw = $this->ask('Database Host', $hostDefault);
            $host = is_string($hostRaw) ? $hostRaw : $hostDefault;

            $portDefault = $connection === 'pgsql' ? '5432' : '3306';
            $portRaw = $this->ask('Database Port', $portDefault);
            $port = is_string($portRaw) ? $portRaw : $portDefault;

            $dbConfig = config('database.connections.'.$connection.'.database', 'ja_apps');
            $dbDefault = is_string($dbConfig) ? $dbConfig : 'ja_apps';
            $databaseRaw = $this->ask('Database Name', $dbDefault);
            $database = is_string($databaseRaw) ? $databaseRaw : $dbDefault;

            $userConfig = config('database.connections.'.$connection.'.username', 'root');
            $userDefault = is_string($userConfig) ? $userConfig : 'root';
            $usernameRaw = $this->ask('Database Username', $userDefault);
            $username = is_string($usernameRaw) ? $usernameRaw : $userDefault;

            $passwordRaw = $this->secret('Database Password');
            $password = is_string($passwordRaw) ? $passwordRaw : '';

            $this->updateEnv([
                'DB_CONNECTION' => $connection,
                'DB_HOST' => $host,
                'DB_PORT' => $port,
                'DB_DATABASE' => $database,
                'DB_USERNAME' => $username,
                'DB_PASSWORD' => $password,
            ]);

            // Temporary update for migration check
            config(["database.connections.$connection.host" => $host]);
            config(["database.connections.$connection.port" => $port]);
            config(["database.connections.$connection.database" => $database]);
            config(["database.connections.$connection.username" => $username]);
            config(["database.connections.$connection.password" => $password]);
        }

        if ($this->confirm('Do you want to run migrations and seeders?', true)) {
            $this->call('migrate:fresh', ['--seed' => true]);
        }
    }

    protected function setupRedis(): void
    {
        if (! $this->confirm('Do you want to configure Redis?', false)) {
            return;
        }

        $this->comment('🚀 Setting up Redis...');

        $redisHostConfig = config('database.redis.default.host', '127.0.0.1');
        $redisHostDefault = is_string($redisHostConfig) ? $redisHostConfig : '127.0.0.1';
        $hostRaw = $this->ask('Redis Host', $redisHostDefault);
        $host = is_string($hostRaw) ? $hostRaw : $redisHostDefault;

        $passwordRaw = $this->secret('Redis Password (leave null if none)');
        $password = is_string($passwordRaw) ? $passwordRaw : '';

        $redisPortConfig = config('database.redis.default.port', '6379');
        $redisPortDefault = is_string($redisPortConfig) ? $redisPortConfig : '6379';
        $portRaw = $this->ask('Redis Port', $redisPortDefault);
        $port = is_string($portRaw) ? $portRaw : $redisPortDefault;

        $this->updateEnv([
            'REDIS_HOST' => $host,
            'REDIS_PASSWORD' => $password === '' ? 'null' : $password,
            'REDIS_PORT' => $port,
        ]);

        $this->info('✅ Redis configured.');
    }

    protected function setupMail(): void
    {
        if (! $this->confirm('Do you want to configure Mail settings?', false)) {
            return;
        }

        $this->comment('📧 Setting up Mail...');

        $mailerRaw = $this->choice('Mail Mailer', ['smtp', 'mailgun', 'ses', 'log'], 'smtp');
        if (is_string($mailerRaw)) {
            $mailer = $mailerRaw;
        } else {
            $first = $mailerRaw[0] ?? 'smtp';
            $mailer = is_string($first) ? $first : 'smtp';
        }

        if ($mailer === 'log') {
            $this->updateEnv(['MAIL_MAILER' => 'log']);

            return;
        }

        $mailHostConfig = config('mail.mailers.smtp.host', '127.0.0.1');
        $mailHostDefault = is_string($mailHostConfig) ? $mailHostConfig : '127.0.0.1';
        $hostRaw = $this->ask('Mail Host', $mailHostDefault);
        $host = is_string($hostRaw) ? $hostRaw : $mailHostDefault;

        $mailPortConfig = config('mail.mailers.smtp.port', '2525');
        $mailPortDefault = is_string($mailPortConfig) ? $mailPortConfig : '2525';
        $portRaw = $this->ask('Mail Port', $mailPortDefault);
        $port = is_string($portRaw) ? $portRaw : $mailPortDefault;

        $usernameConfig = config('mail.mailers.smtp.username');
        $usernameDefault = is_string($usernameConfig) ? $usernameConfig : null;
        $usernameRaw = $this->ask('Mail Username', $usernameDefault);
        $username = is_string($usernameRaw) ? $usernameRaw : '';

        $passwordRaw = $this->secret('Mail Password');
        $password = is_string($passwordRaw) ? $passwordRaw : '';

        $fromConfig = config('mail.from.address', 'hello@example.com');
        $fromDefault = is_string($fromConfig) ? $fromConfig : 'hello@example.com';
        $fromRaw = $this->ask('Mail From Address', $fromDefault);
        $from = is_string($fromRaw) ? $fromRaw : $fromDefault;

        $this->updateEnv([
            'MAIL_MAILER' => $mailer,
            'MAIL_HOST' => $host,
            'MAIL_PORT' => $port,
            'MAIL_USERNAME' => $username,
            'MAIL_PASSWORD' => $password,
            'MAIL_FROM_ADDRESS' => "\"$from\"",
        ]);

        $this->info('✅ Mail configured.');
    }

    protected function setupFrontend(): void
    {
        $this->comment('🌐 Setting up frontend assets...');

        if ($this->confirm('Do you want to install frontend dependencies (npm install)?', true)) {
            $this->info('📦 Running npm install in frontend directory...');
            $this->runExternalCommand('npm install', base_path('../frontend'));
        }

        if ($this->confirm('Do you want to build frontend assets?', true)) {
            $this->info('🏗️ Building assets...');
            $this->runExternalCommand('npm run build', base_path('../frontend'));
            $this->info('🚚 Syncing assets to public...');
            $this->runExternalCommand('npm run deploy:assets:full', base_path('..'));
        }
    }

    /**
     * @param  array<string, string>  $data
     */
    protected function updateEnv(array $data): void
    {
        $path = base_path('.env');

        if (File::exists($path)) {
            $existing = File::get($path);
            foreach ($data as $key => $value) {
                if (str_contains($existing, "{$key}=")) {
                    // Check if value contains spaces, if so wrap in quotes if not already
                    if (str_contains($value, ' ') && ! str_contains($value, '"')) {
                        $value = "\"$value\"";
                    }
                    $updated = preg_replace(
                        "/^{$key}=.*/m",
                        "{$key}={$value}",
                        $existing
                    );
                    if ($updated !== null) {
                        $existing = $updated;
                    }
                } else {
                    $existing .= "\n{$key}={$value}";
                }
            }

            File::put($path, $existing);
        }
    }

    protected function runExternalCommand(string $command, ?string $cwd = null): bool
    {
        $process = Process::fromShellCommandline($command, $cwd);
        $process->setTimeout(null);

        $process->run(function ($type, string|iterable $buffer): void {
            $this->output->write($buffer);
        });

        return $process->isSuccessful();
    }
}
