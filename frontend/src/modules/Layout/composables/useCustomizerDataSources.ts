import { ref, reactive, computed, type Ref } from 'vue';
import api from '@/engine/api/client';
import toast from '@/shared/services/toastService';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import type { Theme } from '@/modules/Layout/types/theme';
import type { SlotBinding } from '@/modules/Layout/composables/useThemeDataBindings';
import type { ComponentSchema } from './useCustomizerNavigation';
import { Layout, LayoutTemplate, Newspaper, Search, Sparkles } from 'lucide-vue-next';

export function useCustomizerDataSources(
  theme: Ref<Theme | null>,
  bindings: Ref<Record<string, { slots?: Record<string, SlotBinding> }>>,
  saveHistory: () => void,
  t: (key: string, ...args: any[]) => string,
  te: (key: string) => boolean,
) {
  const categories = ref<any[]>([]);
  const pages = ref<any[]>([]);
  const availableMenus = ref<{ value: string; label: string }[]>([]);
  const expandedSlots = ref<string[]>([]);
  const previewLoading = ref<string | null>(null);
  const previewResults = reactive<Record<string, any[]>>({});

  async function fetchCategories() {
    try {
      const r = await api.get('/manage/library/categories');
      const parsed = parseResponse<any>(r);
      categories.value = ensureArray<any>(parsed.data);
    } catch {
      /* silent */
    }
  }

  async function fetchMenus() {
    try {
      const r = await api.get('/manage/layout/menus');
      const parsed = parseResponse<any>(r);
      const data = ensureArray<any>(parsed.data);
      availableMenus.value = data.map((m: any) => ({ value: m.id, label: m.name }));
      availableMenus.value.unshift({
        value: 'none',
        label: t('publishing.theme_customizer.editor.menus.placeholder'),
      });
    } catch {
      /* silent */
    }
  }

  async function fetchPages() {
    try {
      const r = await api.get('/manage/publishing/contents', { params: { type: 'page' } });
      const parsed = parseResponse<any>(r);
      pages.value = ensureArray<any>(parsed.data);
    } catch {
      /* silent */
    }
  }

  const menuSections = computed(() => {
    if (!theme.value?.manifest?.menus) return [];
    const menus = theme.value.manifest.menus;
    return Object.entries(menus).map(([locKey, locLabel]) => {
      const locTransKey = `publishing.theme_customizer.items.menus.locations.${locKey}`;
      const finalLabel = te(locTransKey) ? t(locTransKey) : String(locLabel);

      return {
        key: `menu_location_${locKey}`,
        label: finalLabel,
        type: 'select',
        category: 'Menus',
        options: availableMenus.value,
        description: t('publishing.theme_customizer.editor.menus.description', { label: finalLabel }),
      };
    });
  });

  function getSlotConfig(compId: string, slotId: string): SlotBinding {
    if (!bindings.value[compId]) bindings.value[compId] = { slots: {} };
    if (!bindings.value[compId].slots) bindings.value[compId].slots = {};
    if (!bindings.value[compId].slots![slotId]) {
      bindings.value[compId].slots![slotId] = {
        sourceType: 'static',
        categoryFilter: 'all',
        tagFilter: 'all',
        pageSlug: '',
        limit: 5,
        orderBy: 'published_at',
        orderDir: 'desc',
        propMapping: {},
      };
    } else if (!bindings.value[compId].slots![slotId]!.propMapping) {
      bindings.value[compId].slots![slotId]!.propMapping = {};
    }
    return bindings.value[compId].slots![slotId]!;
  }

  function updateBinding(compId: string, slotId: string, field: string, value: any) {
    const config = getSlotConfig(compId, slotId);
    (config as any)[field] = value;
    saveHistory();
  }

  function ensureComponentBindings(compId: string, themeComponents?: ComponentSchema[]) {
    if (!bindings.value[compId]) bindings.value[compId] = { slots: {} };
    if (!bindings.value[compId].slots) bindings.value[compId].slots = {};
    const comp = themeComponents?.find((c) => c.id === compId);
    const compBindings = bindings.value[compId];
    if (comp && compBindings && compBindings.slots) {
      comp.slots.forEach((slot) => {
        if (!compBindings.slots![slot.id]) {
          compBindings.slots![slot.id] = {
            sourceType: 'static',
            categoryFilter: 'all',
            tagFilter: 'all',
            pageSlug: '',
            limit: 5,
            orderBy: 'published_at',
            orderDir: 'desc',
            propMapping: {},
          };
        } else {
          const s = compBindings.slots![slot.id];
          if (s && !s.propMapping) {
            s.propMapping = {};
          }
        }
      });
    }
  }

  function toggleSlot(slotId: string) {
    if (expandedSlots.value.includes(slotId)) {
      expandedSlots.value = expandedSlots.value.filter((s) => s !== slotId);
    } else {
      expandedSlots.value.push(slotId);
    }
  }

  function getSourceLabel(src: string) {
    return (
      ({
        static: t('publishing.theme_customizer.sources.static'),
        api_posts: t('publishing.theme_customizer.sources.api_posts'),
        api_pages: t('publishing.theme_customizer.sources.api_pages'),
        api_categories: t('publishing.theme_customizer.sources.api_categories'),
      } as Record<string, string>)[src] || src
    );
  }

  function getSourceIcon(src: string) {
    return (
      ({
        static: LayoutTemplate,
        api_posts: Newspaper,
        api_pages: Layout,
        api_categories: Sparkles,
      } as Record<string, any>)[src] || Search
    );
  }

  function getFieldsForSource(src: string) {
    if (src === 'api_posts') {
      return [
        { value: 'title', label: t('publishing.theme_customizer.items.news_title') },
        { value: 'excerpt', label: t('publishing.theme_customizer.items.short_info') },
        { value: 'content', label: t('publishing.theme_customizer.items.message') },
        { value: 'thumbnail', label: t('publishing.theme_customizer.items.thumbnail') },
        { value: 'published_at', label: t('publishing.theme_customizer.items.date') },
        { value: 'category.name', label: t('common.labels.category') },
        { value: 'slug', label: t('publishing.theme_customizer.items.path') },
        { value: 'views', label: t('publishing.theme_customizer.editor.bindings.sort_options.views') },
      ];
    }
    if (src === 'api_pages') {
      return [
        { value: 'title', label: t('publishing.theme_customizer.items.title') },
        { value: 'thumbnail', label: t('publishing.theme_customizer.items.thumbnail') },
        { value: 'slug', label: t('publishing.theme_customizer.items.path') },
      ];
    }
    if (src === 'api_categories') {
      return [
        { value: 'name', label: t('common.labels.name') },
        { value: 'slug', label: t('publishing.theme_customizer.items.path') },
        { value: 'posts_count', label: t('publishing.theme_customizer.items.counter') },
      ];
    }
    return [];
  }

  async function previewSlotData(activeItemId: string, slotId: string) {
    if (!activeItemId) return;
    const compId = activeItemId.replace('comp-', '');
    const config = getSlotConfig(compId, slotId);
    if (config.sourceType === 'static') return;

    previewLoading.value = slotId;
    try {
      let results: any[] = [];
      if (config.sourceType === 'api_posts') {
        const params: any = {
          status: 'published',
          type: 'post',
          per_page: config.limit || 5,
          sort_by: config.orderBy || 'published_at',
        };
        if (config.categoryFilter && config.categoryFilter !== 'all') {
          params.category = config.categoryFilter;
        }
        const res = await api.get('/manage/publishing/contents', { params });
        const parsed = parseResponse<any>(res);
        results = ensureArray<any>(parsed.data);
      } else if (config.sourceType === 'api_pages') {
        const res = await api.get('/manage/publishing/contents', {
          params: { type: 'page', status: 'published' },
        });
        const parsed = parseResponse<any>(res);
        results = ensureArray<any>(parsed.data);
        if (config.pageSlug) {
          results = results.filter((item) => String(item?.slug || '') === String(config.pageSlug));
        }
      } else if (config.sourceType === 'api_categories') {
        const res = await api.get('/manage/library/categories');
        const parsed = parseResponse<any>(res);
        results = ensureArray<any>(parsed.data);
      }
      previewResults[slotId] = results;
    } catch {
      toast.error(
        t('publishing.theme_customizer.messages.error'),
        t('publishing.theme_customizer.messages.probe_failed'),
      );
    } finally {
      previewLoading.value = null;
    }
  }

  function filterPreviewFields(item: any) {
    const fields = ['title', 'name', 'slug', 'published_at', 'status'];
    const out: any = {};
    fields.forEach((f) => {
      if (item[f]) out[f] = item[f];
    });
    return out;
  }

  return {
    categories,
    pages,
    availableMenus,
    menuSections,
    expandedSlots,
    previewLoading,
    previewResults,
    fetchCategories,
    fetchMenus,
    fetchPages,
    getSlotConfig,
    updateBinding,
    ensureComponentBindings,
    toggleSlot,
    getSourceLabel,
    getSourceIcon,
    getFieldsForSource,
    previewSlotData,
    filterPreviewFields,
  };
}
