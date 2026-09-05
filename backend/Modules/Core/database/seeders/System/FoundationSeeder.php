<?php

namespace Modules\Core\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Language;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\RedisSetting;
use Modules\Core\System\Models\Role;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\PermissionRegistry;
use Spatie\Permission\PermissionRegistrar;

class FoundationSeeder extends Seeder
{
    /**
     * Run the general foundation seeds.
     */
    public function run(): void
    {
        // 1. Roles & Permissions (Spatie)
        $this->seedRolesAndPermissions();

        // 2. System Settings
        $this->seedSettings();

        // 3. Languages
        $this->seedLanguages();

        // 4. Scheduled Tasks
        $this->seedScheduledTasks();

        // 5. Redis Settings
        $this->seedRedisSettings();

        $this->command->info('Foundation seeded successfully!');
    }

    /**
     * Hub ops RBAC (APP_ROLE=ops):
     * - Internal operators: super, system-admin, security-officer
     * - Subscription Jejakawan users: member (self-service under X-Subscription-Domain)
     */
    protected function seedRolesAndPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Core Identity & Profile
            'view profile', 'edit profile',

            // Media (Global Infrastructure)
            'view media', 'upload media', 'edit media', 'delete media', 'manage media',

            // Users & RBAC
            'view users', 'create users', 'edit users', 'delete users', 'verify users', 'manage users',
            'view roles', 'create roles', 'edit roles', 'delete roles',

            // System Governance
            'view settings', 'manage settings',
            'view system', 'manage system',
            'view logs', 'delete logs',
            'view activity logs',

            // Security Operations
            'manage security operations',
            'manage security logs',
            'manage security ip-lists',
            'manage security integrity',
            'manage security maintenance',
            'manage kyc reviews',
            'view security logs',

            // Infrastructure Services
            'view plugins', 'install plugins', 'manage plugins',
            'view redirects', 'manage redirects',
            'view scheduled tasks', 'manage scheduled tasks',
            'view backups', 'create backups', 'manage backups',
            'view analytics',

            // Module Governance (CMS)
            'manage module access',

            // JA-Mail extension
            'use mail',
            'manage personal mail account',
            'manage multi mail accounts',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        if (app()->bound(PermissionRegistry::class)) {
            app(PermissionRegistry::class)->syncToDatabase();
        }

        // --- Internal (Jejakawan operators) ---

        $superAdmin = Role::firstOrCreate(['name' => 'super', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // System admin (infrastructure / platform console)
        $systemAdmin = Role::firstOrCreate(['name' => 'system-admin', 'guard_name' => 'web']);
        $systemAdmin->syncPermissions(Permission::whereIn('name', [
            'view users', 'create users', 'edit users', 'delete users', 'verify users', 'manage users',
            'view roles', 'create roles', 'edit roles', 'delete roles',
            'view settings', 'manage settings',
            'view system', 'manage system',
            'view logs', 'delete logs',
            'view backups', 'create backups', 'manage backups',
            'view scheduled tasks', 'manage scheduled tasks',
            'view plugins', 'manage plugins',
            'manage security operations',
            'manage security logs',
            'manage security ip-lists',
            'manage security integrity',
            'manage security maintenance',
            'manage kyc reviews',
            'view security logs',
            'manage module access',
            'use mail',
            'manage personal mail account',
            'manage multi mail accounts',
        ])->get());

        // --- Subscription member (public/member API, not platform console) ---

        $member = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        $member->syncPermissions(['view profile', 'edit profile']);

        // --- Internal security ---

        // Security officer
        $securityOfficer = Role::firstOrCreate(['name' => 'security-officer', 'guard_name' => 'web']);
        $securityOfficer->syncPermissions([
            'view settings',
            'view logs',
            'view security logs',
            'manage security operations',
            'manage security logs',
            'manage security ip-lists',
            'manage security integrity',
            'manage security maintenance',
            'manage kyc reviews',
        ]);

        $this->assignSecurityOfficerFromEnv($securityOfficer);

        $profile = config('install.profile', 'core');
        if (in_array($profile, ['cms', 'cms_site'], true)) {
            $this->call(CmsRolesSeeder::class);
        }
    }

    protected function assignSecurityOfficerFromEnv(Role $securityOfficer): void
    {
        $emailsRaw = config('app.security_officer_emails', '');
        if (! is_string($emailsRaw) || trim($emailsRaw) === '') {
            return;
        }

        $emails = collect(explode(',', $emailsRaw))
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '' && filter_var($v, FILTER_VALIDATE_EMAIL))
            ->values();

        if ($emails->isEmpty()) {
            return;
        }

        $emailsList = array_values(array_map(
            static fn (mixed $e): string => (string) $e,
            $emails->all()
        ));

        $users = User::query()
            ->whereIn('email', $emailsList)
            ->get(['id', 'email']);

        foreach ($users as $user) {
            $user->syncRoles([(string) $securityOfficer->name]);
        }

        $assignedEmails = [];
        foreach ($users->pluck('email') as $email) {
            if (is_string($email) && $email !== '') {
                $assignedEmails[] = $email;
            }
        }
        $missingEmails = array_values(array_diff($emailsList, $assignedEmails));

        if ($this->command) {
            if ($assignedEmails !== []) {
                $this->command->info('Assigned security-officer role to: '.implode(', ', $assignedEmails));
            }
            if ($missingEmails !== []) {
                $this->command->warn('SECURITY_OFFICER_EMAILS not found: '.implode(', ', $missingEmails));
            }
        }
    }

    protected function seedSettings(): void
    {
        $appName = (string) config('app.name', 'Jejakawan');
        $superEmailRaw = config('app.super_admin_email');
        $superEmail = is_scalar($superEmailRaw) && (string) $superEmailRaw !== '' ? (string) $superEmailRaw : 'super@jejakawan.com';
        $appUrl = (string) config('app.url', 'http://localhost');

        // APP_NAME is the developer/engine brand (Jejakawan); site_name is the site owner identity.
        // These are intentionally separate: app_name is protected by White Label licensing.
        $siteName = (string) env('SITE_NAME', $appName);

        $settings = [
            // System Settings
            ['key' => 'app_name', 'value' => $appName, 'group' => 'system', 'type' => 'string'],
            ['key' => 'license_type', 'value' => 'enterprise', 'group' => 'system', 'type' => 'string'],
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'system', 'type' => 'boolean'],
            ['key' => 'maintenance_title', 'value' => 'Situs Sedang Dalam Pemeliharaan', 'group' => 'system', 'type' => 'string'],
            ['key' => 'maintenance_message', 'value' => 'Kami sedang melakukan pemeliharaan sistem berkala. Mohon kembali beberapa saat lagi.', 'group' => 'system', 'type' => 'text'],
            ['key' => 'maintenance_countdown_enabled', 'value' => '0', 'group' => 'system', 'type' => 'boolean'],
            ['key' => 'maintenance_end_time', 'value' => '', 'group' => 'system', 'type' => 'string'],
            ['key' => 'timezone', 'value' => 'Asia/Jakarta', 'group' => 'system', 'type' => 'string'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'group' => 'system', 'type' => 'string'],
            ['key' => 'time_format', 'value' => 'H:i:s', 'group' => 'system', 'type' => 'string'],
            ['key' => 'items_per_page', 'value' => '20', 'group' => 'system', 'type' => 'integer'],
            ['key' => 'content.autosave_interval_seconds', 'value' => '30', 'group' => 'system', 'type' => 'integer'],

            // General / Identity (Site Settings)
            // site_name uses $siteName (from SITE_NAME env or fallback to APP_NAME)
            ['key' => 'site_name', 'value' => $siteName, 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_logo', 'value' => '/logo.png', 'group' => 'general', 'type' => 'image'],
            ['key' => 'site_favicon', 'value' => '/favicon.ico', 'group' => 'general', 'type' => 'image'],
            ['key' => 'site_description', 'value' => (string) env('SITE_DESCRIPTION', ''), 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_url', 'value' => $appUrl, 'group' => 'general', 'type' => 'string'],
            ['key' => 'admin_email', 'value' => $superEmail, 'group' => 'brand', 'type' => 'string'],
            ['key' => 'app_logo', 'value' => '/logo.png', 'group' => 'brand', 'type' => 'image'],
            ['key' => 'app_favicon', 'value' => '/favicon.ico', 'group' => 'brand', 'type' => 'image'],
            ['key' => 'brand_logo', 'value' => '/logo.png', 'group' => 'brand', 'type' => 'image'],
            ['key' => 'brand_favicon', 'value' => '/favicon.ico', 'group' => 'brand', 'type' => 'image'],
            ['key' => 'branding_display', 'value' => 'logo', 'group' => 'brand', 'type' => 'string'],

            // Contact & Social
            ['key' => 'contact_email', 'value' => (string) config('mail.from.address', 'hello@example.com'), 'group' => 'general', 'type' => 'string'],
            ['key' => 'contact_phone', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'contact_address', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'social_twitter', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'social_github', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'social_linkedin', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'social_instagram', 'value' => '', 'group' => 'general', 'type' => 'string'],

            // Performance Settings
            ['key' => 'enable_cache', 'value' => '1', 'group' => 'performance', 'type' => 'boolean'],
            ['key' => 'cache_driver', 'value' => 'file', 'group' => 'performance', 'type' => 'string'],
            ['key' => 'cache_ttl', 'value' => '3600', 'group' => 'performance', 'type' => 'integer'],
            ['key' => 'enable_cdn', 'value' => '0', 'group' => 'performance', 'type' => 'boolean'],
            ['key' => 'cdn_url', 'value' => '', 'group' => 'performance', 'type' => 'string'],
            ['key' => 'cdn_preset', 'value' => 'custom', 'group' => 'performance', 'type' => 'string'],
            ['key' => 'cdn_included_dirs', 'value' => 'assets, storage', 'group' => 'performance', 'type' => 'string'],
            ['key' => 'cdn_excluded_extensions', 'value' => '.php, .json', 'group' => 'performance', 'type' => 'string'],
            ['key' => 'enable_compression', 'value' => '1', 'group' => 'performance', 'type' => 'boolean'],
            ['key' => 'minify_html', 'value' => '0', 'group' => 'performance', 'type' => 'boolean'],
            ['key' => 'minify_css', 'value' => '0', 'group' => 'performance', 'type' => 'boolean'],
            ['key' => 'minify_js', 'value' => '0', 'group' => 'performance', 'type' => 'boolean'],
            ['key' => 'query_cache_enabled', 'value' => '1', 'group' => 'performance', 'type' => 'boolean'],
            ['key' => 'query_cache_ttl', 'value' => '3600', 'group' => 'performance', 'type' => 'integer'],

            // Media Settings
            ['key' => 'storage_driver', 'value' => 'local', 'group' => 'media', 'type' => 'string'],
            ['key' => 'max_upload_size', 'value' => '10240', 'group' => 'media', 'type' => 'integer'],
            ['key' => 'allowed_image_types', 'value' => 'jpg,jpeg,png,webp,gif', 'group' => 'media', 'type' => 'string'],
            ['key' => 'allowed_file_types', 'value' => 'pdf,doc,docx,xls,xlsx,zip,rar', 'group' => 'media', 'type' => 'string'],
            ['key' => 'allowed_upload_extensions', 'value' => 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip', 'group' => 'media', 'type' => 'string'],
            ['key' => 'thumbnail_width', 'value' => '300', 'group' => 'media', 'type' => 'integer'],
            ['key' => 'thumbnail_height', 'value' => '300', 'group' => 'media', 'type' => 'integer'],
            ['key' => 'enable_watermark', 'value' => '0', 'group' => 'media', 'type' => 'boolean'],
            ['key' => 'watermark_text', 'value' => $appName, 'group' => 'media', 'type' => 'string'],
            ['key' => 'aws_access_key_id', 'value' => '', 'group' => 'media', 'type' => 'string'],
            ['key' => 'aws_secret_access_key', 'value' => '', 'group' => 'media', 'type' => 'password'],
            ['key' => 'aws_default_region', 'value' => 'us-east-1', 'group' => 'media', 'type' => 'string'],
            ['key' => 'aws_bucket', 'value' => '', 'group' => 'media', 'type' => 'string'],
            ['key' => 'aws_endpoint', 'value' => '', 'group' => 'media', 'type' => 'string'],
            ['key' => 'google_client_id', 'value' => '', 'group' => 'media', 'type' => 'string'],
            ['key' => 'google_client_secret', 'value' => '', 'group' => 'media', 'type' => 'password'],
            ['key' => 'google_refresh_token', 'value' => '', 'group' => 'media', 'type' => 'password'],
            ['key' => 'google_folder_id', 'value' => '', 'group' => 'media', 'type' => 'string'],
            ['key' => 'ftp_host', 'value' => '', 'group' => 'media', 'type' => 'string'],
            ['key' => 'ftp_username', 'value' => '', 'group' => 'media', 'type' => 'string'],
            ['key' => 'ftp_password', 'value' => '', 'group' => 'media', 'type' => 'password'],
            ['key' => 'ftp_root', 'value' => '', 'group' => 'media', 'type' => 'string'],
            ['key' => 'ftp_port', 'value' => '21', 'group' => 'media', 'type' => 'integer'],
            ['key' => 'ftp_ssl', 'value' => '0', 'group' => 'media', 'type' => 'boolean'],
            ['key' => 'dropbox_authorization_token', 'value' => '', 'group' => 'media', 'type' => 'password'],

            // Console appearance (admin dashboard tokens)
            ['key' => 'console_color_preset', 'value' => 'custom', 'group' => 'console_branding', 'type' => 'string'],
            ['key' => 'console_brand_primary', 'value' => '#4f46e5', 'group' => 'console_branding', 'type' => 'string'],
            ['key' => 'console_button_radius', 'value' => '8', 'group' => 'console_branding', 'type' => 'integer'],
            ['key' => 'console_sidebar_accent', 'value' => '#0f172a', 'group' => 'console_branding', 'type' => 'string'],

            // SEO Settings
            ['key' => 'meta_title', 'value' => $appName, 'group' => 'seo', 'type' => 'string'],
            ['key' => 'meta_description', 'value' => 'Portal Resmi', 'group' => 'seo', 'type' => 'text'],
            ['key' => 'meta_keywords', 'value' => 'portal, website, cms', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'google_analytics_id', 'value' => '', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'google_search_console', 'value' => '', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'enable_sitemap', 'value' => '1', 'group' => 'seo', 'type' => 'boolean'],
            ['key' => 'enable_robots_txt', 'value' => '1', 'group' => 'seo', 'type' => 'boolean'],

            // Analytics
            ['key' => 'analytics_retention_days', 'value' => '90', 'group' => 'analytics', 'type' => 'integer'],
            ['key' => 'analytics_event_retention_days', 'value' => '30', 'group' => 'analytics', 'type' => 'integer'],
            ['key' => 'analytics_visitor_retention_days', 'value' => '365', 'group' => 'analytics', 'type' => 'integer'],

            // Email Settings
            ['key' => 'mail_driver', 'value' => 'smtp', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_host', 'value' => 'smtp.mailtrap.io', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_port', 'value' => '2525', 'group' => 'email', 'type' => 'integer'],
            ['key' => 'mail_username', 'value' => '', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_password', 'value' => '', 'group' => 'email', 'type' => 'password'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_from_address', 'value' => (string) config('mail.from.address', 'noreply@example.com'), 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_from_name', 'value' => $appName, 'group' => 'email', 'type' => 'string'],

            // Monitoring Settings
            ['key' => 'log_retention_days', 'value' => '90', 'group' => 'monitoring', 'type' => 'integer'],
            ['key' => 'activity_log_retention_days', 'value' => '90', 'group' => 'monitoring', 'type' => 'integer'],
            ['key' => 'security_log_retention_days', 'value' => '180', 'group' => 'monitoring', 'type' => 'integer'],
            ['key' => 'login_history_retention_days', 'value' => '30', 'group' => 'monitoring', 'type' => 'integer'],
            ['key' => 'security_alert_failed_login_threshold', 'value' => '5', 'group' => 'monitoring', 'type' => 'integer'],
            ['key' => 'backup_retention_days', 'value' => '30', 'group' => 'monitoring', 'type' => 'integer'],

            // AI Settings
            ['key' => 'ai_enabled', 'value' => '1', 'group' => 'ai', 'type' => 'boolean'],
            ['key' => 'ai_default_provider', 'value' => 'gemini', 'group' => 'ai', 'type' => 'string'],
            ['key' => 'gemini_api_key', 'value' => '', 'group' => 'ai', 'type' => 'password'],
            ['key' => 'gemini_model', 'value' => 'gemini-2.0-flash', 'group' => 'ai', 'type' => 'string'],
            ['key' => 'openai_api_key', 'value' => '', 'group' => 'ai', 'type' => 'password'],
            ['key' => 'openai_model', 'value' => 'gpt-4o-mini', 'group' => 'ai', 'type' => 'string'],
            ['key' => 'claude_api_key', 'value' => '', 'group' => 'ai', 'type' => 'password'],
            ['key' => 'claude_model', 'value' => 'claude-3-5-sonnet-20241022', 'group' => 'ai', 'type' => 'string'],
            ['key' => 'deepseek_api_key', 'value' => '', 'group' => 'ai', 'type' => 'password'],
            ['key' => 'deepseek_model', 'value' => 'deepseek-chat', 'group' => 'ai', 'type' => 'string'],
            ['key' => 'grok_api_key', 'value' => '', 'group' => 'ai', 'type' => 'password'],
            ['key' => 'grok_model', 'value' => 'grok-2-latest', 'group' => 'ai', 'type' => 'string'],
            ['key' => 'openrouter_api_key', 'value' => '', 'group' => 'ai', 'type' => 'password'],
            ['key' => 'openrouter_model', 'value' => 'openrouter/auto', 'group' => 'ai', 'type' => 'string'],

            // Security
            ['key' => 'enable_registration', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'enable_member_registration', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'require_email_verification', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'enable_2fa', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'two_factor_method', 'value' => 'authenticator', 'group' => 'security', 'type' => 'string'],
            ['key' => 'two_factor_enforced_roles', 'value' => '["super", "system-admin", "security-officer"]', 'group' => 'security', 'type' => 'json'],
            ['key' => 'password_min_length', 'value' => '8', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'password_require_uppercase', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'password_require_lowercase', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'password_require_number', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'password_require_symbol', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'session_lifetime', 'value' => '120', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'single_session_enabled', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'max_concurrent_sessions', 'value' => '3', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'login_attempts_limit', 'value' => '5', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'block_duration_minutes', 'value' => '30', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'security_alert_blocked_ip_threshold', 'value' => '3', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'security_alert_suspicious_ip_threshold', 'value' => '10', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'security_alert_window_minutes', 'value' => '60', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'enable_captcha', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'captcha_method', 'value' => 'slider', 'group' => 'security', 'type' => 'string'],
            ['key' => 'captcha_on_login', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'captcha_on_register', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'captcha_on_contact', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'captcha_on_forgot_password', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'shield_protection_mode', 'value' => 'off', 'group' => 'security', 'type' => 'string'],
            ['key' => 'shield_protection_difficulty', 'value' => '4', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'shield_log_verification_success', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'shield_enable_ip_intelligence', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'shield_allowed_countries', 'value' => '[]', 'group' => 'security', 'type' => 'json'],
            ['key' => Setting::KEY_CONSOLE_DASHBOARD_SLUG, 'value' => 'ja-dash', 'group' => 'security', 'type' => 'string'],
            ['key' => 'scanner_auto_block_threshold', 'value' => '10', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'security_learned_scanner_paths', 'value' => '[]', 'group' => 'security', 'type' => 'json'],
            ['key' => 'abuseipdb_api_key', 'value' => '', 'group' => 'security', 'type' => 'password'],
            ['key' => 'threat_intel_auto_block_threshold', 'value' => '75', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'telegram_bot_token', 'value' => '', 'group' => 'security', 'type' => 'password'],
            ['key' => 'telegram_chat_id', 'value' => '', 'group' => 'security', 'type' => 'string'],
            ['key' => 'email_to', 'value' => '', 'group' => 'security', 'type' => 'string'],
            ['key' => 'webhook_url', 'value' => '', 'group' => 'security', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }

    protected function seedLanguages(): void
    {
        $languages = [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'flag' => '🇺🇸', 'is_default' => 0, 'is_active' => 1],
            ['code' => 'id', 'name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'flag' => '🇮🇩', 'is_default' => 1, 'is_active' => 1],
            ['code' => 'su', 'name' => 'Basa Sunda', 'native_name' => 'Basa Sunda', 'flag' => '🇮🇩', 'is_default' => 0, 'is_active' => 1],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(['code' => $language['code']], $language);
        }
    }

    protected function seedScheduledTasks(): void
    {
        $this->call(ScheduledTaskSeeder::class);
    }

    protected function seedRedisSettings(): void
    {
        $redisDefault = config('database.redis.default');
        $redisCache = config('database.redis.cache');
        $redisOptions = config('database.redis.options');

        $host = is_array($redisDefault) && isset($redisDefault['host']) && is_scalar($redisDefault['host'])
            ? (string) $redisDefault['host']
            : '127.0.0.1';
        $port = is_array($redisDefault) && isset($redisDefault['port']) && is_scalar($redisDefault['port'])
            ? (string) $redisDefault['port']
            : '6379';
        $username = is_array($redisDefault) && array_key_exists('username', $redisDefault) && $redisDefault['username'] !== null && is_scalar($redisDefault['username'])
            ? (string) $redisDefault['username']
            : 'core_engine';
        $password = is_array($redisDefault) && array_key_exists('password', $redisDefault) && $redisDefault['password'] !== null && is_scalar($redisDefault['password'])
            ? (string) $redisDefault['password']
            : '';
        $database = is_array($redisDefault) && isset($redisDefault['database']) && is_scalar($redisDefault['database'])
            ? (string) $redisDefault['database']
            : '6';
        $cacheDatabase = is_array($redisCache) && isset($redisCache['database']) && is_scalar($redisCache['database'])
            ? (string) $redisCache['database']
            : '7';
        $prefix = is_array($redisOptions) && isset($redisOptions['prefix']) && is_scalar($redisOptions['prefix'])
            ? (string) $redisOptions['prefix']
            : 'ja_core_engine:';

        $sessionDriver = config('session.driver');
        $queueDefault = config('queue.default');

        $settings = [
            [
                'key' => 'redis_host',
                'value' => $host,
                'type' => 'string',
                'group' => 'connection',
                'description' => 'Redis server host address',
                'is_encrypted' => false,
            ],
            [
                'key' => 'redis_port',
                'value' => $port,
                'type' => 'integer',
                'group' => 'connection',
                'description' => 'Redis server port',
                'is_encrypted' => false,
            ],
            [
                'key' => 'redis_username',
                'value' => $username,
                'type' => 'string',
                'group' => 'connection',
                'description' => 'Redis ACL username',
                'is_encrypted' => false,
            ],
            [
                'key' => 'redis_password',
                'value' => $password,
                'type' => 'string',
                'group' => 'connection',
                'description' => 'Redis server password',
                'is_encrypted' => true,
            ],
            [
                'key' => 'redis_database',
                'value' => $database,
                'type' => 'integer',
                'group' => 'connection',
                'description' => 'Redis database index (0-15)',
                'is_encrypted' => false,
            ],
            [
                'key' => 'redis_cache_database',
                'value' => $cacheDatabase,
                'type' => 'integer',
                'group' => 'connection',
                'description' => 'Redis cache database index (0-15)',
                'is_encrypted' => false,
            ],
            [
                'key' => 'cache_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'cache',
                'description' => 'Enable Redis for caching',
                'is_encrypted' => false,
            ],
            [
                'key' => 'cache_prefix',
                'value' => $prefix,
                'type' => 'string',
                'group' => 'cache',
                'description' => 'Cache key prefix',
                'is_encrypted' => false,
            ],
            [
                'key' => 'session_enabled',
                'value' => $sessionDriver === 'redis' ? 'true' : 'false',
                'type' => 'boolean',
                'group' => 'session',
                'description' => 'Use Redis for sessions',
                'is_encrypted' => false,
            ],
            [
                'key' => 'queue_enabled',
                'value' => $queueDefault === 'redis' ? 'true' : 'false',
                'type' => 'boolean',
                'group' => 'queue',
                'description' => 'Use Redis for queue jobs',
                'is_encrypted' => false,
            ],
        ];

        foreach ($settings as $setting) {
            RedisSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
