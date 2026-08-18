<template>
  <section class="space-y-4 p-6">
    <div>
      <h3 class="text-sm font-medium text-foreground">
        {{ t('system.settings.consoleAppearance.advancedComponents.icons.title') }}
      </h3>
      <p class="mt-0.5 text-xs text-muted-foreground">
        {{ t('system.settings.consoleAppearance.advancedComponents.icons.description') }}
      </p>
    </div>
    <div class="grid max-w-md grid-cols-3 gap-2">
      <button
        v-for="opt in iconOptions"
        :key="opt.value"
        type="button"
        class="flex flex-col items-center gap-2 rounded-lg border px-3 py-4 transition-colors"
        :class="form.console_icon_weight === opt.value ? 'border-primary bg-primary/10' : 'border-border/60 hover:bg-muted/40'"
        @click="form.console_icon_weight = opt.value"
      >
        <component :is="opt.icon" class="h-6 w-6 text-foreground" :stroke-width="opt.stroke" />
        <span class="text-xs font-medium">{{ opt.label }}</span>
      </button>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Home, Settings, User } from 'lucide-vue-next';
import { useConsoleAppearanceContext } from '../../composables/useConsoleAppearancePage';

const { t } = useI18n();
const { form } = useConsoleAppearanceContext();

const iconOptions = computed(() => [
    { value: 'light', label: t('system.settings.consoleAppearance.advancedComponents.icons.weights.light'), stroke: 1.5, icon: Home },
    { value: 'regular', label: t('system.settings.consoleAppearance.advancedComponents.icons.weights.regular'), stroke: 2, icon: Settings },
    { value: 'bold', label: t('system.settings.consoleAppearance.advancedComponents.icons.weights.bold'), stroke: 2.5, icon: User },
]);
</script>
