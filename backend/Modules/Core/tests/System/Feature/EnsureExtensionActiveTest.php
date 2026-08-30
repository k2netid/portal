<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Middleware\EnsureExtensionActive;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\User;
use Modules\Mail\Http\Middleware\EnsureMailExtensionActive;
use Tests\TestCase;

class EnsureExtensionActiveTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();
    }

    public function test_blocks_when_extension_inactive(): void
    {
        Extension::create([
            'slug' => 'forms',
            'type' => 'module',
            'name' => 'Forms',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
        ]);

        $middleware = app(EnsureExtensionActive::class);
        $request = Request::create('/api/v1/manage/forms/health', 'GET');
        $response = $middleware->handle($request, static fn () => response('ok'), 'forms');

        $this->assertEquals(403, $response->getStatusCode());
        $payload = json_decode((string) $response->getContent(), true);
        $this->assertEquals('FORMS_EXTENSION_INACTIVE', $payload['error_code'] ?? null);
    }

    public function test_allows_when_extension_active(): void
    {
        Extension::create([
            'slug' => 'forms',
            'type' => 'module',
            'name' => 'Forms',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
        ]);

        $middleware = app(EnsureExtensionActive::class);
        $request = Request::create('/api/v1/manage/forms/health', 'GET');
        $response = $middleware->handle($request, static fn () => response('ok'), 'forms');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('ok', $response->getContent());
    }

    public function test_mail_alias_middleware_still_returns_mail_error_code(): void
    {
        Extension::create([
            'slug' => 'mail',
            'type' => 'module',
            'name' => 'JA-Mail',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
        ]);

        $middleware = app(EnsureMailExtensionActive::class);
        $request = Request::create('/api/v1/manage/mail/health', 'GET');
        $response = $middleware->handle($request, static fn () => response('ok'));

        $this->assertEquals(403, $response->getStatusCode());
        $payload = json_decode((string) $response->getContent(), true);
        $this->assertEquals('MAIL_EXTENSION_INACTIVE', $payload['error_code'] ?? null);
    }
}
