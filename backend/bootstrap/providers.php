<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\RedisConfigServiceProvider::class,
    Modules\Core\Providers\CoreServiceProvider::class,
    Modules\Core\System\Providers\ExtensionAutoloadServiceProvider::class,
];
