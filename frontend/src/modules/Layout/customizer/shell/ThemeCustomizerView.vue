<template>
  <div class="h-[calc(100vh-theme(spacing.36))] min-h-[640px] flex flex-col bg-card border border-border rounded-2xl shadow-sm overflow-hidden text-foreground select-none">
    <!-- Top Header -->
    <CustomizerHeader
      :theme="theme"
      :organization-mode="organizationMode"
      :can-undo="canUndo"
      :can-redo="canRedo"
      :is-dirty="isDirty"
      :saving="saving"
      @back="handleBack"
      @undo="undo"
      @redo="redo"
      @update:organization-mode="organizationMode = $event"
      @open-preview="showPreview = true"
      @reset-initial="resetToInitial"
      @reset-defaults="resetToDefaults"
      @save="saveAll"
    />

    <!-- Main Card Body: Loading State -->
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

    <!-- Main Card Body: Active Customizer -->
    <div
      v-else
      class="flex-1 flex overflow-hidden"
    >
      <!-- Left Sidebar Menu -->
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

      <!-- Right Editor Content Canvas -->
      <CustomizerEditorCanvas
        v-model:custom-css="customCss"
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
      />
    </div>

    <!-- Fullscreen Preview Modal -->
    <Dialog
      :open="showPreview"
      @update:open="(open) => showPreview = open"
    >
      <DialogContent class="console-dialog-full">
        <div class="h-full flex flex-col bg-background">
          <div class="h-12 px-4 border-b border-border flex items-center shrink-0">
            <p class="text-sm font-semibold text-foreground">
              {{ t('publishing.theme_customizer.organization.preview_dialog_title') }}
            </p>
          </div>
          <div class="flex-1 min-h-0">
            <PreviewArea
              :preview-theme="previewTheme"
              preview-url="/"
            />
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Media Library Picker -->
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
import { Dialog, DialogContent } from '@/shared/components/ui';

// Subcomponents
import CustomizerHeader from '@/modules/Layout/components/themes/customizer/header/CustomizerHeader.vue';
import CustomizerSidebar from '@/modules/Layout/components/themes/customizer/sidebar/Sidebar.vue';
import CustomizerEditorCanvas from '@/modules/Layout/components/themes/customizer/editor/CustomizerEditorCanvas.vue';
import PreviewArea from '@/modules/Layout/components/themes/customizer/preview/PreviewArea.vue';
import MediaPicker from '@/modules/Media/components/picker/MediaPicker.vue';

// Composables
import { useThemeCustomizer } from '@/modules/Layout/composables/useThemeCustomizer';
import { useCustomizerNavigation } from '@/modules/Layout/composables/useCustomizerNavigation';
import { useCustomizerDataSources } from '@/modules/Layout/composables/useCustomizerDataSources';

const { t, te } = useI18n();
const route = useRoute();
const router = useRouter();
const slug = route.params.slug as string;

// Core Customizer State & Persistence
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

// Data Sources (Categories, Menus, Pages, Slot Data Probes)
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

// Customizer Navigation (Sidebar Groups, Active Selection, Mode Filtering)
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

// Preview Theme Computed State
const showPreview = ref(false);
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

// Media Picker
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

// Navigation & Exit Guard
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

// Keyboard Shortcuts (Ctrl+Z, Ctrl+Y, Ctrl+S)
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
};

/** Deep-link from Menu Builder / Site Editor: ?panel=menus (or css). */
function applyPanelQuery(): void {
  const panel = typeof route.query.panel === 'string' ? route.query.panel.trim() : '';
  if (!panel) return;
  const match = flatNavItems.value.find((item) => item.panel === panel);
  if (match) {
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
