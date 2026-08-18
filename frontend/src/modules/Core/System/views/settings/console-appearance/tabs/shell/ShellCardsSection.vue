<template>
  <section class="space-y-4 p-6">
    <div>
      <h3 class="text-sm font-medium text-foreground">
        {{ t('system.settings.consoleAppearance.advancedComponents.cards.title') }}
      </h3>
      <p class="mt-0.5 text-xs text-muted-foreground">
        {{ t('system.settings.consoleAppearance.advancedComponents.cards.description') }}
      </p>
    </div>
    <div class="grid max-w-2xl grid-cols-1 gap-3 sm:grid-cols-3">
      <ConsoleStyleOptionCard
        v-for="opt in cardOptions"
        :key="opt.value"
        :label="opt.label"
        :description="opt.description"
        :active="form.console_card_style === opt.value"
        @select="form.console_card_style = opt.value"
      >
        <template #preview>
          <div class="h-12 w-full rounded-md border border-border/50 bg-card p-2" :class="opt.previewClass">
            <div class="h-2 w-8 rounded bg-muted" />
            <div class="mt-2 h-1.5 w-full rounded bg-muted/70" />
          </div>
        </template>
      </ConsoleStyleOptionCard>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import ConsoleStyleOptionCard from '../../components/ConsoleStyleOptionCard.vue';
import { useConsoleAppearanceContext } from '../../composables/useConsoleAppearancePage';

const { t } = useI18n();
const { form } = useConsoleAppearanceContext();

const cardOptions = computed(() => [
    {
        value: 'flat',
        label: t('system.settings.consoleAppearance.advancedComponents.cards.styles.flat.label'),
        description: t('system.settings.consoleAppearance.advancedComponents.cards.styles.flat.description'),
        previewClass: 'shadow-none',
    },
    {
        value: 'soft',
        label: t('system.settings.consoleAppearance.advancedComponents.cards.styles.soft.label'),
        description: t('system.settings.consoleAppearance.advancedComponents.cards.styles.soft.description'),
        previewClass: 'shadow-sm',
    },
    {
        value: 'elevated',
        label: t('system.settings.consoleAppearance.advancedComponents.cards.styles.elevated.label'),
        description: t('system.settings.consoleAppearance.advancedComponents.cards.styles.elevated.description'),
        previewClass: 'shadow-md',
    },
]);
</script>
