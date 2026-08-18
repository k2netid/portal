<template>
  <div class="space-y-6">
    <PageHeader borderless>
      <template #title>{{ contentType?.name ?? 'Records' }}</template>
      <template #subtitle>
        <RouterLink
          :to="{ name: 'cck-index' }"
          class="text-xs text-muted-foreground hover:underline"
        >
          Content types
        </RouterLink>
        <span class="text-xs text-muted-foreground"> / {{ contentType?.name ?? slug }}</span>
        <span class="block font-mono text-xs text-muted-foreground mt-1">/api/v1/dynamic/{{ slug }}</span>
      </template>
      <template #actions>
        <Button
          size="sm"
          :disabled="!contentType"
          @click="router.push({ name: 'dynamic-records-create', params: { slug } })"
        >
          New record
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
      v-else-if="records.length === 0"
      class="text-sm text-muted-foreground"
    >
      No records yet.
    </div>
    <ConsoleListCard>
      <div class="overflow-x-auto min-w-0">
<table class="w-full text-sm">
        <thead>
          <tr class="border-b border-border text-left text-muted-foreground bg-muted/30">
            <th class="py-2 px-4">Preview</th>
            <th class="py-2 px-4">Updated</th>
            <th class="py-2 px-4">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in records"
            :key="row.id"
            class="border-b border-border/60"
          >
            <td class="py-2 px-4 max-w-md truncate">
              {{ recordPreview(row) }}
            </td>
            <td class="py-2 px-4 text-xs text-muted-foreground">
              {{ row.updated_at ? formatDate(row.updated_at) : '—' }}
            </td>
            <td class="py-2 px-4 flex gap-2">
              <Button
                size="sm"
                variant="outline"
                @click="router.push({ name: 'dynamic-records-edit', params: { slug, recordId: row.id } })"
              >
                Edit
              </Button>
              <Button
                size="sm"
                variant="ghost"
                class="text-destructive"
                :disabled="saving"
                @click="removeRecord(row.id)"
              >
                Delete
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

import { onMounted, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { Button } from '@/shared/components/ui';
import { parseResponse, parseSingleResponse } from '@/shared/utils/responseParser';
import CckService, { type CckContentType } from '../../services/cckService';
import DynamicRecordService, { type DynamicRecordRow } from '../../services/dynamicRecordService';

const route = useRoute();
const router = useRouter();
const slug = ref(String(route.params.slug ?? ''));

const loading = ref(true);
const saving = ref(false);
const error = ref('');
const contentType = ref<CckContentType | null>(null);
const records = ref<DynamicRecordRow[]>([]);

function formatDate(iso: string): string {
    try {
        return new Date(iso).toLocaleString();
    } catch {
        return iso;
    }
}

function recordPreview(row: DynamicRecordRow): string {
    const data = row.data ?? {};
    const first = contentType.value?.fields?.[0]?.slug;
    if (first && data[first] !== undefined && data[first] !== null) {
        return String(data[first]);
    }
    const values = Object.values(data).filter((v) => v !== null && v !== '');
    return values.length > 0 ? String(values[0]) : row.id;
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const typeRes = await CckService.getTypeBySlug(slug.value);
        contentType.value = parseSingleResponse<CckContentType>(typeRes);
        if (!contentType.value) {
            error.value = 'Content type not found';
            return;
        }
        const listRes = await DynamicRecordService.list(slug.value, { per_page: 50 });
        const page = parseResponse<DynamicRecordRow>(listRes);
        records.value = page.data ?? [];
    } catch (e: unknown) {
        error.value = e instanceof Error ? e.message : 'Failed to load records';
    } finally {
        loading.value = false;
    }
}

async function removeRecord(id: string): Promise<void> {
    if (!confirm('Delete this record?')) {
        return;
    }
    saving.value = true;
    try {
        await DynamicRecordService.remove(slug.value, id);
        await load();
    } catch (e: unknown) {
        error.value = e instanceof Error ? e.message : 'Delete failed';
    } finally {
        saving.value = false;
    }
}

watch(
    () => route.params.slug,
    (value) => {
        slug.value = String(value ?? '');
        if (slug.value) {
            load();
        }
    },
);

onMounted(() => {
    if (slug.value) {
        load();
    }
});
</script>
