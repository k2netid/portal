import { ref, computed, watch, type Ref } from 'vue';
import type { Theme, ThemeSection } from '@/modules/Layout/types/theme';
import type { SlotBinding } from '@/modules/Layout/composables/useThemeDataBindings';
import {
  Award,
  BarChart3,
  Briefcase,
  Code2,
  Globe,
  GraduationCap,
  ImageIcon,
  LayoutTemplate,
  Megaphone,
  MenuIcon,
  MessageSquare,
  Newspaper,
  Palette,
  PanelBottom,
  PanelsTopLeft,
  Settings2,
  Share2,
  Sparkles,
  Type,
  UserCircle,
} from 'lucide-vue-next';
import {
  getThemeBindingRegistry,
  getReservedManifestCategories,
  resolveThemeCustomizerExtension,
} from '@/modules/Layout/customizer/loaders/resolveThemeCustomizerExtension';
import {
  buildPlatformSidebarGroups,
  type CustomizerNavItem,
} from '@/modules/Layout/customizer/shell/buildPlatformSidebarNav';
import { useThemeCustomizerLabels } from '@/modules/Layout/composables/useThemeCustomizerLabels';
import { themeUsesJanariCanvas } from '@/modules/Layout/utils/themeManifest';

export interface ComponentSchema {
  id: string;
  name: string;
  description: string;
  icon: any;
  slots: { id: string; label: string; props: { key: string; label: string }[] }[];
  manifestCategory?: string;
}

export interface NavItem extends CustomizerNavItem {
  bindingComponent?: ComponentSchema;
}

export type SidebarSelectPayload = Pick<NavItem, 'id' | 'bindingComponent' | 'panel'>;

const iconByRegistryKey: Record<string, any> = {
  hero: ImageIcon,
  principal: UserCircle,
  news: Newspaper,
  majors: GraduationCap,
  stats: BarChart3,
  testimonials: MessageSquare,
  cta: Megaphone,
};

const sidebarNavIconByKey: Record<string, any> = {
  globe: Globe,
  sparkles: Sparkles,
  menu: MenuIcon,
  'share-2': Share2,
  palette: Palette,
  type: Type,
  'code-2': Code2,
  'panels-top-left': PanelsTopLeft,
  'panel-bottom': PanelBottom,
  settings2: Settings2,
  'user-circle': UserCircle,
  award: Award,
  briefcase: Briefcase,
  newspaper: Newspaper,
  'message-square': MessageSquare,
};

export function useCustomizerNavigation(
  slug: string,
  theme: Ref<Theme | null>,
  formValues: Ref<Record<string, unknown>>,
  bindings: Ref<Record<string, { slots?: Record<string, SlotBinding> }>>,
  t: (key: string, ...args: any[]) => string,
  ensureComponentBindings: (compId: string) => void,
  expandedSlots: Ref<string[]>,
) {
  const { categoryLabel } = useThemeCustomizerLabels(slug);
  const themeCustomizerExtension = computed(() => resolveThemeCustomizerExtension(slug));
  const dedicatedManifestCategories = computed(() => getReservedManifestCategories(slug));

  const searchQuery = ref('');
  const activeItemId = ref('');
  const collapsedGroups = ref<string[]>([]);
  const sidebarCollapsed = ref(false);
  const organizationMode = ref<'design' | 'bindings' | 'advanced'>('design');

  function hasComponentBindings(compId: string): boolean {
    const b = bindings.value[compId];
    if (!b || !b.slots) return false;
    const slots = b.slots as Record<string, SlotBinding>;
    return Object.values(slots).some((slot) => slot.sourceType !== 'static');
  }

  // Helper to find manifest sections by category labels
  function findSections(catLabels: string[]): ThemeSection[] {
    if (!theme.value?.manifest?.settings_schema) return [];
    const schema = theme.value.manifest.settings_schema;
    const sections: Record<string, ThemeSection> = {};

    Object.keys(schema).forEach((key) => {
      const s = schema[key];
      if (s && catLabels.includes(s.category || 'General') && !s.hidden) {
        const cat = s.category || 'General';
        const translatedLabel = categoryLabel(cat);

        if (!sections[cat]) sections[cat] = { id: cat, label: translatedLabel, settings: [] };
        sections[cat].settings.push({ key, ...s });
      }
    });
    return Object.values(sections);
  }

  function getVisibleSettings(settings: any[]) {
    if (!Array.isArray(settings)) return [];

    const extension = themeCustomizerExtension.value;
    if (extension?.filterVisibleSettings) {
      return extension.filterVisibleSettings(settings, {
        formValues: formValues.value,
        usesJanariCanvas: themeUsesJanariCanvas(theme.value),
      });
    }

    return settings.filter((setting: { hidden?: boolean }) => !setting.hidden);
  }

  const specialPageNavItems = computed<NavItem[]>(() => {
    const items = themeCustomizerExtension.value?.specialPageNavItems ?? [];
    return items.map((item) => ({
      id: item.id,
      label: t(item.labelKey),
      description: t(item.descriptionKey),
      icon: sidebarNavIconByKey[item.icon] ?? Globe,
      manifestSections: findSections(item.manifestCategories),
      hasBinding: false,
    }));
  });

  const themeComponents = computed<ComponentSchema[]>(() =>
    getThemeBindingRegistry(slug).map((component) => ({
      id: component.id,
      name: t(component.nameKey),
      description: t(component.descriptionKey),
      icon: iconByRegistryKey[component.icon] || LayoutTemplate,
      manifestCategory: component.manifestCategory,
      slots: component.slots.map((slot) => ({
        id: slot.id,
        label: t(slot.labelKey),
        props: slot.props.map((prop) => ({
          key: prop.key,
          label: t(prop.labelKey),
        })),
      })),
    })),
  );

  const platformSidebarGroups = computed(() =>
    buildPlatformSidebarGroups(t, findSections, sidebarNavIconByKey),
  );

  const sidebarGroups = computed(() => {
    const groups: { id: string; label: string; items: NavItem[] }[] = [
      ...platformSidebarGroups.value,
      {
        id: 'components',
        label: t('publishing.theme_customizer.sidebar.categories.components'),
        items: themeComponents.value.map((comp) => ({
          id: `comp-${comp.id}`,
          label: comp.name,
          description: comp.description,
          icon: comp.icon,
          bindingComponent: comp,
          manifestSections:
            comp.manifestCategory && !dedicatedManifestCategories.value.has(comp.manifestCategory)
              ? findSections([comp.manifestCategory])
              : [],
          hasBinding: hasComponentBindings(comp.id),
        })),
      },
      ...(specialPageNavItems.value.length > 0
        ? [
            {
              id: 'special-pages',
              label: t('publishing.theme_customizer.sidebar.categories.special_pages'),
              items: specialPageNavItems.value,
            },
          ]
        : []),
    ];

    return groups
      .map((group) => ({
        ...group,
        items: group.items.filter((item) => {
          if (item.panel) return true;
          if (item.bindingComponent) return true;
          if (item.manifestSections && item.manifestSections.length > 0) {
            return item.manifestSections.some((s) => s.settings && s.settings.length > 0);
          }
          return false;
        }),
      }))
      .filter((group) => group.items.length > 0);
  });

  const filteredGroups = computed(() => {
    if (!searchQuery.value) return sidebarGroups.value;
    const query = searchQuery.value.toLowerCase();
    return sidebarGroups.value
      .map((g) => ({
        ...g,
        items: g.items.filter(
          (i) =>
            i.label.toLowerCase().includes(query) ||
            i.description.toLowerCase().includes(query),
        ),
      }))
      .filter((g) => g.items.length > 0);
  });

  const selectedItem = computed(() => {
    for (const g of sidebarGroups.value) {
      const found = g.items.find((i) => i.id === activeItemId.value);
      if (found) return found as NavItem;
    }
    return null;
  });

  const activeBindingComponentId = computed(() => selectedItem.value?.bindingComponent?.id || '');
  const flatNavItems = computed(() => sidebarGroups.value.flatMap((group) => group.items as NavItem[]));

  const activeGroupLabel = computed(() => {
    for (const g of sidebarGroups.value) {
      if (g.items.some((i) => i.id === activeItemId.value)) return g.label;
    }
    return '';
  });

  function selectItem(item: SidebarSelectPayload) {
    activeItemId.value = item.id;
    if (item.panel === 'css') {
      organizationMode.value = 'advanced';
    } else if (item.bindingComponent) {
      organizationMode.value = 'bindings';
    } else {
      organizationMode.value = 'design';
    }
    if (item.bindingComponent && item.bindingComponent.slots.length > 0) {
      ensureComponentBindings(item.bindingComponent.id);
      expandedSlots.value = [item.bindingComponent.slots[0]!.id];
    }
  }

  function getAllNavItems(): NavItem[] {
    return sidebarGroups.value.flatMap((group) => group.items as NavItem[]);
  }

  function pickItemForMode(mode: 'design' | 'bindings' | 'advanced'): NavItem | null {
    const items = getAllNavItems();
    if (mode === 'advanced') {
      return items.find((item) => item.panel === 'css') || null;
    }
    if (mode === 'bindings') {
      return items.find((item) => !!item.bindingComponent) || null;
    }
    return items.find((item) => !item.bindingComponent && (!item.panel || item.panel === 'menus')) || null;
  }

  function isItemCompatibleWithMode(item: NavItem | null): boolean {
    if (!item) return true;
    if (item.panel === 'css') return organizationMode.value === 'advanced';
    if (item.panel === 'menus') return organizationMode.value === 'design';
    if (item.bindingComponent) return organizationMode.value === 'bindings';
    return organizationMode.value === 'design';
  }

  function ensureSelectionForMode(mode: 'design' | 'bindings' | 'advanced') {
    const current = selectedItem.value;
    if (current && isItemCompatibleWithMode(current)) return;
    const fallback = pickItemForMode(mode);
    if (fallback) selectItem(fallback);
  }

  const modeHintText = computed(() => {
    if (!selectedItem.value) return '';
    if (selectedItem.value.panel === 'css') return t('publishing.theme_customizer.organization.hints.advanced');
    if (selectedItem.value.bindingComponent) return t('publishing.theme_customizer.organization.hints.bindings');
    return t('publishing.theme_customizer.organization.hints.design');
  });

  watch(
    organizationMode,
    (mode) => {
      ensureSelectionForMode(mode);
    },
    { immediate: false },
  );

  watch(
    sidebarGroups,
    () => {
      if (!selectedItem.value) {
        ensureSelectionForMode(organizationMode.value);
      }
    },
    { immediate: true },
  );

  function toggleGroup(groupId: string) {
    if (collapsedGroups.value.includes(groupId)) {
      collapsedGroups.value = collapsedGroups.value.filter((g) => g !== groupId);
    } else {
      collapsedGroups.value.push(groupId);
    }
  }

  return {
    searchQuery,
    activeItemId,
    collapsedGroups,
    sidebarCollapsed,
    organizationMode,
    selectedItem,
    activeBindingComponentId,
    flatNavItems,
    activeGroupLabel,
    sidebarGroups,
    filteredGroups,
    themeComponents,
    modeHintText,
    selectItem,
    toggleGroup,
    findSections,
    getVisibleSettings,
    isItemCompatibleWithMode,
  };
}
