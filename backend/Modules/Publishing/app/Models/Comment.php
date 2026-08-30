<?php

namespace Modules\Publishing\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Core\System\Models\User;
use Modules\Member\Models\Member;
use Modules\Publishing\Database\Factories\CommentFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @property int $id
 * @property int $content_id
 * @property int|null $user_id
 * @property int|null $parent_id
 * @property string $body
 * @property string $status
 * @property int|null $locked_by
 * @property Carbon|null $locked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $name
 * @property string|null $email
 * @property Carbon|null $deleted_at
 * @property-read Content $content
 * @property-read User|null $user
 * @property-read Comment|null $parent
 * @property-read Collection<int, Comment> $replies
 * @property-read User|null $lockedBy
 */
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'pub_comments';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }

    protected $fillable = [
        'content_id',
        'user_id',
        'member_id',
        'parent_id',
        'body',
        'status',
        'name',
        'email',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'body'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $casts = [
        'locked_at' => 'datetime',
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    /**
     * @return BelongsTo<Comment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}
