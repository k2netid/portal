<?php

namespace Modules\Content\Library\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Content\Library\Database\Factories\FieldGroupFactory;

class FieldGroup extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /** @use HasFactory<FieldGroupFactory> */
    protected $table = 'lib_field_groups';

    protected static function newFactory(): FieldGroupFactory
    {
        return FieldGroupFactory::new();
    }

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * @return BelongsToMany<CustomField, $this>
     */
    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(CustomField::class, 'lib_field_group_pivot', 'group_id', 'field_id')
            ->withPivot('sort_order')
            ->orderBy('lib_field_group_pivot.sort_order');
    }

    /**
     * @return HasMany<FieldGroupAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(FieldGroupAssignment::class, 'group_id');
    }
}
