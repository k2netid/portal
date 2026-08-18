<?php

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $id
 * @property string $translatable_type
 * @property string $translatable_id
 * @property string $language_code
 * @property string $field
 * @property string|null $value
 */
class Translation extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_translations';

    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'language_code',
        'field',
        'value',
    ];

    /**
     * Get the parent translatable model.
     *
     * @return MorphTo<Model, $this>
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
