<?php

declare(strict_types=1);

namespace Modules\Core\System\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\System\Services\ExtensionBootstrapService;

class ExtensionDiscoverCommand extends Command
{
    protected $signature = 'extension:discover {--quiet-summary : Only print summary}';

    protected $description = 'Discover and synchronize modules, plugins, and theme packs into sys_extensions (ADR-023)';

    public function handle(ExtensionBootstrapService $bootstrap): int
    {
        $this->info('Discovering modules, plugins, and theme packs…');
        $count = $bootstrap->discover();
        $this->info("Successfully discovered and synchronized {$count} extensions into sys_extensions.");

        return self::SUCCESS;
    }
}
