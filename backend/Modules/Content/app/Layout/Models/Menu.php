<?php

namespace Modules\Content\Layout\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\Content\Layout\Database\Factories\MenuFactory;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $location
 * @property string|null $description
 * @property string|null $module_scope
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    use HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'lay_menus';

    protected static function newFactory(): MenuFactory
    {
        return MenuFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'module_scope',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function parentItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    public static function getByLocation(string $location): ?self
    {
        return self::where('location', $location)
            ->where('is_active', true)
            ->first();
    }
}
