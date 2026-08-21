<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Modules\Core\System\Models\Setting;

/**
 * One-time data migration: legacy `admin_dashboard_slug` → canonical `console_dashboard_slug`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sys_settings')) {
            return;
        }

        $adminRows = Setting::query()
            ->where('key', Setting::LEGACY_KEY_ADMIN_DASHBOARD_SLUG)
            ->get();

        foreach ($adminRows as $adminRow) {
            $slug = trim((string) $adminRow->value);

            if ($slug === '') {
                $adminRow->delete();

                continue;
            }

            $existingConsole = Setting::query()
                ->where('key', Setting::KEY_CONSOLE_DASHBOARD_SLUG)
                ->first();

            $description = 'Console dashboard URL prefix';
            $isPublic = true;
            if ($existingConsole instanceof Setting) {
                $description = is_string($existingConsole->description) ? $existingConsole->description : 'Console dashboard URL prefix';
                $isPublic = (bool) $existingConsole->is_public;
            }

            Setting::updateOrCreate(
                ['key' => Setting::KEY_CONSOLE_DASHBOARD_SLUG],
                [
                    'value' => $slug,
                    'type' => 'string',
                    'group' => 'security',
                    'description' => $description,
                    'is_public' => $isPublic,
                ]
            );

            $adminRow->delete();
        }
    }

    public function down(): void
    {
        // Data migration is intentionally irreversible.
    }
};
