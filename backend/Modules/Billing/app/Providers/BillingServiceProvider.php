<?php

declare(strict_types=1);

namespace Modules\Billing\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $configFile = dirname(__DIR__, 2).'/config/billing.php';
        if (file_exists($configFile)) {
            $this->mergeConfigFrom($configFile, 'billing');
        }
    }

    public function boot(): void
    {
        $moduleRoot = dirname(__DIR__, 2);

        Route::middleware('api')
            ->prefix('api')
            ->group($moduleRoot.'/routes/api.php');
    }
}
