<?php

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\System\Events\PluginActivated;
use Modules\Core\System\Events\PluginDeactivated;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $version
 * @property string|null $description
 * @property string|null $author
 * @property string|null $author_url
 * @property string|null $plugin_url
 * @property string|null $main_file
 * @property array<string, mixed>|null $settings
 * @property bool $is_active
 * @property int $priority
 */
class Plugin extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_plugins';

    protected $fillable = [
        'name',
        'slug',
        'version',
        'description',
        'author',
        'author_url',
        'plugin_url',
        'main_file',
        'settings',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function activate(): void
    {
        $this->update(['is_active' => true]);
        // Trigger plugin activation hook
        event(new PluginActivated($this));
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
        // Trigger plugin deactivation hook
        event(new PluginDeactivated($this));
    }

    /**
     * @return Collection<int, self>
     */
    public static function getActivePlugins(): Collection
    {
        return self::where('is_active', true)
            ->orderBy('priority')
            ->get();
    }
}
