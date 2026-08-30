<?php

namespace Modules\Library\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Core\System\Models\User;
use Modules\Library\Database\Factories\CustomFieldFactory;

class CustomField extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /** @use HasFactory<CustomFieldFactory> */
    protected $table = 'lib_fields';

    protected static function newFactory(): CustomFieldFactory
    {
        return CustomFieldFactory::new();
    }

    protected $fillable = [
        'name',
        'key',
        'type',
        'options',
        'rules',
        'default_value',
        'placeholder',
        'help_text',
        'is_required',
        'is_filterable',
        'sort_order',
        'author_id',
    ];

    protected $casts = [
        'options' => 'array',
        'rules' => 'array',
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsToMany<FieldGroup, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(FieldGroup::class, 'lib_field_group_pivot', 'field_id', 'group_id')
            ->withPivot('sort_order')
            ->orderBy('lib_field_group_pivot.sort_order');
    }
}
