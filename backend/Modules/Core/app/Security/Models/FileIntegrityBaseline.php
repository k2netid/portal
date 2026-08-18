<?php

declare(strict_types=1);

namespace Modules\Core\Security\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * File Integrity Baseline model.
 *
 * @property int $id
 * @property string $file_path
 * @property string $hash
 * @property int $file_size
 * @property string $status
 * @property Carbon $checked_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class FileIntegrityBaseline extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sec_file_integrity_baselines';

    protected $fillable = [
        'file_path',
        'hash',
        'file_size',
        'status',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'file_size' => 'integer',
    ];
}
