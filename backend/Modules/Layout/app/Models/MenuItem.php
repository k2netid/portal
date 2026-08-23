<?php

namespace Modules\Layout\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Layout\Database\Factories\MenuItemFactory;

/**
 * @property string $id
 * @property string $menu_id
 * @property string|null $parent_id
 * @property string $title
 * @property string|null $url
 * @property string $type
 * @property string|null $target_id
 * @property string|null $target_type
 * @property string|null $icon
 * @property string|null $css_class
 * @property int $sort_order
 * @property bool $open_in_new_tab
 * @property array<string, mixed>|null $metadata
 */
class MenuItem extends EloquentModel
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /** @use HasFactory<MenuItemFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'lay_menu_items';

    protected static function newFactory(): MenuItemFactory
    {
        return MenuItemFactory::new();
    }

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'type',
        'target_id',
        'target_type',
        'icon',
        'css_class',
        'sort_order',
        'open_in_new_tab',
        'metadata',
    ];

    protected $casts = [
        'open_in_new_tab' => 'boolean',
        'metadata' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * @return BelongsTo<MenuItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Morph to the target model (Content, Category, etc.)
     *
     * @return MorphTo<EloquentModel, $this>
     */
    public function target(): MorphTo
    {
        return $this->morphTo('target', 'target_type', 'target_id');
    }
}
