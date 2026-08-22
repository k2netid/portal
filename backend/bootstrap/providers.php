<?php

use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\RedisConfigServiceProvider;
use Modules\Core\Providers\CoreServiceProvider;
use Modules\Core\System\Providers\ExtensionAutoloadServiceProvider;

return [
    AppServiceProvider::class,
    HorizonServiceProvider::class,
    RedisConfigServiceProvider::class,
    CoreServiceProvider::class,
    ExtensionAutoloadServiceProvider::class,
];
