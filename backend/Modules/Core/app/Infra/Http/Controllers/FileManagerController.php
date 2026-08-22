<?php

namespace Modules\Core\Infra\Http\Controllers;

use enshrined\svgSanitize\Sanitizer;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Core\Infra\Helpers\UploadSettingsHelper;
use Modules\Core\Infra\Models\DeletedFile;
use Modules\Core\Infra\Services\MediaLibraryBridge;
use Modules\Core\System\Contracts\StorageQuotaServiceInterface;
use Modules\Core\System\Exceptions\StorageQuotaExceededException;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\ActivityLog;
use Modules\Core\System\Models\User;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @OA\Tag(name="File Manager")
 */
class FileManagerController extends BaseApiController
{
    /**
     * Allowed disks for file manager operations.
     *
     * @var array<int, string>
     */
    protected array $allowedDisks = ['public'];

    public function __construct(
        protected MediaLibraryBridge $mediaLibrary,
    ) {}

    /**
     * Validate the requested disk.
     *
     * @throws ValidationException
     */
    protected function validateDisk(?string $disk): string
    {
        $disk = $disk ?: 'public';

        $user = Auth::user();
        if ($user instanceof User && $user->hasRole('super')) {
            $configuredDisks = config('filesystems.disks');
            $configuredDisks = is_array($configuredDisks) ? array_keys($configuredDisks) : [];
            if (in_array($disk, $configuredDisks, true)) {
                return $disk;
            }
        }

        if (! in_array($disk, $this->allowedDisks, true)) {
            throw ValidationException::withMessages([
                'disk' => ["Disk '$disk' is not allowed access."],
            ]);
        }

        return $disk;
    }

    /**
     * Validate and normalize the path to prevent traversal.
     *
     * @throws ValidationException
     */
    protected function validatePath(?string $path): string
    {
        $path = $path ?: '';

        // Remove null bytes
        $path = str_replace("\0", '', $path);

        // Prevent directory traversal
        if (str_contains($path, '..')) {
            abort(403, 'Invalid path detected (traversal).');
        }

        return trim($path, '/');
    }

    /**
     * Resolve Laravel's disk adapter (typed for URL / MIME helpers).
     */
    protected function storageAdapter(string $disk): FilesystemAdapter
    {
        return Storage::disk($disk);
    }

    /**
     * List files and folders in a path.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user || ! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to manage files');
        }

        try {
            $diskRaw = $request->input('disk');
            $disk = $this->validateDisk(is_string($diskRaw) ? $diskRaw : null);
            $pathRaw = $request->input('path');
            $path = $this->validatePath(is_string($pathRaw) ? $pathRaw : null);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $files = [];
        $folders = [];

        $allDirectories = [];
        $allFiles = [];

        try {
            try {
                $allDirectories = Storage::disk($disk)->directories($path);
                $allFiles = Storage::disk($disk)->files($path);
            } catch (\Exception $e) {
                if (stripos($e->getMessage(), 'symbolic link') !== false) {
                    $root = Storage::disk($disk)->path($path);
                    if (is_dir($root)) {
                        $items = scandir($root);
                        if ($items) {
                            foreach ($items as $item) {
                                if ($item === '.' || $item === '..') {
                                    continue;
                                }
                                $itemPath = $path !== '' ? $path.'/'.$item : $item;
                                if (is_dir($root.'/'.$item)) {
                                    $allDirectories[] = $itemPath;
                                } else {
                                    $allFiles[] = $itemPath;
                                }
                            }
                        }
                    }
                } else {
                    throw $e;
                }
            }

            foreach ($allDirectories as $dirPath) {
                $dirName = basename($dirPath);
                if ($dirName === '.trash') {
                    continue;
                }

                $folders[] = [
                    'name' => $dirName,
                    'path' => '/'.$dirPath,
                    'type' => 'folder',
                ];
            }

            foreach ($allFiles as $filePath) {
                $fileName = basename($filePath);
                if (str_starts_with($fileName, '.')) {
                    continue;
                }

                $files[] = [
                    'name' => $fileName,
                    'path' => '/'.$filePath,
                    'type' => 'file',
                    'size' => Storage::disk($disk)->size($filePath),
                    'modified' => date('Y-m-d H:i:s', Storage::disk($disk)->lastModified($filePath)),
                    'extension' => pathinfo($fileName, PATHINFO_EXTENSION),
                    'url' => $this->storageAdapter($disk)->url($filePath),
                ];
            }
        } catch (\Exception $e) {
            return $this->error('Error reading directory: '.$e->getMessage(), 500, [], 'DIRECTORY_READ_ERROR');
        }

        // Server-side Filtering
        $searchRaw = $request->input('search');
        $search = is_string($searchRaw) ? $searchRaw : null;
        $type = $request->input('type');

        if ($search) {
            $folders = array_values(array_filter($folders, fn (array $f): bool => stripos((string) $f['name'], $search) !== false));
            $files = array_values(array_filter($files, fn (array $f): bool => stripos((string) $f['name'], $search) !== false));
        }

        if ($type && $type !== 'all') {
            $extensions = [];
            switch ($type) {
                case 'images': $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];
                    break;
                case 'documents': $extensions = ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt', 'xls', 'xlsx', 'ppt', 'pptx'];
                    break;
                case 'videos': $extensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'];
                    break;
                case 'audio': $extensions = ['mp3', 'wav', 'flac', 'aac', 'ogg', 'm4a'];
                    break;
                case 'archives': $extensions = ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'];
                    break;
            }

            if ($extensions !== []) {
                $files = array_values(array_filter($files, fn (array $f): bool => in_array(strtolower((string) $f['extension']), $extensions)));
            }
        }

        // Server-side Sorting
        $sortRaw = $request->input('sort', 'name');
        $sort = in_array($sortRaw, ['name', 'date', 'size'], true) ? $sortRaw : 'name';
        $directionRaw = $request->input('direction', 'asc');
        $direction = $directionRaw === 'desc' ? 'desc' : 'asc';

        $sortFn = function (array $a, array $b) use ($sort, $direction): int|float {
            $valA = $a[$sort] ?? '';
            $valB = $b[$sort] ?? '';

            if ($sort === 'date') {
                $valA = strtotime($a['modified'] ?? 0);
                $valB = strtotime($b['modified'] ?? 0);
                if ($valA == $valB) {
                    return 0;
                }

                return ($direction === 'asc') ? ($valA - $valB) : ($valB - $valA);
            }

            if ($sort === 'size') {
                $valA = $a['size'] ?? 0;
                $valB = $b['size'] ?? 0;
                if ($valA == $valB) {
                    return 0;
                }

                return ($direction === 'asc') ? ($valA - $valB) : ($valB - $valA);
            }

            // Default string comparison (name)
            return ($direction === 'asc')
                ? strcasecmp((string) $valA, (string) $valB)
                : strcasecmp((string) $valB, (string) $valA);
        };

        usort($folders, $sortFn);
        usort($files, $sortFn);

        return $this->success([
            'path' => $path !== '' && $path !== '0' ? '/'.$path : '/',
            'folders' => $folders,
            'files' => $files,
        ], 'Directory contents retrieved successfully');
    }

    /**
     * Download a file or folder.
     *
     * @return BinaryFileResponse|StreamedResponse|JsonResponse|mixed
     */
    public function download(Request $request)
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user || ! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to download files');
        }

        try {
            $diskRaw = $request->input('disk');
            $disk = $this->validateDisk(is_string($diskRaw) ? $diskRaw : null);
            $pathRaw = $request->input('path');
            $path = $this->validatePath(is_string($pathRaw) ? $pathRaw : null);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        if (! $path || ! Storage::disk($disk)->exists($path)) {
            return $this->notFound('File');
        }

        $fullPath = Storage::disk($disk)->path($path);

        if (is_dir($fullPath)) {
            return $this->downloadFolder($path, $disk);
        }

        return response()->download($fullPath);
    }

    /**
     * Download a folder as ZIP
     */
    protected function downloadFolder(string $path, string $disk): JsonResponse|BinaryFileResponse
    {
        if (! class_exists('ZipArchive')) {
            return $this->error('Zip extension not installed', 500);
        }

        $zipFileName = basename($path).'.zip';
        $zipPath = storage_path('app/temp/'.$zipFileName);

        // Ensure temp directory exists
        if (! File::exists(dirname($zipPath))) {
            File::makeDirectory(dirname($zipPath), 0755, true);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $files = Storage::disk($disk)->allFiles($path);

            foreach ($files as $file) {
                $content = Storage::disk($disk)->get($file);
                if (is_string($content)) {
                    $zip->addFromString($file, $content);
                }
            }

            $zip->close();
        } else {
            return $this->error('Failed to create zip file', 500);
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/file-manager/upload",
     *     summary="Upload file to specific path",
     *     tags={"File Manager"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"file"},
     *
     *                 @OA\Property(property="file", type="string", format="binary"),
     *                 @OA\Property(property="disk", type="string"),
     *                 @OA\Property(property="path", type="string")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Uploaded"),
     *     security={{"sanctum":{}}}
     * )
     * Upload file to specific path.
     */
    public function upload(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user || ! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to upload files');
        }

        // Get settings from UploadSettingsHelper (shared with Media component)
        $maxSize = UploadSettingsHelper::getMaxUploadSize();
        $allowedExtensions = UploadSettingsHelper::getAllowedExtensions();
        $allowedMimes = implode(',', $allowedExtensions);

        $request->validate([
            'file' => "required_without:files|file|max:{$maxSize}|mimes:{$allowedMimes}",
            'files' => 'required_without:file|array',
            'files.*' => "file|max:{$maxSize}|mimes:{$allowedMimes}",
            'path' => 'nullable|string',
            'disk' => 'nullable|string',
        ]);

        try {
            $diskRaw = $request->input('disk');
            $disk = $this->validateDisk(is_string($diskRaw) ? $diskRaw : null);
            $pathRaw = $request->input('path');
            $path = $this->validatePath(is_string($pathRaw) ? $pathRaw : null);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }
        $uploadedFiles = [];

        // Handle single file or multiple files
        $requestFiles = $request->file('files');
        $files = [];
        if (is_array($requestFiles)) {
            $files = $requestFiles;
        } else {
            $singleFile = $request->file('file');
            if ($singleFile instanceof UploadedFile) {
                $files[] = $singleFile;
            }
        }

        $quota = app(StorageQuotaServiceInterface::class);

        foreach ($files as $file) {
            // Double-check extension is allowed (in case mimes validation is bypassed)
            $extension = strtolower($file->getClientOriginalExtension());
            if (! UploadSettingsHelper::isExtensionAllowed($extension)) {
                return $this->validationError([
                    'file' => ["File type '{$extension}' is not allowed."],
                ], 'Invalid file type');
            }

            try {
                $quota->assertCanStore((int) $file->getSize());
            } catch (StorageQuotaExceededException $e) {
                return $this->error($e->getMessage(), 413);
            }

            $fileName = $file->getClientOriginalName();
            $filePath = $path !== '' ? $path.'/'.$fileName : $fileName;

            $content = file_get_contents($file->getRealPath());
            if ($content === false) {
                continue;
            }

            // Sanitize SVG
            if (($extension === 'svg' || $file->getMimeType() === 'image/svg+xml') && class_exists(Sanitizer::class)) {
                try {
                    $sanitizer = new Sanitizer;
                    $sanitizer->removeRemoteReferences(true);
                    $sanitized = $sanitizer->sanitize($content);
                    if (is_string($sanitized)) {
                        $content = $sanitized;
                    }
                } catch (\Exception $e) {
                    Log::warning('SVG sanitization failed in FileManager: '.$e->getMessage());
                }
            }

            Storage::disk($disk)->put($filePath, $content);

            $mediaRecord = null;
            $mediaService = $this->mediaLibrary->sync();
            if ($disk === 'public' && $mediaService && $mediaService->shouldIndexPublicPath($filePath)) {
                $authorId = Auth::id();
                $mediaRecord = $mediaService->registerFromDisk(
                    $disk,
                    $filePath,
                    is_scalar($authorId) ? (string) $authorId : null,
                    str_starts_with((string) $file->getMimeType(), 'image/')
                );
            }

            $uploadedFiles[] = [
                'path' => '/'.$filePath,
                'url' => $this->storageAdapter($disk)->url($filePath),
                'name' => $fileName,
                'type' => 'file',
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
                'updated_at' => now(),
                'media_id' => $mediaRecord?->getId(),
            ];
        }

        return $this->success([
            'files' => $uploadedFiles,
        ], 'Files uploaded successfully', 201);
    }

    /**
     * Move file to trash (soft delete) or delete permanently.
     */
    public function delete(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        Log::info('FileManagerController::delete called', [
            'method' => $request->method(),
            'user_id' => $user->id,
            'has_path' => $request->filled('path'),
            'permanent' => $request->boolean('permanent'),
        ]);

        if (! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to delete files or folders');
        }

        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
            'permanent' => 'nullable|boolean', // If true, skip trash
        ]);

        $pathRaw = $request->input('path');
        $path = trim(is_string($pathRaw) ? $pathRaw : '', '/');
        $diskRaw = $request->input('disk', 'public');
        $disk = is_string($diskRaw) ? $diskRaw : 'public';
        $permanent = (bool) $request->input('permanent', false);

        if (! Storage::disk($disk)->exists($path)) {
            return $this->notFound('File');
        }

        if ($permanent) {
            // Find media if any
            $media = $this->mediaLibrary->findByPath($path);
            $mediaService = $this->mediaLibrary->sync();

            if ($media && $mediaService) {
                $mediaService->delete($media, true);
            } else {
                Storage::disk($disk)->delete($path);
            }

            return $this->success(null, 'File permanently deleted');
        }

        // Move to trash
        $fileName = basename($path);
        $trashPath = '.trash/'.uniqid().'_'.$fileName;

        // Get file info before moving - use Storage methods for compatibility
        try {
            $adapter = $this->storageAdapter($disk);
            $size = $adapter->size($path);
            $mimeType = $adapter->mimeType($path);
        } catch (\Exception $e) {
            $size = 0;
            $mimeType = null;
        }
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        ActivityLog::log('soft_deleted_file', null, ['path' => $path, 'disk' => $disk], $request->user(), "Moved file to trash: {$path}");

        // Create trash directory if not exists
        Storage::disk($disk)->makeDirectory('.trash');

        // Move file to trash
        Storage::disk($disk)->move($path, $trashPath);

        // Sync with Media Library (Delete)
        try {
            $media = $this->mediaLibrary->findByPath($path);
            $mediaService = $this->mediaLibrary->sync();

            if ($media) {
                // Move thumbnails and variants for this media item too
                $mediaService?->moveVariantsToTrash($media, $trashPath);

                $media->setPath($trashPath); // Update path to point to trash
                $media->save();
                $media->delete(); // Soft delete
            }
        } catch (\Exception $e) {
            // Log but don't fail the file operation
            Log::warning('Failed to sync media delete: '.$e->getMessage());
        }

        // Record in database
        DeletedFile::create([
            'original_path' => '/'.$path,
            'trash_path' => $trashPath,
            'disk' => $disk,
            'name' => $fileName,
            'type' => 'file',
            'size' => $size,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'deleted_by' => Auth::id(),
            'deleted_at' => now(),
        ]);

        return $this->success(null, 'File moved to trash');
    }

    /**
     * Move folder to trash (soft delete) or delete permanently.
     */
    public function deleteFolder(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        Log::info('FileManagerController::deleteFolder called', [
            'method' => $request->method(),
            'user_id' => $user->id,
            'has_path' => $request->filled('path'),
            'permanent' => $request->boolean('permanent'),
        ]);

        if (! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to delete folders');
        }

        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
            'permanent' => 'nullable|boolean',
        ]);

        $pathRaw = $request->input('path');
        $path = trim(is_string($pathRaw) ? $pathRaw : '', '/');
        $diskRaw = $request->input('disk', 'public');
        $disk = is_string($diskRaw) ? $diskRaw : 'public';
        $permanent = (bool) $request->input('permanent', false);

        // Check if directory exists using Storage for better compatibility
        if (! Storage::disk($disk)->exists($path)) {
            return $this->notFound('Folder');
        }

        if ($permanent) {
            Storage::disk($disk)->deleteDirectory($path);

            return $this->success(null, 'Folder permanently deleted');
        }

        // Move to trash
        $folderName = basename($path);
        $trashPath = '.trash/'.uniqid().'_'.$folderName;

        // Create trash directory if not exists
        Storage::disk($disk)->makeDirectory('.trash');

        // Move folder to trash
        $sourcePath = Storage::disk($disk)->path($path);
        $targetPath = Storage::disk($disk)->path($trashPath);

        try {
            if (is_dir($sourcePath)) {
                File::moveDirectory($sourcePath, $targetPath);
            } else {
                // Fallback to Storage move if it's not a local directory or failed is_dir
                Storage::disk($disk)->move($path, $trashPath);
            }
        } catch (\Exception $e) {
            Log::error('Failed to move folder to trash: '.$e->getMessage());

            return $this->error('Failed to move folder to trash. It might be in use or permissions are insufficient.');
        }

        ActivityLog::log('soft_deleted_folder', null, ['path' => $path, 'disk' => $disk], $request->user(), "Moved folder to trash: {$path}");

        // Sync with Media Library (Update paths for all files inside the folder) if present
        try {
            $mediaService = $this->mediaLibrary->sync();
            foreach ($this->mediaLibrary->findByFolderPath($path) as $media) {
                $relativePart = str_replace([$path, '/'.$path], '', $media->getPath());
                $newMediaPath = $trashPath.$relativePart;

                $mediaService?->moveVariantsToTrash($media, $newMediaPath);

                $media->setPath($newMediaPath);
                $media->save();
                $media->delete(); // Soft delete found media too
            }
        } catch (\Exception) {
            // ignore
        }

        // Record in database
        DeletedFile::create([
            'original_path' => '/'.$path,
            'trash_path' => $trashPath,
            'disk' => $disk,
            'name' => $folderName,
            'type' => 'folder',
            'size' => null,
            'extension' => null,
            'mime_type' => null,
            'deleted_by' => Auth::id(),
            'deleted_at' => now(),
        ]);

        return $this->success(null, 'Folder moved to trash');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/file-manager/folder",
     *     summary="Create new folder",
     *     tags={"File Manager"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name"},
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="disk", type="string"),
     *             @OA\Property(property="path", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Created"),
     *     security={{"sanctum":{}}}
     * )
     * Create new folder.
     */
    public function createFolder(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user || ! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to create folders');
        }

        $request->validate([
            'name' => 'required|string',
            'path' => 'nullable|string',
            'disk' => 'nullable|string',
        ]);

        $nameRaw = $request->input('name');
        $name = is_string($nameRaw) ? $nameRaw : '';

        try {
            $diskRaw = $request->input('disk');
            $disk = $this->validateDisk(is_string($diskRaw) ? $diskRaw : null);
            $pathRaw = $request->input('path');
            $path = $this->validatePath(is_string($pathRaw) ? $pathRaw : null);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $folderPath = $path !== '' ? $path.'/'.$name : $name;

        Storage::disk($disk)->makeDirectory($folderPath);

        return $this->success([
            'name' => $name,
            'path' => '/'.$folderPath,
            'type' => 'folder',
        ], 'Folder created successfully', 201);
    }

    /**
     * Move a file or folder to a new location.
     */
    public function move(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user || ! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to move files or folders');
        }

        $request->validate([
            'source' => 'required|string',
            'destination' => 'nullable|string',
            'type' => 'required|in:file,folder',
            'disk' => 'nullable|string',
        ]);

        $typeRaw = $request->input('type');
        $type = is_string($typeRaw) ? $typeRaw : 'file';

        try {
            $diskRaw = $request->input('disk');
            $disk = $this->validateDisk(is_string($diskRaw) ? $diskRaw : null);
            $sourceRaw = $request->input('source');
            $source = $this->validatePath(is_string($sourceRaw) ? $sourceRaw : null);
            $destinationRaw = $request->input('destination');
            $destination = $this->validatePath(is_string($destinationRaw) ? $destinationRaw : null);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        try {
            // Get the filename/foldername from source
            $name = basename($source);
            $newPath = $destination !== '' && $destination !== '0' ? $destination.'/'.$name : $name;

            // Handle duplicate names
            $newPath = $this->getUniquePath($disk, $newPath);

            if ($type === 'folder') {
                $sourcePath = Storage::disk($disk)->path($source);
                $destPath = Storage::disk($disk)->path($newPath);

                if (! is_dir($sourcePath)) {
                    return $this->notFound('Source folder');
                }

                // Use File facade for moving directories
                File::moveDirectory($sourcePath, $destPath);
            } else {
                if (! Storage::disk($disk)->exists($source)) {
                    return $this->notFound('Source file');
                }

                Storage::disk($disk)->move($source, $newPath);
            }

            $this->mediaLibrary->sync()?->syncAfterMove($disk, $source, $newPath, $type === 'folder');

            return $this->success([
                'oldPath' => '/'.$source,
                'newPath' => '/'.$newPath,
            ], ($type === 'folder' ? 'Folder' : 'File').' moved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to move: '.$e->getMessage(), 500, [], 'MOVE_ERROR');
        }
    }

    /**
     * Copy a file or folder.
     */
    public function copy(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user || ! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to copy files or folders');
        }

        $request->validate([
            'source' => 'required|string',
            'destination' => 'nullable|string',
            'type' => 'required|in:file,folder',
            'disk' => 'nullable|string',
        ]);

        $typeRaw = $request->input('type');
        $type = is_string($typeRaw) ? $typeRaw : 'file';

        try {
            $diskRaw = $request->input('disk');
            $disk = $this->validateDisk(is_string($diskRaw) ? $diskRaw : null);
            $sourceRaw = $request->input('source');
            $source = $this->validatePath(is_string($sourceRaw) ? $sourceRaw : null);
            $destinationRaw = $request->input('destination');
            $destination = $this->validatePath(is_string($destinationRaw) ? $destinationRaw : null);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        try {
            $name = basename($source);
            $newPath = $destination !== '' && $destination !== '0' ? $destination.'/'.$name : $name;

            // Handle duplicate names
            $newPath = $this->getUniquePath($disk, $newPath);

            if ($type === 'folder') {
                $sourcePath = Storage::disk($disk)->path($source);
                $destPath = Storage::disk($disk)->path($newPath);

                if (! is_dir($sourcePath)) {
                    return $this->notFound('Source folder');
                }

                File::copyDirectory($sourcePath, $destPath);
            } else {
                if (! Storage::disk($disk)->exists($source)) {
                    return $this->notFound('Source file');
                }

                Storage::disk($disk)->copy($source, $newPath);
            }

            $authorId = Auth::id();
            $this->mediaLibrary->sync()?->syncAfterCopy(
                $disk,
                $newPath,
                $type === 'folder',
                is_scalar($authorId) ? (string) $authorId : null,
            );

            return $this->success([
                'newPath' => '/'.$newPath,
            ], ($type === 'folder' ? 'Folder' : 'File').' copied successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to copy: '.$e->getMessage(), 500, [], 'COPY_ERROR');
        }
    }

    /**
     * Helper: Get unique path if file/folder exists
     */
    private function getUniquePath(string $disk, string $path): string
    {
        if (! Storage::disk($disk)->exists($path)) {
            return $path;
        }

        $dir = dirname($path);
        $dir = $dir === '.' ? '' : $dir.'/';
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $extString = $extension !== '' && $extension !== '0' ? '.'.$extension : '';

        $counter = 1;
        while (Storage::disk($disk)->exists($dir.$filename.' ('.$counter.')'.$extString)) {
            $counter++;
        }

        return $dir.$filename.' ('.$counter.')'.$extString;
    }

    /**
     * Rename a file or folder.
     */
    public function rename(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user || ! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to rename files or folders');
        }

        $request->validate([
            'path' => 'required|string',
            'newName' => 'required|string',
            'type' => 'required|in:file,folder',
            'disk' => 'nullable|string',
        ]);

        $newNameRaw = $request->input('newName');
        $newName = is_string($newNameRaw) ? $newNameRaw : '';
        $typeRaw = $request->input('type');
        $type = is_string($typeRaw) ? $typeRaw : 'file';

        try {
            $diskRaw = $request->input('disk');
            $disk = $this->validateDisk(is_string($diskRaw) ? $diskRaw : null);
            $pathRaw = $request->input('path');
            $path = $this->validatePath(is_string($pathRaw) ? $pathRaw : null);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        try {
            $parentDir = dirname($path);
            $parentDir = $parentDir === '.' ? '' : $parentDir;
            $newPath = $parentDir !== '' && $parentDir !== '0' ? $parentDir.'/'.$newName : $newName;

            if ($type === 'folder') {
                $sourcePath = Storage::disk($disk)->path($path);
                $destPath = Storage::disk($disk)->path($newPath);

                if (! is_dir($sourcePath)) {
                    return $this->notFound('Folder');
                }

                File::moveDirectory($sourcePath, $destPath);
            } else {
                if (! Storage::disk($disk)->exists($path)) {
                    return $this->notFound('File');
                }

                Storage::disk($disk)->move($path, $newPath);
            }

            return $this->success([
                'oldPath' => '/'.$path,
                'newPath' => '/'.$newPath,
                'name' => $newName,
            ], ($type === 'folder' ? 'Folder' : 'File').' renamed successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to rename: '.$e->getMessage(), 500, [], 'RENAME_ERROR');
        }
    }

    /**
     * List all items in trash.
     */
    public function trash(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user || ! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to view trashed files');
        }

        $items = DeletedFile::with('deletedByUser')
            ->orderBy('deleted_at', 'desc')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'original_path' => $item->original_path,
                'type' => $item->type,
                'size' => $item->size,
                'formatted_size' => $item->formatted_size,
                'extension' => $item->extension,
                'deleted_at' => $item->deleted_at->toIso8601String(),
                'deleted_by' => $item->deletedByUser ? $item->deletedByUser->name : 'Unknown',
            ]);

        return $this->success([
            'items' => $items,
            'count' => $items->count(),
        ], 'Trash contents retrieved successfully');
    }

    /**
     * Restore item from trash.
     */
    public function restore(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user || ! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to restore files');
        }

        $request->validate([
            'id' => 'required|string|exists:infra_deleted_files,id',
            'disk' => 'nullable|string', // Keep for validation, but actual disk comes from DeletedFile
        ]);

        $deletedFileId = $request->input('id');
        /** @var DeletedFile $deletedFile */
        $deletedFile = DeletedFile::findOrFail(is_scalar($deletedFileId) ? $deletedFileId : 0);

        $trashPath = (string) $deletedFile->trash_path;
        $originalPath = ltrim((string) $deletedFile->original_path, '/');
        $disk = (string) $deletedFile->disk;
        if ($disk === '') {
            $disk = 'public';
        }

        if (! Storage::disk($disk)->exists($trashPath) && ! is_dir(Storage::disk($disk)->path($trashPath))) {
            $deletedFile->delete(); // Remove record if file/folder is already gone

            return $this->error('Trash item no longer exists in storage', 404, [], 'TRASH_ITEM_NOT_FOUND');
        }

        // Check if original path is available, if not, generate a unique one
        $finalOriginalPath = $originalPath;
        if ($deletedFile->type === 'folder') {
            if (is_dir(Storage::disk($disk)->path($originalPath))) {
                $finalOriginalPath = $originalPath.'_restored_'.time();
            }
        } elseif (Storage::disk($disk)->exists($originalPath)) {
            $ext = pathinfo($originalPath, PATHINFO_EXTENSION);
            $name = pathinfo($originalPath, PATHINFO_FILENAME);
            $dir = dirname($originalPath);
            $dir = $dir === '.' ? '' : $dir.'/';
            $finalOriginalPath = $dir.$name.'_restored_'.time().($ext !== '' && $ext !== '0' ? '.'.$ext : '');
        }

        // Restore to original path
        if ($deletedFile->type === 'folder') {
            $fullTrashPath = Storage::disk($disk)->path($trashPath);
            $fullOriginalPath = Storage::disk($disk)->path($finalOriginalPath);
            File::moveDirectory($fullTrashPath, $fullOriginalPath);
        } else {
            Storage::disk($disk)->move($trashPath, $finalOriginalPath);
        }

        // Sync with Media Library (Restore) if present
        $mediaService = $this->mediaLibrary->sync();
        if ($deletedFile->type === 'file') {
            try {
                $media = $this->mediaLibrary->findTrashedByTrashPath($trashPath);

                if ($media) {
                    // Move variants back from trash
                    $mediaService?->moveVariantsFromTrash($media, $trashPath, $finalOriginalPath);

                    $media->setPath($finalOriginalPath); // Update to restored path
                    $media->save(); // Save path change
                    $media->restore(); // Un-soft-delete
                }
            } catch (\Exception $e) {
                // Log but continue
                Log::warning('Failed to sync media restore: '.$e->getMessage());
            }
        } else {
            // Sync with Media Library (Restore items inside folder)
            try {
                foreach ($this->mediaLibrary->findTrashedByFolderTrash($trashPath) as $media) {
                    $relativePart = str_replace([$trashPath, '/'.$trashPath], '', $media->getPath());
                    $newPath = $finalOriginalPath.$relativePart;

                    // Move variants back from trash
                    $mediaService?->moveVariantsFromTrash($media, $media->getPath(), $newPath);

                    $media->setPath($newPath);
                    $media->save();
                    $media->restore();
                }
            } catch (\Exception $e) {
                Log::warning('Failed to sync folder media restore: '.$e->getMessage());
            }
        }

        // Remove from database
        $deletedFile->delete();

        ActivityLog::log('restored_file', null, ['path' => $finalOriginalPath, 'disk' => $disk], $request->user(), "Restored item from trash: {$finalOriginalPath}");

        return $this->success([
            'restored_path' => '/'.$finalOriginalPath,
        ], 'Item restored successfully');
    }

    /**
     * Empty entire trash.
     */
    public function emptyTrash(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user || ! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to empty trash');
        }

        // We'll iterate through all deleted files to handle their specific disks
        $deletedRecords = DeletedFile::all();
        $disksToClean = [];

        foreach ($deletedRecords as $record) {
            $disk = $record->disk ?? 'public';
            $trashPath = $record->trash_path;

            // Sync with Media Library (Force Delete) if present
            $mediaService = $this->mediaLibrary->sync();
            if ($record->type === 'file') {
                try {
                    $media = $this->mediaLibrary->findTrashedByTrashPath((string) $trashPath);

                    if ($media) {
                        $mediaService?->delete($media, true);
                    }
                } catch (\Exception) {
                    // ignore
                }
            } else {
                // For folders, find and force delete all media records inside
                try {
                    foreach ($this->mediaLibrary->findTrashedByFolderTrash((string) $record->trash_path) as $media) {
                        $mediaService?->delete($media, true);
                    }
                } catch (\Exception) {
                    // ignore
                }
            }

            // Track unique disks to clean directories later
            if (! in_array($disk, $disksToClean)) {
                $disksToClean[] = $disk;
            }
        }

        // Clean up .trash folders on all affected disks
        foreach ($disksToClean as $diskName) {
            try {
                if (Storage::disk($diskName)->exists('.trash')) {
                    Storage::disk($diskName)->deleteDirectory('.trash');
                    Storage::disk($diskName)->makeDirectory('.trash');
                }
            } catch (\Exception $e) {
                Log::error("Failed to clean trash on disk {$diskName}: ".$e->getMessage());
            }
        }

        // Clear database records - use delete() instead of truncate() for better compatibility with transactions/locks
        $count = $deletedRecords->count();
        DeletedFile::query()->delete();

        ActivityLog::log('emptied_trash', null, ['deleted_count' => $count, 'disks' => $disksToClean], $user, 'Emptied File Manager trash on disks: '.implode(', ', $disksToClean));

        return $this->success([
            'deleted_count' => $count,
        ], 'Trash emptied successfully');
    }

    /**
     * Permanently delete single item from trash.
     */
    public function deletePermanently(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        Log::info('FileManagerController::deletePermanently called', [
            'method' => $request->method(),
            'user_id' => $user->id,
            'deleted_file_id' => $request->input('id'),
        ]);

        if (! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to permanently delete files');
        }

        $request->validate([
            'id' => 'required|string|exists:infra_deleted_files,id',
            'disk' => 'nullable|string', // Keep for validation, but actual disk comes from DeletedFile
        ]);

        $deletedFileId = $request->input('id');
        /** @var DeletedFile $deletedFile */
        $deletedFile = DeletedFile::findOrFail(is_scalar($deletedFileId) ? $deletedFileId : 0);
        $disk = (string) $deletedFile->disk;
        if ($disk === '') {
            $disk = 'public';
        }

        try {
            $disk = $this->validateDisk($disk);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $trashPath = $deletedFile->trash_path;

        // Sync with Media Library (Force Delete) if present
        $mediaService = $this->mediaLibrary->sync();
        if ($deletedFile->type === 'file') {
            try {
                $media = $this->mediaLibrary->findTrashedByTrashPath((string) $trashPath);

                if ($media) {
                    $mediaService?->delete($media, true);
                }
            } catch (\Exception) {
                // ignore
            }
        } else {
            // For folders, find and force delete all media records inside
            try {
                foreach ($this->mediaLibrary->findTrashedByFolderTrash((string) $deletedFile->trash_path) as $media) {
                    $mediaService?->delete($media, true);
                }
            } catch (\Exception) {
                // ignore
            }
        }

        // Delete from storage
        if ($deletedFile->type === 'folder') {
            if (Storage::disk($disk)->exists($trashPath)) {
                Storage::disk($disk)->deleteDirectory($trashPath);
            }
        } elseif (Storage::disk($disk)->exists($trashPath)) {
            Storage::disk($disk)->delete($trashPath);
        }

        // Remove from database
        $deletedFile->delete();

        ActivityLog::log('permanently_deleted_file', null, ['path' => $deletedFile->original_path, 'disk' => $disk], $user, "Permanently deleted item from trash: {$deletedFile->original_path}");

        return $this->success(null, 'Item permanently deleted');
    }

    /**
     * Extract archive (zip, tar.gz, tar).
     */
    public function extract(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user || ! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to extract archives');
        }

        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
        ]);

        try {
            $diskRaw = $request->input('disk');
            $disk = $this->validateDisk(is_string($diskRaw) ? $diskRaw : null);
            $pathRaw = $request->input('path');
            $path = $this->validatePath(is_string($pathRaw) ? $pathRaw : null);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }
        $fullPath = Storage::disk($disk)->path($path);

        if (! file_exists($fullPath)) {
            return $this->notFound('Archive file');
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $fileName = pathinfo($path, PATHINFO_FILENAME);
        $parentDir = dirname($path);
        $parentDir = $parentDir === '.' ? '' : $parentDir;

        // Create extraction directory
        $extractDir = $parentDir !== '' && $parentDir !== '0' ? $parentDir.'/'.$fileName : $fileName;
        $extractPath = Storage::disk($disk)->path($extractDir);

        // Handle if directory already exists
        if (is_dir($extractPath)) {
            $extractDir = $extractDir.'_'.time();
            $extractPath = Storage::disk($disk)->path($extractDir);
        }

        Storage::disk($disk)->makeDirectory($extractDir);

        try {
            if ($extension === 'zip') {
                $zip = new \ZipArchive;
                if ($zip->open($fullPath) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $filename = $zip->getNameIndex($i);

                        // Prevent Zip Slip
                        // 1. Check for .. in path
                        if ($filename !== false && str_contains($filename, '..')) {
                            $zip->close();
                            throw new \Exception("Security Violation: Zip Slip detected in entry '$filename'");
                        }

                        // 2. Ensure destination is within extract dir
                        if ($filename !== false) {
                            // Extract specific file
                            $zip->extractTo($extractPath, $filename);
                        }
                    }
                    $zip->close();
                } else {
                    return $this->error('Failed to open ZIP file', 500, [], 'EXTRACT_ERROR');
                }
            } elseif (in_array($extension, ['gz', 'tgz'])) {
                // Handle tar.gz and tgz
                $phar = new \PharData($fullPath);
                if ($extension === 'gz' || str_ends_with($path, '.tar.gz')) {
                    $phar->decompress();
                    $tarPath = str_replace(['.tar.gz', '.tgz'], '.tar', $fullPath);
                    $phar = new \PharData($tarPath);
                }
                $phar->extractTo($extractPath);
            } elseif ($extension === 'tar') {
                $phar = new \PharData($fullPath);
                $phar->extractTo($extractPath);
            } else {
                return $this->error('Unsupported archive format. Supported: zip, tar, tar.gz, tgz', 400, [], 'UNSUPPORTED_FORMAT');
            }

            return $this->success([
                'extracted_to' => '/'.$extractDir,
            ], 'Archive extracted successfully');
        } catch (\Exception $e) {
            // Clean up on failure
            if (is_dir($extractPath)) {
                File::deleteDirectory($extractPath);
            }

            return $this->error('Failed to extract: '.$e->getMessage(), 500, [], 'EXTRACT_ERROR');
        }
    }

    /**
     * Compress files/folders to ZIP archive.
     */
    public function compress(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var User|null $user */
        if (! $user || ! $user->can('manage files')) {
            return $this->forbidden('You do not have permission to compress files');
        }

        $request->validate([
            'paths' => 'required|array|min:1',
            'paths.*' => 'string',
            'name' => 'nullable|string',
            'disk' => 'nullable|string',
        ]);

        $requestPaths = $request->input('paths');
        if (! is_array($requestPaths)) {
            return $this->error('Paths must be an array');
        }
        $paths = $requestPaths;
        try {
            $diskRaw = $request->input('disk');
            $disk = $this->validateDisk(is_string($diskRaw) ? $diskRaw : null);
            // Validate all paths in array
            $cleanPaths = [];
            foreach ($paths as $p) {
                $cleanPaths[] = $this->validatePath(is_string($p) ? $p : null);
            }
            $paths = $cleanPaths;
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }
        $archiveNameRaw = $request->input('name');
        $archiveName = is_string($archiveNameRaw) ? $archiveNameRaw : null;

        // Determine archive name
        if (! $archiveName) {
            if (count($paths) === 1) {
                $archiveName = pathinfo(trim($paths[0], '/'), PATHINFO_FILENAME).'.zip';
            } else {
                $archiveName = 'archive_'.date('Y-m-d_His').'.zip';
            }
        }

        // Ensure .zip extension
        if (! str_ends_with(strtolower((string) $archiveName), '.zip')) {
            $archiveName .= '.zip';
        }

        // Determine output directory (same as first item's parent)
        $firstPath = trim($paths[0], '/');
        $parentDir = dirname($firstPath);
        $parentDir = $parentDir === '.' ? '' : $parentDir;
        $archivePath = $parentDir !== '' ? $parentDir.'/'.$archiveName : $archiveName;
        $fullArchivePath = (string) Storage::disk($disk)->path($archivePath);

        // Handle if archive already exists
        if ($fullArchivePath !== '' && file_exists($fullArchivePath)) {
            $baseName = pathinfo((string) $archiveName, PATHINFO_FILENAME);
            $archiveName = $baseName.'_'.time().'.zip';
            $archivePath = $parentDir !== '' ? $parentDir.'/'.$archiveName : $archiveName;
            $fullArchivePath = (string) Storage::disk($disk)->path($archivePath);
        }

        try {
            $zip = new \ZipArchive;
            if ($zip->open($fullArchivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return $this->error('Failed to create ZIP archive', 500, [], 'COMPRESS_ERROR');
            }

            foreach ($paths as $path) {
                $path = trim($path, '/');
                $fullPath = Storage::disk($disk)->path($path);
                $itemName = basename($path);

                if (is_dir($fullPath)) {
                    // Add directory recursively
                    $this->addDirectoryToZip($zip, $fullPath, $itemName);
                } elseif (file_exists($fullPath)) {
                    // Add file
                    $zip->addFile($fullPath, $itemName);
                }
            }

            $zip->close();

            return $this->success([
                'archive_path' => '/'.$archivePath,
                'archive_name' => $archiveName,
            ], 'Files compressed successfully');
        } catch (\Exception $e) {
            if (file_exists($fullArchivePath)) {
                unlink($fullArchivePath);
            }

            return $this->error('Failed to compress: '.$e->getMessage(), 500, [], 'COMPRESS_ERROR');
        }
    }

    /**
     * Helper: Add directory contents to ZIP recursively
     */
    private function addDirectoryToZip(\ZipArchive $zip, string $path, string $relativePath): void
    {
        $zip->addEmptyDir($relativePath);
        $files = scandir($path);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = $path.DIRECTORY_SEPARATOR.$file;
            $localPath = $relativePath.'/'.$file;

            if (is_dir($fullPath)) {
                $this->addDirectoryToZip($zip, $fullPath, $localPath);
            } else {
                $zip->addFile($fullPath, $localPath);
            }
        }
    }
}
