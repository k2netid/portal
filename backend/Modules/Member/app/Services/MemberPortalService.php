<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Modules\Core\System\Models\Extension;
use Modules\Member\Models\Member;

/**
 * Builds adaptive reader portal payload from active extensions + manifest member_area.
 * Capabilities use RFC option A (derived from active packs, no per-member table).
 */
class MemberPortalService
{
    private const CORE_EXTENSION = 'member';

    /** @var list<string> */
    private const CORE_CAPABILITIES = ['member.portal'];

    /**
     * @return array{
     *   member: array{id: string, name: string, email: string, status: string, email_verified: bool},
     *   active_extensions: list<string>,
     *   capabilities: list<string>,
     *   navigation: list<array<string, mixed>>,
     *   widgets: list<array<string, mixed>>
     * }
     */
    public function build(Member $member): array
    {
        $activeExtensions = Extension::query()
            ->where('status', 'active')
            ->pluck('slug')
            ->map(static fn (mixed $slug): string => strtolower((string) $slug))
            ->filter(static fn (string $slug): bool => $slug !== '')
            ->values()
            ->all();

        $verified = $member->email_verified_at !== null;
        $capabilities = self::CORE_CAPABILITIES;
        $navigation = $this->coreNavigation();
        $widgets = [];

        if (in_array(self::CORE_EXTENSION, $activeExtensions, true)) {
            foreach (Extension::query()->where('status', 'active')->get() as $extension) {
                $slug = strtolower((string) $extension->slug);
                if ($slug === self::CORE_EXTENSION) {
                    continue;
                }

                $memberArea = $this->memberAreaFromExtension($extension);
                if ($memberArea === null) {
                    continue;
                }

                if (! $this->dependenciesSatisfied($memberArea, $activeExtensions)) {
                    continue;
                }

                foreach ($memberArea['capabilities'] ?? [] as $capability) {
                    if (is_string($capability) && $capability !== '') {
                        $capabilities[] = $capability;
                    }
                }

                foreach ($memberArea['nav'] ?? [] as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $normalized = $this->normalizeNavItem($item, $slug);
                    if ($normalized === null) {
                        continue;
                    }
                    if ($this->isNavVisible($normalized, $verified, $capabilities, $activeExtensions)) {
                        $navigation[] = $normalized;
                    }
                }

                foreach ($memberArea['widgets'] ?? [] as $widget) {
                    if (! is_array($widget)) {
                        continue;
                    }
                    $normalized = $this->normalizeWidget($widget, $slug);
                    if ($normalized === null) {
                        continue;
                    }
                    if ($this->isWidgetVisible($normalized, $verified, $capabilities, $activeExtensions)) {
                        $widgets[] = $normalized;
                    }
                }
            }
        }

        usort($navigation, static fn (array $a, array $b): int => ((int) ($a['order'] ?? 100)) <=> ((int) ($b['order'] ?? 100)));
        usort($widgets, static fn (array $a, array $b): int => ((int) ($a['order'] ?? 100)) <=> ((int) ($b['order'] ?? 100)));

        return [
            'member' => $member->toPublicProfile(),
            'active_extensions' => $activeExtensions,
            'capabilities' => array_values(array_unique($capabilities)),
            'navigation' => $navigation,
            'widgets' => $widgets,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function coreNavigation(): array
    {
        return [
            [
                'slug' => 'dashboard',
                'label_key' => 'member.portal.nav.dashboard',
                'route' => 'member.dashboard',
                'order' => 10,
                'extension_slug' => self::CORE_EXTENSION,
            ],
            [
                'slug' => 'profile',
                'label_key' => 'member.portal.nav.profile',
                'route' => 'member.profile',
                'order' => 20,
                'extension_slug' => self::CORE_EXTENSION,
            ],
            [
                'slug' => 'security',
                'label_key' => 'member.portal.nav.security',
                'route' => 'member.security',
                'order' => 30,
                'extension_slug' => self::CORE_EXTENSION,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $memberArea
     * @param  list<string>  $activeExtensions
     */
    private function dependenciesSatisfied(array $memberArea, array $activeExtensions): bool
    {
        $deps = $memberArea['depends_on'] ?? [self::CORE_EXTENSION];
        if (! is_array($deps)) {
            return false;
        }

        foreach ($deps as $dep) {
            if (! is_string($dep) || $dep === '') {
                return false;
            }
            if (! in_array(strtolower($dep), $activeExtensions, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function memberAreaFromExtension(Extension $extension): ?array
    {
        $manifest = $this->resolveManifest($extension);
        $memberArea = $manifest['member_area'] ?? null;

        return is_array($memberArea) ? $memberArea : null;
    }

    /**
     * Prefer on-disk Modules/{Slug}/manifest.json so member_area / lifecycle stay current
     * even when sys_extensions.manifest was truncated by older discover().
     *
     * @return array<string, mixed>
     */
    private function resolveManifest(Extension $extension): array
    {
        $fromDisk = $this->manifestFromDisk((string) $extension->slug);
        if ($fromDisk !== []) {
            return $fromDisk;
        }

        if (is_array($extension->manifest) && $extension->manifest !== []) {
            return $extension->manifest;
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private function manifestFromDisk(string $slug): array
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            return [];
        }

        $studly = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $slug)));
        $candidates = array_values(array_unique([
            base_path('Modules/'.$studly.'/manifest.json'),
            base_path('Modules/'.ucfirst($slug).'/manifest.json'),
        ]));

        foreach ($candidates as $path) {
            if (! is_file($path)) {
                continue;
            }
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded) && $decoded !== []) {
                return $decoded;
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    private function normalizeNavItem(array $item, string $extensionSlug): ?array
    {
        $slug = $item['slug'] ?? null;
        $route = $item['route'] ?? null;
        if (! is_string($slug) || $slug === '' || ! is_string($route) || $route === '') {
            return null;
        }

        return [
            'slug' => $slug,
            'label_key' => is_string($item['label_key'] ?? null) && $item['label_key'] !== ''
                ? $item['label_key']
                : 'member.nav.'.$slug,
            'route' => $route,
            'order' => is_numeric($item['order'] ?? null) ? (int) $item['order'] : 100,
            'requires_verified' => (bool) ($item['requires_verified'] ?? false),
            'capability' => is_string($item['capability'] ?? null) ? $item['capability'] : null,
            'extension_slug' => $extensionSlug,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    private function normalizeWidget(array $item, string $extensionSlug): ?array
    {
        $slug = $item['slug'] ?? null;
        $slot = $item['slot'] ?? 'dashboard';
        if (! is_string($slug) || $slug === '' || ! is_string($slot) || $slot === '') {
            return null;
        }

        return [
            'slug' => $slug,
            'slot' => $slot,
            'order' => is_numeric($item['order'] ?? null) ? (int) $item['order'] : 100,
            'requires_verified' => (bool) ($item['requires_verified'] ?? false),
            'capability' => is_string($item['capability'] ?? null) ? $item['capability'] : null,
            'extension_slug' => $extensionSlug,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  list<string>  $capabilities
     * @param  list<string>  $activeExtensions
     */
    private function isNavVisible(array $item, bool $verified, array $capabilities, array $activeExtensions): bool
    {
        if (($item['requires_verified'] ?? false) === true && ! $verified) {
            return false;
        }

        $capability = $item['capability'] ?? null;
        if (is_string($capability) && $capability !== '') {
            if (! in_array($capability, $capabilities, true)) {
                return false;
            }
        }

        $extensionSlug = $item['extension_slug'] ?? null;
        if (is_string($extensionSlug) && $extensionSlug !== '' && $extensionSlug !== self::CORE_EXTENSION) {
            return in_array(strtolower($extensionSlug), $activeExtensions, true);
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  list<string>  $capabilities
     * @param  list<string>  $activeExtensions
     */
    private function isWidgetVisible(array $item, bool $verified, array $capabilities, array $activeExtensions): bool
    {
        return $this->isNavVisible($item, $verified, $capabilities, $activeExtensions);
    }
}
