<?php

namespace Modules\Publishing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Core\System\Models\User;
use Modules\Publishing\Database\Factories\ContentRevisionFactory;

/**
 * @property int $id
 * @property int $content_id
 * @property int|null $author_id
 * @property string $title
 * @property string $body
 * @property array<string, mixed>|null $meta
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Content $content
 * @property-read User|null $author
 * @property-read User|null $user
 */
class ContentRevision extends Model
{
    /** @use HasFactory<ContentRevisionFactory> */
    use HasFactory;

    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'pub_content_revisions';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ContentRevisionFactory
    {
        return ContentRevisionFactory::new();
    }

    protected $fillable = [
        'content_id',
        'author_id',
        'title',
        'body',
        'meta',
        'reason',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * @return BelongsTo<Content, $this>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Alias for backward compatibility if needed, or just remove
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
