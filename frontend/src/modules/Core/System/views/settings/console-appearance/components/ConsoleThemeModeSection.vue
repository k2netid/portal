<template>
  <div class="border-b border-border/50 bg-muted/20 px-6 py-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="space-y-1">
        <div class="flex items-center gap-2">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-foreground/80">
            {{ t('system.settings.consoleAppearance.themeMode.title') }}
          </h3>
          <span
            class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
            :class="themeMode === 'global' ? 'bg-primary/10 text-primary border border-primary/20' : 'bg-secondary text-secondary-foreground border border-border/40'"
          >
            <component :is="themeMode === 'global' ? Sparkles : Sliders" class="w-3 h-3" />
            {{ themeMode === 'global' ? t('system.settings.consoleAppearance.themeMode.global.label') : t('system.settings.consoleAppearance.themeMode.advanced.label') }}
          </span>
        </div>
        <p class="text-xs text-muted-foreground">
          {{ themeMode === 'global' ? t('system.settings.consoleAppearance.themeMode.banner.easyDescription') : t('system.settings.consoleAppearance.themeMode.banner.advancedDescription') }}
        </p>
      </div>

      <!-- Segmented Pill Control -->
      <div
        class="inline-flex items-center rounded-lg border border-border/60 bg-muted/40 p-1 self-start sm:self-auto shadow-2xs"
        role="radiogroup"
        :aria-label="t('system.settings.consoleAppearance.themeMode.title')"
      >
        <button
          v-for="opt in modeOptions"
          :key="opt.id"
          type="button"
          role="radio"
          :aria-checked="themeMode === opt.id"
          class="flex items-center gap-2 rounded-md px-3.5 py-1.5 text-xs font-medium transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring cursor-pointer"
          :class="themeMode === opt.id
            ? 'bg-background text-foreground shadow-xs font-semibold border border-border/60'
            : 'text-muted-foreground hover:text-foreground'"
          @click="onSelectMode(opt.id)"
        >
          <component :is="opt.icon" class="w-3.5 h-3.5" :class="themeMode === opt.id ? 'text-primary' : ''" />
          <span>{{ opt.label }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Sparkles, Sliders } from 'lucide-vue-next';
import type { ConsoleThemeMode } from '@/modules/Core/System/constants/consoleThemeMode';
import { useConsoleAppearanceContext } from '../composables/useConsoleAppearancePage';

defineProps<{
    themeMode: ConsoleThemeMode;
}>();

const { t } = useI18n();
const { requestSwitchMode } = useConsoleAppearanceContext();

const onSelectMode = (mode: ConsoleThemeMode) => {
    void requestSwitchMode(mode);
};

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
</script>
