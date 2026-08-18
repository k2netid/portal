<?php

namespace Modules\Content\Library\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Content\Library\Models\Tag;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\User;
use Modules\Core\System\Support\SqlLikeEscape;

class TagController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Tag::query()->withCount('contents')->orderBy('name');

        /** @var User|null $user */
        $user = $request->user();

        // Scope by organization

        // Admin/Manager can see all, others see own + global
        if ($user && ! $user->can('manage tags')) {
            $query->where(function ($q) use ($user): void {
                $q->whereNull('author_id')->orWhere('author_id', $user->id);
            });
        } elseif (! $user) {
            $query->whereNull('author_id');
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? trim($searchRaw) : '';
            if ($search !== '') {
                SqlLikeEscape::whereContainsAny($query, ['name', 'slug'], mb_strtolower($search, 'UTF-8'));
            }
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('usage')) {
            $usage = $request->input('usage');
            if ($usage === 'used') {
                $query->whereHas('contents');
            } elseif ($usage === 'unused') {
                $query->whereDoesntHave('contents');
            }
        }

        if ($request->has('per_page')) {
            $perPageRaw = $request->get('per_page', 20);
            $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 20;
            $tags = $query->paginate($perPage);

            return $this->success($tags, 'Tags retrieved successfully');
        }

        $tags = $query->get();

        return $this->success($tags, 'Tags retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string',
            'description' => 'nullable|string',
            'type' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        /** @var User|null $user */
        $user = $request->user();

        $type = $validated['type'] ?? 'content';
        $slug = $validated['slug'];

        // Check uniqueness in Library
        $exists = Tag::where('type', $type)
            ->where('slug', $slug)
            ->exists();

        if ($exists) {
            return $this->validationError(['slug' => ['Tag with this slug and type already exists']], 'Validation error');
        }

        if ($user) {
            $validated['author_id'] = $user->id;
        }

        $tag = Tag::create($validated);

        return $this->success($tag, 'Tag created successfully', 201);
    }

    public function show(Tag $tag): JsonResponse
    {
        return $this->success($tag->loadCount('contents'), 'Tag retrieved successfully');
    }

    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|string',
            'metadata' => 'nullable|array',
        ]);

        $tag->update($validated);

        return $this->success($tag, 'Tag updated successfully');
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:lib_tags,id',
        ]);

        $ids = $validated['ids'];
        $query = Tag::whereIn('id', $ids);

        /** @var User|null $user */
        $user = $request->user();

        if ($user && ! $user->can('manage tags')) {
            $query->where('author_id', $user->id);
        }

        $count = $query->delete();

        return $this->success(['deleted_count' => $count], 'Tags deleted successfully');
    }

    public function statistics(Request $request): JsonResponse
    {
        $query = Tag::query();
        /** @var User|null $user */
        $user = $request->user();

        if ($user && ! $user->can('manage tags')) {
            $query->where('author_id', $user->id);
        }

        $stats = [
            'total_tags' => (clone $query)->count(),
            'used_tags' => (clone $query)->whereHas('contents')->count(),
            'total_usage' => (int) DB::table('lib_taggables')
                ->whereIn('tag_id', (clone $query)->select('lib_tags.id'))
                ->count(),
            'types' => (clone $query)->select('type', DB::raw('count(*) as count'))->groupBy('type')->get(),
        ];

        return $this->success($stats, 'Tag statistics retrieved successfully');
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return $this->success(null, 'Tag deleted successfully');
    }
}
