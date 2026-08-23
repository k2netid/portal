<?php

declare(strict_types=1);

namespace Modules\Layout\Console\Commands;

use Illuminate\Console\Command;
use Modules\Layout\Services\ThemeService;

class ThemeScanRegisterCommand extends Command
{
    protected $signature = 'theme:scan-register';

    protected $description = 'Scan theme directories and register/update lay_themes records';

    public function handle(ThemeService $themeService): int
    {
        $themes = $themeService->scanThemes();
        $this->info('Registered '.count($themes).' theme(s):');
        foreach ($themes as $theme) {
            $this->line("  - {$theme->slug} ({$theme->source})");
        }

        return self::SUCCESS;
    }
}
