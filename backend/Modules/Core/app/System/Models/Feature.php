<?php

declare(strict_types=1);

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $extension_slug
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property string $category
 * @property bool $is_active
 */
class Feature extends Model
{
    use HasUuids;

    protected $table = 'sys_features';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'extension_slug',
        'slug',
        'name',
        'description',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the extension that owns the feature.
     *
     * @return BelongsTo<Extension, $this>
     */
    public function extension(): BelongsTo
    {
        return $this->belongsTo(Extension::class, 'extension_slug', 'slug');
    }
}
