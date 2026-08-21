<?php

use App\Providers\AppServiceProvider;
use App\Providers\RedisConfigServiceProvider;
use Modules\Core\Providers\CoreServiceProvider;
use Modules\Core\System\Providers\ExtensionAutoloadServiceProvider;

return [
    AppServiceProvider::class,
    RedisConfigServiceProvider::class,
    CoreServiceProvider::class,
    ExtensionAutoloadServiceProvider::class,
];
