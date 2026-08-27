<?php

declare(strict_types=1);

namespace Modules\Site\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Public theme runtime. Routes live in web.php (/site). No console IAM.
 */
class SiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Theme runtime is the frontend public.html entry at /site.
    }
}
