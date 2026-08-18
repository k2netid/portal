<?php

declare(strict_types=1);

namespace Modules\Core\Infra\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $from_domain
 * @property string $to_domain
 * @property string|null $target_path
 * @property int $status_code
 * @property bool $keep_path
 * @property bool $is_active
 */
class InfraRedirect extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'infra_redirects';

    protected $fillable = [
        'from_domain',
        'to_domain',
        'target_path',
        'status_code',
        'keep_path',
        'is_active',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'keep_path' => 'boolean',
        'is_active' => 'boolean',
    ];
}
