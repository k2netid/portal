<?php

namespace Modules\Publishing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\User;
use Modules\Core\System\Support\SqlLikeEscape;
use Modules\Publishing\Models\ContentTemplate;

class ContentTemplateController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        $query = ContentTemplate::with('category');

        // Scope logic
        if ($user && ! $user->can('manage templates')) {
            $query->where(function ($q) use ($user): void {
                $q->whereNull('author_id')->orWhere('author_id', $user->id);
            });
        } elseif (! $user) {
            $query->whereNull('author_id');
        }

        if ($request->has('type') && is_string($request->type) && $request->type !== 'all') {
            $types = explode(',', $request->type);
            if (count($types) > 1) {
                $query->whereIn('type', $types);
            } else {
                $query->where('type', $request->type);
            }
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_scalar($searchRaw) ? trim((string) $searchRaw) : '';
            if ($search !== '') {
                SqlLikeEscape::whereContainsAny($query, ['name', 'description'], mb_strtolower($search, 'UTF-8'));
            }
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Soft deletes filter
        if ($request->has('trashed')) {
            $trashedRaw = $request->trashed;
            $trashed = is_string($trashedRaw) ? $trashedRaw : '';
            if ($trashed === 'only') {
                $query->onlyTrashed();
            } elseif ($trashed === 'with') {
                $query->withTrashed();
            }
        }

        $perPageRaw = $request->input('per_page', 10);
        $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 10;
        $templates = $query->latest()->paginate($perPage);

        return $this->success($templates, 'Content templates retrieved successfully');
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        $request->validate([
            'action' => 'required|in:delete,restore,force_delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:content_templates,id',
        ]);

        $actionRaw = $request->input('action');
        $action = is_string($actionRaw) ? $actionRaw : '';
        $idsRaw = $request->input('ids');
        $ids = is_array($idsRaw) ? $idsRaw : [];

        $query = ContentTemplate::whereIn('id', $ids);

        // Scope
        if (! $user->can('manage templates')) {
            $query->where('author_id', $user->id);
        }

        $count = 0;

        if ($action === 'delete') {
            $countRaw = $query->delete();
            $count = is_numeric($countRaw) ? (int) $countRaw : 0;
        } elseif ($action === 'restore') {
            $count = (int) ContentTemplate::withTrashed()->whereIn('id', $ids)->restore();
        } elseif ($action === 'force_delete') {
            $countRaw = ContentTemplate::withTrashed()->whereIn('id', $ids)->forceDelete();
            $count = is_numeric($countRaw) ? (int) $countRaw : 0;
        }

        $countStr = (string) $count;

        return $this->success(null, "Bulk action {$action} completed for {$countStr} templates");
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:sys_content_templates,slug',
            'description' => 'nullable|string',
            'type' => 'required|string|in:post,page,custom',
            'title_template' => 'nullable|string',
            'body_template' => 'required|string',
            'excerpt_template' => 'nullable|string',
            'default_fields' => 'nullable|array',
            'meta' => 'nullable|array',
            'category_id' => 'nullable|exists:lib_categories,id',
            'is_active' => 'boolean',
            'author_id' => ['nullable', Rule::exists(User::class, 'id')],
        ]);

        // Author Assignment
        if ($user->can('manage templates')) {
            // Admin can assign
        } else {
            $validated['author_id'] = $user->id;
        }

        $template = ContentTemplate::create($validated);

        return $this->success($template->load('category'), 'Content template created successfully', 201);
    }

    public function show(ContentTemplate $contentTemplate): JsonResponse
    {
        $user = request()->user();
        /** @var User|null $user */

        // Permission/Ownership check?
        // Usually viewing templates is fine if they are visible in index?
        // But let's enforce consistency.
        if ($user && ! $user->can('manage templates') && ($contentTemplate->author_id && $contentTemplate->author_id !== $user->id)) {
            return $this->forbidden('You do not have permission to view this template');
        }

        return $this->success($contentTemplate->load('category'), 'Content template retrieved successfully');
    }

    public function update(Request $request, ContentTemplate $contentTemplate): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        // Ownership check
        if (! $user->can('manage templates')) {
            if ($contentTemplate->author_id && $contentTemplate->author_id !== $user->id) {
                return $this->forbidden('You do not have permission to update this template');
            }
            if (is_null($contentTemplate->author_id)) {
                return $this->forbidden('You cannot update global templates');
            }
            unset($request['author_id']);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:sys_content_templates,slug,'.$contentTemplate->id,
            'description' => 'nullable|string',
            'type' => 'sometimes|required|string|in:post,page,custom',
            'title_template' => 'nullable|string',
            'body_template' => 'sometimes|required|string',
            'excerpt_template' => 'nullable|string',
            'default_fields' => 'nullable|array',
            'meta' => 'nullable|array',
            'category_id' => 'nullable|exists:lib_categories,id',
            'is_active' => 'boolean',
            'author_id' => ['nullable', Rule::exists(User::class, 'id')],
        ]);

        $contentTemplate->update($validated);

        return $this->success($contentTemplate->load('category'), 'Content template updated successfully');
    }

    public function destroy(ContentTemplate $contentTemplate): JsonResponse
    {
        $user = request()->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        // Ownership check
        if (! $user->can('manage templates')) {
            if ($contentTemplate->author_id && $contentTemplate->author_id !== $user->id) {
                return $this->forbidden('You do not have permission to delete this template');
            }
            if (is_null($contentTemplate->author_id)) {
                return $this->forbidden('You cannot delete global templates');
            }
        }

        $contentTemplate->delete();

        return $this->success(null, 'Content template deleted successfully');
    }

    public function restore(int $id): JsonResponse
    {
        $template = ContentTemplate::withTrashed()->findOrFail($id);
        /** @var ContentTemplate $template */
        $template->restore();

        return $this->success(null, 'Content template restored successfully');
    }

    public function forceDelete(int $id): JsonResponse
    {
        $template = ContentTemplate::withTrashed()->findOrFail($id);
        /** @var ContentTemplate $template */
        $template->forceDelete();

        return $this->success(null, 'Content template permanently deleted');
    }

    public function createContent(Request $request, ContentTemplate $contentTemplate): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        // Scope?
        if (! $user->can('manage templates') && ($contentTemplate->author_id && $contentTemplate->author_id !== $user->id)) {
            // But wait, can I use a template if it is Global? YES.
            // So only forbidden if it's someone ELSE'S private template.
            // Global (null) is OK.
            return $this->forbidden('You do not have permission to use this template');
        }

        $dataRaw = $request->input('data', []);
        $data = is_array($dataRaw) ? $dataRaw : [];
        $data['author_id'] = $user->id;

        $content = $contentTemplate->createContent($data);

        return $this->success($content->load(['author', 'category', 'tags']), 'Content created from template successfully', 201);
    }
}
