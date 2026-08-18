<?php

use App\Providers\AppServiceProvider;
use App\Providers\RedisConfigServiceProvider;
use Modules\Content\Providers\ContentServiceProvider;
use Modules\Core\Providers\CoreServiceProvider;
use Modules\Core\System\Providers\ExtensionAutoloadServiceProvider;
use Modules\Intelligence\Providers\IntelligenceServiceProvider;
use Modules\Operational\Providers\OperationalServiceProvider;

return [
    AppServiceProvider::class,
    RedisConfigServiceProvider::class,
    CoreServiceProvider::class,
    ExtensionAutoloadServiceProvider::class,
    ContentServiceProvider::class,
    IntelligenceServiceProvider::class,
    OperationalServiceProvider::class,
];
