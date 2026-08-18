<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="t('ai.title')"
      :subtitle="t('ai.subtitle')"
    />

    <div
      v-if="loading"
      class="flex flex-col items-center justify-center py-20"
    >
      <p class="text-sm text-muted-foreground">
        {{ t('ai.loading') }}
      </p>
    </div>
    <p
      v-else-if="error"
      class="text-sm text-destructive"
    >
      {{ error }}
    </p>

    <template v-else>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <ConsoleStatCard
          :label="t('ai.stats.totalCalls')"
          :value="stats?.total_calls ?? 0"
          :icon="Activity"
          tone="primary"
        />
        <ConsoleStatCard
          :label="t('ai.stats.tokensMonth')"
          :value="stats?.monthly_tokens_used ?? 0"
          :icon="Coins"
          tone="info"
        />
        <ConsoleStatCard
          :label="t('ai.stats.tokenLimit')"
          :value="stats?.monthly_token_limit == null ? '∞' : stats?.monthly_token_limit"
          :icon="Gauge"
          tone="warning"
        />
        <ConsoleStatCard
          :label="t('ai.stats.providers')"
          :value="providers.length"
          :icon="Blocks"
          :tone="providers.length ? 'success' : 'muted'"
        />
      </div>

      <ConsoleListCard>
        <div class="divide-y divide-border/50">
          <section class="space-y-4 p-4 sm:p-6">
            <div class="flex flex-row items-center justify-between gap-3">
              <h3 class="text-base font-semibold text-foreground">
                {{ t('ai.usageByFeature') }}
              </h3>
              <Button
                size="sm"
          class="h-10 inline-flex items-center gap-2"
                variant="outline"
                @click="load"
              >
                {{ t('ai.refresh') }}
              </Button>
            </div>
            <p
              v-if="!stats?.by_feature?.length"
              class="text-sm text-muted-foreground"
            >
              {{ t('ai.noCalls') }}
            </p>
            <div
              v-else
              class="overflow-x-auto"
            >
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b border-border text-left text-muted-foreground">
                    <th class="py-2 pr-4">{{ t('ai.table.feature') }}</th>
                    <th class="py-2 pr-4">{{ t('ai.table.provider') }}</th>
                    <th class="py-2 pr-4">{{ t('ai.table.calls') }}</th>
                    <th class="py-2 pr-4">{{ t('ai.table.tokensIn') }}</th>
                    <th class="py-2">{{ t('ai.table.tokensOut') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(row, idx) in stats.by_feature"
                    :key="`${row.feature}-${row.provider}-${idx}`"
                    class="border-b border-border/60"
                  >
                    <td class="py-2 pr-4 font-mono text-xs">{{ row.feature }}</td>
                    <td class="py-2 pr-4 capitalize">{{ row.provider ?? '—' }}</td>
                    <td class="py-2 pr-4">{{ row.calls }}</td>
                    <td class="py-2 pr-4">{{ row.tokens_in }}</td>
                    <td class="py-2">{{ row.tokens_out }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <section class="space-y-4 p-4 sm:p-6">
            <div class="flex flex-row items-start justify-between gap-4">
              <div>
                <h3 class="text-base font-semibold text-foreground">
                  {{ t('ai.batch.title') }}
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                  {{ t('ai.batch.description') }}
                </p>
              </div>
              <Button
                size="sm"
          class="h-10 inline-flex items-center gap-2"
                variant="outline"
                :disabled="batchLoading"
                @click="loadBatches"
              >
                {{ t('ai.batch.refreshJobs') }}
              </Button>
            </div>
            <div class="space-y-2">
              <Label for="batch-items-json">{{ t('ai.batch.itemsLabel') }}</Label>
              <Textarea
                id="batch-items-json"
                v-model="batchJson"
                class="min-h-[140px] font-mono text-xs"
                :placeholder="batchPlaceholder"
              />
            </div>
            <p
              v-if="batchError"
              class="text-sm text-destructive"
            >
              {{ batchError }}
            </p>
            <Button
              :disabled="batchSubmitting"
              @click="submitBatch"
            >
              {{ batchSubmitting ? t('ai.batch.queuing') : t('ai.batch.queue') }}
            </Button>

            <p
              v-if="batches.length === 0"
              class="text-sm text-muted-foreground"
            >
              {{ t('ai.batch.empty') }}
            </p>
            <div
              v-else
              class="overflow-x-auto"
            >
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b border-border text-left text-muted-foreground">
                    <th class="py-2 pr-4">{{ t('ai.batch.table.id') }}</th>
                    <th class="py-2 pr-4">{{ t('ai.batch.table.status') }}</th>
                    <th class="py-2 pr-4">{{ t('ai.batch.table.progress') }}</th>
                    <th class="py-2 pr-4">{{ t('ai.batch.table.provider') }}</th>
                    <th class="py-2">{{ t('ai.batch.table.created') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="job in batches"
                    :key="job.id"
                    class="cursor-pointer border-b border-border/60 hover:bg-muted/40"
                    @click="selectBatch(job.id)"
                  >
                    <td class="py-2 pr-4 font-mono text-xs">
                      {{ job.id.slice(0, 8) }}…
                    </td>
                    <td class="py-2 pr-4">
                      <Badge :variant="batchStatusVariant(job.status)">
                        {{ job.status }}
                      </Badge>
                    </td>
                    <td class="py-2 pr-4">
                      {{ job.completed_items }}/{{ job.total_items }}
                      <span
                        v-if="job.failed_items > 0"
                        class="text-destructive"
                      > {{ t('ai.batch.failedSuffix', { count: job.failed_items }) }}</span>
                    </td>
                    <td class="py-2 pr-4 capitalize">{{ job.provider ?? '—' }}</td>
                    <td class="py-2 text-xs text-muted-foreground">{{ formatWhen(job.created_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div
              v-if="selectedBatch"
              class="space-y-2 rounded-md border border-border bg-muted/20 p-4"
            >
              <p class="text-sm font-medium">
                {{ t('ai.batch.resultsTitle', { id: selectedBatch.id }) }}
              </p>
              <pre class="max-h-64 overflow-auto rounded bg-muted/30 p-3 font-mono text-xs">{{ JSON.stringify(selectedBatch.results ?? [], null, 2) }}</pre>
            </div>
          </section>

          <section class="space-y-2 p-4 sm:p-6">
            <h3 class="text-base font-semibold text-foreground">
              {{ t('ai.help.title') }}
            </h3>
            <p class="text-sm text-muted-foreground">
              {{ t('ai.help.body') }}
            </p>
            <RouterLink
              class="inline-block text-sm text-primary hover:underline"
              :to="{ name: 'settings', query: { tab: 'ai' } }"
            >
              {{ t('ai.help.settingsLink') }}
            </RouterLink>
          </section>
        </div>
      </ConsoleListCard>
    </template>
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleListCard, ConsoleStatCard } from '@/shared/components/shell';

import { onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink } from 'vue-router';
import { Activity, Blocks, Coins, Gauge } from 'lucide-vue-next';
import { AiService } from '@/modules/Intelligence/Ai/services/aiService';

/** Literal JSON samples — must not go through vue-i18n (braces break message compiler). */
const BATCH_ITEMS_PLACEHOLDER = '[{"ref":"post-1","title":"Title","excerpt":"..."}]';
const BATCH_DEFAULT_JSON = `[
  {
    "ref": "example-1",
    "title": "Semester opening announcement",
    "excerpt": "Welcome students and parents",
    "existing_categories": ["News"],
    "existing_tags": ["publishing"]
  }
]`;

import {
    Badge,
    Button,
    Label,
    Textarea,
} from '@/shared/components/ui';
import { parseResponse, parseSingleResponse } from '@/shared/utils/responseParser';

const { t } = useI18n();

const batchPlaceholder = BATCH_ITEMS_PLACEHOLDER;

interface UsageRow {
    feature: string;
    provider: string | null;
    calls: number;
    tokens_in: number;
    tokens_out: number;
}

interface UsageStats {
    period_days: number;
    total_calls: number;
    monthly_token_limit: number | null;
    monthly_tokens_used: number;
    by_feature: UsageRow[];
}

interface TaxonomyBatchJob {
    id: string;
    status: string;
    total_items: number;
    completed_items: number;
    failed_items: number;
    provider: string | null;
    error_message: string | null;
    results: Array<Record<string, unknown>> | null;
    created_at: string | null;
}

const loading = ref(true);
const error = ref('');
const stats = ref<UsageStats | null>(null);
const providers = ref<Array<{ id?: string; name?: string }>>([]);
const batchJson = ref(BATCH_DEFAULT_JSON);
const batchError = ref('');
const batchSubmitting = ref(false);
const batchLoading = ref(false);
const batches = ref<TaxonomyBatchJob[]>([]);
const selectedBatch = ref<TaxonomyBatchJob | null>(null);
let batchPollTimer: ReturnType<typeof setInterval> | null = null;

function batchStatusVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    if (status === 'completed') {
        return 'default';
    }
    if (status === 'failed') {
        return 'destructive';
    }
    if (status === 'processing' || status === 'pending') {
        return 'secondary';
    }

    return 'outline';
}

function formatWhen(iso: string | null): string {
    if (!iso) {
        return '—';
    }
    try {
        return new Date(iso).toLocaleString();
    } catch {
        return iso;
    }
}

async function loadBatches(): Promise<void> {
    batchLoading.value = true;
    batchError.value = '';
    try {
        const res = await AiService.taxonomyBatches({ limit: 10 });
        const list = parseResponse<TaxonomyBatchJob[]>(res);
        batches.value = Array.isArray(list) ? list : [];
        if (selectedBatch.value) {
            const fresh = batches.value.find((b) => b.id === selectedBatch.value?.id);
            if (fresh) {
                selectedBatch.value = fresh;
            }
        }
        const needsPoll = batches.value.some(
            (b) => b.status === 'pending' || b.status === 'processing',
        );
        if (needsPoll && batchPollTimer === null) {
            batchPollTimer = setInterval(() => {
                void loadBatches();
            }, 2500);
        } else if (!needsPoll && batchPollTimer !== null) {
            clearInterval(batchPollTimer);
            batchPollTimer = null;
        }
    } catch (e: unknown) {
        batchError.value = e instanceof Error ? e.message : t('ai.batch.errors.loadJobs');
    } finally {
        batchLoading.value = false;
    }
}

async function selectBatch(id: string): Promise<void> {
    try {
        const res = await AiService.taxonomyBatch(id);
        selectedBatch.value = parseSingleResponse<TaxonomyBatchJob>(res);
    } catch (e: unknown) {
        batchError.value = e instanceof Error ? e.message : t('ai.batch.errors.loadBatch');
    }
}

async function submitBatch(): Promise<void> {
    batchSubmitting.value = true;
    batchError.value = '';
    try {
        const items = JSON.parse(batchJson.value) as unknown;
        if (!Array.isArray(items)) {
            throw new Error(t('ai.batch.errors.mustBeArray'));
        }
        const res = await AiService.createTaxonomyBatch({ items });
        const created = parseSingleResponse<TaxonomyBatchJob>(res);
        selectedBatch.value = created;
        await loadBatches();
        await load();
    } catch (e: unknown) {
        batchError.value = e instanceof Error ? e.message : t('ai.batch.errors.queue');
    } finally {
        batchSubmitting.value = false;
    }
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const [usageRes, providerRes] = await Promise.all([
            AiService.usageStats(),
            AiService.providers(),
        ]);
        stats.value = parseSingleResponse<UsageStats>(usageRes);
        const providerList = parseResponse<Array<{ id?: string; name?: string }>>(providerRes);
        providers.value = Array.isArray(providerList) ? providerList : [];
    } catch (e: unknown) {
        error.value = e instanceof Error ? e.message : t('ai.errors.load');
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    void load();
    void loadBatches();
});

onUnmounted(() => {
    if (batchPollTimer !== null) {
        clearInterval(batchPollTimer);
        batchPollTimer = null;
    }
});
</script>
