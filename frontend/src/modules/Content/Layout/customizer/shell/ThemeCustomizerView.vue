<template>
  <div class="h-[calc(100vh-theme(spacing.16))] flex flex-col bg-background text-foreground select-none">
    <!-- Header -->
    <header class="flex items-center justify-between border-b border-border px-6 py-3 bg-card shrink-0 shadow-sm z-20">
      <div class="flex items-center gap-3.5">
        <Button
          variant="ghost"
          size="icon"
          class="h-9 w-9 rounded-xl text-muted-foreground hover:text-foreground"
          :aria-label="t('publishing.theme_customizer.actions.back_tooltip')"
          :title="t('publishing.theme_customizer.actions.back_tooltip')"
          @click="handleBack"
        >
          <ArrowLeft class="w-5 h-5" />
        </Button>
        <div>
          <h1 class="text-base font-bold tracking-tight text-foreground">
            {{ t('publishing.theme_customizer.title', 'Theme Customizer') }}
          </h1>
          <p class="text-xs font-semibold text-muted-foreground capitalize">
            {{ theme?.name || t('common.labels.loading') }}
          </p>
        </div>
      </div>
            
      <div class="flex items-center gap-3">
        <!-- History Controls -->
        <div class="flex items-center border border-border rounded-xl bg-background overflow-hidden p-0.5 shadow-sm">
          <button
            type="button"
            :disabled="!canUndo"
            class="p-1.5 rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground disabled:opacity-20 transition-colors"
            :aria-label="t('publishing.theme_customizer.actions.undo')"
            :title="t('publishing.theme_customizer.actions.undo')"
            @click="undo"
          >
            <Undo2 class="w-4 h-4" />
          </button>
          <div class="w-px h-4 bg-border mx-0.5" />
          <button
            type="button"
            :disabled="!canRedo"
            class="p-1.5 rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground disabled:opacity-20 transition-colors"
            :aria-label="t('publishing.theme_customizer.actions.redo')"
            :title="t('publishing.theme_customizer.actions.redo')"
            @click="redo"
          >
            <Redo2 class="w-4 h-4" />
          </button>
        </div>

        <div class="h-6 w-px bg-border hidden sm:block" />

        <!-- Mode Selector (Segmented Control) -->
        <div class="hidden lg:flex items-center gap-1 rounded-xl border border-border bg-muted/40 p-1">
          <button
            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all"
            :class="organizationMode === 'design' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
            @click="organizationMode = 'design'"
          >
            {{ t('publishing.theme_customizer.organization.modes.design', 'Design') }}
          </button>
          <button
            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all"
            :class="organizationMode === 'bindings' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
            @click="organizationMode = 'bindings'"
          >
            {{ t('publishing.theme_customizer.organization.modes.bindings', 'Content') }}
          </button>
          <button
            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all"
            :class="organizationMode === 'advanced' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
            @click="organizationMode = 'advanced'"
          >
            {{ t('publishing.theme_customizer.organization.modes.advanced', 'Advanced') }}
          </button>
        </div>

        <Button
          variant="outline"
          size="sm"
          class="hidden xl:inline-flex h-9 items-center gap-1.5 rounded-xl font-medium text-xs"
          @click="showPreview = true"
        >
          <Eye data-icon="inline-start" class="w-3.5 h-3.5 shrink-0" />
          {{ t('publishing.theme_customizer.organization.preview', 'Pratinjau') }}
        </Button>

        <div class="flex items-center gap-2">
          <span
            v-if="isDirty"
            class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-500 text-xs font-semibold border border-amber-500/20"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse" />
            {{ t('publishing.theme_customizer.status.unsaved', 'Belum Disimpan') }}
          </span>
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button
                variant="outline"
                size="sm"
                class="h-9 inline-flex items-center gap-1.5 rounded-xl font-medium text-xs"
                :disabled="!isDirty"
              >
                <RotateCcw data-icon="inline-start" class="w-3.5 h-3.5 shrink-0" />
                {{ t('publishing.theme_customizer.actions.revert', 'Kembalikan') }}
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
              align="end"
              class="w-56 rounded-xl"
            >
              <DropdownMenuItem @click="resetToInitial">
                <History class="w-4 h-4 mr-2" />
                {{ t('publishing.theme_customizer.revert.session_start', 'Awal Sesi') }}
              </DropdownMenuItem>
              <DropdownMenuItem
                class="text-destructive focus:text-destructive"
                @click="resetToDefaults"
              >
                <Zap class="w-4 h-4 mr-2" />
                {{ t('publishing.theme_customizer.revert.theme_defaults', 'Pengaturan Default') }}
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
          <Button
            size="sm"
            class="h-9 inline-flex items-center gap-1.5 rounded-xl font-semibold text-xs shadow-sm shadow-primary/20 bg-primary hover:bg-primary/90"
            :disabled="saving || !isDirty"
            @click="saveAll"
          >
            <Save
              v-if="!saving"
              data-icon="inline-start"
              class="w-3.5 h-3.5 shrink-0"
            />
            <Loader2
              v-else
              data-icon="inline-start"
              class="w-3.5 h-3.5 shrink-0 animate-spin"
            />
            {{ saving ? t('publishing.theme_customizer.status.saving', 'Menyimpan...') : t('publishing.theme_customizer.actions.publish', 'Terbitkan') }}
          </Button>
        </div>
      </div>
    </header>

    <!-- Main organization -->
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

    <div
      v-else
      class="flex-1 flex overflow-hidden"
    >
      <!-- Sidebar -->
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

      <!-- Main Editor Area -->
      <main class="flex-1 overflow-y-auto relative bg-muted/5 custom-scrollbar">
        <div
          v-if="selectedItem"
          class="max-w-4xl mx-auto p-6 sm:p-8 space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300"
        >
          <!-- Section Header Banner -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-card border border-border shadow-sm">
            <div class="flex items-center gap-3.5">
              <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary border border-primary/20 flex items-center justify-center shrink-0 shadow-inner">
                <component
                  :is="selectedItem.icon"
                  class="w-6 h-6"
                />
              </div>
              <div>
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-foreground">
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

          <!-- ════════ COMBINED EDITOR ════════ -->
          <div class="space-y-6 pb-20">
            <div
              v-if="!isItemCompatibleWithMode(selectedItem)"
              class="rounded-2xl border border-dashed border-border bg-card/60 p-5 text-sm text-muted-foreground"
            >
              {{ modeHintText }}
            </div>

            <!-- Type 1: Static CSS Editor (Direct textarea) -->
            <div
              v-if="selectedItem.panel === 'css' && organizationMode === 'advanced'"
              class="relative"
            >
              <div class="absolute top-4 right-4 z-10 flex items-center gap-2">
                <span class="text-xs px-2.5 py-0.5 rounded-md bg-muted border border-border font-mono text-muted-foreground font-semibold">{{ t('publishing.theme_customizer.editor.css.label') }}</span>
              </div>
              <div class="bg-card border border-border rounded-2xl overflow-hidden shadow-sm">
                <textarea
                  v-model="customCss"
                  rows="24"
                  :aria-label="t('publishing.theme_customizer.editor.css.label')"
                  class="w-full p-5 bg-background text-xs font-mono leading-relaxed focus:outline-none resize-none min-h-[480px] border-0 custom-scrollbar selection:bg-primary/20"
                  :placeholder="t('publishing.theme_customizer.editor.css.placeholder')"
                  spellcheck="false"
                />
              </div>
            </div>

            <!-- Type 2: Manifest-Driven Settings (Cards) -->
            <section
              v-if="selectedItem.manifestSections?.length && organizationMode === 'design'"
              class="space-y-3"
            >
              <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2 px-1">
                <Settings2 class="w-3.5 h-3.5" />
                {{ t('publishing.theme_customizer.editor.sections.visual', 'Pengaturan Visual') }}
              </h4>
              <div class="bg-card border border-border rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">
                <div
                  v-for="section in selectedItem.manifestSections"
                  :key="section.id"
                  class="space-y-5"
                >
                  <SettingControl
                    v-for="setting in getVisibleSettings(section.settings)"
                    :key="setting.key"
                    :theme-slug="slug"
                    :setting="setting"
                    :model-value="formValues[setting.key]"
                    @update:model-value="(val: any) => recordSettingChange(setting.key, val)"
                    @pick-media="openMediaPicker(setting.key)"
                  />
                </div>
              </div>
            </section>

            <!-- Type 3: Menu Locations -->
            <section
              v-if="selectedItem.panel === 'menus' && organizationMode === 'design'"
              class="space-y-3"
            >
              <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2 px-1">
                <MenuIcon class="w-3.5 h-3.5" />
                {{ t('publishing.theme_customizer.editor.sections.menus', 'Lokasi Menu') }}
              </h4>
              <div class="bg-card border border-border rounded-2xl p-6 sm:p-8 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    @update:model-value="(val: string) => recordSettingChange(menuSetting.key, val)"
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
              :preview-slot-data="previewSlotData"
              :save-history="saveHistory"
              @clear-preview="id => delete previewResults[id]"
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
</div>

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

    <!-- Media Picker -->
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
import { ref, computed, reactive, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter, onBeforeRouteLeave } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { 
    Button, Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger,
    Dialog, DialogContent, Badge
} from '@/shared/components/ui'
import SettingControl from '@/modules/Content/Layout/components/themes/customizer/sidebar/SettingControl.vue'
import CustomizerSidebar from '@/modules/Content/Layout/components/themes/customizer/sidebar/Sidebar.vue'
import BindingsSection from '@/modules/Content/Layout/components/themes/customizer/editor/BindingsSection.vue'
import MediaPicker from '@/modules/Content/Media/components/picker/MediaPicker.vue'

// Icons
import {
  ArrowLeft,
  Eye,
  Award,
  BarChart3,
  Briefcase,
  Code2,
  Globe,
  GraduationCap,
  History,
  ImageIcon,
  Layout,
  LayoutTemplate,
  Loader2,
  Megaphone,
  MenuIcon,
  MessageSquare,
  Newspaper,
  Palette,
  PanelBottom,
  PanelsTopLeft,
  Redo2,
  RotateCcw,
  Save,
  Search,
  Settings2,
  Share2,
  Sparkles,
  Type,
  Undo2,
  UserCircle,
  Zap,
} from 'lucide-vue-next';

import api from '@/engine/api/client'
import toast from '@/shared/services/toastService'
import type { ThemeSection } from '@/modules/Content/Layout/types/theme'
import type { Theme } from '@/modules/Content/Layout/types/theme'
import type { SlotBinding } from '@/modules/Content/Layout/composables/useThemeDataBindings'
import {
    getThemeBindingRegistry,
    getReservedManifestCategories,
    resolveThemeCustomizerExtension,
} from '@/modules/Content/Layout/customizer/loaders/resolveThemeCustomizerExtension'
import {
    buildPlatformSidebarGroups,
    type CustomizerNavItem,
} from '@/modules/Content/Layout/customizer/shell/buildPlatformSidebarNav'
import { useThemeCustomizer } from '@/modules/Content/Layout/composables/useThemeCustomizer'
import { useThemeCustomizerLabels } from '@/modules/Content/Layout/composables/useThemeCustomizerLabels'
import { themeUsesJanariCanvas } from '@/modules/Content/Layout/utils/themeManifest'
import PreviewArea from '@/modules/Content/Layout/components/themes/customizer/preview/PreviewArea.vue'
import { parseResponse, ensureArray } from '@/shared/utils/responseParser'

const { t, te } = useI18n()
const route = useRoute()
const router = useRouter()
const slug = route.params.slug as string
const { categoryLabel } = useThemeCustomizerLabels(slug)

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
} = useThemeCustomizer(slug, t)
const availableMenus = ref<{ value: string; label: string }[]>([])
const previewTheme = computed<Theme>(() => {
    const base = (theme.value || {}) as Theme
    const baseSettings = (base.settings || {}) as Record<string, unknown>
    return {
        ...base,
        settings: {
            ...baseSettings,
            ...formValues.value,
        },
        custom_css: customCss.value,
    }
})

// ─────────────────────────────────────────────
// Schema & site profile sections
// ─────────────────────────────────────────────
interface ComponentSchema { 
    id: string; 
    name: string; 
    description: string; 
    icon: any; 
    slots: { id: string; label: string; props: { key: string; label: string }[] }[]; 
    manifestCategory?: string; // Links to manifest category label
}

const iconByRegistryKey: Record<string, any> = {
    hero: ImageIcon,
    principal: UserCircle,
    news: Newspaper,
    majors: GraduationCap,
    stats: BarChart3,
    testimonials: MessageSquare,
    cta: Megaphone,
}

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
}

const themeCustomizerExtension = computed(() => resolveThemeCustomizerExtension(slug));

const specialPageNavItems = computed<NavItem[]>(() => {
    const items = themeCustomizerExtension.value?.specialPageNavItems ?? []
    return items.map((item) => ({
        id: item.id,
        label: t(item.labelKey),
        description: t(item.descriptionKey),
        icon: sidebarNavIconByKey[item.icon] ?? Globe,
        manifestSections: findSections(item.manifestCategories),
        hasBinding: false,
    }))
})

const themeComponents = computed<ComponentSchema[]>(() =>
    getThemeBindingRegistry(slug).map(component => ({
        id: component.id,
        name: t(component.nameKey),
        description: t(component.descriptionKey),
        icon: iconByRegistryKey[component.icon] || LayoutTemplate,
        manifestCategory: component.manifestCategory,
        slots: component.slots.map(slot => ({
            id: slot.id,
            label: t(slot.labelKey),
            props: slot.props.map(prop => ({
                key: prop.key,
                label: t(prop.labelKey),
            })),
        })),
    }))
)

// ─────────────────────────────────────────────
// Sidebar Navigation Logic
// ─────────────────────────────────────────────
const searchQuery = ref('')
const activeItemId = ref('')
const collapsedGroups = ref<string[]>([])
const expandedSlots = ref<string[]>([])
const sidebarCollapsed = ref(false)
const organizationMode = ref<'design' | 'bindings' | 'advanced'>('design')
const showPreview = ref(false)

interface NavItem extends CustomizerNavItem {
    bindingComponent?: ComponentSchema;
}

type SidebarSelectPayload = Pick<NavItem, 'id' | 'bindingComponent' | 'panel'>;

const dedicatedManifestCategories = computed(() => getReservedManifestCategories(slug));

const platformSidebarGroups = computed(() =>
    buildPlatformSidebarGroups(t, findSections, sidebarNavIconByKey),
)

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
            ? [{
                id: 'special-pages',
                label: t('publishing.theme_customizer.sidebar.categories.special_pages'),
                items: specialPageNavItems.value,
            }]
            : []),
    ]
    return groups
})

const filteredGroups = computed(() => {
    if (!searchQuery.value) return sidebarGroups.value
    const query = searchQuery.value.toLowerCase()
    return sidebarGroups.value.map(g => ({
        ...g,
        items: g.items.filter(i => 
            i.label.toLowerCase().includes(query) || 
            i.description.toLowerCase().includes(query)
        )
    })).filter(g => g.items.length > 0)
})

const selectedItem = computed(() => {
    for (const g of sidebarGroups.value) {
        const found = g.items.find(i => i.id === activeItemId.value)
        if (found) return found as NavItem
    }
    return null
})

const activeBindingComponentId = computed(() => selectedItem.value?.bindingComponent?.id || '')
const flatNavItems = computed(() => sidebarGroups.value.flatMap((group) => group.items as NavItem[]))

const activeGroupLabel = computed(() => {
    for (const g of sidebarGroups.value) {
        if (g.items.some(i => i.id === activeItemId.value)) return g.label
    }
    return ''
})

function selectItem(item: SidebarSelectPayload) {
    activeItemId.value = item.id
    if (item.panel === 'css') {
        organizationMode.value = 'advanced'
    } else if (item.bindingComponent) {
        organizationMode.value = 'bindings'
    } else {
        organizationMode.value = 'design'
    }
    if (item.bindingComponent && item.bindingComponent.slots.length > 0) {
        ensureComponentBindings(item.bindingComponent.id)
        expandedSlots.value = [item.bindingComponent.slots[0]!.id]
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

function ensureSelectionForMode(mode: 'design' | 'bindings' | 'advanced') {
    const current = selectedItem.value;
    if (current && isItemCompatibleWithMode(current)) return;
    const fallback = pickItemForMode(mode);
    if (fallback) selectItem(fallback);
}

function isItemCompatibleWithMode(item: NavItem | null): boolean {
    if (!item) return true
    if (item.panel === 'css') return organizationMode.value === 'advanced'
    if (item.panel === 'menus') return organizationMode.value === 'design'
    if (item.bindingComponent) return organizationMode.value === 'bindings'
    return organizationMode.value === 'design'
}

const modeHintText = computed(() => {
    if (!selectedItem.value) return ''
    if (selectedItem.value.panel === 'css') return t('publishing.theme_customizer.organization.hints.advanced')
    if (selectedItem.value.bindingComponent) return t('publishing.theme_customizer.organization.hints.bindings')
    return t('publishing.theme_customizer.organization.hints.design')
})

watch(
    organizationMode,
    (mode) => {
        ensureSelectionForMode(mode);
    },
    { immediate: false }
);

watch(
    sidebarGroups,
    () => {
        if (!selectedItem.value) {
            ensureSelectionForMode(organizationMode.value);
        }
    },
    { immediate: true }
);

function toggleGroup(groupId: string) {
    if (collapsedGroups.value.includes(groupId)) {
        collapsedGroups.value = collapsedGroups.value.filter(g => g !== groupId)
    } else {
        collapsedGroups.value.push(groupId)
    }
}

// Helper to find manifest sections by category labels
function findSections(catLabels: string[]): ThemeSection[] {
    if (!theme.value?.manifest?.settings_schema) return []
    const schema = theme.value.manifest.settings_schema
    const sections: Record<string, ThemeSection> = {}

    Object.keys(schema).forEach(key => {
        const s = schema[key]
        if (s && catLabels.includes(s.category || 'General') && !s.hidden) {
            const cat = s.category || 'General'
            const translatedLabel = categoryLabel(cat);

            if (!sections[cat]) sections[cat] = { id: cat, label: translatedLabel, settings: [] }
            sections[cat].settings.push({ key, ...s })
        }
    })
    return Object.values(sections)
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

// ─────────────────────────────────────────────
// Data Binding Logic
// ─────────────────────────────────────────────
const categories = ref<any[]>([])
const pages = ref<any[]>([])
const previewLoading = ref<string | null>(null)
const previewResults = reactive<Record<string, any[]>>({})

function getSlotConfig(compId: string, slotId: string): SlotBinding {
    if (!bindings.value[compId]) bindings.value[compId] = { slots: {} }
    if (!bindings.value[compId].slots[slotId]) {
        bindings.value[compId].slots[slotId] = { sourceType: 'static', categoryFilter: 'all', tagFilter: 'all', pageSlug: '', limit: 5, orderBy: 'published_at', orderDir: 'desc', propMapping: {} }
    } else if (!bindings.value[compId].slots[slotId].propMapping) {
        // Migration/Repair: Ensure propMapping exists
        bindings.value[compId].slots[slotId].propMapping = {}
    }
    return bindings.value[compId].slots[slotId]!
}

function updateBinding(compId: string, slotId: string, field: string, value: any) {
    const config = getSlotConfig(compId, slotId);
    (config as any)[field] = value
    saveHistory()
}

function ensureComponentBindings(compId: string) {
    if (!bindings.value[compId]) bindings.value[compId] = { slots: {} }
    const comp = themeComponents.value.find(c => c.id === compId)
    const compBindings = bindings.value[compId]
    if (comp && compBindings) {
        comp.slots.forEach(slot => {
            if (!compBindings.slots[slot.id]) {
                compBindings.slots[slot.id] = { sourceType: 'static', categoryFilter: 'all', tagFilter: 'all', pageSlug: '', limit: 5, orderBy: 'published_at', orderDir: 'desc', propMapping: {} }
            } else {
                const s = compBindings.slots[slot.id];
                if (s && !s.propMapping) {
                    s.propMapping = {};
                }
            }
        })
    }
}

function hasComponentBindings(compId: string): boolean {
    const b = bindings.value[compId];
    if (!b || !b.slots) return false;
    const slots = b.slots as Record<string, SlotBinding>;
    return Object.values(slots).some((slot) => slot.sourceType !== 'static');
}

function toggleSlot(slotId: string) {
    if (expandedSlots.value.includes(slotId)) expandedSlots.value = expandedSlots.value.filter(s => s !== slotId)
    else expandedSlots.value.push(slotId)
}

function getSourceLabel(src: string) {
    return ({ 
        static: t('publishing.theme_customizer.sources.static'), 
        api_posts: t('publishing.theme_customizer.sources.api_posts'), 
        api_pages: t('publishing.theme_customizer.sources.api_pages'), 
        api_categories: t('publishing.theme_customizer.sources.api_categories') 
    } as any)[src] || src
}

function getSourceIcon(src: string) {
    return ({ static: LayoutTemplate, api_posts: Newspaper, api_pages: Layout, api_categories: Sparkles } as any)[src] || Search
}

function getFieldsForSource(src: string) {
    if (src === 'api_posts') return [
        { value: 'title', label: t('publishing.theme_customizer.items.news_title') }, 
        { value: 'excerpt', label: t('publishing.theme_customizer.items.short_info') }, 
        { value: 'content', label: t('publishing.theme_customizer.items.message') },
        { value: 'thumbnail', label: t('publishing.theme_customizer.items.thumbnail') }, 
        { value: 'published_at', label: t('publishing.theme_customizer.items.date') },
        { value: 'category.name', label: t('common.labels.category') }, 
        { value: 'slug', label: t('publishing.theme_customizer.items.path') },
        { value: 'views', label: t('publishing.theme_customizer.editor.bindings.sort_options.views') }
    ]
    if (src === 'api_pages') return [
        { value: 'title', label: t('publishing.theme_customizer.items.title') }, 
        { value: 'thumbnail', label: t('publishing.theme_customizer.items.thumbnail') }, 
        { value: 'slug', label: t('publishing.theme_customizer.items.path') }
    ]
    if (src === 'api_categories') return [
        { value: 'name', label: t('common.labels.name') }, 
        { value: 'slug', label: t('publishing.theme_customizer.items.path') },
        { value: 'posts_count', label: t('publishing.theme_customizer.items.counter') }
    ]
    return []
}

async function previewSlotData(slotId: string) {
    if (!activeItemId.value) return
    const compId = activeItemId.value.replace('comp-', '')
    const config = getSlotConfig(compId, slotId)
    if (config.sourceType === 'static') return
    
    previewLoading.value = slotId
    try {
        let results: any[] = []
        if (config.sourceType === 'api_posts') {
            const params: any = { status: 'published', type: 'post', per_page: config.limit || 5, sort_by: config.orderBy || 'published_at' }
            if (config.categoryFilter && config.categoryFilter !== 'all') params.category = config.categoryFilter
            const res = await api.get('/manage/publishing/contents', { params })
            const parsed = parseResponse<any>(res)
            results = ensureArray<any>(parsed.data)
        } else if (config.sourceType === 'api_pages') {
            const res = await api.get('/manage/publishing/contents', { params: { type: 'page', status: 'published' } })
            const parsed = parseResponse<any>(res)
            results = ensureArray<any>(parsed.data)
            if (config.pageSlug) {
                results = results.filter((item) => String(item?.slug || '') === String(config.pageSlug))
            }
        } else if (config.sourceType === 'api_categories') {
            const res = await api.get('/manage/library/categories')
            const parsed = parseResponse<any>(res)
            results = ensureArray<any>(parsed.data)
        }
        previewResults[slotId] = results
    } catch { toast.error(t('publishing.theme_customizer.messages.error'), t('publishing.theme_customizer.messages.probe_failed')) }
    finally { previewLoading.value = null }
}

function filterPreviewFields(item: any) {
    const fields = ['title', 'name', 'slug', 'published_at', 'status']
    const out: any = {}
    fields.forEach(f => { if (item[f]) out[f] = item[f] })
    return out
}

// ─────────────────────────────────────────────
// Actions
// ─────────────────────────────────────────────

async function fetchCategories() {
    try {
        const r = await api.get('/manage/library/categories')
        const parsed = parseResponse<any>(r)
        categories.value = ensureArray<any>(parsed.data)
    } catch { /* silent */ }
}

async function fetchMenus() {
    try {
        const r = await api.get('/manage/layout/menus')
        const parsed = parseResponse<any>(r)
        const data = ensureArray<any>(parsed.data)
        availableMenus.value = data.map((m: any) => ({ value: m.id, label: m.name }))
        availableMenus.value.unshift({ value: 'none', label: t('publishing.theme_customizer.editor.menus.placeholder') })
    } catch { /* silent */ }
}

async function fetchPages() {
    try {
        const r = await api.get('/manage/publishing/contents', { params: { type: 'page' } })
        const parsed = parseResponse<any>(r)
        pages.value = ensureArray<any>(parsed.data)
    } catch { /* silent */ }
}

const menuSections = computed(() => {
    if (!theme.value?.manifest?.menus) return []
    const menus = theme.value.manifest.menus
    return Object.entries(menus).map(([locKey, locLabel]) => {
        const locTransKey = `publishing.theme_customizer.items.menus.locations.${locKey}`;
        const finalLabel = te(locTransKey) ? t(locTransKey) : String(locLabel);

        return {
            key: `menu_location_${locKey}`,
            label: finalLabel,
            type: 'select',
            category: 'Menus',
            options: availableMenus.value,
            description: t('publishing.theme_customizer.editor.menus.description', { label: finalLabel })
        };
    })
})

function handleBack() {
    if (isDirty.value) {
        if (confirm(t('publishing.theme_customizer.messages.confirm_exit'))) router.push({ name: 'themes' })
    } else router.push({ name: 'themes' })
}

const handleBeforeUnload = (event: BeforeUnloadEvent) => {
    if (!isDirty.value) return;
    event.preventDefault();
    event.returnValue = '';
};

// Media
const showMediaPicker = ref(false)
const activeMediaField = ref<string | null>(null)
function openMediaPicker(key: string) { activeMediaField.value = key; showMediaPicker.value = true; }
function handleMediaSelect(m: { url: string }) { if (activeMediaField.value) recordSettingChange(activeMediaField.value, m.url); showMediaPicker.value = false; }

const handleKey = (e: KeyboardEvent) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); undo(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 'y') { e.preventDefault(); redo(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); if (isDirty.value) saveAll(); }
}

onMounted(() => {
    fetchThemeData();
    fetchMenus();
    fetchCategories();
    fetchPages();
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
