<?php

declare(strict_types=1);

namespace Modules\Member\Tests\Concerns;

use Modules\Core\System\Models\Setting;

trait SoftensPasswordPolicyForTests
{
    protected function softenPasswordPolicyForTests(): void
    {
        Setting::set('password_min_length', 8, 'integer', 'security');
        Setting::set('password_require_uppercase', false, 'boolean', 'security');
        Setting::set('password_require_lowercase', false, 'boolean', 'security');
        Setting::set('password_require_number', false, 'boolean', 'security');
        Setting::set('password_require_symbol', false, 'boolean', 'security');
        Setting::set('enable_captcha', false, 'boolean', 'security');
        Setting::set('enable_2fa', false, 'boolean', 'security');
    }
}
