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
        <span class="inline-flex min-w-[2.75rem] justify-center rounded-md border border-border/70 bg-muted/40 px-2 py-0.5 text-xs font-mono font-medium tabular-nums text-foreground shadow-2xs">{{ form.console_popper_opacity }}%</span>
      </div>
      <Slider v-model="form.console_popper_opacity" :min="30" :max="100" class="w-full" />
    </div>

    <div class="space-y-2 max-w-md">
      <div class="flex items-center justify-between text-xs">
        <span class="text-muted-foreground">{{ t('system.settings.consoleAppearance.advancedComponents.overlays.modalBackdropLabel') }}</span>
        <span class="inline-flex min-w-[2.75rem] justify-center rounded-md border border-border/70 bg-muted/40 px-2 py-0.5 text-xs font-mono font-medium tabular-nums text-foreground shadow-2xs">{{ form.console_modal_backdrop_opacity }}%</span>
      </div>
      <Slider v-model="form.console_modal_backdrop_opacity" :min="0" :max="90" class="w-full" />
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Slider from '@/shared/components/ui/Slider.vue';
import { useConsoleAppearanceContext } from '../../composables/useConsoleAppearancePage';

const { t } = useI18n();
const { form } = useConsoleAppearanceContext();

const dropdownOptions = computed(() => [
    { value: 'minimal', label: t('system.settings.consoleAppearance.advancedComponents.overlays.dropdownStyles.minimal') },
    { value: 'standard', label: t('system.settings.consoleAppearance.advancedComponents.overlays.dropdownStyles.standard') },
    { value: 'glass', label: t('system.settings.consoleAppearance.advancedComponents.overlays.dropdownStyles.glass') },
]);
</script>
