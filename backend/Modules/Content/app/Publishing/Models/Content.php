<?php

namespace Modules\Content\Publishing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\Content\Layout\Models\MenuItem;
use Modules\Content\Library\Models\Category;
use Modules\Content\Library\Models\CustomField;
use Modules\Content\Library\Models\Tag;
use Modules\Content\Publishing\Database\Factories\ContentFactory;
use Modules\Core\System\Models\User;
use Modules\Core\System\Traits\CoreLogsActivity;

/**
 * @property string $id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $intro
 * @property string|null $body
 * @property string|null $featured_image
 * @property string $status
 * @property string $type
 * @property string $author_id
 * @property string|null $category_id
 * @property Carbon|null $published_at
 * @property array<string, mixed>|null $meta
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $og_image
 * @property bool $is_featured
 * @property int $views
 * @property string $comment_status
 * @property string|null $locked_by
 * @property Carbon|null $locked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property array<string, mixed>|null $lock_status Runtime attribute set on API responses (not a DB column)
 */
class Content extends Model
{
    protected $table = 'pub_contents';

    use CoreLogsActivity, HasUuids, SoftDeletes;

    /** @use HasFactory<ContentFactory> */
    use HasFactory;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ContentFactory
    {
        return ContentFactory::new();
    }

    protected $fillable = [
        'title', 'slug', 'excerpt', 'intro', 'body', 'featured_image',
        'status', 'type', 'author_id', 'category_id',
        'published_at', 'meta', 'meta_title', 'meta_description',
        'meta_keywords', 'og_image', 'is_featured', 'views', 'comment_status', 'locked_by', 'locked_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'locked_at' => 'datetime',
        'meta' => 'array',
        'is_featured' => 'boolean',
        'views' => 'integer',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return MorphToMany<Tag, $this>
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'lib_taggables');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<ContentRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(ContentRevision::class);
    }

    /**
     * @return HasMany<ContentCustomField, $this>
     */
    public function customFields(): HasMany
    {
        return $this->hasMany(ContentCustomField::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * @return MorphMany<MenuItem, $this>
     */
    public function menuItems(): MorphMany
    {
        return $this->morphMany(MenuItem::class, 'target');
    }

    /**
     * Get custom field value by slug
     */
    public function getCustomFieldValue(string $slug): ?string
    {
        $field = $this->customFields()
            ->whereHas('customField', function ($q) use ($slug): void {
                $q->where('key', $slug);
            })
            ->first();

        if (! $field instanceof ContentCustomField) {
            return null;
        }

        return $field->value;
    }

    /**
     * Persist a value for a library custom field on this content (matched by field key/slug).
     */
    public function setCustomFieldValue(string $slug, mixed $value): void
    {
        $field = CustomField::where('key', $slug)->first();
        if (! $field) {
            return;
        }

        ContentCustomField::updateOrCreate(
            [
                'content_id' => $this->id,
                'custom_field_id' => $field->id,
            ],
            ['value' => is_array($value) ? json_encode($value) : $value]
        );
    }
}
