<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Console\Commands;

use Illuminate\Console\Command;
use Modules\Content\Layout\Support\ThemeViews;

class ThemePathsCommand extends Command
{
    protected $signature = 'theme:paths';

    protected $description = 'Show resolved filesystem path for Vue theme packages';

    public function handle(): int
    {
        $this->info('Theme scan roots:');
        foreach (ThemeViews::scanRootPaths() as $root) {
            $this->line(sprintf('  [%s] %s', $root['source'], $root['path']));
        }
        $this->newLine();

        $diag = ThemeViews::diagnostics();
        $this->info('Primary theme views resolution');
        $this->line('  Environment: '.$diag['environment']);
        $this->line('  Relative:    '.$diag['relative']);
        $this->line('  Absolute:    '.$diag['absolute']);
        $this->line('  Exists:      '.($diag['exists'] ? 'yes' : 'no'));

        if (! $diag['exists']) {
            $this->warn('Theme root is missing. Set PUBLISHING_THEME_VIEWS_RELATIVE_PATH for split deployments.');

            return self::FAILURE;
        }

        $slugs = array_filter(scandir($diag['absolute']) ?: [], static fn (string $f): bool => ! in_array($f, ['.', '..'], true) && is_dir($diag['absolute'].DIRECTORY_SEPARATOR.$f));
        $this->line('  Theme dirs:  '.count($slugs).' ('.implode(', ', array_slice($slugs, 0, 8)).(count($slugs) > 8 ? ', …' : '').')');

        return self::SUCCESS;
    }
}
