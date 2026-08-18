<template>
  <section class="space-y-4 border-b border-border/50 bg-muted/10 p-6">
    <div>
      <h3 class="text-sm font-medium text-foreground">
        {{ t('system.settings.consoleAppearance.themeMode.title') }}
      </h3>
      <p class="mt-0.5 text-xs text-muted-foreground">
        {{ t('system.settings.consoleAppearance.themeMode.description') }}
      </p>
    </div>

    <div
      class="grid max-w-2xl grid-cols-1 gap-2 rounded-xl border border-border/50 bg-muted/20 p-1.5 sm:grid-cols-2"
      role="radiogroup"
      :aria-label="t('system.settings.consoleAppearance.themeMode.title')"
    >
      <button
        v-for="opt in modeOptions"
        :key="opt.id"
        type="button"
        role="radio"
        :aria-checked="themeMode === opt.id"
        class="min-h-[4.5rem] rounded-lg px-4 py-3 text-left transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        :class="themeMode === opt.id
          ? 'bg-primary text-primary-foreground shadow-sm'
          : 'text-muted-foreground hover:bg-muted/50 hover:text-foreground'"
        @click="emit('update:themeMode', opt.id)"
      >
        <span class="block text-sm font-semibold">{{ opt.label }}</span>
        <span
          class="mt-1 block text-xs leading-snug text-foreground/80"
          :class="themeMode === opt.id ? 'text-primary-foreground' : 'text-foreground/80'"
        >
          {{ opt.hint }}
        </span>
      </button>
    </div>

    <p class="max-w-2xl text-xs text-foreground/80">
      {{ themeMode === 'global'
        ? t('system.settings.consoleAppearance.themeMode.globalActiveHint')
        : t('system.settings.consoleAppearance.themeMode.advancedActiveHint') }}
    </p>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { ConsoleThemeMode } from '@/modules/Core/System/constants/consoleThemeMode';

const props = defineProps<{
    themeMode: ConsoleThemeMode;
}>();

const emit = defineEmits<{
    'update:themeMode': [value: ConsoleThemeMode];
}>();

const { t } = useI18n();

const modeOptions = computed(() => [
    {
        id: 'global' as const,
        label: t('system.settings.consoleAppearance.themeMode.global.label'),
        hint: t('system.settings.consoleAppearance.themeMode.global.hint'),
    },
    {
        id: 'advanced' as const,
        label: t('system.settings.consoleAppearance.themeMode.advanced.label'),
        hint: t('system.settings.consoleAppearance.themeMode.advanced.hint'),
    },
]);
</script>
