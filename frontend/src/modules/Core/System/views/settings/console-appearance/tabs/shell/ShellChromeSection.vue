<template>
  <div class="divide-y divide-border/30">
    <section class="space-y-4 p-6">
      <div>
        <h3 class="text-sm font-medium text-foreground">
          {{ t('system.settings.consoleAppearance.sidebarStyleLabel') }}
        </h3>
        <p class="mt-0.5 text-xs text-muted-foreground">
          {{ t('system.settings.consoleAppearance.sidebarStyleDescription') }}
        </p>
      </div>
      <div class="grid max-w-2xl grid-cols-1 gap-3 sm:grid-cols-3">
        <ConsoleStyleOptionCard
          v-for="opt in sidebarOptions"
          :key="opt.value"
          :label="opt.label"
          :description="opt.description"
          :active="form.console_sidebar_style === opt.value"
          @select="form.console_sidebar_style = opt.value"
        >
          <template #preview>
            <div class="h-10 w-full rounded-md" :class="opt.previewClass" />
          </template>
        </ConsoleStyleOptionCard>
      </div>
    </section>


    <section class="space-y-4 p-6">
      <div>
        <h3 class="text-sm font-medium text-foreground">
          {{ t('system.settings.consoleAppearance.sidebarAccentLabel') }}
        </h3>
        <p class="mt-0.5 text-xs text-muted-foreground">
          {{ t('system.settings.consoleAppearance.sidebarAccentDescription') }}
        </p>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <ColorPicker
          v-model="sidebarAccentColor"
          :title="t('system.settings.consoleAppearance.sidebarAccentLabel')"
        >
          <button
            type="button"
            class="h-9 w-9 shrink-0 rounded-md border border-border shadow-sm relative overflow-hidden cursor-pointer transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary/50"
            :style="{ backgroundColor: sidebarAccentColor }"
            :aria-label="t('system.settings.consoleAppearance.sidebarAccentLabel')"
          >
            <span class="absolute inset-0 bg-gradient-to-br from-transparent to-black/10" />
          </button>
        </ColorPicker>
        <Input
          v-model="sidebarAccentColor"
          class="h-9 w-[6.75rem] font-mono text-xs uppercase"
          maxlength="7"
          spellcheck="false"
          :aria-label="t('system.settings.consoleAppearance.sidebarAccentLabel')"
        />
      </div>
    </section>

    <section class="space-y-4 p-6">
      <div>
        <h3 class="text-sm font-medium text-foreground">
          {{ t('system.settings.consoleAppearance.navbarStyleLabel') }}
        </h3>
        <p class="mt-0.5 text-xs text-muted-foreground">
          {{ t('system.settings.consoleAppearance.navbarStyleDescription') }}
        </p>
      </div>
      <div class="grid max-w-2xl grid-cols-1 gap-3 sm:grid-cols-3">
        <ConsoleStyleOptionCard
          v-for="opt in navbarOptions"
          :key="opt.value"
          :label="opt.label"
          :description="opt.description"
          :active="form.console_navbar_style === opt.value"
          @select="form.console_navbar_style = opt.value"
        >
          <template #preview>
            <div class="h-3 w-full rounded-md" :class="opt.previewClass" />
          </template>
        </ConsoleStyleOptionCard>
      </div>
    </section>
</div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import ConsoleStyleOptionCard from '../../components/ConsoleStyleOptionCard.vue';
import { ColorPicker, Input } from '@/shared/components/ui';
import { useConsoleAppearanceContext } from '../../composables/useConsoleAppearancePage';

const { t } = useI18n();
const { form } = useConsoleAppearanceContext();

const sidebarAccentColor = computed({
    get: () => String(form.console_sidebar_accent || '#0f172a'),
    set: (value: string) => { form.console_sidebar_accent = value; },
});

const sidebarOptions = computed(() => [
    {
        value: 'glass',
        label: t('system.settings.consoleAppearance.shellStyles.sidebar.glass.label'),
        description: t('system.settings.consoleAppearance.shellStyles.sidebar.glass.description'),
        previewClass: 'border border-dashed border-white/20 bg-gradient-to-br from-white/10 to-transparent backdrop-blur-sm',
    },
    {
        value: 'solid',
        label: t('system.settings.consoleAppearance.shellStyles.sidebar.solid.label'),
        description: t('system.settings.consoleAppearance.shellStyles.sidebar.solid.description'),
        previewClass: 'bg-primary/90',
    },
    {
        value: 'clean',
        label: t('system.settings.consoleAppearance.shellStyles.sidebar.clean.label'),
        description: t('system.settings.consoleAppearance.shellStyles.sidebar.clean.description'),
        previewClass: 'border border-border/60 bg-muted/40',
    },
]);

const navbarOptions = computed(() => [
    {
        value: 'glass',
        label: t('system.settings.consoleAppearance.shellStyles.navbar.glass.label'),
        description: t('system.settings.consoleAppearance.shellStyles.navbar.glass.description'),
        previewClass: 'border border-white/20 bg-gradient-to-r from-white/10 to-transparent backdrop-blur-md',
    },
    {
        value: 'bordered',
        label: t('system.settings.consoleAppearance.shellStyles.navbar.bordered.label'),
        description: t('system.settings.consoleAppearance.shellStyles.navbar.bordered.description'),
        previewClass: 'border border-border/80 bg-background',
    },
    {
        value: 'blended',
        label: t('system.settings.consoleAppearance.shellStyles.navbar.blended.label'),
        description: t('system.settings.consoleAppearance.shellStyles.navbar.blended.description'),
        previewClass: 'border border-dashed border-muted-foreground/30 bg-transparent',
    },
]);
</script>
