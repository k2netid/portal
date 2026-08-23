<?php

namespace Modules\Publishing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\Core\System\Models\User;
use Modules\Library\Models\Category;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $type
 * @property string|null $title_template
 * @property string|null $body_template
 * @property string|null $excerpt_template
 * @property array<string, mixed>|null $default_fields
 * @property array<string, mixed>|null $meta
 * @property int|null $category_id
 * @property bool $is_active
 * @property int $usage_count
 * @property int|null $author_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $author
 * @property-read Category|null $category
 */
class ContentTemplate extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_content_templates';

    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'title_template',
        'body_template',
        'excerpt_template',
        'default_fields',
        'meta',
        'category_id',
        'is_active',
        'usage_count',
        'author_id',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected $casts = [
        'default_fields' => 'array',
        'meta' => 'array',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createContent(array $data = []): Content
    {
        $title = $this->replaceTemplateVariables($this->title_template ?? '{{ title }}', $data);
        $body = $this->replaceTemplateVariables((string) $this->body_template, $data);
        $excerpt = $this->excerpt_template ? $this->replaceTemplateVariables($this->excerpt_template, $data) : null;

        /** @var array<string, mixed> $contentData */
        $contentData = [
            'title' => $title,
            'body' => $body,
            'excerpt' => $excerpt,
            'type' => $this->type,
            'category_id' => $this->category_id,
            'status' => 'draft',
        ];

        // Merge with provided data
        $contentData = array_merge($contentData, $data);

        // Create content
        /** @var Content $content */
        $content = Content::create($contentData);

        // Apply default custom fields if any
        if (is_array($this->default_fields)) {
            foreach ($this->default_fields as $fieldSlug => $value) {
                $content->setCustomFieldValue((string) $fieldSlug, $value);
            }
        }

        // Increment usage count
        $this->increment('usage_count');

        return $content;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function replaceTemplateVariables(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $template = str_replace('{{'.$key.'}}', (string) $value, $template);
                $template = str_replace('{{ $'.$key.' }}', (string) $value, $template);
            }
        }

        return $template;
    }
}
