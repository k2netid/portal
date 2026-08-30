<?php

declare(strict_types=1);

namespace Modules\Publishing\Support;

/**
 * Integrity checks for visual-builder trees stored on content meta.
 * Does not close the module catalog — unknown types are allowed if they look like slugs.
 */
final class BuilderDocumentValidator
{
    public const MAX_NODES = 400;

    public const MAX_DEPTH = 12;

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, list<string>>
     */
    public function validate(array $meta): array
    {
        // Keys must match the builder save payload in useBuilderSync.ts
        $hasBlocks = array_key_exists('builder_blocks', $meta);
        $hasVersion = array_key_exists('builder_schema_version', $meta);

        if (! $hasBlocks && ! $hasVersion) {
            return [];
        }

        $errors = [];

        if ($hasVersion) {
            $version = $meta['builder_schema_version'];
            if (! is_int($version) && ! (is_string($version) && is_numeric($version))) {
                $errors['meta.builder_schema_version'][] = 'builder_schema_version must be an integer.';
            } else {
                $asInt = (int) $version;
                if ($asInt < 1 || $asInt > 1) {
                    $errors['meta.builder_schema_version'][] = 'Unsupported builder_schema_version.';
                }
            }
        }

        if (! $hasBlocks) {
            return $errors;
        }

        $blocks = $meta['builder_blocks'];
        if (! is_array($blocks) || ! array_is_list($blocks)) {
            $errors['meta.builder_blocks'][] = 'builder_blocks must be a list of nodes.';

            return $errors;
        }

        $count = 0;
        $this->walk($blocks, 0, $errors, $count, 'meta.builder_blocks');

        return $errors;
    }

    /**
     * @param  list<mixed>  $nodes
     * @param  array<string, list<string>>  $errors
     */
    private function walk(array $nodes, int $depth, array &$errors, int &$count, string $path): void
    {
        if ($depth > self::MAX_DEPTH) {
            $errors[$path][] = 'Builder tree exceeds maximum depth.';

            return;
        }

        foreach ($nodes as $index => $node) {
            $nodePath = $path.'.'.$index;
            $count++;
            if ($count > self::MAX_NODES) {
                $errors['meta.builder_blocks'][] = 'Builder tree exceeds '.self::MAX_NODES.' nodes.';

                return;
            }

            if (! is_array($node)) {
                $errors[$nodePath][] = 'Each block must be an object.';

                continue;
            }

            foreach (['__proto__', 'constructor', 'prototype'] as $banned) {
                if (array_key_exists($banned, $node)) {
                    $errors[$nodePath][] = 'Block contains a forbidden key.';
                }
            }

            $type = $node['type'] ?? null;
            if (! is_string($type) || $type === '' || ! preg_match('/^[a-z][a-z0-9_-]{0,63}$/', $type)) {
                $errors[$nodePath.'.type'][] = 'Block type must be a lowercase slug.';
            }

            $id = $node['id'] ?? null;
            if (! is_string($id) || $id === '' || strlen($id) > 80) {
                $errors[$nodePath.'.id'][] = 'Block id must be a non-empty string.';
            }

            if (array_key_exists('settings', $node) && ! is_array($node['settings'])) {
                $errors[$nodePath.'.settings'][] = 'Block settings must be an object.';
            }

            if (array_key_exists('children', $node)) {
                $nested = $node['children'];
                if (! is_array($nested) || ! array_is_list($nested)) {
                    $errors[$nodePath.'.children'][] = 'Block children must be a list.';
                } else {
                    $this->walk($nested, $depth + 1, $errors, $count, $nodePath.'.children');
                }
            }
        }
    }
}
