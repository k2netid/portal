import { JANARI_PRESETS, type JanariPresetKey } from '@/modules/Content/Layout/config/janariPresets';

/**
 * Sync Janari color_preset ↔ color_primary in customizer form state.
 */
export function onJanariSettingChange(
    key: string,
    val: unknown,
    formValues: Record<string, unknown>,
): void {
    if (key === 'color_primary' && formValues.color_preset !== 'custom') {
        const currentPreset = String(formValues.color_preset || '');
        const presetColor = JANARI_PRESETS[currentPreset as JanariPresetKey]?.light.toLowerCase();
        if (String(val).toLowerCase() !== presetColor) {
            formValues.color_preset = 'custom';
        }
    }

    if (key === 'color_preset' && val !== 'custom') {
        const presetColor = JANARI_PRESETS[val as JanariPresetKey]?.light;
        if (presetColor) {
            formValues.color_primary = presetColor;
        }
    }
}
