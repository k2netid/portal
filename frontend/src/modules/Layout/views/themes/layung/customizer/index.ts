import type { ThemeCustomizerExtension } from '@/modules/Layout/customizer/types/extension';
import type { ThemeSpecialPageNavItem } from '@/modules/Layout/customizer/types/extension';
import type { ThemeBindingRegistryComponent } from '@/modules/Layout/config/themeBindingsRegistry';
import bindingsRegistry from '@/modules/Layout/views/themes/layung/customizer/bindings.registry.json';
import sidebarNavigation from '@/modules/Layout/views/themes/layung/customizer/sidebar.navigation.json';
import sidebarPages from '@/modules/Layout/views/themes/layung/customizer/sidebar.pages.json';
import { filterLayungCustomizerSettings } from '@/modules/Layout/views/themes/layung/customizer/composables/filterLayungCustomizerSettings';
import { onLayungSettingChange } from '@/modules/Layout/views/themes/layung/customizer/composables/onLayungSettingChange';

export const layungCustomizerExtension: ThemeCustomizerExtension = {
    slug: 'layung',
    bindings: bindingsRegistry.components as ThemeBindingRegistryComponent[],
    reservedManifestCategories: sidebarNavigation.reservedManifestCategories,
    specialPageNavItems: sidebarPages.specialPageItems as ThemeSpecialPageNavItem[],
    filterVisibleSettings: filterLayungCustomizerSettings,
    onSettingChange: onLayungSettingChange,
};

export default layungCustomizerExtension;
