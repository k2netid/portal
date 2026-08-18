<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Support\ExtensionPaths;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ScaffolderApiController extends BaseApiController
{
    /**
     * Convert slug to StudlyCase namespace.
     */
    protected function toStudlyCase(string $slug): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $slug)));
    }

    /**
     * POST /api/v1/manage/infra/cck/scaffold
     * Scaffold a new dynamic plugin.
     *
     * @return JsonResponse|BinaryFileResponse
     */
    public function scaffold(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/',
            'author' => 'required|string|max:255',
            'version' => 'required|string|max:20',
            'description' => 'nullable|string',
            'install_locally' => 'nullable|boolean',
            'routes' => 'nullable|array',
            'routes.*.method' => 'required|string|in:GET,POST,PUT,DELETE',
            'routes.*.uri' => 'required|string',
            'routes.*.action' => 'required|string|regex:/^[a-zA-Z0-9]+@[a-zA-Z0-9]+$/',
            'sidebar_menu' => 'nullable|array',
            'sidebar_menu.*.id' => 'required|string',
            'sidebar_menu.*.title' => 'required|string',
            'sidebar_menu.*.icon' => 'required|string',
            'sidebar_menu.*.group' => 'required|string',
            'sidebar_menu.*.route' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->error('Validasi Gagal', 422, $validator->errors()->toArray());
        }

        $payload = $validator->validated();

        $name = (string) $payload['name'];
        $slug = (string) $payload['slug'];
        $author = (string) $payload['author'];
        $version = (string) $payload['version'];
        $description = (string) ($payload['description'] ?? 'Scaffolded plugin.');
        $installLocally = (bool) ($payload['install_locally'] ?? false);
        $routes = (array) ($payload['routes'] ?? []);
        $sidebarMenu = (array) ($payload['sidebar_menu'] ?? []);

        $namespace = $this->toStudlyCase($slug);

        // Generate files in memory or temp dir
        $tempDir = storage_path("app/scaffolds/{$slug}");
        if (File::isDirectory($tempDir)) {
            File::deleteDirectory($tempDir);
        }
        File::makeDirectory($tempDir, 0755, true);

        // 1. Write manifest.json
        $manifest = [
            'slug' => $slug,
            'name' => $name,
            'type' => 'plugin',
            'version' => $version,
            'author' => $author,
            'description' => $description,
            'contribution_points' => [
                'routes' => $routes,
                'sidebar_menu' => $sidebarMenu,
            ],
        ];

        $manifestContent = json_encode($manifest, JSON_PRETTY_PRINT);
        if (is_string($manifestContent)) {
            File::put("{$tempDir}/manifest.json", $manifestContent);
        }

        // 2. Create directory structures
        File::makeDirectory("{$tempDir}/src/Providers", 0755, true);
        File::makeDirectory("{$tempDir}/src/Http/Controllers", 0755, true);

        // 3. Write PluginServiceProvider.php
        $serviceProviderTemplate = <<<PHP
<?php

declare(strict_types=1);

namespace Extensions\\{$namespace}\\Providers;

use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Boilerplate register logic
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Boilerplate boot logic
    }
}
PHP;

        File::put("{$tempDir}/src/Providers/PluginServiceProvider.php", $serviceProviderTemplate);

        // 4. Generate Controllers
        foreach ($routes as $route) {
            if (! is_array($route)) {
                continue;
            }

            $actionVal = $route['action'] ?? '';
            $action = is_scalar($actionVal) ? (string) $actionVal : '';
            $parts = explode('@', $action);
            if (count($parts) !== 2) {
                continue;
            }

            $controllerName = $parts[0];
            $methodName = $parts[1];

            $controllerTemplate = <<<PHP
<?php

declare(strict_types=1);

namespace Extensions\\{$namespace}\\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class {$controllerName} extends Controller
{
    /**
     * Scaffolded controller method.
     */
    public function {$methodName}(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Hello from scaffolded controller action!',
        ]);
    }
}
PHP;

            File::put("{$tempDir}/src/Http/Controllers/{$controllerName}.php", $controllerTemplate);
        }

        // 5. If install_locally is true, copy into backend/extensions/{slug}
        if ($installLocally) {
            $localPluginDir = ExtensionPaths::pluginDirectory($slug);
            if (File::isDirectory($localPluginDir)) {
                File::deleteDirectory($localPluginDir);
            }
            File::copyDirectory($tempDir, $localPluginDir);
            File::deleteDirectory($tempDir);

            $relativePath = str_replace(base_path().DIRECTORY_SEPARATOR, '', $localPluginDir);

            return $this->success([
                'slug' => $slug,
                'path' => str_replace(DIRECTORY_SEPARATOR, '/', $relativePath),
            ], 'Plugin scaffolded and installed locally successfully');
        }

        // 6. Otherwise, zip files and return binary download response
        $zipFile = storage_path("app/scaffolds/{$slug}.zip");
        if (File::exists($zipFile)) {
            File::delete($zipFile);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = File::allFiles($tempDir);
            foreach ($files as $file) {
                $relativeName = str_replace($tempDir.'/', '', $file->getPathname());
                $zip->addFile($file->getPathname(), $relativeName);
            }
            $zip->close();
        }

        File::deleteDirectory($tempDir);

        return response()->download($zipFile)->deleteFileAfterSend(true);
    }
}
