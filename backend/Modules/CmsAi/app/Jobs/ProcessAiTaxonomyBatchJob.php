<?php

declare(strict_types=1);

namespace Modules\CmsAi\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\CmsAi\Services\AiTaxonomyBatchService;

class ProcessAiTaxonomyBatchJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $batchId,
    ) {}

    public function handle(AiTaxonomyBatchService $batches): void
    {
        $batches->process($this->batchId);
    }
}
