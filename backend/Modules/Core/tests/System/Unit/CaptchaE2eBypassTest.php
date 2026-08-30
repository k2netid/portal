<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Unit;

use Illuminate\Http\Request;
use Modules\Core\System\Services\CaptchaService;
use Tests\TestCase;

class CaptchaE2eBypassTest extends TestCase
{
    public function test_matching_header_bypasses_captcha_in_testing(): void
    {
        $this->assertFalse(CaptchaService::isE2eBypassed());

        $this->app->instance('request', Request::create('/', 'GET', server: [
            'HTTP_X_E2E_CAPTCHA_BYPASS' => 'local-e2e',
        ]));

        $this->assertTrue(CaptchaService::isE2eBypassed());
        $this->assertFalse(CaptchaService::isEnabled('login'));
    }

    public function test_wrong_header_does_not_bypass_captcha(): void
    {
        $this->app->instance('request', Request::create('/', 'GET', server: [
            'HTTP_X_E2E_CAPTCHA_BYPASS' => 'nope',
        ]));

        $this->assertFalse(CaptchaService::isE2eBypassed());
    }

    public function test_production_never_bypasses_captcha(): void
    {
        $this->app['env'] = 'production';
        $this->app->instance('request', Request::create('/', 'GET', server: [
            'HTTP_X_E2E_CAPTCHA_BYPASS' => 'local-e2e',
        ]));

        $this->assertFalse(CaptchaService::isE2eBypassed());
    }
}
