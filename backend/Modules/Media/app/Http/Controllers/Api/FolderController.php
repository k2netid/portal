<?php

namespace Modules\Media\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Media\Models\Folder;

class FolderController extends Controller
{
    /**
     * Display a listing of the folders.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Folder::query();

        if ($request->input('trashed') === 'only') {
            $query->onlyTrashed();
        } elseif ($request->input('trashed') === 'with') {
            $query->withTrashed();
        }

        if ($request->has('parent_id')) {
            $parentId = $request->input('parent_id');
            if ($parentId === 'null' || $parentId === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('sort_order')->orderBy('name')->get(),
            'message' => 'Folders retrieved successfully',
        ]);
    }

    /**
     * Create a new folder.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:srv_media_folders,id',
            'module' => 'nullable|string',
            'is_shared' => 'sometimes|boolean',
        ]);

        $folder = Folder::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'parent_id' => $validated['parent_id'] ?? null,
            'module' => $validated['module'] ?? 'system',
            'author_id' => Auth::id(),
            'is_shared' => $request->boolean('is_shared', false),
        ]);

        return response()->json([
            'success' => true,
            'data' => $folder,
            'message' => 'Folder created successfully',
        ], 201);
    }

    /**
     * Update the specified folder.
     */
    public function update(Request $request, Folder $folder): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'parent_id' => 'nullable|exists:srv_media_folders,id',
            'sort_order' => 'sometimes|integer',
            'is_shared' => 'sometimes|boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $folder->update($validated);

        return response()->json([
            'success' => true,
            'data' => $folder,
            'message' => 'Folder updated successfully',
        ]);
    }

    /**
     * Remove the specified folder.
     */
    public function destroy(Folder $folder): JsonResponse
    {
        // Check if folder has files or children?
        // For now, let's just delete (cascade is handled by DB)
        $folder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Folder moved to trash',
        ]);
    }
}
