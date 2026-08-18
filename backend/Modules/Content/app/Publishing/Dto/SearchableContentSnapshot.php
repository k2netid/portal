<?php

declare(strict_types=1);

namespace Modules\Content\Publishing\Dto;

use Modules\Content\Publishing\Models\Content;

/**
 * Immutable read model for cross-tier search indexing (no Eloquent outside Content).
 */
final readonly class SearchableContentSnapshot
{
    public function __construct(
        public string $searchableType,
        public string $searchableId,
        public string $status,
        public string $type,
        public string $title,
        public string $slug,
        public ?string $excerpt,
        public string $intro,
        public string $body,
        public string $categoryName,
        public string $authorName,
    ) {}

    public static function fromContent(Content $model): self
    {
        $model->loadMissing(['category', 'author']);

        $categoryName = $model->category !== null ? (string) $model->category->name : '';
        $authorName = $model->author !== null ? (string) $model->author->name : '';

        $key = $model->getKey();

        return new self(
            searchableType: $model::class,
            searchableId: is_scalar($key) ? (string) $key : '',
            status: (string) $model->status,
            type: (string) $model->type,
            title: (string) $model->title,
            slug: (string) $model->slug,
            excerpt: $model->excerpt !== null ? (string) $model->excerpt : null,
            intro: (string) ($model->intro ?? ''),
            body: (string) ($model->body ?? ''),
            categoryName: $categoryName,
            authorName: $authorName,
        );
    }
}
