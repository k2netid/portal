<?php

declare(strict_types=1);

namespace Modules\Media\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $original_path
 * @property string $trash_path
 * @property string $disk
 * @property string $name
 * @property string $type
 * @property int $size
 * @property string $extension
 * @property string $mime_type
 * @property int|null $deleted_by
 * @property Carbon $deleted_at
 */
class DeletedFile extends Model
{
    use HasUuids;

    protected $table = 'srv_media_deleted_files';

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
}
