<?php

namespace Modules\Content\Library\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Content\Library\Models\Category;
use Modules\Content\Library\Services\LibraryCacheService;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\User;
use Modules\Core\System\Support\SqlLikeEscape;

/**
 * @OA\Tag(name="Categories")
 */
class CategoryController extends BaseApiController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show', 'tree']);
        $this->middleware('permission:view content')->only(['tree']);
        $this->middleware('permission:manage categories')->only(['store', 'update', 'destroy', 'reorder', 'bulkDelete']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     summary="List all categories",
     *     tags={"Categories"},
     *
     *     @OA\Parameter(
     *         name="tree",
     *         in="query",
     *         description="Return as tree structure",
     *         required=false,
     *
     *         @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Results per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $request->user();
        $query = Category::query();

        // Public categories are always visible if active
        $query->where('is_active', true);

        // Tree view
        if ($request->has('tree') && $request->boolean('tree')) {
            // Note: Tree view with partial permissions might break hierarchy if parent is missing.
            // For now, we assume global categories are parents.
            $categories = $query->whereNull('parent_id')
                ->where('is_active', true)
                ->withCount(['contents' => function ($q): void {
                    $q->where('status', 'published')
                        ->where('type', 'post')
                        ->where(function ($sq): void {
                            $sq->whereNull('published_at')
                                ->orWhere('published_at', '<=', now()->toDateTimeString());
                        });
                }])
                ->with(['children' => function ($q): void {
                    $q->where('is_active', true)
                        ->withCount(['contents' => function ($sq): void {
                            $sq->where('status', 'published')
                                ->where('type', 'post')
                                ->where(function ($ssq): void {
                                    $ssq->whereNull('published_at')
                                        ->orWhere('published_at', '<=', now()->toDateTimeString());
                                });
                        }])
                        ->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->get();

            return $this->success($categories, 'Categories tree retrieved successfully');
        }

        // Flat list
        $query->where('is_active', true)
            ->with('parent')
            ->withCount(['contents' => function ($q): void {
                $q->where('status', 'published')
                    ->where('type', 'post')
                    ->where(function ($sq): void {
                        $sq->whereNull('published_at')
                            ->orWhere('published_at', '<=', now()->toDateTimeString());
                    });
            }]);

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_scalar($searchRaw) ? trim((string) $searchRaw) : '';
            if ($search !== '') {
                SqlLikeEscape::whereContainsAny($query, ['name', 'description'], mb_strtolower($search, 'UTF-8'));
            }
        }

        $query->orderBy('sort_order');

        if ($request->has('per_page')) {
            $perPageRaw = $request->input('per_page', 20);
            $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 20;
            $categories = $query->paginate($perPage);

            return $this->success($categories, 'Categories retrieved successfully');
        }

        $categoriesFlat = $query->get();

        return $this->success($categoriesFlat, 'Categories retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/categories",
     *     summary="Create a new category",
     *     tags={"Categories"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "slug"},
     *
     *             @OA\Property(property="name", type="string", example="Technology"),
     *             @OA\Property(property="slug", type="string", example="technology"),
     *             @OA\Property(property="description", type="string", example="Tech related posts"),
     *             @OA\Property(property="parent_id", type="integer", example=1),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:lib_categories,slug',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:lib_categories,id',
            'sort_order' => 'integer|min:0',
            'author_id' => ['nullable', Rule::exists(User::class, 'id')],
        ]);

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            $parentId = (int) $validated['parent_id'];
            /** @var Category|null $parent */
            $parent = Category::find($parentId);
            $requestId = $request->input('id');
            if ($parent && $requestId && $parent->parent_id == $requestId) {
                return $this->validationError(['parent_id' => ['Cannot set parent to a child category']], 'Cannot set parent to a child category');
            }
        }

        // Assign author logic
        if ($user->can('manage categories')) {
            // Admin can assign author or leave null (Global)
            // validated['author_id'] is already in array if sent
        } else {
            // Regular user forces ownership
            $validated['author_id'] = $user->id;
        }

        $category = Category::create($validated);

        // Clear caches (kept for global lists, though index is not cached per auth now)
        $cacheService = app(LibraryCacheService::class);
        $cacheService->clearCategoryCaches();
        $cacheService->clearSeoCaches();

        return $this->success($category->load('parent'), 'Category created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/{category}",
     *     summary="Get category details",
     *     tags={"Categories"},
     *
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="Category ID or Slug",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function show(Category $category): JsonResponse
    {
        $user = request()->user();
        /** @var User|null $user */

        // Scope check for show?
        if ($user && ! $user->can('manage categories') && ($category->author_id && $category->author_id !== $user->id)) {
            return $this->forbidden('You do not have permission to view this category');
        }

        return $this->success($category->load(['parent', 'children', 'contents']), 'Category retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/categories/{category}",
     *     summary="Update a category",
     *     tags={"Categories"},
     *
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if ($category->locked_by && $category->locked_by !== $user->id && ($category->locked_at && $category->locked_at->diffInMinutes(now()) < 60)) {
            return $this->error('Category is currently being edited by another user', 423);
        }

        // Ownership check
        if (! $user->can('manage categories')) {
            if ($category->author_id && $category->author_id !== $user->id) {
                return $this->forbidden('You do not have permission to update this category');
            }
            // Cannot change author_id
            unset($request['author_id']);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:lib_categories,slug,'.$category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:lib_categories,id',
            'sort_order' => 'integer|min:0',
            'author_id' => ['nullable', Rule::exists(User::class, 'id')],
        ]);

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            return $this->validationError(['parent_id' => ['Category cannot be its own parent']], 'Category cannot be its own parent');
        }

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            $parentId = (int) $validated['parent_id'];
            /** @var Category|null $parent */
            $parent = Category::find($parentId);
            if ($parent) {
                // Check if parent is a descendant of this category
                $checkParent = $parent;
                while ($checkParent && $checkParent->parent_id) {
                    if ($checkParent->parent_id == $category->id) {
                        return $this->validationError(['parent_id' => ['Cannot set parent to a child category']], 'Cannot set parent to a child category');
                    }
                    /** @var Category|null $nextParent */
                    $nextParent = Category::find($checkParent->parent_id);
                    $checkParent = $nextParent;
                }
            }
        }

        $category->update($validated);

        // Clear caches
        $cacheService = app(LibraryCacheService::class);
        $cacheService->clearCategoryCaches();

        return $this->success($category->load(['parent', 'children']), 'Category updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/categories/{category}",
     *     summary="Delete a category",
     *     tags={"Categories"},
     *
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function destroy(Category $category): JsonResponse
    {
        $user = request()->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        if ($category->locked_by && $category->locked_by !== $user->id && ($category->locked_at && $category->locked_at->diffInMinutes(now()) < 60)) {
            return $this->error('Cannot delete: Category is currently being edited by another user', 423);
        }

        // Ownership check
        if (! $user->can('manage categories')) {
            if ($category->author_id && $category->author_id !== $user->id) {
                return $this->forbidden('You do not have permission to delete this category');
            }

            // Global categories (null author) cannot be deleted by non-managers
            return $this->forbidden('You do not have permission to delete global categories');
        }

        // Check if has children
        if ($category->children()->count() > 0) {
            return $this->validationError([
                'category' => ['Cannot delete category with child categories'],
                'children_count' => [(string) $category->children()->count()],
            ], 'Cannot delete category with child categories');
        }

        // Check if has contents
        if ($category->contents()->count() > 0) {
            return $this->validationError([
                'category' => ['Cannot delete category with associated contents'],
                'contents_count' => [(string) $category->contents()->count()],
            ], 'Cannot delete category with associated contents');
        }

        $categoryId = $category->id;
        $category->delete();

        // Clear caches
        $cacheService = app(LibraryCacheService::class);
        $cacheService->clearCategoryCaches($categoryId);
        $cacheService->clearSeoCaches();

        return $this->success(null, 'Category deleted successfully');
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/categories/{category}/move",
     *     summary="Move category (hierarchical)",
     *     tags={"Categories"},
     *
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="parent_id", type="integer"),
     *             @OA\Property(property="sort_order", type="integer")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Category moved successfully"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function move(Request $request, Category $category): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        if (! $user->can('manage categories')) {
            return $this->forbidden('You do not have permission to move categories');
        }

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:lib_categories,id',
            'sort_order' => 'integer|min:0',
        ]);

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            return $this->validationError(['parent_id' => ['Category cannot be its own parent']], 'Category cannot be its own parent');
        }

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            $parentId = (int) $validated['parent_id'];
            /** @var Category|null $parent */
            $parent = Category::find($parentId);
            if ($parent) {
                $checkParent = $parent;
                while ($checkParent && $checkParent->parent_id) {
                    if ($checkParent->parent_id == $category->id) {
                        return $this->validationError(['parent_id' => ['Cannot set parent to a child category']], 'Cannot set parent to a child category');
                    }
                    /** @var Category|null $nextParent */
                    $nextParent = Category::find($checkParent->parent_id);
                    $checkParent = $nextParent;
                }
            }
        }

        $category->update($validated);

        // Clear caches
        $cacheService = app(LibraryCacheService::class);
        $cacheService->clearCategoryCaches();

        return $this->success($category->load(['parent', 'children']), 'Category moved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/categories/bulk-delete",
     *     summary="Bulk delete categories",
     *     tags={"Categories"},
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Bulk deletion processed"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:lib_categories,id',
        ]);

        $idsRaw = $validated['ids'];
        $ids = is_array($idsRaw) ? $idsRaw : [];
        $categories = Category::whereIn('id', $ids)->get();
        $count = 0;
        $errors = [];

        foreach ($categories as $category) {
            /** @var Category $category */
            // Respect edit locks
            if ($category->locked_by && $category->locked_at && $category->locked_at->diffInMinutes(now()) < 60) {
                $errors[] = "Category '{$category->name}' is currently locked by another user.";

                continue;
            }

            // Check ownership/permissions
            if (! $user->can('manage categories')) {
                if ($category->author_id && $category->author_id !== $user->id) {
                    $errors[] = "Permission denied for category ID {$category->id}: Not owner.";

                    continue;
                }
                if (is_null($category->author_id)) {
                    $errors[] = "Permission denied for category ID {$category->id}: Cannot delete global category.";

                    continue;
                }
            }

            // Check children
            if ($category->children()->count() > 0) {
                $errors[] = "Category '{$category->name}' has sub-categories and cannot be deleted.";

                continue;
            }

            // Check contents
            if ($category->contents()->count() > 0) {
                $errors[] = "Category '{$category->name}' has associated contents and cannot be deleted.";

                continue;
            }

            $category->delete();
            $count++;
        }

        // Clear caches
        $cacheService = app(LibraryCacheService::class);
        $cacheService->clearCategoryCaches();
        $cacheService->clearSeoCaches();

        if (count($errors) > 0) {
            // If partial success, we still return 200 but with messages
            return $this->success([
                'deleted_count' => $count,
                'errors' => $errors,
            ], count($errors) === count($ids) ? 'Failed to delete categories' : 'Categories processed with some errors');
        }

        return $this->success(['deleted_count' => $count], 'Categories deleted successfully');
    }

    /**
     * Restore a deleted category.
     */
    public function restore(int|string $id): JsonResponse
    {
        /** @var Category $category */
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();

        app(LibraryCacheService::class)->clearCategoryCaches();

        return $this->success($category, 'Category restored successfully');
    }

    /**
     * Permanently delete a category.
     */
    public function forceDelete(int|string $id): JsonResponse
    {
        /** @var Category $category */
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->forceDelete();

        app(LibraryCacheService::class)->clearCategoryCaches();

        return $this->success(null, 'Category permanently deleted');
    }
}
