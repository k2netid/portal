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
    use CoreLogsActivity;
    use HasUuids;

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

    /**
     * @return HasMany<ConsoleMenu, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order', 'asc');
    }

    /**
     * @return BelongsTo<ConsoleMenu, $this>
     */
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
            // Group: Data Studio
            [
                'group_slug' => 'studio',
                'name' => 'Data Model Studio',
                'label_key' => 'infra.models.title',
                'icon' => 'layers',
                'order' => 5,
                'children' => [
                    [
                        'name' => 'Data Models',
                        'label_key' => 'infra.models.title',
                        'route_name' => 'model-index',
                        'icon' => 'layers',
                        'permission' => 'manage settings',
                        'order' => 1,
                    ],
                ],
            ],

            // Group: Editorial (Publishing pack — visible when extension active)
            [
                'group_slug' => 'editorial',
                'name' => 'Editorial',
                'label_key' => 'sharedConsole.navigation.menu.editorial',
                'icon' => 'file-text',
                'order' => 6,
                'children' => [
                    [
                        'name' => 'Content',
                        'label_key' => 'publishing.navigation.menu.studio',
                        'route_name' => 'contents.index',
                        'icon' => 'file-text',
                        'permission' => 'view content',
                        'extension_slug' => 'publishing',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 1,
                    ],
                    [
                        'name' => 'Categories',
                        'label_key' => 'publishing.navigation.menu.categories',
                        'route_name' => 'categories.index',
                        'icon' => 'folder',
                        'permission' => 'view categories',
                        'extension_slug' => 'publishing',
                        'order' => 2,
                    ],
                    [
                        'name' => 'Comments',
                        'label_key' => 'publishing.navigation.menu.comments',
                        'route_name' => 'comments.index',
                        'icon' => 'message-square',
                        'permission' => 'view comments',
                        'extension_slug' => 'publishing',
                        'order' => 3,
                    ],
                    [
                        'name' => 'Media Library',
                        'label_key' => 'sharedConsole.navigation.menu.mediaLibrary',
                        'route_name' => 'media',
                        'icon' => 'image',
                        'permission' => 'view media',
                        'extension_slug' => 'media',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 4,
                    ],
                    [
                        'name' => 'Site Editor',
                        'label_key' => 'layout.navigation.menu.siteEditor',
                        'route_name' => 'builder.site',
                        'icon' => 'layout',
                        'permission' => 'edit content',
                        'extension_slug' => 'layout',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 5,
                    ],
                    [
                        'name' => 'Themes',
                        'label_key' => 'publishing.navigation.menu.themes',
                        'route_name' => 'themes',
                        'icon' => 'palette',
                        'permission' => 'manage themes',
                        'extension_slug' => 'layout',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 6,
                    ],
                    [
                        'name' => 'Menus',
                        'label_key' => 'layout.navigation.menu.menus',
                        'route_name' => 'menus',
                        'icon' => 'menu',
                        'permission' => 'view menus',
                        'extension_slug' => 'layout',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 7,
                    ],
                    [
                        'name' => 'Widgets',
                        'label_key' => 'layout.navigation.menu.widgets',
                        'route_name' => 'widgets',
                        'icon' => 'layers',
                        'permission' => 'view widgets',
                        'extension_slug' => 'layout',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 8,
                    ],
                    [
                        'name' => 'SEO',
                        'label_key' => 'publishing.navigation.menu.seo',
                        'route_name' => 'publishing.seo',
                        'icon' => 'globe',
                        'permission' => 'view seo',
                        'extension_slug' => 'publishing',
                        'order' => 9,
                    ],
                    [
                        'name' => 'Publishing Settings',
                        'label_key' => 'publishing.navigation.menu.publishingSettings',
                        'route_name' => 'publishing-settings',
                        'icon' => 'settings',
                        'permission' => 'view settings',
                        'extension_slug' => 'publishing',
                        'order' => 10,
                    ],
                ],
            ],

            // Group: Insight (Analytics pack)
            [
                'group_slug' => 'insight',
                'name' => 'Insight',
                'label_key' => 'sharedConsole.navigation.menu.insight',
                'icon' => 'bar-chart-2',
                'order' => 7,
                'children' => [
                    [
                        'name' => 'Analytics',
                        'label_key' => 'sharedConsole.navigation.menu.analytics',
                        'route_name' => 'analytics',
                        'icon' => 'bar-chart',
                        'permission' => 'view analytics',
                        'extension_slug' => 'analytics',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 1,
                    ],
                    [
                        'name' => 'Search',
                        'label_key' => 'search.navigation.menu.search',
                        'route_name' => 'search',
                        'icon' => 'search',
                        'permission' => 'manage search',
                        'extension_slug' => 'search',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 2,
                    ],
                    [
                        'name' => 'AI Assistant',
                        'label_key' => 'ai.navigation.panel',
                        'route_name' => 'ai-panel',
                        'icon' => 'sparkles',
                        'permission' => 'manage settings',
                        'extension_slug' => 'cms-ai',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 3,
                    ],
                ],
            ],

            // Group: Library (taxonomy pack — visible when extension active)
            [
                'group_slug' => 'library',
                'name' => 'Library',
                'label_key' => 'sharedConsole.navigation.menu.library',
                'icon' => 'tags',
                'order' => 7,
                'children' => [
                    [
                        'name' => 'Tags',
                        'label_key' => 'library.navigation.menu.tags',
                        'route_name' => 'tags',
                        'icon' => 'tags',
                        'permission' => 'manage tags',
                        'extension_slug' => 'library',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 1,
                    ],
                    [
                        'name' => 'Custom Fields',
                        'label_key' => 'library.navigation.menu.customFields',
                        'route_name' => 'custom-fields',
                        'icon' => 'layers',
                        'permission' => 'manage tags',
                        'extension_slug' => 'library',
                        'order' => 2,
                    ],
                ],
            ],

            // Group: Audience (Forms pack)
            [
                'group_slug' => 'audience',
                'name' => 'Audience',
                'label_key' => 'forms.navigation.menu.audience',
                'icon' => 'users',
                'order' => 8,
                'children' => [
                    [
                        'name' => 'Members',
                        'label_key' => 'member.navigation.menu.members',
                        'route_name' => 'members.index',
                        'icon' => 'user',
                        'permission' => 'view members',
                        'extension_slug' => 'member',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 0,
                    ],
                    [
                        'name' => 'Forms',
                        'label_key' => 'forms.navigation.menu.forms',
                        'route_name' => 'forms',
                        'icon' => 'clipboard-list',
                        'permission' => 'view forms',
                        'extension_slug' => 'forms',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 1,
                    ],
                    [
                        'name' => 'Newsletter',
                        'label_key' => 'newsletter.navigation.menu.newsletter',
                        'route_name' => 'newsletter',
                        'icon' => 'mail',
                        'permission' => 'view newsletter',
                        'extension_slug' => 'newsletter',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 2,
                    ],
                    [
                        'name' => 'Email Templates',
                        'label_key' => 'newsletter.navigation.menu.emailTemplates',
                        'route_name' => 'email-templates',
                        'icon' => 'layout',
                        'permission' => 'manage settings',
                        'extension_slug' => 'newsletter',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 3,
                    ],
                ],
            ],

            // Group: Users & Access
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
                        'permission' => 'use mail',
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
                        'name' => 'Security Journal',
                        'label_key' => 'system.navigation.menu.securityJournal',
                        'route_name' => 'security-journal',
                        'icon' => 'shield',
                        'permission' => 'view security logs',
                        'role' => 'super',
                        'order' => 3,
                    ],
                    [
                        'name' => 'System Journal',
                        'label_key' => 'system.navigation.menu.systemJournal',
                        'route_name' => 'system-journal',
                        'icon' => 'terminal',
                        'permission' => 'view system logs',
                        'role' => 'super',
                        'order' => 4,
                    ],
                    [
                        'name' => 'Access History',
                        'label_key' => 'system.navigation.menu.accessJournal',
                        'route_name' => 'access-journal',
                        'icon' => 'key',
                        'permission' => 'view users',
                        'role' => 'super',
                        'order' => 5,
                    ],
                ],
            ],

            // Group: System Config
            [
                'group_slug' => 'system_config',
                'name' => 'Configuration',
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
                        'name' => 'File Manager',
                        'label_key' => 'infra.fileManager.title',
                        'route_name' => 'file-manager',
                        'icon' => 'folder',
                        'permission' => 'manage settings',
                        'role' => 'super',
                        'order' => 2,
                    ],
                    [
                        'name' => 'Redirects',
                        'label_key' => 'layout.navigation.menu.redirects',
                        'route_name' => 'redirects',
                        'icon' => 'undo',
                        'permission' => 'view redirects',
                        'extension_slug' => 'layout',
                        'badge_text' => 'PRO',
                        'badge_variant' => 'primary',
                        'order' => 3,
                    ],
                    [
                        'name' => 'Backups',
                        'label_key' => 'system.navigation.menu.backups',
                        'route_name' => 'backups',
                        'icon' => 'database',
                        'permission' => 'view backups',
                        'role' => 'super',
                        'order' => 4,
                    ],
                    [
                        'name' => 'Redis Status',
                        'label_key' => 'system.navigation.menu.redis',
                        'route_name' => 'redis',
                        'icon' => 'activity',
                        'permission' => 'manage settings',
                        'role' => 'super',
                        'order' => 5,
                    ],
                    [
                        'name' => 'Scheduled Tasks',
                        'label_key' => 'system.scheduled_tasks.title',
                        'route_name' => 'scheduled-tasks',
                        'icon' => 'clock',
                        'permission' => 'manage scheduled tasks',
                        'role' => 'super',
                        'order' => 6,
                    ],
                ],
            ],

            // Group: Identity & Integrations
            [
                'group_slug' => 'integrations_dev',
                'name' => 'Identity & Integrations',
                'label_key' => 'system.navigation.menu.identityIntegrations',
                'icon' => 'code',
                'order' => 50,
                'children' => [
                    [
                        'name' => 'Extensions & App Store',
                        'label_key' => 'system.navigation.menu.extensions',
                        'route_name' => 'extensions',
                        'icon' => 'box',
                        'permission' => 'manage settings',
                        'role' => 'super',
                        'order' => 1,
                    ],
                    [
                        'name' => 'Overview',
                        'label_key' => 'system.navigation.menu.integrations',
                        'route_name' => 'platform-integrations',
                        'icon' => 'code',
                        'permission' => 'manage system',
                        'role' => 'super',
                        'order' => 2,
                    ],
                    [
                        'name' => 'OAuth Clients',
                        'label_key' => 'system.navigation.menu.oauthClients',
                        'route_name' => 'oauth-clients',
                        'icon' => 'shield',
                        'permission' => 'manage system',
                        'role' => 'super',
                        'order' => 3,
                    ],
                    [
                        'name' => 'Webhooks',
                        'label_key' => 'system.navigation.menu.webhooks',
                        'route_name' => 'webhooks',
                        'icon' => 'zap',
                        'permission' => 'manage system',
                        'role' => 'super',
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

        foreach (self::getDefaultMenus() as $groupIndex => $group) {
            self::createGroupWithChildren($group, $groupIndex);
        }
    }

    /**
     * Soft-sync: add any default groups/items missing from an already-seeded table
     * (e.g. after new optional packs land) without wiping custom menu edits.
     */
    public static function ensureMissingDefaults(): void
    {
        if (self::count() === 0) {
            self::seedDefaults();

            return;
        }

        $existingRoutes = self::query()
            ->whereNotNull('route_name')
            ->pluck('route_name')
            ->flip()
            ->all();

        $parentsBySlug = self::query()
            ->whereNull('parent_id')
            ->get()
            ->keyBy('group_slug');

        foreach (self::getDefaultMenus() as $groupIndex => $group) {
            $groupSlugRaw = $group['group_slug'] ?? '';
            $groupSlug = is_string($groupSlugRaw) ? $groupSlugRaw : '';
            if ($groupSlug === '') {
                continue;
            }

            /** @var list<array<string, mixed>> $children */
            $children = [];
            $childrenRaw = $group['children'] ?? null;
            if (is_array($childrenRaw)) {
                foreach ($childrenRaw as $child) {
                    if (is_array($child)) {
                        $children[] = $child;
                    }
                }
            }

            $parent = $parentsBySlug->get($groupSlug);
            if (! $parent) {
                $parent = self::createGroupWithChildren($group, $groupIndex);
                $parentsBySlug->put($groupSlug, $parent);
                foreach ($children as $child) {
                    $route = $child['route_name'] ?? null;
                    if (is_string($route) && $route !== '') {
                        $existingRoutes[$route] = true;
                    }
                }

                continue;
            }

            foreach ($children as $childIndex => $child) {
                $route = $child['route_name'] ?? null;
                if (! is_string($route) || $route === '' || isset($existingRoutes[$route])) {
                    continue;
                }

                self::create([
                    'parent_id' => $parent->id,
                    'group_slug' => $groupSlug,
                    'name' => $child['name'],
                    'label_key' => $child['label_key'] ?? null,
                    'route_name' => $route,
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
                $existingRoutes[$route] = true;
            }
        }
    }

    /**
     * Remove duplicate root groups (same group_slug) — keep lowest order, re-parent children.
     */
    public static function deduplicateRootMenus(): void
    {
        $roots = self::query()
            ->whereNull('parent_id')
            ->orderBy('order')
            ->orderBy('created_at')
            ->get();

        /** @var array<string, self> $keepers */
        $keepers = [];

        foreach ($roots as $root) {
            $slug = $root->group_slug !== '' ? $root->group_slug : $root->name;
            if ($slug === '') {
                continue;
            }

            if (! isset($keepers[$slug])) {
                $keepers[$slug] = $root;

                continue;
            }

            $keeper = $keepers[$slug];

            self::query()
                ->where('parent_id', $root->id)
                ->update(['parent_id' => $keeper->id, 'group_slug' => $keeper->group_slug]);

            $root->delete();
        }
    }

    /**
     * @param  array<string, mixed>  $group
     */
    protected static function createGroupWithChildren(array $group, int $groupIndex): self
    {
        /** @var list<array<string, mixed>> $children */
        $children = [];
        $childrenRaw = $group['children'] ?? null;
        if (is_array($childrenRaw)) {
            foreach ($childrenRaw as $child) {
                if (is_array($child)) {
                    $children[] = $child;
                }
            }
        }
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

        return $parent;
    }

    /**
     * @return list<array{slug: string, name: string, icon: string}>
     */
    public static function catalogGroups(): array
    {
        $groups = [];
        foreach (self::getDefaultMenus() as $group) {
            $slug = $group['group_slug'] ?? null;
            if (! is_string($slug) || $slug === '') {
                continue;
            }
            $groups[] = [
                'slug' => $slug,
                'name' => is_string($group['name'] ?? null) ? $group['name'] : $slug,
                'icon' => is_string($group['icon'] ?? null) ? $group['icon'] : 'folder',
                'label_key' => 'system.navigation.menuGroups.'.$slug,
            ];
        }

        return $groups;
    }

    /**
     * Catalog groups plus any extra roots already in the database.
     *
     * @return list<array{slug: string, name: string, icon: string}>
     */
    public static function groupsForEditor(): array
    {
        $catalog = [];
        foreach (self::catalogGroups() as $group) {
            $catalog[$group['slug']] = $group;
        }

        $roots = self::query()->whereNull('parent_id')->orderBy('order')->get();
        foreach ($roots as $root) {
            $slug = $root->group_slug;
            if (! is_string($slug) || $slug === '' || isset($catalog[$slug])) {
                continue;
            }
            $catalog[$slug] = [
                'slug' => $slug,
                'name' => $root->name,
                'icon' => $root->icon ?: 'folder',
                'label_key' => 'system.navigation.menuGroups.'.$slug,
            ];
        }

        return array_values($catalog);
    }

    public static function syncVisibilityForExtension(string $slug, bool $visible): void
    {
        self::query()->where('extension_slug', $slug)->update(['is_visible' => $visible]);
    }

    public static function applyActiveExtensionVisibility(): void
    {
        $active = Extension::query()->where('status', 'active')->pluck('slug')->all();
        $items = self::query()
            ->whereNotNull('extension_slug')
            ->where('extension_slug', '!=', '')
            ->get();

        foreach ($items as $item) {
            $shouldShow = in_array($item->extension_slug, $active, true);
            if ((bool) $item->is_visible !== $shouldShow) {
                $item->update(['is_visible' => $shouldShow]);
            }
        }
    }
}
