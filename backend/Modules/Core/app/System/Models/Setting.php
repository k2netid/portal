<?php

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string $group
 * @property string|null $description
 * @property bool $is_public
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Setting extends Model
{
    public const KEY_CONSOLE_DASHBOARD_SLUG = 'console_dashboard_slug';

    public const LEGACY_KEY_ADMIN_DASHBOARD_SLUG = 'admin_dashboard_slug';

    /**
     * Setting groups owned by product packs (Publishing / Analytics), not kernel settings.
     *
     * @var list<string>
     */
    public const PRODUCT_SETTING_GROUPS = ['seo', 'comments', 'analytics'];

    public static function isProductSettingGroup(string $group): bool
    {
        return in_array($group, self::PRODUCT_SETTING_GROUPS, true);
    }

    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * In-memory runtime cache for the active request lifecycle.
     *
     * @var array<string, mixed>
     */
    protected static array $runtimeCache = [];

    public static function clearRuntimeCache(): void
    {
        static::$runtimeCache = [];
    }

    protected static function booted(): void
    {
        static::saved(function (self $setting): void {
            static::$runtimeCache[$setting->key] = static::castValue($setting->value, (string) $setting->type);
            try {
                Cache::forget("sys_setting_{$setting->key}");
            } catch (\Throwable) {
                // Ignore cache forget failure
            }
        });

        static::deleted(function (self $setting): void {
            unset(static::$runtimeCache[$setting->key]);
            try {
                Cache::forget("sys_setting_{$setting->key}");
            } catch (\Throwable) {
                // Ignore cache forget failure
            }
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, static::$runtimeCache)) {
            return static::$runtimeCache[$key];
        }

        try {
            $setting = static::where('key', $key)->first();

            if (! $setting) {
                return static::$runtimeCache[$key] = $default;
            }

            return static::$runtimeCache[$key] = static::castValue($setting->value, (string) $setting->type);
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
                'group' => $group,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function getGroup(string $group): array
    {
        $settings = static::where('group', $group)->get();

        return $settings->mapWithKeys(fn ($setting) => [(string) $setting->key => static::castValue($setting->value, (string) $setting->type)])->toArray();
    }

    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => is_numeric($value) ? (int) $value : 0,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => is_string($value) ? json_decode($value, true) : $value,
            'array' => is_string($value) ? json_decode($value, true) : (array) $value,
            default => is_scalar($value) || $value === null ? $value : (string) json_encode($value),
        };
    }

    public static function clearCache(?string $key = null): void
    {
        if ($key !== null) {
            Cache::forget("sys_setting_{$key}");
            return;
        }

        try {
            $keys = static::query()->pluck('key');
            foreach ($keys as $k) {
                Cache::forget("sys_setting_{$k}");
            }
        } catch (\Throwable) {
            // silent fail
        }
    }

    public static function resolveConsoleDashboardSlug(): string
    {
        $raw = self::get(self::KEY_CONSOLE_DASHBOARD_SLUG, 'dash');
        $slug = trim(is_scalar($raw) ? (string) $raw : '');

        return $slug !== '' ? $slug : 'dash';
    }
}
