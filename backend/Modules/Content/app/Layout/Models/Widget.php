<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Content\Layout\Database\Factories\WidgetFactory;

class Widget extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /** @use HasFactory<WidgetFactory> */
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
