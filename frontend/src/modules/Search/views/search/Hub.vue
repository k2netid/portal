<template>
  <div class="console-page min-w-0 max-w-full space-y-6">
    <PageHeader
      borderless
      :title="t('search.hub.title')"
      :subtitle="t('search.hub.subtitle')"
    />

    <div class="flex flex-wrap gap-2 border-b border-border pb-3">
      <Button
        size="sm"
        :variant="activeTab === 'search' ? 'default' : 'outline'"
        @click="activeTab = 'search'"
      >
        {{ t('search.hub.tabs.search') }}
      </Button>
      <Button
        size="sm"
        :variant="activeTab === 'ops' ? 'default' : 'outline'"
        @click="activeTab = 'ops'"
      >
        {{ t('search.hub.tabs.ops') }}
      </Button>
    </div>

    <SearchExplore v-show="activeTab === 'search'" />
    <SearchOps v-show="activeTab === 'ops'" />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { PageHeader } from '@/shared/components/shell';
import { Button } from '@/shared/components/ui';
import SearchExplore from './Index.vue';
import SearchOps from './Ops.vue';

const { t } = useI18n();
const activeTab = ref<'search' | 'ops'>('search');
</script>
