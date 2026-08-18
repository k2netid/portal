<?php

declare(strict_types=1);

namespace Modules\Core\System\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Core\System\Services\SandboxStorage as SandboxStorageService;

/**
 * @method static \Illuminate\Contracts\Filesystem\Filesystem for(string $slug)
 *
 * @see SandboxStorageService
 */
class SandboxStorage extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return SandboxStorageService::class;
    }
}
