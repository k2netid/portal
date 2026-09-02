/**
 * Theme Jejakawan binding registry types + backward-compatible default export.
 * Per-theme data: views/themes/<slug>/customizer/bindings.registry.json
 */
import { getThemeBindingRegistry } from '@/modules/Layout/customizer/loaders/resolveThemeCustomizerExtension';

export interface ThemeBindingRegistryProp {
    key: string;
    labelKey: string;
}

export interface ThemeBindingRegistrySlot {
    id: string;
    labelKey: string;
    props: ThemeBindingRegistryProp[];
}

export interface ThemeBindingRegistryComponent {
    id: string;
    nameKey: string;
    descriptionKey: string;
    icon: string;
    manifestCategory?: string;
    manifestCategories?: string[];
    slots: ThemeBindingRegistrySlot[];
}

/** @deprecated Use getThemeBindingRegistry(slug) from customizer/loaders */
export const THEME_BINDING_REGISTRY: ThemeBindingRegistryComponent[] = getThemeBindingRegistry('janari');
