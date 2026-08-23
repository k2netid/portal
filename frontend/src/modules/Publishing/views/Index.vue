<template>
  <div class="space-y-6">
        <PageHeader
      borderless
      :title="t('publishing.content.title')"
      :subtitle="t('publishing.content.description')"
    >
    </PageHeader>

    <ConsoleListCard>
      <div class="w-full px-4 pt-4">
      <Tabs
        v-model="activeTab"
        class="w-full"
      >
        <div class="mb-10 flex items-center justify-between">
          <TabsList class="bg-transparent p-0 h-auto">
            <TabsTrigger
              value="contents"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none inline-flex items-center gap-2"
            >
              <FileText class="size-4 shrink-0" />
              {{ $t('publishing.content.tabs.contents') }}
            </TabsTrigger>
            <TabsTrigger
              value="categories"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none inline-flex items-center gap-2"
            >
              <Layers class="size-4 shrink-0" />
              {{ $t('publishing.content.tabs.categories') }}
            </TabsTrigger>
            <TabsTrigger
              value="tags"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none inline-flex items-center gap-2"
            >
              <Tag class="size-4 shrink-0" />
              {{ $t('publishing.content.tabs.tags') }}
            </TabsTrigger>
            <TabsTrigger
              value="templates"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none inline-flex items-center gap-2"
            >
              <LayoutTemplate class="size-4 shrink-0" />
              {{ $t('publishing.content.list.templates') }}
            </TabsTrigger>
          </TabsList>

          <div class="flex items-center gap-2 pb-1">
            <!-- Redundant button removed as it's now a tab -->
          </div>
        </div>

        <div class="p-0">
          <TabsContent value="contents">
            <ContentsIndex v-if="activeTab === 'contents'" :is-embedded="true" />
          </TabsContent>
          <TabsContent value="categories">
            <CategoriesIndex v-if="activeTab === 'categories'" :is-embedded="true" />
          </TabsContent>
          <TabsContent value="tags">
            <TagsIndex v-if="activeTab === 'tags'" :is-embedded="true" scope="content" />
          </TabsContent>
          <TabsContent value="templates">
            <TemplatesIndex v-if="activeTab === 'templates'" :is-embedded="true" />
          </TabsContent>
        </div>
      </Tabs>
      </div>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';

import {
    Tabs,
    TabsList,
    TabsTrigger,
    TabsContent
} from '@/shared/components/ui';
import {
  FileText,
  Layers,
  LayoutTemplate,
  Tag,
} from 'lucide-vue-next';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useHead } from '@unhead/vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { ref, watch, computed, defineAsyncComponent } from 'vue';

const ContentsIndex = defineAsyncComponent(() => import('./contents/Index.vue'));
const CategoriesIndex = defineAsyncComponent(() => import('./categories/Index.vue'));
const TagsIndex = defineAsyncComponent(() => import('@/modules/Library/views/tags/Index.vue'));
const TemplatesIndex = defineAsyncComponent(() => import('./content-templates/Index.vue'));

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const systemStore = useSystemStore();

useHead({
    title: computed(() => `${systemStore.siteSettings?.site_name || 'JA Jejakawan'} | ${t('publishing.content.title')}`)
});

const activeTab = ref((route.query.tab as string) || 'contents');

watch(activeTab, (newTab) => {
    router.replace({ 
        query: { ...route.query, tab: newTab } 
    });
});
</script>


