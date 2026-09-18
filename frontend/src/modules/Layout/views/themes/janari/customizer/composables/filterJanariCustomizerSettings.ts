import type { ThemeSetting } from '@/modules/Layout/types/theme';
import type { CustomizerFilterContext } from '@/modules/Layout/customizer/types/extension';

/**
 * Janari canvas: conditional visibility for color preset / monochrome fields.
 */
export function filterJanariCustomizerSettings(
    settings: (ThemeSetting & { key: string })[],
    ctx: CustomizerFilterContext,
): (ThemeSetting & { key: string })[] {
    if (!ctx.usesJanariCanvas) {
        return settings.filter((setting) => !(setting as { hidden?: boolean }).hidden);
    }

    const preset = String(ctx.formValues.color_preset || 'custom');
    const isMonochromePreset = preset === 'monochrome_clean';
    const allowPrimaryColor = preset !== 'monochrome_clean';

    const sideNavDotsEnabled = ctx.formValues.home_side_nav_dots !== false;
    const floatingSocialEnabled = ctx.formValues.enable_floating_social !== false;

    return settings.filter((setting) => {
        const key = String(setting?.key || '');
        if (!key) return true;
        if ((setting as { hidden?: boolean }).hidden) return false;
        if (key.startsWith('home_side_nav_') && key !== 'home_side_nav_dots' && !sideNavDotsEnabled) return false;
        if (key.startsWith('floating_social_') && !floatingSocialEnabled) return false;
        if (key === 'color_background') return false;
        if (key === 'monochrome_variant') return isMonochromePreset;
        if (key === 'color_primary') return allowPrimaryColor;
        return true;
    });
}
