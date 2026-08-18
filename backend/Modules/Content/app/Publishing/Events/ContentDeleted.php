<?php

declare(strict_types=1);

namespace Modules\Content\Publishing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $contentId,
    ) {}
}
