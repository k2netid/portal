<?php

declare(strict_types=1);

namespace Modules\Core\System\Support;

/**
 * Lightweight first-party manifest checks aligned with
 * docs/extensions/module-manifest.schema.json (no Composer JSON Schema runtime dep).
 */
final class ModuleManifestValidator
{
    /**
     * @param  array<mixed, mixed>  $manifest
     * @return list<string> Validation error messages (empty = ok)
     */
    public static function validateFirstParty(array $manifest): array
    {
        $errors = [];

        foreach (['name', 'slug', 'version', 'type', 'author', 'description'] as $field) {
            if (! isset($manifest[$field]) || ! is_string($manifest[$field]) || $manifest[$field] === '') {
                $errors[] = "Missing or invalid string field: {$field}";
            }
        }

        if (isset($manifest['slug']) && is_string($manifest['slug'])
            && ! preg_match('/^[a-z][a-z0-9_-]*$/', $manifest['slug'])) {
            $errors[] = 'slug must match ^[a-z][a-z0-9_-]*$';
        }

        if (isset($manifest['type']) && is_string($manifest['type'])
            && ! in_array($manifest['type'], ['module', 'plugin'], true)) {
            $errors[] = 'type must be module or plugin';
        }

        if (! array_key_exists('is_core', $manifest) || ! is_bool($manifest['is_core'])) {
            $errors[] = 'is_core must be a boolean';
        }

        if (isset($manifest['license_tier']) && is_string($manifest['license_tier'])
            && ! in_array($manifest['license_tier'], ['free', 'pro', 'pro_plus'], true)) {
            $errors[] = 'license_tier must be free, pro, or pro_plus';
        }

        if (isset($manifest['features']) && is_array($manifest['features'])) {
            foreach ($manifest['features'] as $i => $feat) {
                if (! is_array($feat)) {
                    $errors[] = "features[{$i}] must be an object";

                    continue;
                }
                if (! isset($feat['slug'], $feat['name']) || ! is_string($feat['slug']) || ! is_string($feat['name'])) {
                    $errors[] = "features[{$i}] requires string slug and name";
                }
            }
        }

        return $errors;
    }

    /**
     * @param  array<mixed, mixed>  $manifest
     */
    public static function isValidFirstParty(array $manifest): bool
    {
        return self::validateFirstParty($manifest) === [];
    }
}
