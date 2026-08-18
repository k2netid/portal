<?php

declare(strict_types=1);

namespace Modules\Content\Library\Services;

use Modules\Content\Library\Contracts\TaxonomySearchPortInterface;
use Modules\Content\Library\Dto\TaxonomySearchSnapshot;
use Modules\Content\Library\Models\Category;
use Modules\Content\Library\Models\Tag;

class TaxonomySearchPortAdapter implements TaxonomySearchPortInterface
{
    public function categorySearchableType(): string
    {
        return Category::class;
    }

    public function tagSearchableType(): string
    {
        return Tag::class;
    }

    public function snapshotForIndex(string $taxonomyType, string $searchableId): ?TaxonomySearchSnapshot
    {
        return match ($taxonomyType) {
            'category' => $this->categoryToSnapshot(Category::query()->find($searchableId)),
            'tag' => $this->tagToSnapshot(Tag::query()->find($searchableId)),
            default => null,
        };
    }

    public function activeCategorySnapshots(): iterable
    {
        foreach (Category::query()->where('is_active', true)->cursor() as $category) {
            if ($category instanceof Category) {
                $snapshot = $this->categoryToSnapshot($category);
                if ($snapshot !== null) {
                    yield $snapshot;
                }
            }
        }
    }

    public function tagSnapshots(): iterable
    {
        foreach (Tag::query()->cursor() as $tag) {
            if ($tag instanceof Tag) {
                $snapshot = $this->tagToSnapshot($tag);
                if ($snapshot !== null) {
                    yield $snapshot;
                }
            }
        }
    }

    private function categoryToSnapshot(?Category $model): ?TaxonomySearchSnapshot
    {
        if (! $model instanceof Category) {
            return null;
        }

        $key = $model->getKey();

        return new TaxonomySearchSnapshot(
            searchableType: $model::class,
            searchableId: is_scalar($key) ? (string) $key : '',
            taxonomyKind: 'category',
            name: (string) $model->name,
            slug: (string) $model->slug,
            description: (string) ($model->description ?? ''),
            isActive: (bool) $model->is_active,
        );
    }

    public function activeCategoryCount(): int
    {
        return Category::query()->where('is_active', true)->count();
    }

    public function tagCount(): int
    {
        return Tag::query()->count();
    }

    private function tagToSnapshot(?Tag $model): ?TaxonomySearchSnapshot
    {
        if (! $model instanceof Tag) {
            return null;
        }

        $key = $model->getKey();

        return new TaxonomySearchSnapshot(
            searchableType: $model::class,
            searchableId: is_scalar($key) ? (string) $key : '',
            taxonomyKind: 'tag',
            name: (string) $model->name,
            slug: (string) $model->slug,
            description: '',
            isActive: true,
        );
    }
}
