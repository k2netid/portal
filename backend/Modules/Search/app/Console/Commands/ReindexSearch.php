<?php

namespace Modules\Search\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\System\Console\Concerns\SkipsWhenProductInactive;
use Modules\Search\Models\SearchIndex;
use Modules\Search\Services\SearchService;

class ReindexSearch extends Command
{
    use SkipsWhenProductInactive;

    protected $signature = 'search:reindex {--clear : Clear the search index table before reindexing}';

    protected $description = 'Rebuild the full search database index for all contents, categories, and tags';

    public function handle(SearchService $searchService): int
    {
        if ($this->skipUnlessProductActive('search')) {
            return self::SUCCESS;
        }

        $this->info('Starting Search Index Rebuild...');

        if ($this->option('clear')) {
            $this->warn('Clearing existing search index...');
            SearchIndex::query()->delete();
            $this->info('Search index cleared successfully.');
        }

        $this->info('Reindexing via ports (publishing + taxonomy)...');

        $stats = $searchService->reindexAll();

        $this->newLine();
        $this->info('Search Index Rebuilt Successfully!');
        $this->table(
            ['Resource Type', 'Indexed Count'],
            [
                ['Publishing Contents', $stats['pub_contents'] ?? 0],
                ['Library Categories', $stats['pub_categories'] ?? 0],
                ['Library Tags', $stats['pub_tags'] ?? 0],
                ['System Pages & Features', $stats['system_pages'] ?? 0],
            ]
        );

        return 0;
    }
}
