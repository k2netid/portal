<?php

declare(strict_types=1);

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\System\Traits\CoreLogsActivity;

/**
 * @property string $id
 * @property string|null $parent_id
 * @property string $group_slug
 * @property string $name
 * @property string|null $label_key
 * @property string|null $route_name
 * @property string|null $url
 * @property string|null $icon
 * @property string|null $permission
 * @property string|null $role
 * @property string|null $extension_slug
 * @property string|null $badge_text
 * @property string $badge_variant
 * @property int $order
 * @property bool $is_visible
 * @property array<string, mixed>|null $meta
 */
class ConsoleMenu extends Model
{
    use HasUuids;
    use CoreLogsActivity;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_console_menus';

    protected $fillable = [
        'parent_id',
        'group_slug',
        'name',
        'label_key',
        'route_name',
        'url',
        'icon',
        'permission',
        'role',
        'extension_slug',
        'badge_text',
        'badge_variant',
        'order',
        'is_visible',
        'meta',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'order' => 'integer',
        'meta' => 'array',
    ];

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order', 'asc');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Factory default console menus.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getDefaultMenus(): array
    {
        return [
            // Group: Identity & Access
            [
                'group_slug' => 'identity',
                'name' => 'Users & Access',
                'label_key' => 'system.navigation.sections.usersAccess',
                'icon' => 'users',
                'order' => 10,
                'children' => [
                    [
                        'name' => 'KYC Reviews',
                        'label_key' => 'system.navigation.menu.kycReviews',
                        'route_name' => 'kyc-reviews',
                        'icon' => 'user-check',
                        'permission' => 'manage kyc reviews',
                        'order' => 1,
                    ],
                    [
                        'name' => 'Users',
                        'label_key' => 'system.navigation.menu.users',
                        'route_name' => 'users.index',
                        'icon' => 'users',
                        'permission' => 'view users',
                        'order' => 2,
                    ],
                    [
                        'name' => 'Roles & Permissions',
                        'label_key' => 'system.navigation.menu.roles',
                        'route_name' => 'roles',
                        'icon' => 'shield',
                        'permission' => 'view roles',
                        'order' => 3,
                    ],
                ],
            ],

            // Group: Communications
            [
                'group_slug' => 'communications',
                'name' => 'Communications',
                'label_key' => 'system.navigation.sections.communications',
                'icon' => 'mail',
                'order' => 15,
                'children' => [
                    [
                        'name' => 'JA-Mail',
                        'label_key' => 'system.navigation.menu.mail',
                        'route_name' => 'mail',
                        'icon' => 'mail',
                        'permission' => 'manage system',
                        'extension_slug' => 'mail',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 1,
                    ],
                    [
                        'name' => 'Notifications',
                        'label_key' => 'system.navigation.menu.systemNotifications',
                        'route_name' => 'system-notifications',
                        'icon' => 'bell',
                        'permission' => 'manage system',
                        'order' => 2,
                    ],
                ],
            ],

            // Group: Observability & Journals
            [
                'group_slug' => 'observability',
                'name' => 'Journals',
                'label_key' => 'sharedConsole.navigation.menu.journals',
                'icon' => 'book-open',
                'order' => 20,
                'children' => [
                    [
                        'name' => 'Journal Dashboard',
                        'label_key' => 'system.navigation.menu.journalDashboard',
                        'route_name' => 'journal-dashboard',
                        'icon' => 'activity',
                        'permission' => 'view logs',
                        'role' => 'super',
                        'order' => 1,
                    ],
                    [
                        'name' => 'Activity Journal',
                        'label_key' => 'system.navigation.menu.activityJournal',
                        'route_name' => 'activity-journal',
                        'icon' => 'file-text',
                        'permission' => 'view activity logs',
                        'role' => 'super',
                        'order' => 2,
                    ],
                    [
                        'name' => 'System Journal',
                        'label_key' => 'system.navigation.menu.systemJournal',
                        'route_name' => 'system-journal',
                        'icon' => 'terminal',
                        'permission' => 'view system logs',
                        'role' => 'super',
                        'order' => 3,
                    ],
                    [
                        'name' => 'Access History',
                        'label_key' => 'system.navigation.menu.accessJournal',
                        'route_name' => 'access-journal',
                        'icon' => 'key',
                        'permission' => 'view users',
                        'role' => 'super',
                        'order' => 4,
                    ],
                ],
            ],

            // Group: System Config
            [
                'group_slug' => 'system_config',
                'name' => 'System Config',
                'label_key' => 'sharedConsole.navigation.menu.systemConfig',
                'icon' => 'sliders',
                'order' => 30,
                'children' => [
                    [
                        'name' => 'System Settings',
                        'label_key' => 'system.navigation.menu.settings',
                        'route_name' => 'settings',
                        'icon' => 'settings',
                        'permission' => 'view settings',
                        'order' => 1,
                    ],
                    [
                        'name' => 'Console Appearance',
                        'label_key' => 'system.navigation.menu.consoleAppearance',
                        'route_name' => 'settings-console-appearance',
                        'icon' => 'palette',
                        'permission' => 'manage settings',
                        'order' => 2,
                    ],
                    [
                        'name' => 'Menu Editor',
                        'label_key' => 'system.navigation.menu.menuEditor',
                        'route_name' => 'settings-menus',
                        'icon' => 'menu',
                        'permission' => 'manage settings',
                        'role' => 'super',
                        'badge_text' => 'NEW',
                        'badge_variant' => 'emerald',
                        'order' => 3,
                    ],
                    [
                        'name' => 'Languages',
                        'label_key' => 'system.navigation.menu.languages',
                        'route_name' => 'languages',
                        'icon' => 'languages',
                        'permission' => 'view settings',
                        'order' => 4,
                    ],
                ],
            ],

            // Group: Infrastructure
            [
                'group_slug' => 'infrastructure',
                'name' => 'Infrastructure',
                'label_key' => 'system.navigation.sections.infrastructure',
                'icon' => 'cpu',
                'order' => 40,
                'children' => [
                    [
                        'name' => 'System Info',
                        'label_key' => 'system.navigation.menu.systemInfo',
                        'route_name' => 'system',
                        'icon' => 'info',
                        'permission' => 'manage system',
                        'order' => 1,
                    ],
                    [
                        'name' => 'Redis Status',
                        'label_key' => 'system.navigation.menu.redis',
                        'route_name' => 'redis',
                        'icon' => 'database',
                        'permission' => 'manage settings',
                        'order' => 2,
                    ],
                    [
                        'name' => 'Scheduled Tasks',
                        'label_key' => 'system.scheduled_tasks.title',
                        'route_name' => 'scheduled-tasks',
                        'icon' => 'clock',
                        'permission' => 'manage scheduled tasks',
                        'order' => 3,
                    ],
                    [
                        'name' => 'Extensions',
                        'label_key' => 'system.extensions.title',
                        'route_name' => 'settings-extensions',
                        'icon' => 'box',
                        'permission' => 'manage extensions',
                        'order' => 4,
                    ],
                ],
            ],
        ];
    }

    /**
     * Seed or reset default console menus.
     */
    public static function seedDefaults(bool $forceReset = false): void
    {
        if ($forceReset) {
            self::truncate();
        } elseif (self::count() > 0) {
            return;
        }

        $defaults = self::getDefaultMenus();

        foreach ($defaults as $groupIndex => $group) {
            $children = $group['children'] ?? [];
            unset($group['children']);

            $parent = self::create([
                'group_slug' => $group['group_slug'],
                'name' => $group['name'],
                'label_key' => $group['label_key'] ?? null,
                'icon' => $group['icon'] ?? 'folder',
                'order' => $group['order'] ?? ($groupIndex * 10),
                'is_visible' => true,
            ]);

            foreach ($children as $childIndex => $child) {
                self::create([
                    'parent_id' => $parent->id,
                    'group_slug' => $group['group_slug'],
                    'name' => $child['name'],
                    'label_key' => $child['label_key'] ?? null,
                    'route_name' => $child['route_name'] ?? null,
                    'url' => $child['url'] ?? null,
                    'icon' => $child['icon'] ?? 'circle',
                    'permission' => $child['permission'] ?? null,
                    'role' => $child['role'] ?? null,
                    'extension_slug' => $child['extension_slug'] ?? null,
                    'badge_text' => $child['badge_text'] ?? null,
                    'badge_variant' => $child['badge_variant'] ?? 'primary',
                    'order' => $child['order'] ?? $childIndex,
                    'is_visible' => true,
                ]);
            }
        }
    }
}
