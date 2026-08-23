<?php

declare(strict_types=1);

namespace Modules\Publishing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Publishing\Models\Content;

class ContentUnpublished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Content $content,
    ) {}
}
