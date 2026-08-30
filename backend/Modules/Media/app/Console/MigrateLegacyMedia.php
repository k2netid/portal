<?php

namespace Modules\Media\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Media\Models\Folder;

class MigrateLegacyMedia extends Command
{
    protected $signature = 'media:migrate-legacy';

    protected $description = 'Migrate data from legacy core_media tables to new srv_media tables';

    public function handle(): void
    {
        $this->info('Starting media migration...');

        // 1. Migrate Folders
        $this->info('Migrating folders...');
        $oldFolders = DB::table('core_media_folders')->get();
        $folderMap = []; // old_id => new_uuid

        foreach ($oldFolders as $oldFolder) {
            $newUuid = (string) Str::uuid();
            DB::table('srv_media_folders')->insert([
                'id' => $newUuid,
                'module' => $oldFolder->module ?? 'publishing',
                'name' => $oldFolder->name,
                'slug' => $oldFolder->slug,
                'parent_id' => null, // Will update in second pass
                'sort_order' => $oldFolder->sort_order ?? 0,
                'author_id' => $oldFolder->author_id ?? null,
                'is_shared' => $oldFolder->is_shared ?? false,
                'created_at' => $oldFolder->created_at,
                'updated_at' => $oldFolder->updated_at,
            ]);
            $folderMap[$oldFolder->id] = $newUuid;
        }

        // Second pass for folder parent_id
        foreach ($oldFolders as $oldFolder) {
            if ($oldFolder->parent_id && isset($folderMap[$oldFolder->parent_id])) {
                DB::table('srv_media_folders')
                    ->where('id', $folderMap[$oldFolder->id])
                    ->update(['parent_id' => $folderMap[$oldFolder->parent_id]]);
            }
        }
        $this->info('Folders migrated: '.count($oldFolders));

        // 2. Migrate Files
        $this->info('Migrating files...');
        $oldFiles = DB::table('core_media')->get();
        $fileMap = []; // old_id => new_uuid

        foreach ($oldFiles as $oldFile) {
            $newUuid = (string) Str::uuid();
            DB::table('srv_media_files')->insert([
                'id' => $newUuid,
                'module' => $oldFile->module ?? 'publishing',
                'name' => $oldFile->name,
                'file_name' => $oldFile->file_name,
                'mime_type' => $oldFile->mime_type,
                'disk' => $oldFile->disk ?? 'public',
                'path' => $oldFile->path,
                'thumbnail_path' => $oldFile->thumbnail_path ?? null,
                'size' => $oldFile->size,
                'alt' => $oldFile->alt ?? null,
                'description' => $oldFile->description ?? null,
                'caption' => $oldFile->caption ?? null,
                'folder_id' => $oldFile->folder_id ? ($folderMap[$oldFile->folder_id] ?? null) : null,
                'author_id' => $oldFile->author_id ?? null,
                'is_shared' => $oldFile->is_shared ?? false,
                'created_at' => $oldFile->created_at,
                'updated_at' => $oldFile->updated_at,
            ]);
            $fileMap[$oldFile->id] = $newUuid;
        }
        $this->info('Files migrated: '.count($oldFiles));

        // 3. Migrate Usages
        $this->info('Migrating usages...');
        $oldUsages = DB::table('core_media_usages')->get();
        foreach ($oldUsages as $oldUsage) {
            if (isset($fileMap[$oldUsage->media_id])) {
                DB::table('srv_media_usages')->insert([
                    'id' => (string) Str::uuid(),
                    'file_id' => $fileMap[$oldUsage->media_id],
                    'usageable_type' => $oldUsage->usageable_type,
                    'usageable_id' => $oldUsage->usageable_id,
                    'field' => $oldUsage->field ?? 'unknown',
                    'created_at' => $oldUsage->created_at,
                ]);
            }
        }
        $this->info('Usages migrated: '.count($oldUsages));

        $this->info('Migration completed successfully!');

        // Save the map for later reference
        $mapPath = storage_path('media_migration_map.json');
        file_put_contents($mapPath, json_encode(['files' => $fileMap, 'folders' => $folderMap], JSON_PRETTY_PRINT));
        $this->info('Mapping saved to: '.$mapPath);
    }
}
