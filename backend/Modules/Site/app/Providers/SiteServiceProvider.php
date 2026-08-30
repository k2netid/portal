<?php

declare(strict_types=1);

namespace Modules\Site\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Public theme host. Apex `/` boot-gated by product-active status (see SpaController).
 */
class SiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Runtime HTML is served by SpaController when slug `site` is product-active.
    }
}
