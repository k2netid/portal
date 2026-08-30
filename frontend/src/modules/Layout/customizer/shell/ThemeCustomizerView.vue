<template>
  <div class="h-[calc(100vh-theme(spacing.36))] min-h-[640px] flex flex-col bg-card border border-border rounded-2xl shadow-sm overflow-hidden text-foreground select-none">
    <CustomizerHeader
      :theme="theme"
      :organization-mode="organizationMode"
      :can-undo="canUndo"
      :can-redo="canRedo"
      :is-dirty="isDirty"
      :saving="saving"
      :settings-open="settingsOpen"
      @back="handleBack"
      @undo="undo"
      @redo="redo"
      @update:organization-mode="organizationMode = $event"
      @toggle-settings="settingsOpen = !settingsOpen"
      @reset-initial="resetToInitial"
      @reset-defaults="resetToDefaults"
      @save="saveAll"
    />

    <div
      v-if="loading"
      class="flex-1 flex items-center justify-center bg-muted/5"
    >
      <div class="flex flex-col items-center gap-4">
        <div class="relative w-12 h-12">
          <div class="absolute inset-0 rounded-full border-4 border-primary/20" />
          <div class="absolute inset-0 rounded-full border-4 border-primary border-t-transparent animate-spin" />
        </div>
        <span class="text-sm font-medium animate-pulse text-muted-foreground">{{ t('publishing.theme_customizer.status.initializing') }}</span>
      </div>
    </div>

    <!-- WordPress/Shopify-style: nav + controls (left) | live preview canvas (dominant) -->
    <div
      v-else
      class="flex-1 flex overflow-hidden min-h-0"
    >
      <template v-if="settingsOpen">
        <CustomizerSidebar
          :groups="filteredGroups"
          :flat-items="flatNavItems"
          :active-item-id="activeItemId"
          :collapsed-groups="collapsedGroups"
          :search-query="searchQuery"
          :sidebar-collapsed="sidebarCollapsed"
          @select-item="selectItem"
          @toggle-group="toggleGroup"
          @update:search-query="searchQuery = $event"
          @update:sidebar-collapsed="sidebarCollapsed = $event"
        />

        <div class="w-[min(100%,22rem)] xl:w-[24rem] shrink-0 border-r border-border bg-background flex flex-col min-h-0 overflow-hidden">
          <CustomizerEditorCanvas
            v-model:custom-css="customCss"
            panel-mode
            :selected-item="selectedItem"
            :active-group-label="activeGroupLabel"
            :sidebar-collapsed="sidebarCollapsed"
            :organization-mode="organizationMode"
            :mode-hint-text="modeHintText"
            :slug="slug"
            :form-values="formValues"
            :menu-sections="menuSections"
            :get-visible-settings="getVisibleSettings"
            :is-item-compatible-with-mode="isItemCompatibleWithMode"
            :expanded-slots="expandedSlots"
            :active-binding-component-id="activeBindingComponentId"
            :categories="categories"
            :pages="pages"
            :preview-loading="previewLoading"
            :preview-results="previewResults"
            :get-slot-config="getSlotConfig"
            :get-source-label="getSourceLabel"
            :get-source-icon="getSourceIcon"
            :update-binding="updateBinding"
            :get-fields-for-source="getFieldsForSource"
            :filter-preview-fields="filterPreviewFields"
            :preview-slot-data="previewSlotData"
            :save-history="saveHistory"
            :toggle-slot="toggleSlot"
            @record-setting-change="recordSettingChange"
            @open-media-picker="openMediaPicker"
            @clear-preview="(id) => delete previewResults[id]"
            @open-nav-item="openNavItemById"
          />
        </div>
      </template>

      <section class="flex-1 min-w-0 min-h-0 flex flex-col bg-muted/30 border-l border-border/60">
        <PreviewArea
          class="flex-1 min-h-0"
          :preview-theme="previewTheme"
          :preview-url="publicPreviewUrl"
          enable-click-select
          @select-target="handlePreviewSelectTarget"
        />
      </section>
    </div>

    <MediaPicker
      v-model:open="showMediaPicker"
      @selected="handleMediaSelect"
    >
      <template #trigger>
        <span class="hidden" />
      </template>
    </MediaPicker>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRoute, useRouter, onBeforeRouteLeave } from 'vue-router';
import { useI18n } from 'vue-i18n';
import type { Theme } from '@/modules/Layout/types/theme';

import CustomizerHeader from '@/modules/Layout/components/themes/customizer/header/CustomizerHeader.vue';
import CustomizerSidebar from '@/modules/Layout/components/themes/customizer/sidebar/Sidebar.vue';
import CustomizerEditorCanvas from '@/modules/Layout/components/themes/customizer/editor/CustomizerEditorCanvas.vue';
import PreviewArea from '@/modules/Layout/components/themes/customizer/preview/PreviewArea.vue';
import MediaPicker from '@/modules/Media/components/picker/MediaPicker.vue';

import { useThemeCustomizer } from '@/modules/Layout/composables/useThemeCustomizer';
import { useCustomizerNavigation } from '@/modules/Layout/composables/useCustomizerNavigation';
import { useCustomizerDataSources } from '@/modules/Layout/composables/useCustomizerDataSources';
import { resolvePublicEmbedUrl } from '@/modules/Layout/utils/publicSiteUrl';
import {
  getThemePreviewTargets,
  resolvePreviewNavItemId,
} from '@/modules/Layout/customizer/preview/getThemePreviewTargets';
import type { CustomizerPreviewMode } from '@/modules/Layout/customizer/preview/protocol';

const { t, te } = useI18n();
const route = useRoute();
const router = useRouter();
const slug = route.params.slug as string;
const publicPreviewUrl = resolvePublicEmbedUrl('/');

const {
  theme,
  loading,
  saving,
  formValues,
  customCss,
  bindings,
  isDirty,
  canUndo,
  canRedo,
  fetchThemeData,
  saveAll,
  resetToInitial,
  resetToDefaults,
  undo,
  redo,
  saveHistory,
  recordSettingChange,
} = useThemeCustomizer(slug, t);

const {
  categories,
  pages,
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
} = useCustomizerDataSources(theme, bindings, saveHistory, t, te);

const {
  searchQuery,
  activeItemId,
  collapsedGroups,
  sidebarCollapsed,
  organizationMode,
  selectedItem,
  activeBindingComponentId,
  flatNavItems,
  activeGroupLabel,
  filteredGroups,
  themeComponents,
  modeHintText,
  selectItem,
  toggleGroup,
  getVisibleSettings,
  isItemCompatibleWithMode,
} = useCustomizerNavigation(
  slug,
  theme,
  formValues,
  bindings,
  t,
  (compId) => ensureComponentBindings(compId, themeComponents.value),
  expandedSlots,
);

/** Controls pane open (WP Customizer collapse). Live preview always stays on canvas. */
const settingsOpen = ref(true);

const previewTheme = computed<Theme>(() => {
  const base = (theme.value || {}) as Theme;
  const baseSettings = (base.settings || {}) as Record<string, unknown>;
  return {
    ...base,
    settings: {
      ...baseSettings,
      ...formValues.value,
    },
    custom_css: customCss.value,
  };
});

const showMediaPicker = ref(false);
const activeMediaField = ref<string | null>(null);
function openMediaPicker(key: string) {
  activeMediaField.value = key;
  showMediaPicker.value = true;
}
function handleMediaSelect(m: { url: string }) {
  if (activeMediaField.value) recordSettingChange(activeMediaField.value, m.url);
  showMediaPicker.value = false;
}

function openNavItemById(itemId: string) {
  const item = flatNavItems.value.find((nav) => nav.id === itemId);
  if (item) {
    settingsOpen.value = true;
    selectItem(item);
  }
}

/** Click section in live canvas → open matching controls (preview stays visible). */
function handlePreviewSelectTarget(payload: { target: string; mode?: CustomizerPreviewMode }) {
  const targets = getThemePreviewTargets(slug);
  const navItemId = resolvePreviewNavItemId(targets, payload.target, payload.mode);
  if (!navItemId) return;
  openNavItemById(navItemId);
}

function handleBack() {
  if (isDirty.value) {
    if (confirm(t('publishing.theme_customizer.messages.confirm_exit'))) router.push({ name: 'themes' });
  } else {
    router.push({ name: 'themes' });
  }
}

const handleBeforeUnload = (event: BeforeUnloadEvent) => {
  if (!isDirty.value) return;
  event.preventDefault();
  event.returnValue = '';
};

const handleKey = (e: KeyboardEvent) => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
    e.preventDefault();
    undo();
  }
  if ((e.ctrlKey || e.metaKey) && e.key === 'y') {
    e.preventDefault();
    redo();
  }
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault();
    if (isDirty.value) saveAll();
  }
  if ((e.ctrlKey || e.metaKey) && e.key === '\\') {
    e.preventDefault();
    settingsOpen.value = !settingsOpen.value;
  }
};

function applyPanelQuery(): void {
  const panel = typeof route.query.panel === 'string' ? route.query.panel.trim() : '';
  if (!panel) return;
  const match = flatNavItems.value.find((item) => item.panel === panel);
  if (match) {
    settingsOpen.value = true;
    selectItem(match);
  }
}

watch(flatNavItems, () => applyPanelQuery(), { once: true });
watch(() => route.query.panel, () => applyPanelQuery());

onMounted(() => {
  fetchThemeData();
  fetchMenus();
  fetchCategories();
  fetchPages();
  applyPanelQuery();
  window.addEventListener('keydown', handleKey);
  window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeRouteLeave(() => {
  if (!isDirty.value) {
    return true;
  }
  return confirm(t('publishing.theme_customizer.messages.confirm_exit'));
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKey);
  window.removeEventListener('beforeunload', handleBeforeUnload);
});
</script>
