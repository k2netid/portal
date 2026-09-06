<template>
  <div class="relative">
    <Tabs v-model="shellSection" class="w-full">
      <div class="border-b border-border/50 bg-muted/10 px-6 py-2.5">
        <TabsList class="h-auto flex-wrap gap-1.5 bg-transparent p-0">
          <TabsTrigger
            v-for="tab in shellTabs"
            :key="tab.id"
            :value="tab.id"
            class="rounded-lg px-3 py-1.5 text-xs font-medium transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-xs data-[state=active]:border data-[state=active]:border-border/60 text-muted-foreground hover:text-foreground cursor-pointer"
          >
            <component :is="tab.icon" class="w-3.5 h-3.5 mr-1.5" />
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
import { Layout, MousePointer, CreditCard, Layers, Smile, Type } from 'lucide-vue-next';
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
    { id: 'chrome', label: t('system.settings.consoleAppearance.advancedComponents.tabs.chrome'), icon: Layout },
    { id: 'buttons', label: t('system.settings.consoleAppearance.advancedComponents.tabs.buttons'), icon: MousePointer },
    { id: 'cards', label: t('system.settings.consoleAppearance.advancedComponents.tabs.cards'), icon: CreditCard },
    { id: 'overlays', label: t('system.settings.consoleAppearance.advancedComponents.tabs.overlays'), icon: Layers },
    { id: 'icons', label: t('system.settings.consoleAppearance.advancedComponents.tabs.icons'), icon: Smile },
    { id: 'type', label: t('system.settings.consoleAppearance.advancedComponents.tabs.type'), icon: Type },
]);
</script>
