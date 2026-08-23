<?php

declare(strict_types=1);

namespace Modules\Layout\Services;

use Modules\Core\System\Models\Plugin;

final class PluginThemeBlocksService
{
    public function __construct(
        private readonly PluginThemeBlocksRemoteUrlValidator $remoteUrls,
    ) {}

    /**
     * @return list<array{slug: string, priority: int, slots: list<string>, blocks_url?: string}>
     */
    public function getPublicManifest(): array
    {
        $defaults = config('layout.plugin_theme_blocks', []);
        if (! is_array($defaults)) {
            $defaults = [];
        }

        $out = [];

        foreach (Plugin::getActivePlugins() as $plugin) {
            $slug = $plugin->slug;
            $slots = $this->resolveSlotsForPlugin($plugin, $defaults);
            if ($slots === []) {
                continue;
            }

            $entry = [
                'slug' => $slug,
                'priority' => (int) $plugin->priority,
                'slots' => $slots,
            ];
            $blocksUrl = $this->resolveRemoteBlocksUrl($plugin);
            if ($blocksUrl !== null) {
                $entry['blocks_url'] = $blocksUrl;
            }
            $out[] = $entry;
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $defaults
     * @return list<string>
     */
    private function resolveSlotsForPlugin(Plugin $plugin, array $defaults): array
    {
        $slug = $plugin->slug;
        $fromConfig = isset($defaults[$slug]) && is_array($defaults[$slug])
            ? array_values(array_map(static fn (mixed $v): string => is_scalar($v) ? (string) $v : '', $defaults[$slug]))
            : [];

        $settings = is_array($plugin->settings) ? $plugin->settings : [];
        $blocks = $settings['theme_blocks'] ?? null;

        if (! is_array($blocks) || $blocks === []) {
            return $this->filterAllowedSlots(array_values(array_unique($fromConfig)));
        }

        $fromDb = [];
        foreach ($blocks as $block) {
            if (is_string($block)) {
                $fromDb[] = $block;

                continue;
            }
            if (is_array($block) && isset($block['slot']) && is_string($block['slot'])) {
                $fromDb[] = $block['slot'];
            }
        }

        $merged = array_values(array_unique(array_merge($fromConfig, $fromDb)));

        return $this->filterAllowedSlots($merged);
    }

    private function resolveRemoteBlocksUrl(Plugin $plugin): ?string
    {
        $settings = is_array($plugin->settings) ? $plugin->settings : [];
        $raw = $settings['theme_blocks_remote_url'] ?? $settings['blocks_url'] ?? null;

        return $this->remoteUrls->validate(is_string($raw) ? $raw : null);
    }

    /**
     * @param  list<string>  $slots
     * @return list<string>
     */
    private function filterAllowedSlots(array $slots): array
    {
        $allowed = array_flip(app(PluginThemeBlocksValidator::class)->allowedSlotIds());

        return array_values(array_filter($slots, static fn (string $s): bool => isset($allowed[$s])));
    }
}
