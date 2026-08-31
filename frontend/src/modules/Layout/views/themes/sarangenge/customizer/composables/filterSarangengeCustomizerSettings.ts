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

    return settings.filter((setting) => {
        const key = String(setting?.key || '');
        if (!key) return true;
        if ((setting as { hidden?: boolean }).hidden) return false;

        // Hide specific PPDB fields if PPDB is marked closed
        if (key === 'ppdb_year' && !isPpdbOpen) return false;

        return true;
    });
}
