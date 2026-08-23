<?php

declare(strict_types=1);

namespace Modules\Layout\Console\Commands;

use Illuminate\Console\Command;
use Modules\Layout\Models\Theme;
use Modules\Layout\Services\ThemeService;

class ThemeValidateCommand extends Command
{
    protected $signature = 'theme:validate {slug? : Theme slug (optional, validates all if omitted)}';

    protected $description = 'Validate theme package structure and manifest';

    public function handle(ThemeService $themeService): int
    {
        $slug = $this->argument('slug');
        $query = Theme::query();
        if (is_string($slug) && $slug !== '') {
            $query->where('slug', $slug);
        }

        $themes = $query->get();
        if ($themes->isEmpty()) {
            $this->warn('No themes found.');

            return self::FAILURE;
        }

        $hasErrors = false;
        foreach ($themes as $theme) {
            $errors = $themeService->validateTheme($theme);
            if ($errors === []) {
                $this->info("✓ {$theme->slug} ({$theme->source})");

                continue;
            }
            $hasErrors = true;
            $this->error("✗ {$theme->slug}");
            foreach ($errors as $err) {
                $this->line('  - '.$err);
            }
        }

        return $hasErrors ? self::FAILURE : self::SUCCESS;
    }
}
