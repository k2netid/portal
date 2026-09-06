<template>
  <div class="space-y-6">
    <PageHeader
      :title="t('system.settings.consoleAppearance.title')"
      :subtitle="t('system.settings.consoleAppearance.description')"
      borderless
    />

    <ConsoleListCard v-if="loading">
      <div class="space-y-6 p-6">
        <div class="h-4 w-48 animate-pulse rounded bg-muted" />
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
          <div
            v-for="i in 8"
            :key="i"
            class="h-12 animate-pulse rounded-lg bg-muted/60"
          />
        </div>
      </div>
    </ConsoleListCard>

    <Tabs
      v-else
      v-model="activeTab"
      class="w-full"
    >
      <ConsoleListCard>
        <div class="border-b border-border/50 px-5 sm:px-6 py-2.5 sm:py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-card">
          <TabsList class="h-auto flex-wrap gap-1 bg-transparent p-0">
            <TabsTrigger
              v-for="tab in tabItems"
              :key="tab.id"
              :value="tab.id"
              class="relative rounded-lg border border-transparent px-3.5 py-2 text-xs sm:text-sm font-medium transition-all data-[state=active]:bg-muted/60 data-[state=active]:text-foreground data-[state=active]:shadow-xs data-[state=active]:border-border/60 text-muted-foreground hover:text-foreground cursor-pointer flex items-center gap-1.5"
            >
              <component
                :is="tab.icon"
                class="h-4 w-4"
              />
              <span>{{ tab.label }}</span>
              <span
                v-if="tab.locked"
                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20"
                :title="t('system.settings.white_label_required')"
              >
                <Lock class="w-2.5 h-2.5" />
                White Label
              </span>
            </TabsTrigger>
          </TabsList>

          <!-- Segmented Pill Mode Switcher -->
          <div
            class="inline-flex items-center rounded-lg border border-border/60 bg-muted/30 p-1 self-start sm:self-auto shadow-2xs"
            role="radiogroup"
            :aria-label="t('system.settings.consoleAppearance.themeMode.title')"
          >
            <button
              v-for="opt in modeOptions"
              :key="opt.id"
              type="button"
              role="radio"
              :aria-checked="themeMode === opt.id"
              class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring cursor-pointer"
              :class="themeMode === opt.id
                ? 'bg-background text-foreground shadow-xs font-semibold border border-border/60'
                : 'text-muted-foreground hover:text-foreground'"
              @click="requestSwitchMode(opt.id)"
            >
              <component :is="opt.icon" class="w-3.5 h-3.5" :class="themeMode === opt.id ? 'text-primary' : ''" />
              <span>{{ opt.label }}</span>
            </button>
          </div>
        </div>

        <form @submit.prevent="save">
          <TabsContent
            value="colors"
            class="relative mt-0 focus-visible:outline-none"
          >
            <ColorsTab />

            <!-- Easy Mode CTA to switch to Advanced Mode -->
            <div
              v-if="themeMode === 'global'"
              class="m-6 rounded-xl border border-dashed border-border/80 bg-muted/20 p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3"
            >
              <div class="space-y-0.5">
                <div class="flex items-center gap-1.5 text-xs font-semibold text-foreground">
                  <Sliders class="w-3.5 h-3.5 text-primary" />
                  <span>{{ t('system.settings.consoleAppearance.themeMode.banner.advancedTitle') }}</span>
                </div>
                <p class="text-xs text-muted-foreground">
                  {{ t('system.settings.consoleAppearance.themeMode.banner.advancedDescription') }}
                </p>
              </div>
              <Button
                type="button"
                variant="outline"
                size="sm"
                class="shrink-0 text-xs gap-1.5 cursor-pointer"
                @click="requestSwitchMode('advanced')"
              >
                <span>{{ t('system.settings.consoleAppearance.themeMode.banner.easyCta') }}</span>
              </Button>
            </div>
          </TabsContent>

          <TabsContent
            value="shell"
            class="relative mt-0 focus-visible:outline-none"
          >
            <ShellTab />

            <!-- Advanced Mode CTA to switch to Easy Mode -->
            <div
              v-if="themeMode === 'advanced'"
              class="m-6 rounded-xl border border-dashed border-border/80 bg-muted/20 p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3"
            >
              <div class="space-y-0.5">
                <div class="flex items-center gap-1.5 text-xs font-semibold text-foreground">
                  <Sparkles class="w-3.5 h-3.5 text-primary" />
                  <span>{{ t('system.settings.consoleAppearance.themeMode.banner.easyTitle') }}</span>
                </div>
                <p class="text-xs text-muted-foreground">
                  {{ t('system.settings.consoleAppearance.themeMode.banner.easyDescription') }}
                </p>
              </div>
              <Button
                type="button"
                variant="outline"
                size="sm"
                class="shrink-0 text-xs gap-1.5 cursor-pointer"
                @click="requestSwitchMode('global')"
              >
                <span>{{ t('system.settings.consoleAppearance.themeMode.banner.advancedCta') }}</span>
              </Button>
            </div>
          </TabsContent>

          <TabsContent
            value="logos"
            class="mt-0 focus-visible:outline-none"
          >
            <LogosTab />
          </TabsContent>

          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-border/50 bg-muted/10 px-6 py-4 rounded-b-xl">
            <div class="flex items-center gap-2 text-xs text-muted-foreground">
              <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
              <span>{{ t('system.settings.consoleAppearance.statusLiveDraft', 'Pratinjau langsung aktif pada konsol') }}</span>
            </div>
            <div class="flex items-center gap-2">
              <Button
                type="button"
                variant="outline"
                size="sm"
                :disabled="saving"
                @click="load"
              >
                {{ t('common.actions.reset') }}
              </Button>
              <Button
                type="submit"
                size="sm"
                :disabled="saving"
                class="min-w-[7rem]"
              >
                {{ saving ? t('common.actions.saving') : t('common.actions.save') }}
              </Button>
            </div>
          </div>
        </form>
      </ConsoleListCard>
    </Tabs>

    <MediaPicker
      v-if="hasWhiteLabel"
      v-model:open="showLogoLightPicker"
      @selected="(media) => { form.app_logo_light = media.url; syncDraft(); }"
    >
      <template #trigger><span /></template>
    </MediaPicker>
    <MediaPicker
      v-if="hasWhiteLabel"
      v-model:open="showLogoDarkPicker"
      @selected="(media) => { form.app_logo_dark = media.url; syncDraft(); }"
    >
      <template #trigger><span /></template>
    </MediaPicker>
    <MediaPicker
      v-if="hasWhiteLabel"
      v-model:open="showLogoCompactPicker"
      @selected="(media) => { form.app_logo_compact = media.url; syncDraft(); }"
    >
      <template #trigger><span /></template>
    </MediaPicker>
    <MediaPicker
      v-if="hasWhiteLabel"
      v-model:open="showFaviconPicker"
      @selected="(media) => { form.app_favicon = media.url; syncDraft(); }"
    >
      <template #trigger><span /></template>
    </MediaPicker>
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { Image, LayoutTemplate, Lock, Palette, Sliders, Sparkles } from 'lucide-vue-next';
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';
import { Tabs, TabsList, TabsTrigger, TabsContent, Button } from '@/shared/components/ui';
import MediaPicker from '@/shared/components/ui/MediaPicker.vue';
import {
    provideConsoleAppearance,
    useConsoleAppearancePage,
    type ConsoleAppearanceTabId,
} from './composables/useConsoleAppearancePage';

const ColorsTab = defineAsyncComponent(() => import('./tabs/ColorsTab.vue'));
const ShellTab = defineAsyncComponent(() => import('./tabs/ShellTab.vue'));
const LogosTab = defineAsyncComponent(() => import('./tabs/LogosTab.vue'));

const { t } = useI18n();
const route = useRoute();

const appearance = useConsoleAppearancePage();
provideConsoleAppearance(appearance);

const {
    loading,
    saving,
    activeTab,
    themeMode,
    hasWhiteLabel,
    form,
    syncDraft,
    load,
    save,
    requestSwitchMode,
    showLogoLightPicker,
    showLogoDarkPicker,
    showLogoCompactPicker,
    showFaviconPicker,
} = appearance;

const modeOptions = computed(() => [
    {
        id: 'global' as const,
        label: t('system.settings.consoleAppearance.themeMode.global.label'),
        hint: t('system.settings.consoleAppearance.themeMode.global.hint'),
        icon: Sparkles,
    },
    {
        id: 'advanced' as const,
        label: t('system.settings.consoleAppearance.themeMode.advanced.label'),
        hint: t('system.settings.consoleAppearance.themeMode.advanced.hint'),
        icon: Sliders,
    },
]);

const validTabs: ConsoleAppearanceTabId[] = ['colors', 'shell', 'logos'];
const queryTab = route.query.tab as string;
if (validTabs.includes(queryTab as ConsoleAppearanceTabId)) {
    if (themeMode.value === 'global' && queryTab === 'shell') {
        activeTab.value = 'colors';
    } else {
        activeTab.value = queryTab as ConsoleAppearanceTabId;
    }
}

const tabItems = computed(() => {
    const logosTab = {
        id: 'logos' as const,
        label: t('system.settings.consoleAppearance.tabs.logos'),
        icon: Image,
        locked: !hasWhiteLabel.value,
    };
    if (themeMode.value === 'global') {
        return [
            { id: 'colors' as const, label: t('system.settings.consoleAppearance.tabs.colorPreset'), icon: Palette, locked: false },
            logosTab,
        ];
    }
    return [
        { id: 'shell' as const, label: t('system.settings.consoleAppearance.tabs.uiShell'), icon: LayoutTemplate, locked: false },
        { id: 'colors' as const, label: t('system.settings.consoleAppearance.tabs.colorPreset'), icon: Palette, locked: false },
        logosTab,
    ];
});

onMounted(() => {
    void load();
});
</script>
