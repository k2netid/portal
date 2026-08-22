<?php

declare(strict_types=1);

namespace Modules\Mail\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Core\System\Contracts\OutboundMailPortInterface;
use Modules\Mail\Console\Commands\ProcessScheduledMailCommand;
use Modules\Mail\Console\Commands\ProcessSnoozedMailCommand;
use Modules\Mail\Console\Commands\PurgeTrashMailCommand;
use Modules\Mail\Contracts\MailInboundSyncInterface;
use Modules\Mail\Events\MailMessageFailed;
use Modules\Mail\Events\VacationAutoReplySent;
use Modules\Mail\Http\Middleware\EnsureMailExtensionActive;
use Modules\Mail\Listeners\NotifyUserOnMailFailure;
use Modules\Mail\Listeners\NotifyUserOnVacationAutoReply;
use Modules\Mail\Services\LocalIndexInboundSync;
use Modules\Mail\Services\MailDispatchService;
use Modules\Mail\Services\OutboundMailDispatcher;

class MailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/config.php', 'mail_module');

        $this->app->singleton(MailDispatchService::class);
        $this->app->singleton(OutboundMailPortInterface::class, OutboundMailDispatcher::class);
        $this->app->singleton(MailInboundSyncInterface::class, LocalIndexInboundSync::class);
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

        Event::listen(MailMessageFailed::class, NotifyUserOnMailFailure::class);
        Event::listen(VacationAutoReplySent::class, NotifyUserOnVacationAutoReply::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessScheduledMailCommand::class,
                ProcessSnoozedMailCommand::class,
                PurgeTrashMailCommand::class,
            ]);
        }
    }
}
