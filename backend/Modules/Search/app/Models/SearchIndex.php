<?php

namespace Modules\Search\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Publishing\Dto\SearchableContentSnapshot;

/**
 * @property string $id
 * @property string $searchable_type
 * @property string $searchable_id
 * @property string $title
 * @property string|null $content
 * @property string|null $excerpt
 * @property array<string, mixed>|null $meta
 * @property string|null $url
 * @property string|null $type
 * @property int $relevance_score
 * @property-read Model $searchable
 */
class SearchIndex extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'srch_indexes';

    protected $fillable = [
        'searchable_type',
        'searchable_id',
        'title',
        'content',
        'excerpt',
        'meta',
        'url',
        'type',
        'relevance_score',
    ];

    protected $casts = [
        'meta' => 'array',
        'relevance_score' => 'integer',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function searchable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function indexFromSnapshot(SearchableContentSnapshot $snapshot, array $data = []): self
    {
        $title = self::toString($data['title'] ?? $snapshot->title);
        $content = self::toString($data['content'] ?? '');
        $excerptRaw = $data['excerpt'] ?? $snapshot->excerpt;
        $excerpt = is_string($excerptRaw) ? $excerptRaw : null;
        $urlRaw = $data['url'] ?? null;
        $url = is_string($urlRaw) ? $urlRaw : null;
        $typeRaw = $data['type'] ?? $snapshot->type;
        $type = is_string($typeRaw) ? $typeRaw : null;

        return self::updateOrCreate(
            [
                'searchable_type' => $snapshot->searchableType,
                'searchable_id' => $snapshot->searchableId,
            ],
            [
                'title' => $title,
                'content' => $content,
                'excerpt' => $excerpt,
                'meta' => $data['meta'] ?? [],
                'url' => $url,
                'type' => $type,
                'relevance_score' => self::calculateRelevance($title, $content),
            ]
        );
    }

    /**
     * @param  Model  $model
     * @param  array<string, mixed>  $data
     */
    public static function index($model, array $data = []): self
    {
        $searchableType = $model::class;
        $searchableId = self::toString($model->getAttribute('id'));

        $title = self::toString($data['title'] ?? $model->getAttribute('title') ?? $model->getAttribute('name') ?? '');
        $content = self::toString($data['content'] ?? $model->getAttribute('body') ?? $model->getAttribute('description') ?? '');
        $excerptRaw = $data['excerpt'] ?? $model->getAttribute('excerpt') ?? null;
        $excerpt = is_string($excerptRaw) ? $excerptRaw : null;
        $urlRaw = $data['url'] ?? null;
        $url = is_string($urlRaw) ? $urlRaw : null;
        $typeRaw = $data['type'] ?? null;
        $type = is_string($typeRaw) ? $typeRaw : null;

        return self::updateOrCreate(
            [
                'searchable_type' => $searchableType,
                'searchable_id' => $searchableId,
            ],
            [
                'title' => $title,
                'content' => $content,
                'excerpt' => $excerpt,
                'meta' => $data['meta'] ?? [],
                'url' => $url,
                'type' => $type,
                'relevance_score' => self::calculateRelevance($title, $content),
            ]
        );
    }

    private static function toString(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    protected static function calculateRelevance(string $title, string $content): int
    {
        $score = strlen($title) * 2 + strlen($content);

        return min(100, max(1, (int) ($score / 100)));
    }

    public static function calculateRelevanceScore(string $title, ?string $content = null): int
    {
        return self::calculateRelevance($title, $content ?? '');
    }
}
