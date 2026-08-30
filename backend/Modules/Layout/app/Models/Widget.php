<?php

declare(strict_types=1);

namespace Modules\Layout\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\Layout\Database\Factories\WidgetFactory;

/**
 * @property string $id
 * @property string $name
 * @property string $type
 * @property string $location
 * @property array<string, mixed>|null $settings
 * @property string $module_scope
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Widget extends Model
{
    /** @use HasFactory<WidgetFactory> */
    use HasFactory;

    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'lay_widgets';

    protected static function newFactory(): WidgetFactory
    {
        return WidgetFactory::new();
    }

    protected $fillable = [
        'name',
        'type',
        'location',
        'settings',
        'module_scope',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
