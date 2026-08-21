<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\RedisSetting;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\LicenseService;

class SettingController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query();

        if ($request->has('group')) {
            $groupRaw = $request->group;
            $group = is_string($groupRaw) ? $groupRaw : '';
            $query->where('group', $group);
        } else {
            // Exclude Publishing specific groups from core settings list
            $cmsGroups = ['general', 'seo', 'comments', 'analytics'];
            $query->whereNotIn('group', $cmsGroups);
        }

        if ($request->has('public_only')) {
            $query->where('is_public', true);
        }

        $settings = $query->orderBy('group')->orderBy('key')->get();

        return $this->success($settings, 'Settings retrieved successfully');
    }

    public function getGroup(string $group): JsonResponse
    {
        $user = request()->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        // Permission check: if user doesn't have 'view settings',
        // they might still need specific group settings (e.g. media settings for authors)
        if (! $user->can('view settings')) {
            $allowedGroups = [
                'media' => ['view media', 'upload media'],
                'system' => [],
                'monitoring' => [],
                'console_branding' => [],
            ];

            $requiredPermissions = $allowedGroups[$group] ?? null;

            if (is_null($requiredPermissions)) {
                return $this->forbidden('You do not have permission to view these settings');
            }

            if ($requiredPermissions !== []) {
                $hasAny = false;
                foreach ($requiredPermissions as $perm) {
                    if ($user->can($perm)) {
                        $hasAny = true;
                        break;
                    }
                }
                if (! $hasAny) {
                    return $this->forbidden('You do not have permission to view these settings');
                }
            }
        }

        $settings = Setting::getGroup($group);

        return $this->success($settings, 'Settings retrieved successfully');
    }

    public function show(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        return $this->success($setting, 'Setting retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:sys_settings,key',
            'value' => 'nullable',
            'type' => 'required|in:string,integer,boolean,json,text,password,number,datetime,image,media',
            'group' => 'required|string',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $setting = Setting::create($validated);

        return $this->success($setting, 'Setting created successfully', 201);
    }

    public function update(Request $request, Setting $setting): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'nullable',
            'type' => 'sometimes|in:string,integer,boolean,json,text,password,number,datetime,image,media',
            'group' => 'sometimes|string',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $setting->update($validated);

        return $this->success($setting, 'Setting updated successfully');
    }

    public function bulkUpdate(Request $request, LicenseService $licenseService): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.type' => 'sometimes|in:string,integer,boolean,json,text,password,number,datetime,image,media',
            'settings.*.group' => 'sometimes|string',
        ]);

        $settings = is_array($validated['settings']) ? $validated['settings'] : [];
        $hasWhiteLabel = $licenseService->hasWhiteLabel();

        foreach ($settings as $settingData) {
            if (is_array($settingData) && isset($settingData['key'])) {
                $sKey = is_scalar($settingData['key']) ? (string) $settingData['key'] : '';

                // Security Check: White Label Protection
                if ($licenseService->isProtectedKey($sKey) && ! $hasWhiteLabel) {
                    continue; // Skip protected keys if no white label license
                }

                $sValue = $settingData['value'] ?? null;
                $sTypeRaw = $settingData['type'] ?? 'string';
                $sType = is_scalar($sTypeRaw) ? (string) $sTypeRaw : 'string';
                $sGroupRaw = $settingData['group'] ?? 'system';
                $sGroup = is_scalar($sGroupRaw) ? (string) $sGroupRaw : 'system';

                // In Core context, we want settings to be Global by default unless explicitly specified.
                // Hub-wide system settings (no subscription or organization column on Setting).
                Setting::set($sKey, $sValue, $sType, $sGroup);

                // Sync with Redis Settings if cache driver or enable_cache is changed
                if ($sKey === 'cache_driver' || $sKey === 'enable_cache') {
                    try {
                        $currentDriverRaw = Setting::get('cache_driver', 'file');
                        $currentDriver = is_scalar($currentDriverRaw) ? (string) $currentDriverRaw : 'file';
                        $currentEnabledRaw = Setting::get('enable_cache', true);
                        $currentEnabled = filter_var($currentEnabledRaw, FILTER_VALIDATE_BOOLEAN);

                        $isRedis = $currentEnabled && in_array($currentDriver, ['redis', 'failover']);
                        RedisSetting::setValue('enable_redis', $isRedis);
                    } catch (\Throwable) {
                        // Silent fail if RedisSetting not available
                    }
                }
            }
        }

        // Clear config cache to apply changes immediately
        try {
            Artisan::call('config:clear');
        } catch (\Throwable) {
            // Silent fail
        }

        return $this->success(null, 'Settings updated successfully');
    }

    public function destroy(Setting $setting): JsonResponse
    {
        $setting->delete();

        return $this->success(null, 'Setting deleted successfully');
    }

    /**
     * Test storage connection dynamically
     */
    public function testStorage(Request $request): JsonResponse
    {
        $driverRaw = $request->input('driver');
        $driver = is_string($driverRaw) ? $driverRaw : '';
        $configRaw = $request->input('config'); // Array of settings formData
        /** @var array<string, mixed> $config */
        $config = is_array($configRaw) ? $configRaw : [];

        if (! $driver || empty($config)) {
            return $this->error('Driver and configuration are required', 400);
        }

        // Map config keys to Laravel filesystem config
        $diskConfig = ['driver' => $driver];

        if ($driver === 's3') {
            $awsKeyRaw = $config['aws_access_key_id'] ?? '';
            $awsSecretRaw = $config['aws_secret_access_key'] ?? '';
            $awsRegionRaw = $config['aws_default_region'] ?? 'us-east-1';
            $awsBucketRaw = $config['aws_bucket'] ?? '';
            $awsEndpointRaw = $config['aws_endpoint'] ?? '';

            $diskConfig = array_merge($diskConfig, [
                'key' => is_scalar($awsKeyRaw) ? (string) $awsKeyRaw : '',
                'secret' => is_scalar($awsSecretRaw) ? (string) $awsSecretRaw : '',
                'region' => is_scalar($awsRegionRaw) ? (string) $awsRegionRaw : 'us-east-1',
                'bucket' => is_scalar($awsBucketRaw) ? (string) $awsBucketRaw : '',
                'endpoint' => is_scalar($awsEndpointRaw) ? (string) $awsEndpointRaw : '',
                'use_path_style_endpoint' => ! empty($config['aws_endpoint']),
            ]);
        } elseif ($driver === 'google') {
            $gClientIdRaw = $config['google_client_id'] ?? '';
            $gClientSecretRaw = $config['google_client_secret'] ?? '';
            $gRefreshTokenRaw = $config['google_refresh_token'] ?? '';
            $gFolderIdRaw = $config['google_folder_id'] ?? '/';

            $diskConfig = array_merge($diskConfig, [
                'clientId' => is_scalar($gClientIdRaw) ? (string) $gClientIdRaw : '',
                'clientSecret' => is_scalar($gClientSecretRaw) ? (string) $gClientSecretRaw : '',
                'refreshToken' => is_scalar($gRefreshTokenRaw) ? (string) $gRefreshTokenRaw : '',
                'folderId' => is_scalar($gFolderIdRaw) ? (string) $gFolderIdRaw : '/',
            ]);
        } elseif ($driver === 'ftp') {
            $ftpHostRaw = $config['ftp_host'] ?? '';
            $ftpUsernameRaw = $config['ftp_username'] ?? '';
            $ftpPasswordRaw = $config['ftp_password'] ?? '';
            $ftpPortRaw = $config['ftp_port'] ?? 21;
            $ftpRootRaw = $config['ftp_root'] ?? '';

            $diskConfig = array_merge($diskConfig, [
                'host' => is_scalar($ftpHostRaw) ? (string) $ftpHostRaw : '',
                'username' => is_scalar($ftpUsernameRaw) ? (string) $ftpUsernameRaw : '',
                'password' => is_scalar($ftpPasswordRaw) ? (string) $ftpPasswordRaw : '',
                'port' => is_numeric($ftpPortRaw) ? (int) $ftpPortRaw : 21,
                'root' => is_scalar($ftpRootRaw) ? (string) $ftpRootRaw : '',
                'ssl' => (bool) ($config['ftp_ssl'] ?? false),
            ]);
        } elseif ($driver === 'dropbox') {
            $dbTokenRaw = $config['dropbox_authorization_token'] ?? '';
            $diskConfig = array_merge($diskConfig, [
                'authorizationToken' => is_scalar($dbTokenRaw) ? (string) $dbTokenRaw : '',
            ]);
        }

        // Set dynamic config
        Config::set("filesystems.disks.test_{$driver}", $diskConfig);

        try {
            // Attempt to list contents
            Storage::disk("test_{$driver}")->listContents('/');

            return $this->success(null, 'Connection successful! Storage is accessible.');
        } catch (\Throwable $e) {
            $message = $e->getMessage();

            // Helpful messages for missing drivers
            if (str_contains($message, 'Driver [google] is not supported')) {
                $message = 'Google Drive adapter is missing. Please run: composer require spatie/flysystem-google-drive';
            }
            if (str_contains($message, 'Driver [s3] is not supported')) {
                $message = 'AWS S3 adapter is missing. Please run: composer require league/flysystem-aws-s3-v3';
            }
            if (str_contains($message, 'Driver [dropbox] is not supported')) {
                $message = 'Dropbox adapter is missing. Please run: composer require spatie/flysystem-dropbox';
            }
            if (str_contains($message, 'Driver [ftp] is not supported')) {
                $message = 'FTP adapter is missing. Please run: composer require league/flysystem-ftp';
            }

            return $this->error('Connection failed: '.$message, 500);
        }
    }
}
