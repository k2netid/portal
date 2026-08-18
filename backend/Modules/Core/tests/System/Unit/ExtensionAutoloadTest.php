<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Unit;

use Extensions\TestPlugin\DummyClass;
use Extensions\TestPlugin\TestPluginServiceProvider;
use Illuminate\Support\Facades\File;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Providers\ExtensionAutoloadServiceProvider;
use Modules\Core\System\Support\ExtensionPaths;
use Tests\TestCase;

class ExtensionAutoloadTest extends TestCase
{
    protected string $tempPluginPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Define temporary plugin path
        $this->tempPluginPath = ExtensionPaths::pluginDirectory('test-plugin');
    }

    protected function tearDown(): void
    {
        // Cleanup database
        Extension::where('slug', 'test-plugin')->forceDelete();

        // Cleanup temporary files and folders
        if (File::exists($this->tempPluginPath)) {
            File::deleteDirectory($this->tempPluginPath);
        }

        parent::tearDown();
    }

    /**
     * Test dynamic PSR-4 class loading for active extensions.
     */
    public function test_can_dynamically_autoload_active_extension_classes(): void
    {
        // 1. Create a dummy PHP class in the test-plugin folder
        $srcPath = $this->tempPluginPath.'/src';
        File::makeDirectory($srcPath, 0755, true, true);

        $classContent = <<<'PHP'
<?php

namespace Extensions\TestPlugin;

class DummyClass
{
    public function greet(): string
    {
        return 'Hello from PnP Engine';
    }
}
PHP;

        File::put($srcPath.'/DummyClass.php', $classContent);

        // 2. Create the active extension record in database
        Extension::create([
            'slug' => 'test-plugin',
            'type' => 'plugin',
            'name' => 'Test Plugin Autoload',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
            'author' => 'Test Author',
            'license' => 'MIT',
        ]);

        // 3. Instantiate and run the autoloader provider
        $provider = new ExtensionAutoloadServiceProvider(app());
        $provider->register();

        // 4. Verify class is auto-loaded and fully instantiable!
        $this->assertTrue(class_exists(DummyClass::class));

        $dummyInstance = new DummyClass;
        $this->assertEquals('Hello from PnP Engine', $dummyInstance->greet());
    }

    /**
     * Test active extensions list caching and automatic invalidation.
     */
    public function test_caches_active_extensions_and_invalidates_on_model_events(): void
    {
        $cacheFile = storage_path('framework/cache/active_extensions.json');

        // 1. Clear cache initially
        @unlink($cacheFile);

        // 2. Trigger cache generation by executing autoload active extensions
        $provider = new ExtensionAutoloadServiceProvider(app());

        // Setup database record
        $ext = Extension::create([
            'slug' => 'test-plugin',
            'type' => 'plugin',
            'name' => 'Test Plugin Autoload',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
            'author' => 'Test Author',
            'license' => 'MIT',
        ]);

        $provider->register();

        // 3. Verify file cache was generated
        $this->assertFileExists($cacheFile);

        $cached = json_decode(file_get_contents($cacheFile), true);
        $this->assertIsArray($cached);

        $slugs = array_column($cached, 'slug');
        $this->assertContains('test-plugin', $slugs);

        // 4. Update status to inactive and verify file cache is deleted
        $ext->update(['status' => 'inactive']);
        $this->assertFileDoesNotExist($cacheFile);
    }

    /**
     * Test dynamic service provider loading and lifecycle registration.
     */
    public function test_can_dynamically_register_service_providers_for_active_plugins(): void
    {
        // 1. Create a dummy ServiceProvider PHP class in the test-plugin folder
        $srcPath = $this->tempPluginPath.'/src';
        File::makeDirectory($srcPath, 0755, true, true);

        $providerContent = <<<'PHP'
<?php

namespace Extensions\TestPlugin;

use Illuminate\Support\ServiceProvider;

class TestPluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('test-plugin-service', function () {
            return 'fully-integrated';
        });
    }
}
PHP;

        File::put($srcPath.'/TestPluginServiceProvider.php', $providerContent);

        // 2. Create the active extension record in database
        Extension::create([
            'slug' => 'test-plugin',
            'type' => 'plugin',
            'name' => 'Test Plugin Autoload',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
            'author' => 'Test Author',
            'license' => 'MIT',
        ]);

        // Clear cached static extension data to force reload
        @unlink(storage_path('framework/cache/active_extensions.json'));

        // 3. Run the autoloader provider
        $provider = new ExtensionAutoloadServiceProvider(app());
        $provider->register();
        $reflection = new \ReflectionClass($provider);
        $reflection->setStaticPropertyValue('runtimeBooted', false);
        $provider->bootExtensionRuntime();

        // 4. Verify class is registered in autoloader and container binding is active!
        $this->assertTrue(class_exists(TestPluginServiceProvider::class));
        $this->assertTrue(app()->bound('test-plugin-service'));
        $this->assertEquals('fully-integrated', app('test-plugin-service'));
    }
}
