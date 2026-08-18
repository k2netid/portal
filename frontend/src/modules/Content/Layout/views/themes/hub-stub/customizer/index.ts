import type { ThemeCustomizerExtension } from '@/modules/Content/Layout/customizer/types/extension';
import bindingsRegistry from '@/modules/Content/Layout/views/themes/hub-stub/customizer/bindings.registry.json';
import sidebarNavigation from '@/modules/Content/Layout/views/themes/hub-stub/customizer/sidebar.navigation.json';
import sidebarPages from '@/modules/Content/Layout/views/themes/hub-stub/customizer/sidebar.pages.json';
import type { ThemeBindingRegistryComponent } from '@/modules/Content/Layout/config/themeBindingsRegistry';
import type { ThemeSpecialPageNavItem } from '@/modules/Content/Layout/customizer/types/extension';

/** Validates host-only customizer (platform schema + sidebar, no theme fields). */
export const hubStubCustomizerExtension: ThemeCustomizerExtension = {
    slug: 'hub-stub',
    bindings: bindingsRegistry.components as ThemeBindingRegistryComponent[],
    reservedManifestCategories: sidebarNavigation.reservedManifestCategories,
    specialPageNavItems: sidebarPages.specialPageItems as ThemeSpecialPageNavItem[],
};

export default hubStubCustomizerExtension;
