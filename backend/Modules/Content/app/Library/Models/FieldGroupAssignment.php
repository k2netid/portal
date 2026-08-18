<?php

namespace Modules\Content\Library\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldGroupAssignment extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'lib_field_group_assignments';

    protected $fillable = [
        'group_id',
        'assignable_type',
        'module_scope',
    ];

    /**
     * @return BelongsTo<FieldGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(FieldGroup::class, 'group_id');
    }
}
