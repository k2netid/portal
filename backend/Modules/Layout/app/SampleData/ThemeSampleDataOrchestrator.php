<?php

declare(strict_types=1);

namespace Modules\Layout\SampleData;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Core\System\Models\User;
use Modules\Forms\Database\Seeders\ContactFormSeeder;
use Modules\Layout\Models\Menu;
use Modules\Layout\Models\MenuItem;
use Modules\Layout\Models\Theme;
use Modules\Layout\Services\ThemeService;
use Modules\Publishing\Models\Content;

final class ThemeSampleDataOrchestrator
{
    public function __construct(
        private readonly ThemeSampleDataReader $reader,
        private readonly ThemeService $themeService,
        private readonly ThemeSampleBlocksFactory $blocksFactory,
    ) {
    }

    public function install(Theme $theme, ThemeSampleDataInstallOptions $options): ThemeSampleDataInstallResult
    {
        $slug = (string) $theme->slug;
        $bundle = $this->reader->readBundle($slug);
        if ($bundle === null) {
            throw new \RuntimeException("No sample-data bundle found for theme [{$slug}].");
        }

        $messages = [];
        $warnings = [];
        $menusInstalled = 0;
        $pagesInstalled = 0;
        $postsInstalled = 0;
        $settingsApplied = 0;

        if ($options->forms) {
            $this->ensureContactForm();
            $messages[] = 'Contact form ensured.';
        }

        if ($options->settings) {
            $settingsApplied = $this->applySettings($theme, $bundle, $options->force);
            if ($settingsApplied > 0) {
                $messages[] = "Applied {$settingsApplied} theme settings.";
            }
        }

        if ($options->menus) {
            $menusInstalled = $this->applyMenus($theme, $bundle, $options->force, $warnings);
            if ($menusInstalled > 0) {
                $messages[] = "Installed {$menusInstalled} menu slot(s).";
            }
        }

        if ($options->pages) {
            $pagesInstalled = $this->applyPages($theme, $slug, $bundle, $options->force, $warnings);
            if ($pagesInstalled > 0) {
                $messages[] = "Installed {$pagesInstalled} CMS page shell(s) (Vue theme layout preserved).";
            }

            $postsInstalled = $this->applyPosts($theme, $slug, $bundle, $options->force, $warnings);
            if ($postsInstalled > 0) {
                $messages[] = "Installed {$postsInstalled} sample news post(s).";
            }
        }

        $this->themeService->clearThemeCache($theme);

        return new ThemeSampleDataInstallResult(
            themeSlug: $slug,
            menusInstalled: $menusInstalled,
            pagesInstalled: $pagesInstalled,
            postsInstalled: $postsInstalled,
            settingsApplied: $settingsApplied,
            messages: $messages,
            warnings: $warnings,
        );
    }

    private function ensureContactForm(): void
    {
        if (! class_exists(ContactFormSeeder::class)) {
            return;
        }

        ContactFormSeeder::ensure();
    }

    /**
     * @param  array<string, mixed>  $bundle
     */
    private function applySettings(Theme $theme, array $bundle, bool $force): int
    {
        $incoming = $bundle['settings'] ?? null;
        if (! is_array($incoming) || $incoming === []) {
            return 0;
        }

        $existing = is_array($theme->settings) ? $theme->settings : [];
        $applied = 0;

        foreach ($incoming as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }
            if (str_starts_with($key, 'menu_location_')) {
                continue;
            }
            if (! $force && array_key_exists($key, $existing) && $this->hasNonEmptyValue($existing[$key])) {
                continue;
            }
            $existing[$key] = $value;
            $applied++;
        }

        if ($applied > 0) {
            $theme->update(['settings' => $existing]);
        }

        return $applied;
    }

    /**
     * @param  array<string, mixed>  $bundle
     * @param  list<string>  $warnings
     */
    private function applyMenus(Theme $theme, array $bundle, bool $force, array &$warnings): int
    {
        $menus = $bundle['menus'] ?? null;
        if (! is_array($menus) || $menus === []) {
            return 0;
        }

        $installed = 0;
        $settings = is_array($theme->settings) ? $theme->settings : [];

        foreach ($menus as $location => $menuDef) {
            if (! is_string($location) || ! is_array($menuDef)) {
                continue;
            }

            $slotKey = "menu_location_{$location}";
            $existingAssignment = $settings[$slotKey] ?? null;
            $sampleSlug = $this->sampleMenuSlug((string) $theme->slug, $location);

            $menu = Menu::query()->where('slug', $sampleSlug)->first();
            if ($menu !== null && ! $force) {
                $this->replaceMenuItems($menu, $menuDef);
            } else {
                if ($menu !== null) {
                    $menu->items()->delete();
                    $menu->delete();
                }

                $menu = Menu::query()->create([
                    'name' => (string) ($menuDef['name'] ?? Str::headline($location).' Navigation'),
                    'slug' => $sampleSlug,
                    'location' => $location,
                    'description' => 'Theme sample data ('.(string) $theme->slug.')',
                    'module_scope' => 'publishing',
                    'is_active' => true,
                ]);

                $this->replaceMenuItems($menu, $menuDef);
            }

            $shouldAssign = $force
                || empty($existingAssignment)
                || (string) $existingAssignment === (string) $menu->id;

            if ($shouldAssign) {
                $previousLocation = is_string($menu->location) ? $menu->location : null;
                $menu->update(['location' => $location]);
                $this->themeService->syncMenuLocationAssignment($menu, $previousLocation);
                $settings[$slotKey] = (string) $menu->id;
                $installed++;
            } else {
                $warnings[] = "Skipped menu slot assignment for [{$location}] — already assigned. Use force to overwrite.";
            }
        }

        if ($installed > 0) {
            $theme->update(['settings' => $settings]);
        }

        return $installed;
    }

    /**
     * @param  array<string, mixed>  $menuDef
     */
    private function replaceMenuItems(Menu $menu, array $menuDef): void
    {
        $menu->items()->delete();
        $items = $menuDef['items'] ?? [];
        if (! is_array($items)) {
            return;
        }

        $this->createMenuItems($menu, $items, null);
    }

    /**
     * @param  list<mixed>  $items
     */
    private function createMenuItems(Menu $menu, array $items, ?string $parentId): void
    {
        foreach ($items as $index => $itemDef) {
            if (! is_array($itemDef)) {
                continue;
            }

            $metadata = is_array($itemDef['metadata'] ?? null) ? $itemDef['metadata'] : [];
            foreach (['title_en', 'title_id', 'description'] as $metaKey) {
                if (isset($itemDef[$metaKey]) && is_string($itemDef[$metaKey]) && $itemDef[$metaKey] !== '') {
                    $metadata[$metaKey] = $itemDef[$metaKey];
                }
            }

            $created = $menu->items()->create([
                'parent_id' => $parentId,
                'title' => (string) ($itemDef['title'] ?? 'Menu Item'),
                'url' => (string) ($itemDef['url'] ?? '/'),
                'type' => (string) ($itemDef['type'] ?? 'custom'),
                'sort_order' => (int) ($itemDef['sort_order'] ?? $index),
                'open_in_new_tab' => (bool) ($itemDef['open_in_new_tab'] ?? false),
                'metadata' => $metadata !== [] ? $metadata : null,
            ]);

            $children = $itemDef['children'] ?? [];
            if (is_array($children) && $children !== []) {
                $this->createMenuItems($menu, $children, (string) $created->id);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $bundle
     * @param  list<string>  $warnings
     */
    private function applyPages(Theme $theme, string $themeSlug, array $bundle, bool $force, array &$warnings): int
    {
        if (! Schema::hasTable('pub_contents')) {
            $warnings[] = 'Publishing tables missing — skipped page seeds.';

            return 0;
        }

        $pages = $bundle['pages'] ?? null;
        if (! is_array($pages) || $pages === []) {
            return 0;
        }

        $authorId = $this->resolveAuthorId();
        $installed = 0;

        foreach ($pages as $pageDef) {
            if (! is_array($pageDef)) {
                continue;
            }

            $slug = (string) ($pageDef['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $existing = Content::query()
                ->where('slug', $slug)
                ->where('type', 'page')
                ->first();

            if ($existing !== null) {
                $meta = is_array($existing->meta) ? $existing->meta : [];
                $isSample = ($meta['sample_theme'] ?? null) === $themeSlug;
                if (! $force && ! $isSample) {
                    $warnings[] = "Skipped page [{$slug}] — already exists and is not sample data.";
                    continue;
                }
            }

            $themePage = (string) ($pageDef['theme_page'] ?? '');
            $title = (string) ($pageDef['title'] ?? Str::headline($slug));
            $excerpt = (string) ($pageDef['excerpt'] ?? '');

            // Sample installs keep Vue theme layout intact (menus + settings + CMS shells only).
            // Full builder_blocks require an explicit opt-in via pageDef / builder_override later.
            $builderBlocks = [];
            if (isset($pageDef['builder_blocks']) && is_array($pageDef['builder_blocks']) && ($pageDef['builder_override'] ?? false) === true) {
                $builderBlocks = $this->resolveBuilderBlocks($pageDef, $themeSlug, $slug, $bundle, $title, $excerpt);
            }

            $meta = [
                'theme_page' => $themePage,
                'builder_blocks' => $builderBlocks,
                'builder_schema_version' => 1,
                'sample_theme' => $themeSlug,
                'builder_override' => false,
                'use_theme_template' => $themePage !== '',
            ];

            if (isset($pageDef['title_en']) && is_string($pageDef['title_en'])) {
                $meta['title_en'] = $pageDef['title_en'];
            }
            if (isset($pageDef['excerpt_en']) && is_string($pageDef['excerpt_en'])) {
                $meta['excerpt_en'] = $pageDef['excerpt_en'];
            }
            if (isset($pageDef['body_en']) && is_string($pageDef['body_en'])) {
                $meta['body_en'] = $pageDef['body_en'];
            }

            $payload = [
                'title' => $title,
                'slug' => $slug,
                'type' => 'page',
                'status' => (string) ($pageDef['status'] ?? 'published'),
                'excerpt' => $excerpt,
                'body' => (string) ($pageDef['body'] ?? ''),
                'author_id' => $authorId,
                'meta' => $meta,
                'published_at' => now(),
            ];

            if ($existing !== null) {
                $existing->update($payload);
            } else {
                Content::query()->create($payload);
            }

            $installed++;
        }

        return $installed;
    }

    /**
     * @param  array<string, mixed>  $bundle
     */
    private function applyPosts(Theme $theme, string $themeSlug, array $bundle, bool $force, array &$warnings): int
    {
        if (! Schema::hasTable('pub_contents')) {
            return 0;
        }

        $posts = $bundle['posts'] ?? null;
        if (! is_array($posts) || $posts === []) {
            return 0;
        }

        $authorId = $this->resolveAuthorId();
        $installed = 0;

        foreach ($posts as $postDef) {
            if (! is_array($postDef)) {
                continue;
            }

            $slug = (string) ($postDef['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $existing = Content::query()
                ->where('slug', $slug)
                ->where('type', 'post')
                ->first();

            if ($existing !== null) {
                $meta = is_array($existing->meta) ? $existing->meta : [];
                $isSample = ($meta['sample_theme'] ?? null) === $themeSlug;
                if (! $force && ! $isSample) {
                    $warnings[] = "Skipped post [{$slug}] — already exists and is not sample data.";
                    continue;
                }
            }

            $title = (string) ($postDef['title'] ?? Str::headline($slug));
            $excerpt = (string) ($postDef['excerpt'] ?? '');

            $meta = [
                'sample_theme' => $themeSlug,
            ];

            if (isset($postDef['title_en']) && is_string($postDef['title_en'])) {
                $meta['title_en'] = $postDef['title_en'];
            }
            if (isset($postDef['excerpt_en']) && is_string($postDef['excerpt_en'])) {
                $meta['excerpt_en'] = $postDef['excerpt_en'];
            }
            if (isset($postDef['body_en']) && is_string($postDef['body_en'])) {
                $meta['body_en'] = $postDef['body_en'];
            }

            $payload = [
                'title' => $title,
                'slug' => $slug,
                'type' => 'post',
                'status' => (string) ($postDef['status'] ?? 'published'),
                'excerpt' => $excerpt,
                'body' => (string) ($postDef['body'] ?? ''),
                'author_id' => $authorId,
                'meta' => $meta,
                'published_at' => now()->subDays(max(0, 3 - $installed)),
                'is_featured' => $installed === 0,
                'comment_status' => 'closed',
            ];

            if ($existing !== null) {
                $existing->update($payload);
            } else {
                Content::query()->create($payload);
            }

            $installed++;
        }

        return $installed;
    }

    /**
     * @param  array<string, mixed>  $pageDef
     * @param  array<string, mixed>  $bundle
     * @return list<array<string, mixed>>
     */
    private function resolveBuilderBlocks(
        array $pageDef,
        string $themeSlug,
        string $pageSlug,
        array $bundle,
        string $title,
        string $excerpt,
    ): array {
        if (isset($pageDef['builder_blocks']) && is_array($pageDef['builder_blocks']) && $pageDef['builder_blocks'] !== []) {
            /** @var list<array<string, mixed>> $blocks */
            $blocks = array_values(array_filter(
                $pageDef['builder_blocks'],
                static fn ($node): bool => is_array($node)
            ));

            return $blocks;
        }

        $template = (string) ($pageDef['blocks_template'] ?? $pageSlug);
        $settings = is_array($bundle['settings'] ?? null) ? $bundle['settings'] : [];

        $ctx = [
            'brand' => (string) ($settings['site_title'] ?? Str::headline($themeSlug)),
            'hero_title' => (string) ($settings['hero_title'] ?? ''),
            'hero_subtitle' => (string) ($settings['hero_subtitle'] ?? ''),
            'cta_primary_label' => (string) ($settings['cta_primary_label'] ?? ''),
            'cta_primary_url' => (string) ($settings['cta_primary_url'] ?? ''),
            'cta_secondary_label' => (string) ($settings['cta_secondary_label'] ?? ''),
            'cta_secondary_url' => (string) ($settings['cta_secondary_url'] ?? ''),
            'title' => $title,
            'excerpt' => $excerpt,
        ];

        return $this->blocksFactory->forTemplate($template, $themeSlug, $pageSlug, $ctx);
    }

    private function sampleMenuSlug(string $themeSlug, string $location): string
    {
        return "{$themeSlug}-sample-{$location}";
    }

    private function resolveAuthorId(): string
    {
        $user = User::query()->orderBy('created_at')->first();
        if ($user === null) {
            throw new \RuntimeException('Cannot seed pages — no user exists to assign as author.');
        }

        return (string) $user->id;
    }

    private function hasNonEmptyValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }
        if (is_string($value)) {
            return trim($value) !== '';
        }
        if (is_array($value)) {
            return $value !== [];
        }

        return true;
    }
}
