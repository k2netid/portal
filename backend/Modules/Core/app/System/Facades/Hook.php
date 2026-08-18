<?php

declare(strict_types=1);

namespace Modules\Core\System\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Core\System\Registries\HookRegistry;

/**
 * @method static void listen(string $hookName, mixed $callback, int $priority = 10)
 * @method static void register(string $hookName, mixed $callback, int $priority = 10)
 * @method static void action(string $actionName, ...$params)
 * @method static mixed filter(string $filterName, mixed $value, ...$params)
 * @method static array<int, mixed> run(string $hookName, mixed ...$params)
 * @method static mixed get(string $key)
 * @method static array<string, mixed> all()
 *
 * @see HookRegistry
 */
class Hook extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return HookRegistry::class;
    }
}
