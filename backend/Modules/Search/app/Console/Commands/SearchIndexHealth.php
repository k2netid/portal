<?php

declare(strict_types=1);

namespace Modules\Search\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\System\Console\Concerns\SkipsWhenProductInactive;
use Modules\Search\Services\SearchIndexHealthService;

class SearchIndexHealth extends Command
{
    use SkipsWhenProductInactive;
    protected $signature = 'search:index-health';

    protected $description = 'Report search index lag vs published content and active taxonomy';

    public function handle(SearchIndexHealthService $health): int
    {
        if ($this->skipUnlessProductActive('search')) {
            return self::SUCCESS;
        }

        $snapshot = $health->snapshot();

        $rows = [];
        foreach ($snapshot['resources'] as $resource) {
            $rows[] = [
                $resource['label'],
                $resource['source'],
                $resource['indexed'],
                $resource['lag'],
            ];
        }

        $this->table(
            ['Resource', 'Source count', 'Indexed', 'Lag (approx)'],
            $rows,
        );

        if (! $snapshot['in_sync']) {
            $this->warn('Index lag detected. Run: php artisan search:reindex');

            return 1;
        }

        $this->info('Search index is in sync (approximate counts).');

        return 0;
    }
}
