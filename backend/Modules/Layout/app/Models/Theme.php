<?php

declare(strict_types=1);

namespace Modules\Layout\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Minimal Theme model for menu usage analysis (P3-3a).
 * Full theme management ships in P3-3b.
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property array<string, mixed>|null $settings
 * @property bool $is_active
 */
class Theme extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'lay_themes';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'path',
        'settings',
        'is_active',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
