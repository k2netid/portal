import type { ThemeSetting } from '@/modules/Layout/types/theme';
import type { CustomizerFilterContext } from '@/modules/Layout/customizer/types/extension';

export function filterLayungCustomizerSettings(
    settings: (ThemeSetting & { key: string })[],
    ctx: CustomizerFilterContext,
): (ThemeSetting & { key: string })[] {
    const calculatorEnabled = ctx.formValues.speed_calculator_enabled !== false;
    const heroAnimationEnabled = ctx.formValues.hero_animation_enabled !== false;
    const heroSliderEnabled = ctx.formValues.hero_slider_enabled === true;
    const heroBgType = String(ctx.formValues.hero_bg_type || 'preset');

    const sideNavDotsEnabled = ctx.formValues.home_side_nav_dots !== false;
    const floatingSocialEnabled = ctx.formValues.enable_floating_social !== false;

    return settings.filter((setting) => {
        const key = String(setting?.key || '');
        if (!key) return true;
        if ((setting as { hidden?: boolean }).hidden) return false;
        if (key.startsWith('home_side_nav_') && key !== 'home_side_nav_dots' && !sideNavDotsEnabled) return false;
        if (key.startsWith('floating_social_') && !floatingSocialEnabled) return false;
        if (key.startsWith('speed_calculator_') && !calculatorEnabled) return false;
        if (key === 'hero_animation_type' && !heroAnimationEnabled) return false;
        if ((key === 'hero_slider_autoplay' || key === 'hero_slider_interval') && !heroSliderEnabled) return false;
        if (key === 'hero_bg_preset' && heroBgType !== 'preset') return false;
        if ((key === 'hero_bg_image' || key === 'hero_bg_overlay_opacity') && heroBgType !== 'custom_image') return false;
        return true;
    });
}
