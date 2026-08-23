<?php

declare(strict_types=1);

namespace Modules\Forms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormSubmission;

class FormSubmitted
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly Form $form,
        public readonly FormSubmission $submission,
        public readonly array $payload,
    ) {}
}
