<?php

declare(strict_types=1);

namespace Modules\Core\System\Registries;

use Throwable;

class HookCrashToken
{
    /**
     * Create a new crash token instance.
     */
    public function __construct(protected Throwable $exception) {}

    /**
     * Get the trapped exception.
     */
    public function getException(): Throwable
    {
        return $this->exception;
    }
}
