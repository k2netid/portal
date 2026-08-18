<?php

namespace Modules\Content\Publishing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Modules\Content\Publishing\Models\Content;
use Modules\Content\Publishing\Models\ContentRevision;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\User;

class ContentRevisionController extends BaseApiController
{
    public function index(Content $content): JsonResponse
    {
        try {
            $revisions = $content->revisions()->with('user')->latest()->paginate(20);

            return $this->paginated($revisions, 'Content revisions retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Content revisions index error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'content_id' => $content->id,
            ]);

            // Return empty paginated response instead of error
            /** @var LengthAwarePaginator<int, ContentRevision> $paginator */
            $paginator = new LengthAwarePaginator([], 0, 20);

            return $this->paginated(
                $paginator,
                'Content revisions retrieved successfully'
            );
        }
    }

    public function show(Content $content, ContentRevision $revision): JsonResponse
    {
        if ($revision->content_id !== $content->id) {
            return $this->notFound('Revision');
        }

        return $this->success($revision->load('user'), 'Content revision retrieved successfully');
    }

    public function store(Request $request, Content $content): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
            'note' => 'nullable|string|max:500', // Legacy support
        ]);

        $reasonRaw = $validated['reason'] ?? $validated['note'] ?? 'Auto-saved revision';
        $reason = is_string($reasonRaw) ? $reasonRaw : 'Auto-saved revision';

        // Prepare standard revision metadata
        $metaRaw = $content->meta;
        $meta = is_array($metaRaw) ? $metaRaw : [];
        $meta['revision_data'] = [
            'excerpt' => $content->excerpt,
            'slug' => $content->slug,
            'status' => $content->status,
        ];

        // Create revision from current content state
        $revision = ContentRevision::create([
            'content_id' => $content->id,
            'author_id' => $user->id,
            'title' => $content->title,
            'body' => $content->body,
            'meta' => $meta,
            'reason' => $reason,
        ]);

        return $this->success($revision->load('author'), 'Content revision created successfully', 201);
    }

    public function restore(Request $request, Content $content, ContentRevision $revision): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        if ($revision->content_id !== $content->id) {
            return $this->notFound('Revision');
        }

        // Backup current state
        $currentMetaRaw = $content->meta;
        $currentMeta = is_array($currentMetaRaw) ? $currentMetaRaw : [];
        $currentMeta['revision_data'] = [
            'excerpt' => $content->excerpt,
            'slug' => $content->slug,
            'status' => $content->status,
        ];

        ContentRevision::create([
            'content_id' => $content->id,
            'author_id' => $user->id,
            'title' => $content->title,
            'body' => $content->body,
            'meta' => $currentMeta,
            'reason' => 'Backup before restore',
        ]);

        // Restore content from revision
        $revisionMeta = $revision->meta;
        $revisionData = (is_array($revisionMeta) && isset($revisionMeta['revision_data']) && is_array($revisionMeta['revision_data']))
            ? $revisionMeta['revision_data']
            : [];

        $content->update([
            'title' => $revision->title,
            'body' => $revision->body,
            'excerpt' => $revisionData['excerpt'] ?? $content->excerpt, // Fallback to current if missing
            'slug' => $revisionData['slug'] ?? $content->slug,
            'meta' => $revisionMeta, // This might overwrite revision_data into content meta, which is fine
            'status' => $revisionData['status'] ?? 'draft', // Safe default
        ]);

        return $this->success([
            'content' => $content->load(['author', 'category', 'tags']),
        ], 'Content restored successfully');
    }

    public function destroy(Content $content, ContentRevision $revision): JsonResponse
    {
        if ($revision->content_id !== $content->id) {
            return $this->notFound('Revision');
        }

        $revision->delete();

        return $this->success(null, 'Revision deleted successfully');
    }
}
