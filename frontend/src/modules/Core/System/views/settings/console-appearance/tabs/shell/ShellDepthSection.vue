<template>
  <section class="space-y-4 p-6">
    <div>
      <h3 class="text-sm font-medium text-foreground">
        {{ t('system.settings.consoleAppearance.typographySectionTitle') }}
      </h3>
      <p class="mt-0.5 text-xs text-muted-foreground">
        {{ t('system.settings.consoleAppearance.typographySectionDescription') }}
      </p>
    </div>
    <div class="grid max-w-2xl grid-cols-1 gap-4 sm:grid-cols-2">
      <div class="space-y-2">
        <label for="console_font_primary" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
          {{ t('system.settings.consoleAppearance.fontPrimaryLabel') }}
        </label>
        <select
          id="console_font_primary"
          v-model="form.console_font_primary"
          class="h-10 w-full rounded-lg border border-border/70 bg-background px-3 text-sm"
        >
          <option v-for="opt in primaryFontOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
      </div>
      <div class="space-y-2">
        <label for="console_font_mono" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
          {{ t('system.settings.consoleAppearance.fontMonoLabel') }}
        </label>
        <select
          id="console_font_mono"
          v-model="form.console_font_mono"
          class="h-10 w-full rounded-lg border border-border/70 bg-background px-3 text-sm"
        >
          <option v-for="opt in monoFontOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useConsoleAppearanceContext } from '../../composables/useConsoleAppearancePage';

const { t } = useI18n();
const { form } = useConsoleAppearanceContext();

const primaryFontOptions = computed(() => [
    { value: 'system', label: t('system.settings.consoleAppearance.fontOptions.system') },
    { value: 'inter', label: t('system.settings.consoleAppearance.fontOptions.inter') },
    { value: 'outfit', label: t('system.settings.consoleAppearance.fontOptions.outfit') },
    { value: 'poppins', label: t('system.settings.consoleAppearance.fontOptions.poppins') },
]);

const monoFontOptions = computed(() => [
    { value: 'system', label: t('system.settings.consoleAppearance.fontOptions.systemMono') },
    { value: 'jetbrains_mono', label: t('system.settings.consoleAppearance.fontOptions.jetbrains_mono') },
    { value: 'fira_code', label: t('system.settings.consoleAppearance.fontOptions.fira_code') },
]);
</script>
