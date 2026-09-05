import type { CustomizerSettingsSchema } from '@/modules/Layout/customizer/types/customizer-schema';
import platformSchemaJson from '@/modules/Layout/customizer/platform/schema/global.settings.schema.json';

const themeSchemaModules = import.meta.glob(
    '@/modules/Layout/views/themes/*/customizer/schema.settings.json',
    { eager: true },
) as Record<string, CustomizerSettingsSchema>;

const themeSchemaBySlug: Record<string, CustomizerSettingsSchema> = {};
for (const [modulePath, schema] of Object.entries(themeSchemaModules)) {
    const match = modulePath.match(/views\/themes\/([^/]+)\/customizer\/schema\.settings\.json$/);
    if (match?.[1]) {
        const raw = ((schema as { default?: CustomizerSettingsSchema })?.default ?? schema) as CustomizerSettingsSchema;
        themeSchemaBySlug[match[1]] = raw;
    }
}

function omitMeta(schema: CustomizerSettingsSchema): CustomizerSettingsSchema {
    const out: CustomizerSettingsSchema = {};
    for (const [key, def] of Object.entries(schema)) {
        if (key === '_meta') continue;
        out[key] = def;
    }
    return out;
}

/**
 * Merged settings_schema for customizer + runtime (platform first, theme overrides).
 * Falls back to API manifest keys not present in split files (legacy DB).
 */
export function mergeThemeSettingsSchema(
    slug: string,
    apiSchema?: Record<string, unknown> | null,
): CustomizerSettingsSchema {
    const platform = omitMeta(platformSchemaJson as unknown as CustomizerSettingsSchema);
    const theme = omitMeta(themeSchemaBySlug[slug] ?? {});
    const merged: CustomizerSettingsSchema = { ...platform, ...theme };

    if (apiSchema && typeof apiSchema === 'object') {
        for (const [key, def] of Object.entries(apiSchema)) {
            if (merged[key] === undefined && def && typeof def === 'object') {
                merged[key] = def as CustomizerSettingsSchema[string];
            }
        }
    }

    return merged;
}

/** Apply merged schema onto theme manifest in memory (customizer + optional public runtime). */
export function applyMergedSettingsSchema<T extends { manifest?: { settings_schema?: Record<string, unknown> } | null }>(
    theme: T | null,
    slug: string,
): T | null {
    if (!theme?.manifest) return theme;
    const apiSchema = theme.manifest.settings_schema;
    theme.manifest.settings_schema = mergeThemeSettingsSchema(slug, apiSchema);
    return theme;
}
