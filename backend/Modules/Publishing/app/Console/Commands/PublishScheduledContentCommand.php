<?php

declare(strict_types=1);

namespace Modules\Publishing\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Core\System\Console\Concerns\SkipsWhenProductInactive;
use Modules\Publishing\Models\Content;
use Modules\Publishing\Services\ContentService;

class PublishScheduledContentCommand extends Command
{
    use SkipsWhenProductInactive;

    protected $signature = 'content:publish-scheduled';

    protected $description = 'Publish content with status=scheduled when published_at is due';

    public function handle(ContentService $contentService): int
    {
        if ($this->skipUnlessProductActive('publishing')) {
            return self::SUCCESS;
        }
        $due = Content::withoutGlobalScopes()
            ->where('status', 'scheduled')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now())
            ->get();

        if ($due->isEmpty()) {
            $this->info('No scheduled content due.');

            return self::SUCCESS;
        }

        $count = 0;
        foreach ($due as $content) {
            $contentService->publishScheduled($content);
            $count++;
            $this->line("Published: {$content->id} ({$content->title})");
        }

        $this->info("Published {$count} item(s).");

        return self::SUCCESS;
    }
}
