<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Plugin;

class PluginController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $plugins = Plugin::latest()->get();

        return $this->success($plugins, 'Plugins retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:plugins,slug',
            'version' => 'nullable|string',
            'description' => 'nullable|string',
            'author' => 'nullable|string',
            'author_url' => 'nullable|url',
            'plugin_url' => 'nullable|url',
            'main_file' => 'nullable|string',
            'settings' => 'nullable|array',
            'priority' => 'integer|min:1|max:100',
        ]);

        $plugin = Plugin::create($validated);

        return $this->success($plugin, 'Plugin created successfully', 201);
    }

    public function show(Plugin $plugin): JsonResponse
    {
        return $this->success($plugin, 'Plugin retrieved successfully');
    }

    public function update(Request $request, Plugin $plugin): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:plugins,slug,'.$plugin->id,
            'version' => 'nullable|string',
            'description' => 'nullable|string',
            'author' => 'nullable|string',
            'author_url' => 'nullable|url',
            'plugin_url' => 'nullable|url',
            'main_file' => 'nullable|string',
            'settings' => 'nullable|array',
            'priority' => 'integer|min:1|max:100',
        ]);

        $plugin->update($validated);

        return $this->success($plugin, 'Plugin updated successfully');
    }

    public function destroy(Plugin $plugin): JsonResponse
    {
        $plugin->delete();

        return $this->success(null, 'Plugin deleted successfully');
    }

    public function activate(Plugin $plugin): JsonResponse
    {
        $plugin->activate();

        return $this->success([
            'plugin' => $plugin->fresh(),
        ], 'Plugin activated successfully');
    }

    public function deactivate(Plugin $plugin): JsonResponse
    {
        $plugin->deactivate();

        return $this->success([
            'plugin' => $plugin->fresh(),
        ], 'Plugin deactivated successfully');
    }

    public function updateSettings(Request $request, Plugin $plugin): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $plugin->update(['settings' => $validated['settings']]);

        return $this->success($plugin, 'Plugin settings updated successfully');
    }

    public function getActive(): JsonResponse
    {
        $plugins = Plugin::getActivePlugins();

        return $this->success($plugins, 'Active plugins retrieved successfully');
    }
}
