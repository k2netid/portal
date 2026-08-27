<?php

declare(strict_types=1);

namespace Modules\Member\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Member\Services\SanctumMemberIdentityPort;
use Modules\Publishing\Contracts\MemberIdentityPort;

class MemberServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MemberIdentityPort::class, SanctumMemberIdentityPort::class);
    }

    public function boot(): void
    {
        $moduleRoot = dirname(__DIR__, 2);

        $this->loadMigrationsFrom($moduleRoot.'/database/migrations');

        Route::middleware('api')
            ->prefix('api')
            ->group($moduleRoot.'/routes/api.php');
    }
}
