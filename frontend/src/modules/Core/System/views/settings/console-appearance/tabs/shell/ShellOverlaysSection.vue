<template>
  <section class="space-y-6 p-6">
    <div>
      <h3 class="text-sm font-medium text-foreground">
        {{ t('system.settings.consoleAppearance.advancedComponents.overlays.title') }}
      </h3>
      <p class="mt-0.5 text-xs text-muted-foreground">
        {{ t('system.settings.consoleAppearance.advancedComponents.overlays.description') }}
      </p>
    </div>

    <div class="space-y-2 max-w-md">
      <label class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
        {{ t('system.settings.consoleAppearance.advancedComponents.overlays.dropdownStyleLabel') }}
      </label>
      <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
        <button
          v-for="opt in dropdownOptions"
          :key="opt.value"
          type="button"
          class="rounded-lg border px-3 py-2 text-left text-sm transition-colors"
          :class="form.console_dropdown_style === opt.value ? 'border-primary bg-primary/10 text-foreground' : 'border-border/60 text-muted-foreground hover:bg-muted/40'"
          @click="form.console_dropdown_style = opt.value"
        >
          <span class="font-medium">{{ opt.label }}</span>
        </button>
      </div>
    </div>

    <div class="space-y-2 max-w-md">
      <div class="flex items-center justify-between text-xs">
        <span class="text-muted-foreground">{{ t('system.settings.consoleAppearance.popperOpacityLabel') }}</span>
        <span class="tabular-nums text-foreground">{{ form.console_popper_opacity }}%</span>
      </div>
      <input v-model.number="form.console_popper_opacity" type="range" min="30" max="100" class="console-range-input h-2 w-full accent-primary">
    </div>

    <div class="space-y-2 max-w-md">
      <div class="flex items-center justify-between text-xs">
        <span class="text-muted-foreground">{{ t('system.settings.consoleAppearance.advancedComponents.overlays.modalBackdropLabel') }}</span>
        <span class="tabular-nums text-foreground">{{ form.console_modal_backdrop_opacity }}%</span>
      </div>
      <input v-model.number="form.console_modal_backdrop_opacity" type="range" min="0" max="90" class="console-range-input h-2 w-full accent-primary">
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useConsoleAppearanceContext } from '../../composables/useConsoleAppearancePage';

const { t } = useI18n();
const { form } = useConsoleAppearanceContext();

const dropdownOptions = computed(() => [
    { value: 'minimal', label: t('system.settings.consoleAppearance.advancedComponents.overlays.dropdownStyles.minimal') },
    { value: 'standard', label: t('system.settings.consoleAppearance.advancedComponents.overlays.dropdownStyles.standard') },
    { value: 'glass', label: t('system.settings.consoleAppearance.advancedComponents.overlays.dropdownStyles.glass') },
]);
</script>
