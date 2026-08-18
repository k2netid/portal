<?php

namespace Modules\Content\Publishing\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Content\Library\Models\CustomField;
use Modules\Content\Library\Models\Tag;
use Modules\Content\Media\Models\Usage as MediaUsage;
use Modules\Content\Publishing\Events\ContentDeleted;
use Modules\Content\Publishing\Events\ContentPublished;
use Modules\Content\Publishing\Events\ContentUnpublished;
use Modules\Content\Publishing\Models\Content;
use Modules\Content\Publishing\Models\ContentCustomField;
use Modules\Content\Publishing\Models\ContentRevision;
use Modules\Core\System\Models\Webhook;
use Modules\Core\System\Support\SqlLikeEscape;

class ContentService
{
    protected PublishingCacheService $cacheService;

    public function __construct()
    {
        $this->cacheService = new PublishingCacheService;
    }

    /**
     * Get published contents with filtering and caching
     *
     * @return array{data: LengthAwarePaginator<int, Content>|Collection<int, Content>, paginated: bool}
     */
    public function getPublishedContents(Request $request): array
    {
        $cacheKey = 'contents_published_'.md5((string) $request->getQueryString());

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($request): array {
            /** @var Builder<Content> $query */
            $query = Content::with(['author', 'category', 'tags'])
                ->where('status', 'published')
                ->where(function (Builder $q): void {
                    $q->whereNull('published_at')
                        ->orWhere('published_at', '<=', Carbon::now());
                });

            $this->applyFilters($query, $request);
            $this->applySorting($query, $request);

            // Limit or pagination
            $limitRaw = $request->get('limit');
            if ($limitRaw !== null && is_numeric($limitRaw)) {
                return [
                    'data' => $query->limit((int) $limitRaw)->get(),
                    'paginated' => false,
                ];
            }

            $perPageRaw = $request->get('per_page', 12);
            $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 12;

            return [
                'data' => $query->paginate($perPage),
                'paginated' => true,
            ];
        });
    }

    /**
     * Apply common filters to content query
     *
     * @param  Builder<Content>  $query
     */
    public function applyFilters($query, Request $request): void
    {
        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', filter_var($request->input('is_featured'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request): void {
                $q->where('slug', $request->input('category'));
            });
        }

        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request): void {
                $q->where('slug', $request->input('tag'));
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? trim($searchRaw) : '';
            if ($search !== '') {
                $term = mb_strtolower($search, 'UTF-8');
                $query->where(function ($q) use ($term): void {
                    SqlLikeEscape::whereContainsAny($q, ['title', 'body', 'excerpt'], $term);
                    $q->orWhereHas('category', function ($cq) use ($term): void {
                        SqlLikeEscape::whereContainsAny($cq, ['name'], $term);
                    });
                    $q->orWhereHas('author', function ($aq) use ($term): void {
                        SqlLikeEscape::whereContainsAny($aq, ['name'], $term);
                    });
                });
            }
        }
    }

    /**
     * Apply sorting to query
     *
     * @param  Builder<Content>  $query
     */
    public function applySorting($query, Request $request): void
    {
        $sortByRaw = $request->get('sort', '-published_at');
        $sortBy = is_string($sortByRaw) ? $sortByRaw : '-published_at';

        if (str_starts_with($sortBy, '-')) {
            $query->orderBy(substr($sortBy, 1), 'desc');
        } else {
            $query->orderBy($sortBy, 'asc');
        }
    }

    /**
     * Get related content by tags and category
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRelatedContent(string $slug, int $limit = 5): array
    {
        $cacheKey = 'content_related_'.$slug;

        /** @var array<int, array<string, mixed>> */
        return Cache::remember($cacheKey, now()->addHours(2), function () use ($slug, $limit) {
            $content = Content::where('slug', $slug)->first();
            if (! $content) {
                return [];
            }

            // Get related by tags first
            $relatedByTags = Content::where('status', 'published')
                ->where('id', '!=', $content->id)
                ->where(function ($q): void {
                    $q->whereNull('published_at')
                        ->orWhere('published_at', '<=', Carbon::now());
                })
                ->whereHas('tags', function ($q) use ($content): void {
                    $q->whereIn('lib_tags.id', $content->tags->pluck('id'));
                })
                ->with(['author', 'category', 'tags'])
                ->latest('published_at')
                ->limit($limit)
                ->get();

            // Fill with category-related if not enough
            if ($relatedByTags->count() < $limit && $content->category_id) {
                $relatedByCategory = Content::where('status', 'published')
                    ->where('id', '!=', $content->id)
                    ->where('category_id', $content->category_id)
                    ->whereNotIn('id', $relatedByTags->pluck('id'))
                    ->where(function ($q): void {
                        $q->whereNull('published_at')
                            ->orWhere('published_at', '<=', Carbon::now());
                    })
                    ->with(['author', 'category', 'tags'])
                    ->latest('published_at')
                    ->limit($limit - $relatedByTags->count())
                    ->get();

                return $relatedByTags->concat($relatedByCategory)->toArray();
            }

            return $relatedByTags->toArray();
        });
    }

    /**
     * Create new content
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, string $userId, bool $createRevision = false): Content
    {
        $data['author_id'] = $userId;

        // Handle published_at scheduling
        if (isset($data['published_at']) && $data['published_at']) {
            /** @var mixed $pubAt */
            $pubAt = $data['published_at'];
            $data['published_at'] = is_numeric($pubAt) ? Carbon::createFromTimestamp((int) $pubAt) : Carbon::parse(is_string($pubAt) ? $pubAt : null);
        }

        // Handle comment_status conversion
        if (isset($data['comment_status']) && is_bool($data['comment_status'])) {
            $data['comment_status'] = $data['comment_status'] ? 'open' : 'closed';
        }

        // Extract related data
        /** @var mixed $tagsRaw */
        $tagsRaw = $data['tags'] ?? [];
        $tags = is_array($tagsRaw) ? $tagsRaw : [];

        /** @var mixed $newTagsRaw */
        $newTagsRaw = $data['new_tags'] ?? [];
        $newTags = is_array($newTagsRaw) ? $newTagsRaw : [];

        $customFieldsRaw = $data['custom_fields'] ?? null;
        $customFields = is_array($customFieldsRaw) ? $customFieldsRaw : null;

        // Generate slug if not provided
        if (empty($data['slug'])) {
            /** @var mixed $titleRaw */
            $titleRaw = $data['title'] ?? '';
            $data['slug'] = $this->generateUniqueSlug(is_string($titleRaw) ? $titleRaw : (is_numeric($titleRaw) ? (string) $titleRaw : ''));
        }

        $content = Content::create($data);

        // Create new tags and get their IDs
        foreach ($newTags as $tagName) {
            $tagNameStr = is_scalar($tagName) ? (string) $tagName : '';
            if ($tagNameStr === '') {
                continue;
            }
            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($tagNameStr)],
                ['name' => $tagNameStr, 'slug' => Str::slug($tagNameStr)]
            );
            $tags[] = $tag->id;
        }

        // Sync tags
        if ($tags !== []) {
            $content->tags()->sync($tags);
        }

        // Save custom fields
        if ($customFields !== null) {
            /** @var array<string, mixed> $customFields */
            $this->saveCustomFields($content, $customFields);
        }

        // Track media usage
        if (! empty($content->featured_image)) {
            $this->trackMediaUsage($content, 'featured_image');
        }
        if (! empty($content->og_image)) {
            $this->trackMediaUsage($content, 'og_image');
        }

        // Create initial revision if requested
        if ($createRevision) {
            $this->createRevision($content, $userId, 'Initial version');
        }

        if ($content->status === 'published') {
            ContentPublished::dispatch($content);
        }

        // Trigger webhook
        Webhook::triggerForEvent('content.created', $content->toArray());

        // Clear caches
        $this->clearContentCaches($content->id);

        return $content;
    }

    /**
     * Update existing content
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Content $content, array $data, string $userId, bool $createRevision = false, ?string $revisionNote = null): Content
    {
        // Create revision before update if requested
        if ($createRevision) {
            $this->createRevision($content, $userId, $revisionNote ?? 'Revision before update');
        }

        // Handle comment_status conversion
        if (isset($data['comment_status']) && is_bool($data['comment_status'])) {
            $data['comment_status'] = $data['comment_status'] ? 'open' : 'closed';
        }

        // Extract related data
        /** @var mixed $tagsRaw */
        $tagsRaw = $data['tags'] ?? [];
        $tags = is_array($tagsRaw) ? $tagsRaw : [];

        /** @var mixed $newTagsRaw */
        $newTagsRaw = $data['new_tags'] ?? [];
        $newTags = is_array($newTagsRaw) ? $newTagsRaw : [];

        $customFieldsRaw = $data['custom_fields'] ?? null;
        $customFields = is_array($customFieldsRaw) ? $customFieldsRaw : null;

        unset($data['create_revision'], $data['revision_note'], $data['tags'], $data['new_tags'], $data['custom_fields']);

        // Handle published_at
        if (isset($data['published_at'])) {
            /** @var mixed $pubAt */
            $pubAt = $data['published_at'];
            $data['published_at'] = $pubAt ? (is_numeric($pubAt) ? Carbon::createFromTimestamp((int) $pubAt) : Carbon::parse(is_string($pubAt) ? $pubAt : null)) : null;
        }

        $content->update($data);

        // Create new tags and get their IDs
        foreach ($newTags as $tagName) {
            $tagNameStr = is_scalar($tagName) ? (string) $tagName : '';
            if ($tagNameStr === '') {
                continue;
            }
            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($tagNameStr)],
                ['name' => $tagNameStr, 'slug' => Str::slug($tagNameStr)]
            );
            $tags[] = $tag->id;
        }

        // Sync tags
        if ($tags !== []) {
            $content->tags()->sync($tags);
        }

        // Save custom fields
        if ($customFields !== null) {
            /** @var array<string, mixed> $customFields */
            $this->saveCustomFields($content, $customFields);
        }

        // Track media usage
        if (array_key_exists('featured_image', $data)) {
            $this->trackMediaUsage($content, 'featured_image');
        }
        if (array_key_exists('og_image', $data)) {
            $this->trackMediaUsage($content, 'og_image');
        }

        if ($content->status === 'published') {
            ContentPublished::dispatch($content);
        } else {
            ContentUnpublished::dispatch($content);
        }

        // Trigger webhook
        Webhook::triggerForEvent('content.updated', $content->toArray());

        // Clear caches
        $this->clearContentCaches($content->id);

        return $content;
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Content $content): bool
    {
        $content->is_featured = ! $content->is_featured;
        $content->save();

        $this->clearContentCaches($content->id);

        return $content->is_featured;
    }

    /**
     * Track media usage
     */
    protected function trackMediaUsage(Content $content, string $fieldName): void
    {
        $idValue = $content->getAttribute($fieldName);
        $mediaId = is_numeric($idValue) ? (string) $idValue : null;
        if ($mediaId) {
            MediaUsage::track($mediaId, $content, $fieldName);
        } else {
            MediaUsage::untrack(null, $content, $fieldName);
        }
    }

    /**
     * Delete content
     */
    public function delete(Content $content): void
    {
        $contentId = (string) $content->id;

        // Untrack media usage
        MediaUsage::untrack(null, $content);

        $content->delete();

        ContentDeleted::dispatch($contentId);

        Webhook::triggerForEvent('content.deleted', ['id' => $contentId]);
        $this->clearContentCaches($contentId);
    }

    /**
     * Duplicate content
     */
    public function duplicate(Content $content, string $userId): Content
    {
        $newContent = $content->replicate();
        $newContent->title = $content->title.' (Copy)';
        $newContent->slug = $this->generateUniqueSlug($newContent->title);
        $newContent->status = 'draft';
        $newContent->author_id = $userId;
        $newContent->views = 0;
        $newContent->published_at = null;
        $newContent->is_featured = false;
        $newContent->save();

        // Copy tags
        if ($content->tags()->count() > 0) {
            $newContent->tags()->sync($content->tags->pluck('id'));
        }

        return $newContent;
    }

    /**
     * Perform bulk action on contents
     *
     * @param  array<int, int|string>  $contentIds
     */
    public function bulkAction(string $action, array $contentIds, ?string $categoryId = null): int
    {
        $contents = Content::withTrashed()->whereIn('id', $contentIds)->get();

        foreach ($contents as $content) {
            /** @var Content $content */
            // Respect edit locks
            if ($content->locked_by && $content->locked_at && $content->locked_at->diffInMinutes(now()) < 60) {
                continue; // Skip locked records in bulk action
            }

            switch ($action) {
                case 'publish':
                case 'approve':
                    $content->update(['status' => 'published', 'published_at' => $content->published_at ?? now()]);
                    $this->dispatchContentPublishedIfExists($content);
                    break;
                case 'reject':
                case 'draft':
                    $content->update(['status' => 'draft']);
                    $this->dispatchContentUnpublishedIfExists($content);
                    break;
                case 'archive':
                    $content->update(['status' => 'archived']);
                    $this->dispatchContentUnpublishedIfExists($content);
                    break;
                case 'delete':
                    $contentId = (string) $content->id;
                    $content->delete();
                    ContentDeleted::dispatch($contentId);
                    break;
                case 'change_category':
                    if ($categoryId) {
                        $content->update(['category_id' => $categoryId]);
                        if ($content->status === 'published') {
                            $this->dispatchContentPublishedIfExists($content, ['category', 'author', 'tags']);
                        }
                    }
                    break;
                case 'restore':
                    if ($content->trashed()) {
                        $content->restore();
                        if ($content->status === 'published') {
                            $this->dispatchContentPublishedIfExists($content, ['category', 'author', 'tags']);
                        }
                    }
                    break;
                case 'force_delete':
                    $contentId = (string) $content->id;
                    $content->forceDelete();
                    ContentDeleted::dispatch($contentId);
                    break;
            }
        }

        // Clear all content cache since we don't know exactly which pages are affected
        $this->cacheService->clearContentCaches();

        return $contents->count();
    }

    /**
     * Save custom fields
     *
     * @param  array<string, mixed>  $customFields
     */
    public function saveCustomFields(Content $content, array $customFields): void
    {
        foreach ($customFields as $fieldSlug => $value) {
            $field = CustomField::where('key', $fieldSlug)->first();
            if ($field) {
                ContentCustomField::updateOrCreate(
                    [
                        'content_id' => $content->id,
                        'custom_field_id' => $field->id,
                    ],
                    ['value' => is_array($value) ? json_encode($value) : $value]
                );
            }
        }
    }

    /**
     * Create content revision
     */
    public function createRevision(Content $content, string $userId, string $note = ''): ContentRevision
    {
        // Prepare standard revision metadata
        $meta = $content->meta ?? [];
        $meta['revision_data'] = [
            'excerpt' => $content->excerpt,
            'slug' => $content->slug,
            'status' => $content->status,
        ];

        return ContentRevision::create([
            'content_id' => $content->id,
            'author_id' => $userId,
            'title' => $content->title,
            'body' => $content->body,

            'meta' => $meta,
            'reason' => $note,
        ]);
    }

    /**
     * Publish scheduled content
     */
    public function publishScheduled(Content $content): void
    {
        $content->update([
            'status' => 'published',
            'published_at' => $content->published_at ?? now(),
        ]);
        $this->dispatchContentPublishedIfExists($content, ['category', 'author', 'tags']);
    }

    /**
     * @param  array<int, string>  $relations
     */
    protected function dispatchContentPublishedIfExists(Content $content, array $relations = []): void
    {
        $fresh = $relations === [] ? $content->fresh() : $content->fresh($relations);
        if ($fresh instanceof Content) {
            ContentPublished::dispatch($fresh);
        }
    }

    protected function dispatchContentUnpublishedIfExists(Content $content): void
    {
        $fresh = $content->fresh();
        if ($fresh instanceof Content) {
            ContentUnpublished::dispatch($fresh);
        }
    }

    /**
     * Check if content is locked by another user
     */
    public function isLockedByOther(Content $content, string $userId): bool
    {
        return $content->locked_by && $content->locked_by !== $userId;
    }

    /**
     * Lock content for editing
     */
    public function lock(Content $content, string $userId): void
    {
        $content->update([
            'locked_by' => $userId,
            'locked_at' => now(),
        ]);
    }

    /**
     * Unlock content
     */
    public function unlock(Content $content): void
    {
        $content->update([
            'locked_by' => null,
            'locked_at' => null,
        ]);
    }

    /**
     * Clear content-related caches
     */
    protected function clearContentCaches(?string $contentId = null): void
    {
        $this->cacheService->clearContentCaches($contentId);
        $this->cacheService->clearSeoCaches();
    }

    /**
     * Generate unique slug
     */
    public function generateUniqueSlug(string $title, ?string $excludeId = null): string
    {
        $slug = Str::slug($title);
        $baseSlug = $slug;
        $counter = 1;

        while (Content::withTrashed()->where('slug', $slug)->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }

    /**
     * Restore trashed content
     */
    public function restore(string $id): bool
    {
        $content = Content::withTrashed()->findOrFail($id);
        if ($content->trashed()) {
            $content->restore();
            if ($content->status === 'published') {
                $this->dispatchContentPublishedIfExists($content, ['category', 'author', 'tags']);
            }
            $this->clearContentCaches($id);
            Webhook::triggerForEvent('content.restored', ['id' => $id]);

            return true;
        }

        return false;
    }

    /**
     * Force delete content
     */
    public function forceDelete(string $id): bool
    {
        $content = Content::withTrashed()->findOrFail($id);

        MediaUsage::untrack(null, $content);

        $content->forceDelete();
        ContentDeleted::dispatch($id);
        Webhook::triggerForEvent('content.force_deleted', ['id' => $id]);
        $this->clearContentCaches($id);

        return true;
    }

    /**
     * Empty trash
     */
    public function emptyTrash(): int
    {
        $count = Content::onlyTrashed()->count();
        Content::onlyTrashed()->forceDelete();

        $this->cacheService->clearContentCaches();

        return $count;
    }
}
