<?php

namespace Modules\Media\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Infra\Contracts\MediaFileRecordInterface;
use Modules\Library\Models\Tag;
use Modules\Media\Database\Factories\FileFactory;

/**
 * @property string $id
 * @property string $module
 * @property string $name
 * @property string $file_name
 * @property string $mime_type
 * @property string $disk
 * @property string $path
 * @property string|null $thumbnail_path
 * @property int $size
 * @property string|null $alt
 * @property string|null $description
 * @property string|null $caption
 * @property string|null $folder_id
 * @property int|null $author_id
 * @property bool $is_shared
 */
class File extends Model implements MediaFileRecordInterface
{
    /** @use HasFactory<FileFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function newFactory(): FileFactory
    {
        return FileFactory::new();
    }

    protected $table = 'srv_media_files';

    public bool $isSharedScoped = true;

    protected $fillable = [
        'module',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'path',
        'size',
        'alt',
        'description',
        'caption',
        'folder_id',
        'author_id',
        'is_shared',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_shared' => 'boolean',
    ];

    protected $appends = ['url', 'thumbnail_url', 'is_trashed'];

    /**
     * @return BelongsTo<Folder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    /**
     * @return HasMany<Usage, $this>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(Usage::class, 'file_id');
    }

    /**
     * @return MorphToMany<Tag, $this>
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'lib_taggables');
    }

    public function getId(): int|string|null
    {
        $key = $this->getKey();

        return is_int($key) || is_string($key) ? $key : null;
    }

    public function getPath(): string
    {
        return (string) $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return array<int, string>
     */
    public function getTagNamesAttribute(): array
    {
        $names = $this->tags->pluck('name')->all();

        return array_values(array_map(static function (mixed $n): string {
            if (is_string($n)) {
                return $n;
            }

            return is_scalar($n) ? (string) $n : '';
        }, $names));
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        // For public disk, use relative path to avoid localhost URL issues
        if ($this->disk === 'public') {
            return '/storage/'.ltrim($this->path, '/');
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    public function getThumbnailPathAttribute(): ?string
    {
        if (! $this->path || ! str_starts_with((string) $this->mime_type, 'image/')) {
            return null;
        }

        $pathInfo = pathinfo($this->path);
        $fileName = isset($pathInfo['filename']) && is_string($pathInfo['filename']) ? $pathInfo['filename'] : '';
        $extension = isset($pathInfo['extension']) && is_string($pathInfo['extension']) ? $pathInfo['extension'] : '';
        $dirnameRaw = isset($pathInfo['dirname']) && is_string($pathInfo['dirname']) ? $pathInfo['dirname'] : '.';
        $dirname = ($dirnameRaw === '.' || $dirnameRaw === '/') ? '' : $dirnameRaw.'/';

        $isSvg = $this->mime_type === 'image/svg+xml' || strtolower((string) $extension) === 'svg';
        $thumbnailExtension = $isSvg ? 'png' : $extension;

        return $dirname.'thumbnails/'.$fileName.'_thumb.'.$thumbnailExtension;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $thumbnailPath = $this->thumbnail_path;
        if (! $thumbnailPath) {
            return $this->url;
        }

        if (Storage::disk($this->disk)->exists($thumbnailPath)) {
            if ($this->disk === 'public') {
                return '/storage/'.ltrim($thumbnailPath, '/');
            }

            return Storage::disk($this->disk)->url($thumbnailPath);
        }

        return $this->url;
    }

    public function getIsTrashedAttribute(): bool
    {
        return $this->trashed();
    }
}
