<template>
  <main class="flex-1 overflow-y-auto relative bg-background custom-scrollbar">
    <div
      v-if="selectedItem"
      class="w-full transition-all duration-300 p-6 sm:p-8 lg:p-10 space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-300"
      :class="sidebarCollapsed ? 'max-w-7xl mx-auto' : 'max-w-5xl mx-auto'"
    >
      <!-- Section Title Header / Banner -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-border">
        <div class="flex items-center gap-3.5">
          <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary border border-primary/20 flex items-center justify-center shrink-0 shadow-inner">
            <component
              :is="selectedItem.icon"
              class="w-5 h-5"
            />
          </div>
          <div>
            <h2 class="text-xl font-bold tracking-tight text-foreground">
              {{ selectedItem.label }}
            </h2>
            <p class="text-xs sm:text-sm text-muted-foreground mt-0.5 font-medium">
              {{ selectedItem.description }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <Badge
            variant="secondary"
            class="rounded-full text-xs font-semibold px-3 py-1 gap-1.5 bg-muted border-border"
          >
            <Layout class="w-3 h-3 text-muted-foreground" />
            <span>{{ activeGroupLabel }}</span>
          </Badge>
        </div>
      </div>

      <!-- Settings Form Content -->
      <div class="space-y-6 pb-12">
        <div
          v-if="!isItemCompatibleWithMode(selectedItem)"
          class="rounded-xl border border-dashed border-border bg-muted/20 p-5 text-sm text-muted-foreground"
        >
          {{ modeHintText }}
        </div>

        <!-- Type 1: Static CSS Editor (Direct textarea) -->
        <div
          v-if="selectedItem.panel === 'css' && organizationMode === 'advanced'"
          class="space-y-3 relative"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
              <Code2 class="w-3.5 h-3.5 text-primary" />
              {{ t('publishing.theme_customizer.editor.css.label', 'Kustomisasi CSS') }}
            </span>
          </div>
          <div class="border border-border rounded-xl overflow-hidden shadow-inner bg-background">
            <textarea
              :value="customCss"
              rows="24"
              :aria-label="t('publishing.theme_customizer.editor.css.label')"
              class="w-full p-4 bg-transparent text-xs font-mono leading-relaxed focus:outline-none resize-none min-h-[480px] border-0 custom-scrollbar selection:bg-primary/20"
              :placeholder="t('publishing.theme_customizer.editor.css.placeholder')"
              spellcheck="false"
              @input="emit('update:customCss', ($event.target as HTMLTextAreaElement).value)"
            />
          </div>
        </div>

        <!-- Type 2: Manifest-Driven Settings (Responsive Grid) -->
        <section
          v-if="selectedItem.manifestSections?.length && organizationMode === 'design'"
          class="space-y-6"
        >
          <div
            v-for="section in selectedItem.manifestSections"
            :key="section.id"
            class="space-y-6"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div
                v-for="setting in getVisibleSettings(section.settings)"
                :key="setting.key"
                :class="[
                  setting.type === 'repeater' || setting.type === 'textarea' || setting.type === 'media' || setting.type === 'checkbox_list'
                    ? 'col-span-1 md:col-span-2'
                    : 'col-span-1'
                ]"
              >
                <SettingControl
                  :theme-slug="slug"
                  :setting="setting"
                  :model-value="formValues[setting.key]"
                  @update:model-value="(val: any) => emit('recordSettingChange', setting.key, val)"
                  @pick-media="emit('openMediaPicker', setting.key)"
                />
              </div>
            </div>
          </div>
        </section>

        <!-- Type 3: Menu Locations -->
        <section
          v-if="selectedItem.panel === 'menus' && organizationMode === 'design'"
          class="space-y-4"
        >
          <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
            <MenuIcon class="w-3.5 h-3.5 text-primary" />
            {{ t('publishing.theme_customizer.editor.sections.menus', 'Lokasi Menu') }}
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
              v-for="menuSetting in menuSections"
              :key="menuSetting.key"
              class="space-y-2.5 p-4 bg-muted/20 rounded-xl border border-border transition-all hover:border-primary/30"
            >
              <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-foreground tracking-wide">{{ menuSetting.label }}</label>
                <div
                  class="w-2 h-2 rounded-full"
                  :class="formValues[menuSetting.key] && formValues[menuSetting.key] !== 'none' ? 'bg-emerald-500 animate-pulse' : 'bg-muted-foreground/30'"
                />
              </div>
              <Select
                :model-value="String(formValues[menuSetting.key] || 'none')"
                @update:model-value="(val: string) => emit('recordSettingChange', menuSetting.key, val)"
              >
                <SelectTrigger
                  :aria-label="menuSetting.label"
                  class="w-full h-10 bg-background border-border rounded-xl text-sm font-medium"
                >
                  <SelectValue :placeholder="t('publishing.theme_customizer.editor.menus.placeholder')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="opt in (menuSetting.options || [])"
                    :key="String(opt.value)"
                    :value="String(opt.value)"
                  >
                    {{ opt.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p class="text-[11px] text-muted-foreground italic">
                {{ menuSetting.description }}
              </p>
            </div>
          </div>
        </section>

        <!-- SEO & Social Share Preview (Core Branding / Site Profile) -->
        <section
          v-if="(selectedItem.id === 'brand_core' || selectedItem.id === 'site_profile') && organizationMode === 'design'"
          class="p-6 rounded-2xl border border-border bg-card shadow-sm space-y-4"
        >
          <SocialSharePreview
            :site-title="String(formValues['site_title'] || '')"
            :site-tagline="String(formValues['site_tagline'] || '')"
            :brand-logo="String(formValues['brand_logo'] || '')"
            :favicon="String(formValues['site_favicon'] || '')"
            :social-links="(formValues['social_links'] as any) || []"
          />
        </section>

        <!-- Bindings Section -->
        <BindingsSection
          :selected-item="selectedItem"
          :organization-mode="organizationMode"
          :expanded-slots="expandedSlots"
          :active-binding-component-id="activeBindingComponentId"
          :categories="categories"
          :pages="pages"
          :preview-loading="previewLoading"
          :preview-results="previewResults"
          :toggle-slot="toggleSlot"
          :get-slot-config="getSlotConfig"
          :get-source-label="getSourceLabel"
          :get-source-icon="getSourceIcon"
          :update-binding="updateBinding"
          :get-fields-for-source="getFieldsForSource"
          :filter-preview-fields="filterPreviewFields"
          :preview-slot-data="(slotId: string) => previewSlotData(selectedItem?.id || '', slotId)"
          :save-history="saveHistory"
          @clear-preview="(id: string) => emit('clearPreview', id)"
        />
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="h-full flex flex-col items-center justify-center text-muted-foreground p-12 text-center animate-in zoom-in-95 duration-500"
    >
      <div class="relative mb-8">
        <div class="absolute inset-[-40px] bg-primary/10 rounded-full blur-3xl animate-pulse" />
        <LayoutTemplate class="w-24 h-24 mb-4 relative opacity-40 text-primary" />
      </div>
      <h3 class="text-2xl font-black text-foreground tracking-tight">
        {{ t('publishing.theme_customizer.empty_state.title') }}
      </h3>
      <p class="text-sm mt-3 max-w-sm font-medium">
        {{ t('publishing.theme_customizer.empty_state.description') }}
      </p>
    </div>
  </main>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import type { NavItem } from '@/modules/Content/Layout/composables/useCustomizerNavigation';
import type { SlotBinding } from '@/modules/Content/Layout/composables/useThemeDataBindings';
import {
  Badge,
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from '@/shared/components/ui';
import SettingControl from '@/modules/Content/Layout/components/themes/customizer/sidebar/SettingControl.vue';
import BindingsSection from '@/modules/Content/Layout/components/themes/customizer/editor/BindingsSection.vue';
import SocialSharePreview from '@/modules/Content/Layout/components/themes/customizer/seo/SocialSharePreview.vue';
import { Code2, Layout, LayoutTemplate, MenuIcon } from 'lucide-vue-next';

defineProps<{
  selectedItem: NavItem | null;
  activeGroupLabel: string;
  sidebarCollapsed: boolean;
  organizationMode: 'design' | 'bindings' | 'advanced';
  modeHintText: string;
  slug: string;
  customCss: string;
  formValues: Record<string, unknown>;
  menuSections: any[];
  getVisibleSettings: (settings: any[]) => any[];
  isItemCompatibleWithMode: (item: NavItem | null) => boolean;
  expandedSlots: string[];
  activeBindingComponentId: string;
  categories: any[];
  pages: any[];
  previewLoading: string | null;
  previewResults: Record<string, any[]>;
  getSlotConfig: (compId: string, slotId: string) => SlotBinding;
  getSourceLabel: (src: string) => string;
  getSourceIcon: (src: string) => any;
  updateBinding: (compId: string, slotId: string, field: string, value: any) => void;
  getFieldsForSource: (src: string) => any[];
  filterPreviewFields: (item: any) => any;
  previewSlotData: (activeItemId: string, slotId: string) => Promise<void>;
  saveHistory: () => void;
  toggleSlot: (slotId: string) => void;
}>();

const emit = defineEmits<{
  (e: 'update:customCss', val: string): void;
  (e: 'recordSettingChange', key: string, val: unknown): void;
  (e: 'openMediaPicker', key: string): void;
  (e: 'clearPreview', id: string): void;
}>();

const { t } = useI18n();
</script>
