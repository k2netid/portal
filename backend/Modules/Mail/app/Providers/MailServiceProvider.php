<?php

declare(strict_types=1);

namespace Modules\Mail\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Mail\Console\Commands\ProcessScheduledMailCommand;
use Modules\Mail\Http\Middleware\EnsureMailExtensionActive;
use Modules\Mail\Services\MailDispatchService;

class MailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MailDispatchService::class);
    }

    public function boot(): void
    {
        $moduleRoot = dirname(__DIR__, 2);

        $this->loadMigrationsFrom($moduleRoot.'/database/migrations');

        // Match Core macro-tier registration: api middleware group (avoids web AuthenticateSession → 419)
        Route::middleware('api')
            ->prefix('api')
            ->group($moduleRoot.'/routes/api.php');

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('mail.extension', EnsureMailExtensionActive::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessScheduledMailCommand::class,
            ]);
        }
    }
}
