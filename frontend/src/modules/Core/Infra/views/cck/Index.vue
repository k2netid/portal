<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="$t('infra.cck.title')"
      :subtitle="$t('infra.cck.subtitle')"
    >
      <template #actions>
        <Button
size="sm"
          class="h-10 inline-flex items-center gap-2" @click="router.push({ name: 'cck-create' })"
>
          {{ $t('infra.cck.newType') }}
        </Button>
      </template>
    </PageHeader>

    <div
      v-if="loading"
      class="text-sm text-muted-foreground"
    >
      Loading…
    </div>
    <p
      v-else-if="error"
      class="text-sm text-destructive"
    >
      {{ error }}
    </p>
    <div
      v-else-if="types.length === 0"
      class="text-sm text-muted-foreground"
    >
      {{ $t('infra.cck.empty') }}
    </div>
    <ConsoleListCard>
      <div class="overflow-x-auto min-w-0">
<table class="w-full text-sm">
        <thead>
          <tr class="border-b border-border text-left text-muted-foreground bg-muted/30">
            <th class="py-2 px-4">Name</th>
            <th class="py-2 px-4">Slug</th>
            <th class="py-2 px-4">Fields</th>
            <th class="py-2 px-4">Status</th>
            <th class="py-2 px-4">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="type in types"
            :key="type.id"
            class="border-b border-border/60"
          >
            <td class="py-2 px-4 font-medium">{{ type.name }}</td>
            <td class="py-2 px-4 font-mono text-xs">{{ type.slug }}</td>
            <td class="py-2 px-4">{{ type.fields?.length ?? 0 }}</td>
            <td class="py-2 px-4">
              <span :class="type.is_active === false ? 'text-muted-foreground' : 'text-foreground'">
                {{ type.is_active === false ? 'inactive' : 'active' }}
              </span>
            </td>
            <td class="py-2 px-4 flex flex-wrap gap-2">
              <Button
                size="sm"
          class="h-10 inline-flex items-center gap-2"
                variant="default"
                @click="router.push({ name: 'dynamic-records-index', params: { slug: type.slug } })"
              >
                Records
              </Button>
              <Button
                size="sm"
          class="h-10 inline-flex items-center gap-2"
                variant="outline"
                @click="router.push({ name: 'cck-edit', params: { id: type.id } })"
              >
                Schema
              </Button>
            </td>
          </tr>
        </tbody>
      </table>
</div>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import {PageHeader, ConsoleListCard} from '@/shared/components/shell';

import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { Button } from '@/shared/components/ui';
import { parseResponse } from '@/shared/utils/responseParser';
import CckService, { type CckContentType } from '../../services/cckService';

const router = useRouter();
const loading = ref(true);
const error = ref('');
const types = ref<CckContentType[]>([]);

onMounted(async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await CckService.listTypes();
        const page = parseResponse<CckContentType>(response);
        types.value = page.data ?? [];
    } catch (e: unknown) {
        error.value = e instanceof Error ? e.message : 'Failed to load content types';
    } finally {
        loading.value = false;
    }
});
</script>
