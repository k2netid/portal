import type { ThemeCustomizerExtension } from '@/modules/Layout/customizer/types/extension';
import type { ThemeSpecialPageNavItem } from '@/modules/Layout/customizer/types/extension';
import type { ThemeBindingRegistryComponent } from '@/modules/Layout/config/themeBindingsRegistry';
import bindingsRegistry from '@/modules/Layout/views/themes/sareupna/customizer/bindings.registry.json';
import sidebarNavigation from '@/modules/Layout/views/themes/sareupna/customizer/sidebar.navigation.json';
import sidebarPages from '@/modules/Layout/views/themes/sareupna/customizer/sidebar.pages.json';
import { filterSareupnaCustomizerSettings } from '@/modules/Layout/views/themes/sareupna/customizer/composables/filterSareupnaCustomizerSettings';
import { onSareupnaSettingChange } from '@/modules/Layout/views/themes/sareupna/customizer/composables/onSareupnaSettingChange';

export const sareupnaCustomizerExtension: ThemeCustomizerExtension = {
    slug: 'sareupna',
    bindings: bindingsRegistry.components as ThemeBindingRegistryComponent[],
    reservedManifestCategories: sidebarNavigation.reservedManifestCategories,
    specialPageNavItems: sidebarPages.specialPageItems as ThemeSpecialPageNavItem[],
    filterVisibleSettings: filterSareupnaCustomizerSettings,
    onSettingChange: onSareupnaSettingChange,
};

export default sareupnaCustomizerExtension;
