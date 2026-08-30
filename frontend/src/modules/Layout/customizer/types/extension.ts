import type { ThemeSetting } from '@/modules/Layout/types/theme';
import type { ThemeBindingRegistryComponent } from '@/modules/Layout/config/themeBindingsRegistry';

/** Who owns the setting definition and persistence semantics. */
export type CustomizerSettingScope = 'platform' | 'theme';

export interface CustomizerFilterContext {
    formValues: Record<string, unknown>;
    usesJanariCanvas: boolean;
}

/** Optional sidebar entries for theme-owned “special page” manifest categories. */
export interface ThemeSpecialPageNavItem {
    id: string;
    manifestCategories: string[];
    /** Lucide icon key used by host shell (e.g. settings2, user-circle). */
    icon: string;
    labelKey: string;
    descriptionKey: string;
}

/**
 * Optional theme package hook registered in views/themes/<slug>/customizer/index.ts
 */
export interface ThemeCustomizerExtension {
    /** Folder name under views/themes/ (e.g. janari). */
    slug: string;
    /** Jejakawan data bindings for theme sections (hero, cta, …). */
    bindings: ThemeBindingRegistryComponent[];
    /**
     * Manifest categories owned by host sidebar (identity, design, layout, …).
     * Component-specific categories are linked via bindings.manifestCategory.
     */
    reservedManifestCategories: string[];
    /** Hub landing pages exposed in the customizer “Special pages” group. */
    specialPageNavItems?: ThemeSpecialPageNavItem[];
    /** Hide or show settings based on other form values (e.g. Janari color preset). */
    filterVisibleSettings?: (
        settings: (ThemeSetting & { key: string })[],
        ctx: CustomizerFilterContext,
    ) => (ThemeSetting & { key: string })[];
    /** React to a single setting change (e.g. sync preset → primary color). */
    onSettingChange?: (
        key: string,
        value: unknown,
        formValues: Record<string, unknown>,
    ) => void;
}
