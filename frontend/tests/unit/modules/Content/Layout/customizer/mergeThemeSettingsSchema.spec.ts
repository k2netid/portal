import { describe, expect, it } from 'vitest';
import {
    applyMergedSettingsSchema,
    mergeThemeSettingsSchema,
} from '@/modules/Content/Layout/customizer/loaders/mergeThemeSettingsSchema';
import platformSchema from '@/modules/Content/Layout/customizer/platform/schema/global.settings.schema.json';
import janariThemeSchema from '@/modules/Content/Layout/views/themes/janari/customizer/schema.settings.json';

describe('mergeThemeSettingsSchema', () => {
    it('merges platform then theme (theme overrides platform keys)', () => {
        const merged = mergeThemeSettingsSchema('janari');
        const platformKeys = Object.keys(platformSchema).filter((k) => k !== '_meta');
        expect(platformKeys.length).toBeGreaterThan(0);
        for (const key of platformKeys) {
            expect(merged[key]).toBeDefined();
        }
        const sharedKey = platformKeys.find((k) => k in janariThemeSchema);
        if (sharedKey) {
            expect(merged[sharedKey]).toEqual(
                (janariThemeSchema as Record<string, unknown>)[sharedKey],
            );
        }
    });

    it('fills legacy API keys not present in split files', () => {
        const legacyOnly = {
            legacy_custom_key: { type: 'text', default: 'x', label: 'Legacy' },
        };
        const merged = mergeThemeSettingsSchema('janari', legacyOnly);
        expect(merged.legacy_custom_key).toEqual(legacyOnly.legacy_custom_key);
    });

    it('applyMergedSettingsSchema mutates manifest in memory', () => {
        const theme = {
            manifest: {
                settings_schema: { orphan_api: { type: 'text', default: '' } },
            },
        };
        applyMergedSettingsSchema(theme, 'janari');
        expect(theme.manifest?.settings_schema?.orphan_api).toBeDefined();
        expect(Object.keys(theme.manifest!.settings_schema!).length).toBeGreaterThan(1);
    });
});
