import type { ThemeCustomizerExtension } from '@/modules/Layout/customizer/types/extension';
import bindingsRegistry from '@/modules/Layout/views/themes/sarangenge/customizer/bindings.registry.json';
import sidebarNavigation from '@/modules/Layout/views/themes/sarangenge/customizer/sidebar.navigation.json';
import sidebarPages from '@/modules/Layout/views/themes/sarangenge/customizer/sidebar.pages.json';
import type { ThemeSpecialPageNavItem } from '@/modules/Layout/customizer/types/extension';
import type { ThemeBindingRegistryComponent } from '@/modules/Layout/config/themeBindingsRegistry';
import { filterSarangengeCustomizerSettings } from '@/modules/Layout/views/themes/sarangenge/customizer/composables/filterSarangengeCustomizerSettings';
import { onSarangengeSettingChange } from '@/modules/Layout/views/themes/sarangenge/customizer/composables/onSarangengeSettingChange';

export const sarangengeCustomizerExtension: ThemeCustomizerExtension = {
    slug: 'sarangenge',
    bindings: bindingsRegistry.components as ThemeBindingRegistryComponent[],
    reservedManifestCategories: sidebarNavigation.reservedManifestCategories,
    specialPageNavItems: sidebarPages.specialPageItems as ThemeSpecialPageNavItem[],
    filterVisibleSettings: filterSarangengeCustomizerSettings,
    onSettingChange: onSarangengeSettingChange,
};

export default sarangengeCustomizerExtension;
