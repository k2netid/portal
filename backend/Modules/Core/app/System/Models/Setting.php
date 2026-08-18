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

    protected static function booted(): void
    {
        static::saved(function (self $setting): void {
            Cache::forget("sys_setting_{$setting->key}");
        });

        static::deleted(function (self $setting): void {
            Cache::forget("sys_setting_{$setting->key}");
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::rememberForever("sys_setting_{$key}", function () use ($key, $default) {
                $setting = static::where('key', $key)->first();

                if (! $setting) {
                    return $default;
                }

                return static::castValue($setting->value, (string) $setting->type);
            });
        } catch (QueryException) {
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

    public static function resolveConsoleDashboardSlug(): string
    {
        $raw = self::get(self::KEY_CONSOLE_DASHBOARD_SLUG, 'dash');
        $slug = trim(is_scalar($raw) ? (string) $raw : '');

        return $slug !== '' ? $slug : 'dash';
    }
}
