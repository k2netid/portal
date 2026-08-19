import type { ThemeCustomizerExtension } from '@/modules/Content/Layout/customizer/types/extension';
import bindingsRegistry from '@/modules/Content/Layout/views/themes/janari/customizer/bindings.registry.json';
import sidebarNavigation from '@/modules/Content/Layout/views/themes/janari/customizer/sidebar.navigation.json';
import sidebarPages from '@/modules/Content/Layout/views/themes/janari/customizer/sidebar.pages.json';
import type { ThemeSpecialPageNavItem } from '@/modules/Content/Layout/customizer/types/extension';
import type { ThemeBindingRegistryComponent } from '@/modules/Content/Layout/config/themeBindingsRegistry';
import { filterJanariCustomizerSettings } from '@/modules/Content/Layout/views/themes/janari/customizer/composables/filterJanariCustomizerSettings';
import { onJanariSettingChange } from '@/modules/Content/Layout/views/themes/janari/customizer/composables/onJanariSettingChange';

export const janariCustomizerExtension: ThemeCustomizerExtension = {
    slug: 'janari',
    bindings: bindingsRegistry.components as ThemeBindingRegistryComponent[],
    reservedManifestCategories: sidebarNavigation.reservedManifestCategories,
    specialPageNavItems: sidebarPages.specialPageItems as ThemeSpecialPageNavItem[],
    filterVisibleSettings: filterJanariCustomizerSettings,
    onSettingChange: onJanariSettingChange,
};

export default janariCustomizerExtension;
