<?php

namespace Modules\Content\Media\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Content\Media\Contracts\MediaServiceInterface;
use Modules\Content\Media\Models\File;
use Modules\Core\System\Exceptions\StorageQuotaExceededException;
use Modules\Core\System\Models\User;
use Modules\Core\System\Support\SqlLikeEscape;

class MediaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected MediaServiceInterface $mediaService) {}

    /**
     * Display a listing of the media files.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', File::class);
        $query = File::with(['folder']);

        if ($request->input('trashed') === 'only') {
            $query->onlyTrashed();
        }

        if ($request->has('folder_id')) {
            $folderId = $request->input('folder_id');
            if ($folderId === 'null' || $folderId === null) {
                $query->whereNull('folder_id');
            } elseif (is_string($folderId)) {
                $query->where('folder_id', $folderId);
            }
        }

        if ($request->has('mime_type')) {
            $mimeType = $request->input('mime_type');
            if (is_string($mimeType) && $mimeType !== '') {
                $query->where('mime_type', 'like', "{$mimeType}/%");
            }
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_scalar($searchRaw) ? trim((string) $searchRaw) : '';
            if ($search !== '') {
                SqlLikeEscape::whereContainsAny($query, ['name', 'file_name', 'alt'], mb_strtolower($search, 'UTF-8'));
            }
        }

        $perPageInput = $request->input('per_page', 24);
        $perPage = 24;
        if (is_numeric($perPageInput)) {
            $perPage = max(1, min(200, (int) $perPageInput));
        }

        $files = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $files,
            'message' => 'Media retrieved successfully',
        ]);
    }

    /**
     * Upload a new media file.
     */
    public function upload(Request $request): JsonResponse
    {
        $this->authorize('create', File::class);
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB to match test expectations
            'folder_id' => 'nullable|exists:srv_media_folders,id',
            'is_shared' => 'sometimes|boolean',
            'caption' => 'nullable|string',
            'alt' => 'nullable|string',
            'module' => 'nullable|string',
        ]);

        $file = $request->file('file');
        if (! $file instanceof UploadedFile) {
            return response()->json([
                'success' => false,
                'message' => 'A valid file upload is required.',
            ], 422);
        }

        $folderId = null;
        if (array_key_exists('folder_id', $validated) && $validated['folder_id'] !== null) {
            $fid = $validated['folder_id'];
            if (is_string($fid) && $fid !== '') {
                $folderId = $fid;
            }
        }

        $module = 'system';
        if (isset($validated['module']) && is_string($validated['module']) && $validated['module'] !== '') {
            $module = $validated['module'];
        }

        try {
            $media = $this->mediaService->upload(
                $file,
                $folderId,
                true,
                is_scalar(Auth::id()) ? (string) Auth::id() : null,
                $request->boolean('is_shared', false),
                [
                    'caption' => isset($validated['caption']) && is_string($validated['caption']) ? $validated['caption'] : null,
                    'alt' => isset($validated['alt']) && is_string($validated['alt']) ? $validated['alt'] : null,
                ],
                null,
                $module,
            );
        } catch (StorageQuotaExceededException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 413);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'media' => $media->load('folder'),
                'url' => $media->url,
            ],
            'message' => 'Media uploaded successfully',
        ], 201);
    }

    /**
     * Display the specified media file.
     */
    public function show(File $file): JsonResponse
    {
        $this->authorize('view', $file);

        return response()->json([
            'success' => true,
            'data' => $file->load('folder'),
            'message' => 'Media retrieved successfully',
        ]);
    }

    /**
     * Update the specified media file.
     */
    public function update(Request $request, File $file): JsonResponse
    {
        $this->authorize('update', $file);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'alt' => 'nullable|string',
            'description' => 'nullable|string',
            'caption' => 'nullable|string',
            'is_shared' => 'sometimes|boolean',
            'tags' => 'sometimes|array',
        ]);

        $payload = array_intersect_key(
            $validated,
            array_flip(['name', 'alt', 'description', 'caption', 'is_shared'])
        );
        $file->update($payload);

        if ($request->has('tags')) {
            $tags = $request->input('tags');
            $this->mediaService->syncTags($file, is_array($tags) ? $tags : []);
        }

        return response()->json([
            'success' => true,
            'data' => $file,
            'message' => 'Media updated successfully',
        ]);
    }

    /**
     * Remove the specified media file.
     */
    public function destroy(Request $request, File $file): JsonResponse
    {
        $this->authorize('delete', $file);
        $permanent = $request->boolean('permanent', false);
        $this->mediaService->delete($file, $permanent);

        return response()->json([
            'success' => true,
            'message' => $permanent ? 'Media permanently deleted' : 'Media moved to trash',
        ]);
    }

    /**
     * Bulk actions on media files.
     */
    public function bulk(Request $request): JsonResponse
    {
        // Bulk actions usually require manage media or specific ones
        $this->authorize('viewAny', File::class);
        $validated = $request->validate([
            'action' => 'required|string|in:delete,delete_permanent,restore,move',
            'media_ids' => 'required_without:ids|array',
            'ids' => 'required_without:media_ids|array',
            'folder_id' => 'nullable|required_if:action,move|exists:srv_media_folders,id',
        ]);

        $action = $validated['action'] ?? null;
        if (! is_string($action) || $action === '') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid bulk action.',
            ], 422);
        }

        $rawIds = $validated['media_ids'] ?? $validated['ids'] ?? [];
        $mediaIds = [];
        if (is_array($rawIds)) {
            foreach ($rawIds as $id) {
                if (is_string($id) && $id !== '') {
                    $mediaIds[] = $id;
                }
            }
        }

        $folderId = null;
        if (array_key_exists('folder_id', $validated) && $validated['folder_id'] !== null) {
            $fid = $validated['folder_id'];
            if (is_string($fid) && $fid !== '') {
                $folderId = $fid;
            }
        }

        $result = $this->mediaService->bulkAction(
            $action,
            $mediaIds,
            $folderId,
            null,
            []
        );

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Bulk action completed',
        ]);
    }

    /**
     * Restore a soft-deleted media file.
     */
    public function restore(string $id): JsonResponse
    {
        $file = $this->mediaService->restore($id);
        if (! $file instanceof File) {
            return response()->json(['success' => false, 'message' => 'Media not found or not in trash'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $file,
            'message' => 'Media restored successfully',
        ]);
    }

    /**
     * Generate thumbnail for media.
     */
    public function thumbnail(File $file): JsonResponse
    {
        $this->authorize('update', $file);
        $path = $this->mediaService->generateThumbnail($file);

        if (! $path) {
            return response()->json(['success' => false, 'message' => 'Failed to generate thumbnail'], 400);
        }

        return response()->json([
            'success' => true,
            'data' => ['path' => $path, 'url' => Storage::disk($file->disk)->url($path)],
            'message' => 'Thumbnail generated successfully',
        ]);
    }

    /**
     * Resize image media.
     */
    public function resize(Request $request, File $file): JsonResponse
    {
        $this->authorize('update', $file);
        $validated = $request->validate([
            'width' => 'required_without:height|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'quality' => 'nullable|integer|min:1|max:100',
        ]);

        $width = $this->coercePositiveInt($validated['width'] ?? null);
        if ($width === null) {
            return response()->json(['success' => false, 'message' => 'Invalid width'], 422);
        }

        $heightInt = null;
        if (array_key_exists('height', $validated) && $validated['height'] !== null) {
            $heightInt = $this->coercePositiveInt($validated['height']);
            if ($heightInt === null) {
                return response()->json(['success' => false, 'message' => 'Invalid height'], 422);
            }
        }

        $quality = $this->coercePositiveInt($validated['quality'] ?? 85) ?? 85;
        $quality = min(100, max(1, $quality));

        $success = $this->mediaService->resize(
            $file,
            $width,
            $heightInt,
            $quality
        );

        if (! $success) {
            return response()->json(['success' => false, 'message' => 'Failed to resize image'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image resized successfully',
        ]);
    }

    /**
     * Get media usage information.
     */
    public function usage(File $file): JsonResponse
    {
        $this->authorize('view', $file);
        $usage = $this->mediaService->getUsageInfo($file);

        return response()->json([
            'success' => true,
            'data' => $usage,
            'message' => 'Media usage retrieved successfully',
        ]);
    }

    /**
     * Empty trash.
     */
    public function emptyTrash(): JsonResponse
    {
        // This is a bulk action usually
        $files = File::onlyTrashed()->get();
        foreach ($files as $file) {
            $this->mediaService->delete($file, true);
        }

        return response()->json([
            'success' => true,
            'message' => 'Trash emptied successfully',
        ]);
    }

    /**
     * Get media statistics.
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('viewAny', File::class);
        $stats = $this->mediaService->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistics retrieved successfully',
        ]);
    }

    /**
     * Get media filters.
     */
    public function filters(): JsonResponse
    {
        $this->authorize('viewAny', File::class);

        $authorIds = File::whereNotNull('author_id')
            ->distinct()
            ->pluck('author_id');

        $authors = User::whereIn('id', $authorIds)
            ->select('id', 'name')
            ->get();

        return response()->json([
            'success' => true,
            'authors' => $authors,
            'message' => 'Filters retrieved successfully',
        ]);
    }

    private function coercePositiveInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }
        if (is_string($value) && is_numeric($value)) {
            $i = (int) $value;

            return $i > 0 ? $i : null;
        }

        return null;
    }
}
