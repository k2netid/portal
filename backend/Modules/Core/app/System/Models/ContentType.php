<?php

declare(strict_types=1);

namespace Modules\Core\System\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Data Studio operational schema (kernel Infra), not Publishing CCK.
 *
 * Editorial custom fields live on Library `lib_fields`. This table stores
 * runtime entity types for operator-defined records (`sys_dynamic_records`).
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property array<mixed>|null $fields
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ContentType extends Model
{
    use HasUuids;

    /**
     * Slugs owned by CMS packs or kernel identity. Data Studio must not use them.
     *
     * @var list<string>
     */
    public const RESERVED_SLUGS = [
        'post', 'posts', 'page', 'pages', 'content', 'contents',
        'category', 'categories', 'tag', 'tags',
        'media', 'comment', 'comments',
        'member', 'members', 'user', 'users',
        'form', 'forms', 'mail', 'newsletter',
        'site', 'sites',
    ];

    public static function isReservedSlug(string $slug): bool
    {
        $normalized = strtolower(trim($slug));

        return $normalized !== '' && in_array($normalized, self::RESERVED_SLUGS, true);
    }

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_content_types';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'fields',
        'is_active',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get records created for this content type.
     *
     * @return HasMany<DynamicRecord, $this>
     */
    public function records(): HasMany
    {
        return $this->hasMany(DynamicRecord::class, 'content_type_id');
    }
}
