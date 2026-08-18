<?php

namespace Modules\Core\Infra\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Core\System\Models\User;

/**
 * @property int $id
 * @property string $original_path
 * @property string $trash_path
 * @property string $disk
 * @property string $name
 * @property string $type
 * @property int $size
 * @property string $extension
 * @property string $mime_type
 * @property string|null $deleted_by
 * @property Carbon $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $deletedByUser
 * @property-read string $formatted_size
 */
class DeletedFile extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'infra_deleted_files';

    protected $fillable = [
        'original_path',
        'trash_path',
        'disk',
        'name',
        'type',
        'size',
        'extension',
        'mime_type',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'size' => 'integer',
    ];

    /**
     * Get the user who deleted this file
     *
     * @return BelongsTo<User, $this>
     */
    public function deletedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope for files only
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeFiles($query)
    {
        return $query->where('type', 'file');
    }

    /**
     * Scope for folders only
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeFolders($query)
    {
        return $query->where('type', 'folder');
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        if (! $this->size || $this->size <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($this->size, 1024));

        // Safety check for units index
        if (! isset($units[$i])) {
            $i = count($units) - 1;
        }

        $val = round($this->size / 1024 ** $i, 2);

        return $val.' '.$units[$i];
    }
}
