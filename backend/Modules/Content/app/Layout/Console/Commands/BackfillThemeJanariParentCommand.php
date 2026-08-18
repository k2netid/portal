<?php

namespace Modules\Content\Layout\Console\Commands;

use Illuminate\Console\Command;
use Modules\Content\Layout\Models\Theme;
use Modules\Content\Layout\Services\ThemeService;
use Modules\Content\Layout\Support\ThemeViews;

/**
 * Tema frontend yang tidak punya folder `views/themes/{slug}` di paket Vue (mis. hanya baris DB)
 * sebelumnya bisa memakai komponen Janari lewat fallback resolver. Fallback itu sudah dihapus;
 * perintah ini membantu mengisi `parent_theme` agar ThemePageResolver memuat komponen induk.
 */
class BackfillThemeJanariParentCommand extends Command
{
    protected $signature = 'layout:themes:backfill-janari-parent
                            {--apply : Tulis parent_theme ke database (tanpa flag ini hanya laporan)}
                            {--parent=janari : Slug tema induk (folder harus ada dan punya theme.json)}';

    protected $description = 'Set parent_theme ke tema Vue yang ada di disk untuk baris tema tanpa folder sendiri (mis. migrasi dari fallback Janari)';

    public function handle(ThemeService $themeService): int
    {
        $parentSlug = (string) $this->option('parent');
        $parentSlug = trim($parentSlug) !== '' ? trim($parentSlug) : 'janari';

        if (! $this->themeFolderLooksValid($parentSlug)) {
            $this->error("Folder tema induk tidak valid untuk slug \"{$parentSlug}\" (harus ada direktori + theme.json di ".ThemeViews::pathForSlug($parentSlug).').');

            return self::FAILURE;
        }

        $candidates = Theme::query()
            ->where('type', 'frontend')
            ->whereNull('parent_theme')
            ->where('slug', '!=', $parentSlug)
            ->orderBy('slug')
            ->get()
            ->filter(fn (Theme $t): bool => ! $this->themeFolderLooksValid($t->slug));

        if ($candidates->isEmpty()) {
            $this->info('Tidak ada tema frontend yang cocok (sudah punya folder sendiri atau sudah punya parent_theme).');

            return self::SUCCESS;
        }

        $this->warn('Tema berikut tidak punya folder tema di disk dan parent_theme masih kosong:');
        $this->table(
            ['id', 'slug', 'name', 'status'],
            $candidates->map(fn (Theme $t): array => [
                $t->id,
                $t->slug,
                $t->name,
                $t->status,
            ])->all()
        );

        if (! $this->option('apply')) {
            $this->newLine();
            $this->comment('Dry-run: tidak ada perubahan database. Jalankan lagi dengan --apply untuk mengisi parent_theme = "'.$parentSlug.'".');

            return self::SUCCESS;
        }

        if ($this->input->isInteractive() && ! $this->confirm('Set parent_theme = "'.$parentSlug.'" untuk '.$candidates->count().' tema di atas?', true)) {
            $this->warn('Dibatalkan.');

            return self::SUCCESS;
        }

        foreach ($candidates as $theme) {
            $theme->update(['parent_theme' => $parentSlug]);
            $themeService->clearThemeCache($theme->fresh());
            $this->line("OK: {$theme->slug} → parent_theme = {$parentSlug}");
        }

        $this->info('Selesai. Kosongkan cache CDN/browser jika tema publik di-cache.');

        return self::SUCCESS;
    }

    private function themeFolderLooksValid(string $slug): bool
    {
        if ($slug === '') {
            return false;
        }

        $path = ThemeViews::pathForSlug($slug);

        return is_dir($path) && is_file($path.DIRECTORY_SEPARATOR.'theme.json');
    }
}
