<?php

namespace Modules\Content\Publishing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Content\Library\Models\CustomField;

/**
 * @property string $id
 * @property string $content_id
 * @property string $custom_field_id
 * @property string|null $value
 */
class ContentCustomField extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'pub_content_custom_fields';

    protected $fillable = [
        'content_id',
        'custom_field_id',
        'value',
    ];

    /**
     * @return BelongsTo<Content, $this>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * @return BelongsTo<CustomField, $this>
     */
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class);
    }
}
