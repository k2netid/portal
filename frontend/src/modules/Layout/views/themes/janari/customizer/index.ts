import type { ThemeCustomizerExtension } from '@/modules/Layout/customizer/types/extension';
import bindingsRegistry from '@/modules/Layout/views/themes/janari/customizer/bindings.registry.json';
import sidebarNavigation from '@/modules/Layout/views/themes/janari/customizer/sidebar.navigation.json';
import sidebarPages from '@/modules/Layout/views/themes/janari/customizer/sidebar.pages.json';
import type { ThemeSpecialPageNavItem } from '@/modules/Layout/customizer/types/extension';
import type { ThemeBindingRegistryComponent } from '@/modules/Layout/config/themeBindingsRegistry';
import { filterJanariCustomizerSettings } from '@/modules/Layout/views/themes/janari/customizer/composables/filterJanariCustomizerSettings';
import { onJanariSettingChange } from '@/modules/Layout/views/themes/janari/customizer/composables/onJanariSettingChange';

export const janariCustomizerExtension: ThemeCustomizerExtension = {
    slug: 'janari',
    bindings: bindingsRegistry.components as ThemeBindingRegistryComponent[],
    reservedManifestCategories: sidebarNavigation.reservedManifestCategories,
    specialPageNavItems: sidebarPages.specialPageItems as ThemeSpecialPageNavItem[],
    filterVisibleSettings: filterJanariCustomizerSettings,
    onSettingChange: onJanariSettingChange,
};

export default janariCustomizerExtension;
