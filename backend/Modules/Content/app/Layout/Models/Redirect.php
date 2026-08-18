<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\Content\Layout\Database\Factories\RedirectFactory;

/**
 * @property string $id
 * @property string $source_path
 * @property string $target_path
 * @property int $status_code
 * @property string|null $module_scope
 * @property int $hits
 * @property Carbon|null $last_hit_at
 * @property bool $is_active
 */
class Redirect extends Model
{
    /** @use HasFactory<RedirectFactory> */
    use HasFactory;

    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'lay_redirects';

    protected $fillable = [
        'source_path',
        'target_path',
        'status_code',
        'module_scope',
        'hits',
        'last_hit_at',
        'is_active',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'hits' => 'integer',
        'last_hit_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): RedirectFactory
    {
        return RedirectFactory::new();
    }
}
