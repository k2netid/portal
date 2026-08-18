<?php

declare(strict_types=1);

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AbacPolicy extends Model
{
    use HasUuids;

    protected $table = 'sys_abac_policies';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'target_resource',
        'action',
        'conditions',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];
}
