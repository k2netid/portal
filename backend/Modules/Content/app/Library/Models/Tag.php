<?php

namespace Modules\Content\Library\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Content\Library\Database\Factories\TagFactory;
use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\User;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string|null $author_id
 * @property int $usage_count
 * @property array<string, mixed>|null $metadata
 */
class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    use HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }

    protected $table = 'lib_tags';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'author_id',
        'usage_count',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'usage_count' => 'integer',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Contents using this tag (pivot lib_taggables).
     *
     * @return MorphToMany<Content, $this>
     */
    public function contents(): MorphToMany
    {
        return $this->morphedByMany(Content::class, 'taggable', 'lib_taggables');
    }

    /**
     * Get all of the posts that are assigned this tag.
     *
     * @param  class-string<Model>  $class
     * @return MorphToMany<Model, $this>
     */
    public function taggables(string $class): MorphToMany
    {
        return $this->morphedByMany($class, 'taggable', 'lib_taggables');
    }
}
