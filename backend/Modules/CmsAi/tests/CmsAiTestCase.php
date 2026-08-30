<?php

declare(strict_types=1);

namespace Modules\CmsAi\Tests;

use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\Setting;
use Tests\TestCase as BaseTestCase;

abstract class CmsAiTestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->activateCmsAiExtension();
        $this->enableGlobalAi();
    }

    protected function activateCmsAiExtension(): void
    {
        Extension::query()->updateOrCreate(
            ['slug' => 'cms-ai'],
            [
                'type' => 'module',
                'name' => 'CMS AI',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'active',
                'is_core' => false,
            ],
        );
    }

    protected function enableGlobalAi(): void
    {
        Setting::set('ai_enabled', true, 'boolean', 'ai');
        Setting::set('ai_default_provider', 'gemini', 'string', 'ai');
        Setting::set('gemini_api_key', 'test-key', 'string', 'ai');
    }
}
