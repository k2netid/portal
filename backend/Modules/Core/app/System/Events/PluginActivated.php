<?php

declare(strict_types=1);

namespace Modules\Core\System\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\System\Models\Plugin;

class PluginActivated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Plugin $plugin) {}
}
