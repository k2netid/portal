<?php

namespace Modules\Core\Infra\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $disk
 * @property string $path
 * @property int $size
 * @property string $type
 * @property string $status
 * @property string|null $error_message
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 */
class Backup extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'infra_backups';

    protected $fillable = [
        'name',
        'type',
        'path',
        'disk',
        'size',
        'status',
        'error_message',
        'completed_at',
        'password',
    ];

    protected $casts = [
        'size' => 'integer',
        'completed_at' => 'datetime',
        'password' => 'encrypted',
    ];

    public function getSizeHumanAttribute(): string
    {
        if (! $this->size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2).' '.$units[$unit];
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
