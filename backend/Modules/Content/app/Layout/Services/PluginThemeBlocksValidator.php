<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Services;

final class PluginThemeBlocksValidator
{
    /**
     * @return list<string>
     */
    public function allowedSlotIds(): array
    {
        $slots = config('layout.plugin_theme_slots', []);
        if (! is_array($slots)) {
            return [];
        }

        return array_values(array_map(static fn (mixed $v): string => is_scalar($v) ? (string) $v : '', $slots));
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, list<string>>
     */
    public function validateSettings(array $settings): array
    {
        $errors = [];
        $blocks = $settings['theme_blocks'] ?? null;
        if ($blocks === null) {
            return [];
        }
        if (! is_array($blocks)) {
            return ['theme_blocks' => ['theme_blocks must be an array.']];
        }

        $allowed = array_flip($this->allowedSlotIds());

        foreach ($blocks as $index => $block) {
            $slot = $this->extractSlotId($block);
            if ($slot === null) {
                $errors["theme_blocks.{$index}"] = ['Each theme block must define a slot (string or { slot: "..." }).'];

                continue;
            }
            if (! isset($allowed[$slot])) {
                $errors["theme_blocks.{$index}"] = ["Unknown slot \"{$slot}\". Allowed: ".implode(', ', array_keys($allowed)).'.'];
            }
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public function normalizeSettings(array $settings): array
    {
        $blocks = $settings['theme_blocks'] ?? null;
        if (! is_array($blocks) || $blocks === []) {
            return $settings;
        }

        $allowed = array_flip($this->allowedSlotIds());
        $normalized = [];
        foreach ($blocks as $block) {
            $slot = $this->extractSlotId($block);
            if ($slot === null || ! isset($allowed[$slot])) {
                continue;
            }
            $normalized[] = ['slot' => $slot];
        }

        $settings['theme_blocks'] = $normalized;

        return $settings;
    }

    private function extractSlotId(mixed $block): ?string
    {
        if (is_string($block) && $block !== '') {
            return $block;
        }
        if (is_array($block) && isset($block['slot']) && is_string($block['slot']) && $block['slot'] !== '') {
            return $block['slot'];
        }

        return null;
    }
}
