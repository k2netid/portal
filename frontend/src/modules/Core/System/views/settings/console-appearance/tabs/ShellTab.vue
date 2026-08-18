<template>
  <div class="relative">
    <Tabs v-model="shellSection" class="w-full">
      <div class="border-b border-border/40 px-6 pt-2">
        <TabsList class="h-auto flex-wrap gap-1 bg-transparent p-0">
          <TabsTrigger
            v-for="tab in shellTabs"
            :key="tab.id"
            :value="tab.id"
            class="rounded-md px-3 py-1.5 text-xs data-[state=active]:bg-muted data-[state=active]:shadow-none"
          >
            {{ tab.label }}
          </TabsTrigger>
        </TabsList>
      </div>

      <TabsContent value="chrome" class="mt-0 focus-visible:outline-none">
        <ShellChromeSection />
      </TabsContent>
      <TabsContent value="buttons" class="mt-0 focus-visible:outline-none">
        <ShellButtonsSection />
      </TabsContent>
      <TabsContent value="cards" class="mt-0 focus-visible:outline-none">
        <ShellCardsSection />
      </TabsContent>
      <TabsContent value="overlays" class="mt-0 focus-visible:outline-none">
        <ShellOverlaysSection />
      </TabsContent>
      <TabsContent value="icons" class="mt-0 focus-visible:outline-none">
        <ShellIconsSection />
      </TabsContent>
      <TabsContent value="type" class="mt-0 focus-visible:outline-none">
        <ShellDepthSection />
      </TabsContent>
    </Tabs>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/shared/components/ui';
import ShellChromeSection from '@/modules/Core/System/views/settings/console-appearance/tabs/shell/ShellChromeSection.vue';
import ShellButtonsSection from '@/modules/Core/System/views/settings/console-appearance/tabs/shell/ShellButtonsSection.vue';
import ShellCardsSection from '@/modules/Core/System/views/settings/console-appearance/tabs/shell/ShellCardsSection.vue';
import ShellOverlaysSection from '@/modules/Core/System/views/settings/console-appearance/tabs/shell/ShellOverlaysSection.vue';
import ShellIconsSection from '@/modules/Core/System/views/settings/console-appearance/tabs/shell/ShellIconsSection.vue';
import ShellDepthSection from '@/modules/Core/System/views/settings/console-appearance/tabs/shell/ShellDepthSection.vue';

const { t } = useI18n();
const shellSection = ref('chrome');

const shellTabs = computed(() => [
    { id: 'chrome', label: t('system.settings.consoleAppearance.advancedComponents.tabs.chrome') },
    { id: 'buttons', label: t('system.settings.consoleAppearance.advancedComponents.tabs.buttons') },
    { id: 'cards', label: t('system.settings.consoleAppearance.advancedComponents.tabs.cards') },
    { id: 'overlays', label: t('system.settings.consoleAppearance.advancedComponents.tabs.overlays') },
    { id: 'icons', label: t('system.settings.consoleAppearance.advancedComponents.tabs.icons') },
    { id: 'type', label: t('system.settings.consoleAppearance.advancedComponents.tabs.type') },
]);
</script>
