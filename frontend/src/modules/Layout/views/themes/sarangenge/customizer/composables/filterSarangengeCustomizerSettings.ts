import type { ThemeSetting } from '@/modules/Layout/types/theme';
import type { CustomizerFilterContext } from '@/modules/Layout/customizer/types/extension';

/**
 * Filter visible customizer settings for Sarangenge school theme.
 */
export function filterSarangengeCustomizerSettings(
    settings: (ThemeSetting & { key: string })[],
    ctx: CustomizerFilterContext,
): (ThemeSetting & { key: string })[] {
    const isPpdbOpen = ctx.formValues.ppdb_is_open !== false;
    const heroBgType = String(ctx.formValues.hero_bg_type || 'preset');

    return settings.filter((setting) => {
        const key = String(setting?.key || '');
        if (!key) return true;
        if ((setting as { hidden?: boolean }).hidden) return false;

        // Hide specific PPDB fields if PPDB is marked closed
        if (key === 'ppdb_year' && !isPpdbOpen) return false;

        // Hero background conditional visibility
        if (key === 'hero_bg_preset' && heroBgType !== 'preset') return false;
        if ((key === 'hero_bg_image' || key === 'hero_bg_overlay_opacity') && heroBgType !== 'custom_image') return false;

        return true;
    });
}
