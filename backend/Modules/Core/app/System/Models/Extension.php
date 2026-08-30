<?php

declare(strict_types=1);

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $slug
 * @property string $type
 * @property string $name
 * @property string $version
 * @property string $database_version
 * @property string $status
 * @property bool $is_core
 * @property string|null $author
 * @property string|null $description
 * @property string $license
 * @property array<string, mixed>|null $requirements
 * @property array<string, mixed>|null $manifest
 * @property array<string, mixed>|null $settings
 */
class Extension extends Model
{
    use HasUuids, SoftDeletes;

    protected static function booted(): void
    {
        static::saved(function () {
            @unlink(storage_path('framework/cache/active_extensions.json'));
            self::flushProductActiveMemo();
        });

        static::deleted(function () {
            @unlink(storage_path('framework/cache/active_extensions.json'));
            self::flushProductActiveMemo();
        });
    }

    /**
     * Product-on switch (sys_extensions.status), not nwidart module boot.
     */
    public static function isProductActive(string $slug): bool
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            return false;
        }

        $memo = [];
        if (app()->bound('extensions.product_active_memo')) {
            $bound = app('extensions.product_active_memo');
            $memo = is_array($bound) ? $bound : [];
            if (array_key_exists($slug, $memo)) {
                return (bool) $memo[$slug];
            }
        }

        try {
            $active = self::query()
                ->where('slug', $slug)
                ->where('status', 'active')
                ->exists();
        } catch (\Throwable) {
            $active = false;
        }

        $memo[$slug] = $active;
        app()->instance('extensions.product_active_memo', $memo);

        return $active;
    }

    public static function flushProductActiveMemo(): void
    {
        app()->instance('extensions.product_active_memo', []);
    }

    protected $table = 'sys_extensions';

    /**
     * @return HasMany<Feature, $this>
     */
    public function features(): HasMany
    {
        return $this->hasMany(Feature::class, 'extension_slug', 'slug');
    }

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'slug',
        'type',
        'family',
        'parent_slug',
        'name',
        'version',
        'database_version',
        'status',
        'is_core',
        'author',
        'description',
        'license',
        'requirements',
        'manifest',
        'settings',
    ];

    protected $casts = [
        'is_core' => 'boolean',
        'requirements' => 'array',
        'manifest' => 'array',
        'settings' => 'array',
    ];
}
