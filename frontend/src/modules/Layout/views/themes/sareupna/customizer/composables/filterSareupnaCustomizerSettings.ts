import type { ThemeSetting } from '@/modules/Layout/types/theme';
import type { CustomizerFilterContext } from '@/modules/Layout/customizer/types/extension';

export function filterSareupnaCustomizerSettings(
    settings: (ThemeSetting & { key: string })[],
    ctx: CustomizerFilterContext,
): (ThemeSetting & { key: string })[] {
    const heroBgType = String(ctx.formValues.hero_bg_type || 'preset');
    const sideNavDotsEnabled = ctx.formValues.home_side_nav_dots !== false;
    const floatingSocialEnabled = ctx.formValues.enable_floating_social === true;

    return settings.filter((setting) => {
        const key = String(setting?.key || '');
        if (!key) return true;
        if ((setting as { hidden?: boolean }).hidden) return false;

        // Hero background conditional fields
        if (key === 'hero_bg_preset' && heroBgType !== 'preset') return false;
        if ((key === 'hero_bg_image' || key === 'hero_bg_overlay_opacity') && heroBgType !== 'custom_image') return false;

        // Side nav dots styling
        if (key === 'home_side_nav_style' && !sideNavDotsEnabled) return false;

        // Floating social
        if (key.startsWith('floating_social_') && !floatingSocialEnabled) return false;

        return true;
    });
}
