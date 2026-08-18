<template>
  <div class="console-page min-w-0 max-w-full space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-lg font-semibold text-foreground">
          {{ t('search.ops.title') }}
        </h2>
        <p class="text-sm text-muted-foreground">
          {{ t('search.ops.subtitle') }}
        </p>
      </div>
      <div class="flex gap-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="loading"
          @click="loadHealth"
        >
          {{ t('search.ops.refresh') }}
        </Button>
        <Button
          size="sm"
          :disabled="reindexing || loading"
          @click="runReindex"
        >
          {{ reindexing ? t('search.ops.reindexing') : t('search.ops.reindex') }}
        </Button>
      </div>
    </div>

    <p
      v-if="error"
      class="text-sm text-destructive"
    >
      {{ error }}
    </p>

    <p
      v-if="message"
      class="text-sm text-emerald-600 dark:text-emerald-400"
    >
      {{ message }}
    </p>

    <div
      v-if="loading && !health"
      class="text-sm text-muted-foreground"
    >
      {{ t('search.ops.loading') }}
    </div>

    <template v-else-if="health">
      <div
        class="rounded-lg border p-4"
        :class="health.in_sync
          ? 'border-emerald-500/40 bg-emerald-500/5'
          : 'border-amber-500/40 bg-amber-500/5'"
      >
        <p class="font-medium text-foreground">
          {{ health.in_sync
            ? t('search.ops.inSync')
            : t('search.ops.lagDetected', { count: health.total_lag }) }}
        </p>
        <p class="text-xs text-muted-foreground mt-1">
          {{ t('search.ops.checkedMeta', {
            time: formatCheckedAt(health.checked_at),
            total: health.index_totals.all,
          }) }}
        </p>
      </div>

      <div class="overflow-x-auto rounded-lg border border-border">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-border text-left text-muted-foreground">
              <th class="py-2 px-3">{{ t('search.ops.table.resource') }}</th>
              <th class="py-2 px-3 text-right">{{ t('search.ops.table.source') }}</th>
              <th class="py-2 px-3 text-right">{{ t('search.ops.table.indexed') }}</th>
              <th class="py-2 px-3 text-right">{{ t('search.ops.table.lag') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in health.resources"
              :key="row.key"
              class="border-b border-border/60"
            >
              <td class="py-2 px-3">{{ row.label }}</td>
              <td class="py-2 px-3 text-right tabular-nums">{{ row.source }}</td>
              <td class="py-2 px-3 text-right tabular-nums">{{ row.indexed }}</td>
              <td
                class="py-2 px-3 text-right tabular-nums font-medium"
                :class="row.lag > 0 ? 'text-amber-600' : ''"
              >
                {{ row.lag }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <p class="text-xs text-muted-foreground">
        {{ t('search.ops.cliHint') }}
      </p>
    </template>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/shared/components/ui';
import { SearchService, type SearchIndexHealthSnapshot } from '@/modules/Intelligence/Search/services/searchService';
import { parseSingleResponse } from '@/shared/utils/responseParser';

const { t } = useI18n();

const health = ref<SearchIndexHealthSnapshot | null>(null);
const loading = ref(false);
const reindexing = ref(false);
const error = ref('');
const message = ref('');

async function loadHealth(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const response = await SearchService.indexHealth();
        health.value = parseSingleResponse<SearchIndexHealthSnapshot>(response);
    } catch (e: unknown) {
        error.value = e instanceof Error ? e.message : t('search.ops.loadFailed');
    } finally {
        loading.value = false;
    }
}

async function runReindex(): Promise<void> {
    reindexing.value = true;
    error.value = '';
    message.value = '';
    try {
        await SearchService.reindex();
        message.value = t('search.ops.reindexDone');
        await loadHealth();
    } catch (e: unknown) {
        error.value = e instanceof Error ? e.message : t('search.ops.reindexFailed');
    } finally {
        reindexing.value = false;
    }
}

function formatCheckedAt(iso: string): string {
    try {
        return new Date(iso).toLocaleString();
    } catch {
        return iso;
    }
}

onMounted(() => {
    void loadHealth();
});
</script>
