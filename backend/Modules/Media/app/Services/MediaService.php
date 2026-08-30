<?php

namespace Modules\Media\Services;

use enshrined\svgSanitize\Sanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\DriverInterface;
use Modules\Core\Infra\Contracts\MediaFileRecordInterface;
use Modules\Core\Infra\Contracts\MediaLibrarySyncInterface;
use Modules\Core\System\Contracts\StorageQuotaServiceInterface;
use Modules\Core\System\Models\Setting;
use Modules\Library\Models\Tag;
use Modules\Media\Contracts\MediaServiceInterface;
use Modules\Media\Models\DeletedFile;
use Modules\Media\Models\File;
use Modules\Media\Models\Folder;
use Modules\Media\Models\Usage;
use Symfony\Component\Mime\MimeTypes;

class MediaService implements MediaLibrarySyncInterface, MediaServiceInterface
{
    public function __construct(
        protected ?StorageQuotaServiceInterface $storageQuota = null,
    ) {}

    /**
     * Upload and process a media file.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function upload(
        UploadedFile $file,
        ?string $folderId = null,
        bool $optimize = true,
        ?string $authorId = null,
        bool $isShared = false,
        array $metadata = [],
        ?string $subPath = null,
        string $module = 'system'
    ): File {
        $this->quota()->assertCanStore((int) $file->getSize());

        // SVG Sanitization
        if ($file->getMimeType() === 'image/svg+xml' || strtolower($file->getClientOriginalExtension()) === 'svg') {
            $this->sanitizeSvg($file->getRealPath());
        }

        $uploadPath = $subPath ?: 'media';
        $pathRaw = $file->store($uploadPath, 'public');
        $path = is_string($pathRaw) ? $pathRaw : '';
        $fullPath = Storage::disk('public')->path($path);

        $mimeType = (string) $file->getMimeType();
        $fileName = $file->getClientOriginalName();

        // Image Optimization
        if ($optimize && str_starts_with($mimeType, 'image/') && $mimeType !== 'image/svg+xml') {
            $maxWidth = self::intFromSetting('media_max_width', 1920);
            $quality = self::intFromSetting('media_optimization_quality', 85);
            $autoConvert = self::boolFromSetting('media_auto_convert_webp', true);

            $this->optimizeImage($fullPath, $maxWidth, $quality);

            if ($autoConvert && $mimeType !== 'image/webp') {
                $webpPath = $this->convertToWebP($fullPath, $quality);
                if ($webpPath) {
                    $fullPath = $webpPath;
                    $path = $uploadPath.'/'.basename($fullPath);
                    $mimeType = 'image/webp';
                    $fileName = pathinfo($fileName, PATHINFO_FILENAME).'.webp';
                }
            }
        }

        $nameMeta = $metadata['name'] ?? null;
        $displayName = is_string($nameMeta) ? $nameMeta : pathinfo($fileName, PATHINFO_FILENAME);
        $captionMeta = $metadata['caption'] ?? null;
        $caption = is_string($captionMeta) ? $captionMeta : null;
        $altMeta = $metadata['alt'] ?? null;
        $alt = is_string($altMeta) ? $altMeta : $fileName;

        $mediaFile = File::create([
            'module' => $module,
            'name' => $displayName,
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'disk' => 'public',
            'path' => $path,
            'size' => (int) (filesize($fullPath) ?: 0),
            'folder_id' => $folderId,
            'author_id' => $authorId ?: Auth::id(),
            'is_shared' => $isShared,
            'caption' => $caption,
            'alt' => $alt,
        ]);

        // Sync Tags
        if (! empty($metadata['tags']) && is_array($metadata['tags'])) {
            $this->syncTags($mediaFile, $metadata['tags']);
        }

        // Generate Thumbnail
        if (str_starts_with($mimeType, 'image/')) {
            $this->generateThumbnail($mediaFile);
        }

        return $mediaFile;
    }

    /**
     * Register an existing file on disk in the media library (idempotent by disk + path).
     */
    public function registerFromDisk(
        string $disk,
        string $relativePath,
        ?string $authorId = null,
        bool $generateThumbnail = false,
        string $module = 'system'
    ): ?File {
        $relativePath = ltrim($relativePath, '/');

        if ($this->shouldSkipDiskRegistration($relativePath)) {
            return null;
        }

        if ($disk === 'public' && ! $this->shouldIndexPublicPath($relativePath)) {
            return null;
        }

        if (! Storage::disk($disk)->exists($relativePath)) {
            return null;
        }

        $existing = File::withoutGlobalScopes()
            ->where('disk', $disk)
            ->where('path', $relativePath)
            ->first();

        if ($existing) {
            return $existing;
        }

        $fullPath = Storage::disk($disk)->path($relativePath);
        if (! is_file($fullPath)) {
            return null;
        }

        $fileName = basename($relativePath);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $mimeTypes = new MimeTypes;
        $mimeCandidates = $mimeTypes->getMimeTypes($extension);
        $mimeType = $mimeCandidates !== []
            ? $mimeCandidates[0]
            : (mime_content_type($fullPath) ?: 'application/octet-stream');
        $size = filesize($fullPath) ?: 0;

        $this->quota()->assertCanStore((int) $size);

        $mediaFile = File::withoutGlobalScopes()->create([
            'module' => $module,
            'name' => pathinfo($fileName, PATHINFO_FILENAME),
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'disk' => $disk,
            'path' => $relativePath,
            'size' => $size,
            'folder_id' => $this->resolveFolderIdFromPath($relativePath),
            'author_id' => $authorId ?: Auth::id(),
            'is_shared' => true,
            'alt' => pathinfo($fileName, PATHINFO_FILENAME),
        ]);

        if ($generateThumbnail && str_starts_with($mimeType, 'image/')) {
            $this->generateThumbnail($mediaFile);
        }

        return $mediaFile;
    }

    /**
     * Paths under public disk that belong in the media library (srv_media_files).
     */
    public function shouldIndexPublicPath(string $relativePath): bool
    {
        $relativePath = ltrim($relativePath, '/');

        if ($relativePath === '' || str_starts_with($relativePath, '.trash/')) {
            return false;
        }

        return str_starts_with($relativePath, 'media/');
    }

    public function findByDiskPath(string $disk, string $path): ?File
    {
        $path = ltrim($path, '/');

        return File::withoutGlobalScopes()
            ->where('disk', $disk)
            ->where(function ($q) use ($path): void {
                $q->where('path', $path)->orWhere('path', '/'.$path);
            })
            ->first();
    }

    /**
     * Keep media DB paths aligned after File Manager move/rename.
     */
    public function syncAfterMove(string $disk, string $source, string $destination, bool $isFolder): void
    {
        if ($disk !== 'public') {
            return;
        }

        $source = ltrim($source, '/');
        $destination = ltrim($destination, '/');

        if (! $this->shouldIndexPublicPath($source) && ! $this->shouldIndexPublicPath($destination)) {
            return;
        }

        if (! $isFolder) {
            $media = $this->findByDiskPath($disk, $source);
            if ($media) {
                $media->update([
                    'path' => $destination,
                    'folder_id' => $this->resolveFolderIdFromPath($destination),
                ]);
            } elseif ($this->shouldIndexPublicPath($destination)) {
                $this->registerFromDisk($disk, $destination);
            }

            return;
        }

        $prefix = rtrim($source, '/').'/';
        File::withoutGlobalScopes()
            ->where('disk', $disk)
            ->where(function ($q) use ($source, $prefix): void {
                $q->where('path', $source)->orWhere('path', 'like', $prefix.'%');
            })
            ->get()
            ->each(function (File $media) use ($source, $destination): void {
                $suffix = Str::after(ltrim($media->path, '/'), $source);
                $suffix = ltrim($suffix, '/');
                $newPath = $suffix === '' ? $destination : rtrim($destination, '/').'/'.$suffix;
                $media->update([
                    'path' => $newPath,
                    'folder_id' => $this->resolveFolderIdFromPath($newPath),
                ]);
            });
    }

    /**
     * Register copied files that live under media/ on the public disk.
     */
    public function syncAfterCopy(string $disk, string $destination, bool $isFolder, ?string $authorId = null): void
    {
        if ($disk !== 'public' || ! $this->shouldIndexPublicPath($destination)) {
            return;
        }

        if ($isFolder) {
            foreach (Storage::disk($disk)->allFiles($destination) as $filePath) {
                if ($this->shouldIndexPublicPath($filePath)) {
                    $mime = mime_content_type(Storage::disk($disk)->path($filePath)) ?: '';
                    $this->registerFromDisk($disk, $filePath, $authorId, str_starts_with($mime, 'image/'));
                }
            }

            return;
        }

        $this->registerFromDisk($disk, $destination, $authorId, false);
    }

    /**
     * Move thumbnail sidecar when parent file goes to FM trash.
     */
    public function moveVariantsToTrash(MediaFileRecordInterface $media, string $trashPath): void
    {
        if (! $media instanceof File) {
            return;
        }

        if (! $media->thumbnail_path || ! Storage::disk($media->disk)->exists($media->thumbnail_path)) {
            return;
        }

        $thumbTrash = '.trash/thumbnails/'.uniqid().'_'.basename($media->thumbnail_path);
        Storage::disk($media->disk)->makeDirectory('.trash/thumbnails');
        Storage::disk($media->disk)->move($media->thumbnail_path, $thumbTrash);
        $media->forceFill(['thumbnail_path' => $thumbTrash])->save();
    }

    /**
     * Restore thumbnail sidecar when parent file is restored from FM trash.
     */
    public function moveVariantsFromTrash(MediaFileRecordInterface $media, string $fromPath, string $toPath): void
    {
        if (! $media instanceof File) {
            return;
        }

        unset($fromPath, $toPath);

        if (! $media->thumbnail_path || ! str_starts_with($media->thumbnail_path, '.trash/')) {
            return;
        }

        $originalDir = dirname(ltrim($media->path, '/'));
        $originalDir = $originalDir === '.' ? 'media/thumbnails' : $originalDir.'/thumbnails';
        Storage::disk($media->disk)->makeDirectory($originalDir);
        $newThumbPath = $originalDir.'/'.basename($media->thumbnail_path);

        if (Storage::disk($media->disk)->exists($media->thumbnail_path)) {
            Storage::disk($media->disk)->move($media->thumbnail_path, $newThumbPath);
            $media->forceFill(['thumbnail_path' => $newThumbPath])->save();
        }
    }

    protected function shouldSkipDiskRegistration(string $relativePath): bool
    {
        if (str_contains($relativePath, '/thumbnails/') || str_starts_with($relativePath, 'thumbnails/')) {
            return true;
        }

        return str_starts_with(basename($relativePath), '.');
    }

    protected function resolveFolderIdFromPath(string $relativePath): ?string
    {
        if (! str_starts_with($relativePath, 'media/')) {
            return null;
        }

        $subPath = Str::after($relativePath, 'media/');
        $segments = explode('/', $subPath);

        if (count($segments) < 2) {
            return null;
        }

        $folderSlug = Str::slug($segments[0]);
        $folder = Folder::withoutGlobalScopes()
            ->where('slug', $folderSlug)
            ->first();

        return $folder?->id;
    }

    /**
     * Optimize an image file.
     */
    public function optimizeImage(string $fullPath, int $maxWidth = 1920, int $quality = 85): bool
    {
        if (! class_exists(ImageManager::class)) {
            return false;
        }

        try {
            $driver = $this->getImageDriver();
            if (! $driver instanceof DriverInterface) {
                return false;
            }

            $manager = new ImageManager($driver);
            $image = $manager->read($fullPath);

            if ($image->width() > $maxWidth) {
                $image->scale(width: $maxWidth);
            }

            $image->save($fullPath, quality: $quality);

            return true;
        } catch (\Exception $e) {
            Log::channel('media')->warning('Image optimization failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Convert an image to WebP format.
     */
    public function convertToWebP(string $fullPath, int $quality = 85): ?string
    {
        if (! class_exists(ImageManager::class)) {
            return null;
        }

        try {
            $driver = $this->getImageDriver();
            if (! $driver instanceof DriverInterface) {
                return null;
            }

            $manager = new ImageManager($driver);
            $image = $manager->read($fullPath);

            $pathInfo = pathinfo($fullPath);
            $dirname = isset($pathInfo['dirname']) && is_string($pathInfo['dirname']) ? $pathInfo['dirname'] : '.';
            $filename = isset($pathInfo['filename']) && is_string($pathInfo['filename']) ? $pathInfo['filename'] : '';
            $newPath = $dirname.'/'.$filename.'.webp';

            $image->toWebp($quality)->save($newPath);

            if ($fullPath !== $newPath && file_exists($newPath)) {
                unlink($fullPath);

                return $newPath;
            }

            return $newPath;
        } catch (\Exception $e) {
            Log::channel('media')->warning('WebP conversion failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Generate thumbnail for media.
     */
    public function generateThumbnail(File $file, ?int $width = null, ?int $height = null): ?string
    {
        $width ??= self::intFromSetting('media_thumbnail_width', 300);
        $height ??= self::intFromSetting('media_thumbnail_height', 300);
        $fullPath = Storage::disk($file->disk)->path($file->path);

        $pathInfo = pathinfo($file->path);
        $fileName = isset($pathInfo['filename']) && is_string($pathInfo['filename']) ? $pathInfo['filename'] : '';
        $extension = isset($pathInfo['extension']) && is_string($pathInfo['extension']) ? $pathInfo['extension'] : '';
        $dirnameRaw = isset($pathInfo['dirname']) && is_string($pathInfo['dirname']) ? $pathInfo['dirname'] : '.';
        $dirname = ($dirnameRaw === '.' || $dirnameRaw === '/') ? '' : $dirnameRaw.'/';

        $thumbnailDir = Storage::disk($file->disk)->path($dirname.'thumbnails');
        if (! is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        $isSvg = $file->mime_type === 'image/svg+xml' || strtolower($extension) === 'svg';
        $thumbnailExtension = $isSvg ? 'png' : $extension;
        $thumbnailPath = $dirname.'thumbnails/'.$fileName.'_thumb.'.$thumbnailExtension;
        $thumbnailFullPath = Storage::disk($file->disk)->path($thumbnailPath);

        // Handle SVG with Imagick if available
        if ($isSvg && extension_loaded('imagick') && class_exists('Imagick')) {
            try {
                $imagick = new \Imagick;
                $imagick->setBackgroundColor(new \ImagickPixel('transparent'));
                $imagick->readImage($fullPath);
                $imagick->setImageFormat('png');
                $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, true);
                $imagick->writeImage($thumbnailFullPath);

                return $thumbnailPath;
            } catch (\Exception $e) {
                Log::channel('media')->warning('SVG thumbnail generation failed: '.$e->getMessage());
            }
        }

        // Fallback to Intervention
        $driver = $this->getImageDriver();
        if (! $driver instanceof DriverInterface) {
            return null;
        }

        try {
            $manager = new ImageManager($driver);
            $image = $manager->read($fullPath);
            $image->cover($width, $height);

            if ($isSvg) {
                $image->toPng()->save($thumbnailFullPath);
            } else {
                $image->save($thumbnailFullPath, quality: 85);
            }

            return $thumbnailPath;
        } catch (\Exception $e) {
            Log::channel('media')->warning('Thumbnail generation failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Resize an image.
     */
    public function resize(File $file, int $width, ?int $height = null, int $quality = 85): bool
    {
        $driver = $this->getImageDriver();
        if (! $driver instanceof DriverInterface) {
            return false;
        }

        try {
            $fullPath = Storage::disk($file->disk)->path($file->path);
            $manager = new ImageManager($driver);
            $image = $manager->read($fullPath);

            if ($height) {
                $image->resize($width, $height);
            } else {
                $image->scale(width: $width);
            }

            $image->save($fullPath, quality: $quality);
            $file->update(['size' => filesize($fullPath)]);

            return true;
        } catch (\Exception $e) {
            Log::channel('media')->error('Image resize failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Delete media.
     */
    public function delete(MediaFileRecordInterface $file, bool $permanent = false, bool $unlinkPhysical = true): void
    {
        if (! $file instanceof File) {
            return;
        }

        if ($permanent) {
            if ($unlinkPhysical) {
                Storage::disk($file->disk)->delete($file->path);
                if ($file->thumbnail_path) {
                    Storage::disk($file->disk)->delete($file->thumbnail_path);
                }
            }
            $this->forceDelete($file);

            return;
        }

        if ($unlinkPhysical) {
            // Delete the physical file directly to save disk space
            Storage::disk($file->disk)->delete($file->path);
            if ($file->thumbnail_path) {
                Storage::disk($file->disk)->delete($file->thumbnail_path);
            }
            // Just soft-delete the database record, skip moving to trash
            $file->delete();

            return;
        }

        $originalPath = $file->path;
        $disk = $file->disk ?: 'public';
        $fileName = basename($originalPath);
        $trashPath = '.trash/'.uniqid().'_'.$fileName;

        try {
            Storage::disk($disk)->makeDirectory('.trash');
            if (Storage::disk($disk)->exists($originalPath)) {
                Storage::disk($disk)->move($originalPath, $trashPath);
            }

            DeletedFile::create([
                'original_path' => '/'.ltrim($originalPath, '/'),
                'trash_path' => $trashPath,
                'disk' => $disk,
                'name' => $file->name ?: $fileName,
                'type' => 'file',
                'size' => $file->size,
                'extension' => pathinfo($fileName, PATHINFO_EXTENSION),
                'mime_type' => $file->mime_type ?: 'application/octet-stream',
                'deleted_by' => Auth::id(),
                'deleted_at' => now(),
            ]);

            $file->path = $trashPath;
            $file->save();
            $file->delete();
        } catch (\Exception $e) {
            Log::channel('media')->error('Soft delete move to trash failed: '.$e->getMessage());
            $file->delete();
        }
    }

    /**
     * Restore a soft-deleted media item.
     */
    public function restore(string $fileId): ?File
    {
        $file = File::onlyTrashed()->find($fileId);
        if (! $file) {
            return null;
        }

        $deletedFile = DeletedFile::where('trash_path', $file->path)->first();
        if ($deletedFile) {
            $originalPath = ltrim((string) $deletedFile->original_path, '/');
            try {
                if (Storage::disk($file->disk)->exists($file->path)) {
                    Storage::disk($file->disk)->move($file->path, $originalPath);
                }
                $file->path = $originalPath;
                $file->save();
                $file->restore();
                $deletedFile->delete();

                return $file;
            } catch (\Exception $e) {
                Log::channel('media')->error('Restore failed: '.$e->getMessage());
            }
        }

        $file->restore();

        return $file;
    }

    /**
     * Perform bulk action on media.
     */
    public function bulkAction(string $action, array $mediaIds, ?string $folderId = null, ?string $altText = null, array $folderIds = [], bool $unlinkPhysical = true): array
    {
        $affectedMedia = 0;
        $affectedFolders = 0;

        foreach ($mediaIds as $id) {
            $file = File::withTrashed()->find($id);
            if (! $file) {
                continue;
            }

            switch ($action) {
                case 'delete': $this->delete($file, false, $unlinkPhysical);
                    break;
                case 'delete_permanent': $this->delete($file, true, $unlinkPhysical);
                    break;
                case 'restore': $this->restore($file->id);
                    break;
                case 'move': $file->update(['folder_id' => $folderId]);
                    break;
            }
            $affectedMedia++;
        }

        return ['media_count' => $affectedMedia, 'folder_count' => $affectedFolders];
    }

    /**
     * Create ZIP download from multiple media.
     */
    public function createZip(array $mediaIds): ?string
    {
        $files = File::whereIn('id', $mediaIds)->get();
        if ($files->isEmpty()) {
            return null;
        }

        $zipFileName = 'media-'.now()->format('Y-m-d-His').'.zip';
        $zipPath = storage_path('app/temp/'.$zipFileName);

        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        foreach ($files as $file) {
            $filePath = Storage::disk($file->disk)->path($file->path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $file->file_name ?: $file->name);
            }
        }
        $zip->close();

        return $zipPath;
    }

    /**
     * Get usage information for media.
     */
    public function getUsageInfo(File $file): array
    {
        return Usage::where('file_id', $file->id)->get()->toArray();
    }

    /**
     * Get media statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total_count' => File::count(),
            'total_size' => (int) File::sum('size'),
            'types' => File::selectRaw('mime_type as type, count(*) as count, sum(size) as size')
                ->groupBy('mime_type')
                ->get()
                ->map(function ($item) {
                    return [
                        'type' => ($typeRaw = $item->getAttribute('type')) && is_string($typeRaw)
                            ? explode('/', $typeRaw)[0]
                            : 'unknown',
                        'count' => is_numeric($item->getAttribute('count')) ? (int) $item->getAttribute('count') : 0,
                        'size' => is_numeric($item->getAttribute('size')) ? (int) $item->getAttribute('size') : 0,
                    ];
                })->toArray(),
            'trash_count' => File::onlyTrashed()->count(),
        ];
    }

    /**
     * Internal Helpers
     */
    protected static function intFromSetting(string $key, int $default): int
    {
        $value = Setting::get($key, $default);

        return is_numeric($value) ? (int) $value : $default;
    }

    protected static function boolFromSetting(string $key, bool $default): bool
    {
        $value = Setting::get($key, $default);
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value !== 0;
        }
        if (is_string($value)) {
            $lower = strtolower(trim($value));
            if (in_array($lower, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }
            if (in_array($lower, ['0', 'false', 'no', 'off', ''], true)) {
                return false;
            }
        }

        return $default;
    }

    protected function getImageDriver(): ?DriverInterface
    {
        if (extension_loaded('gd')) {
            return new Driver;
        }
        if (extension_loaded('imagick')) {
            return new \Intervention\Image\Drivers\Imagick\Driver;
        }

        return null;
    }

    protected function sanitizeSvg(string $filePath): void
    {
        if (! class_exists(Sanitizer::class)) {
            return;
        }
        try {
            $sanitizer = new Sanitizer;
            $content = file_get_contents($filePath);
            if ($content) {
                $cleanContent = $sanitizer->sanitize($content);
                file_put_contents($filePath, $cleanContent);
            }
        } catch (\Exception $e) {
            Log::channel('media')->error('SVG sanitization failed: '.$e->getMessage());
        }
    }

    public function syncTags(File $file, array $tags): void
    {
        if (! class_exists(Tag::class)) {
            return;
        }

        $tagIds = [];
        foreach ($tags as $tagName) {
            if (! is_scalar($tagName)) {
                continue;
            }
            $label = trim((string) $tagName);
            if (in_array($label, ['', '0'], true)) {
                continue;
            }
            $tag = Tag::firstOrCreate(['name' => $label], ['slug' => Str::slug($label)]);
            $tagIds[] = $tag->id;
        }

        $file->tags()->sync($tagIds);
    }

    protected function forceDelete(File $file): void
    {
        Storage::disk($file->disk)->delete($file->path);
        $file->forceDelete();
    }

    protected function quota(): StorageQuotaServiceInterface
    {
        return $this->storageQuota ??= app(StorageQuotaServiceInterface::class);
    }
}
