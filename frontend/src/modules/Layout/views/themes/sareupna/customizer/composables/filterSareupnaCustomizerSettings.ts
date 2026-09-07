import type { ThemeSetting } from '@/modules/Layout/types/theme';
import type { CustomizerFilterContext } from '@/modules/Layout/customizer/types/extension';

function isTruthy(val: unknown, defaultValue = true): boolean {
    if (val === undefined || val === null) return defaultValue;
    if (typeof val === 'boolean') return val;
    if (typeof val === 'number') return val !== 0;
    if (typeof val === 'string') {
        const s = val.trim().toLowerCase();
        if (s === 'false' || s === '0' || s === 'no' || s === 'off') return false;
        if (s === 'true' || s === '1' || s === 'yes' || s === 'on') return true;
    }
    return Boolean(val);
}

export function filterSareupnaCustomizerSettings(
    settings: (ThemeSetting & { key: string })[],
    ctx: CustomizerFilterContext,
): (ThemeSetting & { key: string })[] {
    const heroBgType = String(ctx.formValues.hero_bg_type || 'preset');
    const sideNavDotsEnabled = isTruthy(ctx.formValues.home_side_nav_dots, true);
    const floatingSocialEnabled = isTruthy(ctx.formValues.enable_floating_social, true);

    return settings.filter((setting) => {
        const key = String(setting?.key || '');
        if (!key) return true;
        if ((setting as { hidden?: boolean }).hidden) return false;

        // Hero background conditional fields
        if (key === 'hero_bg_preset' && heroBgType !== 'preset') return false;
        if ((key === 'hero_bg_image' || key === 'hero_bg_overlay_opacity') && heroBgType !== 'custom_image') return false;

        // Side nav dots hierarchy (Single Source of Truth: home_side_nav_dots)
        // If master toggle is disabled, hide all derivative settings
        if (key.startsWith('home_side_nav_') && key !== 'home_side_nav_dots' && !sideNavDotsEnabled) {
            return false;
        }

        // Floating social dock hierarchy (Single Source of Truth: enable_floating_social)
        // If master toggle is disabled, hide all derivative settings
        if (key.startsWith('floating_social_') && !floatingSocialEnabled) {
            return false;
        }

        return true;
    });
}

