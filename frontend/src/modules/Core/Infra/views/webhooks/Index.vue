<template>
  <div class="space-y-6">
    <PageHeader
borderless
      :title="t('infra.webhooks.title')"
    :subtitle="t('infra.webhooks.subtitle')"
    >
    </PageHeader>

    <!-- Statistics -->
    <div
      v-if="statistics"
      class="grid grid-cols-1 gap-4 md:grid-cols-4"
    >
      <ConsoleStatCard
        :label="t('infra.webhooks.stats.total')"
        :value="statistics.total || 0"
        :icon="Zap"
        tone="primary"
      />
      <ConsoleStatCard
        :label="t('infra.webhooks.stats.active')"
        :value="statistics.active || 0"
        :icon="CheckCircle"
        tone="success"
      />
      <ConsoleStatCard
        :label="t('infra.webhooks.stats.total_calls')"
        :value="statistics.total_calls || 0"
        :icon="BarChart3"
        tone="info"
      />
      <ConsoleStatCard
        :label="t('infra.webhooks.stats.failed_calls')"
        :value="statistics.failed_calls || 0"
        :icon="AlertCircle"
        tone="destructive"
      />
    </div>

    <ConsoleListCard>
      <template #toolbar>
        <div class="relative w-full sm:max-w-xs shrink-0">
          <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            v-model="search"
            type="text"
            :placeholder="t('infra.webhooks.search')"
            class="h-10 w-full pl-9 bg-background"
          />
        </div>
      </template>

      <div
        v-if="loading"
        class="p-6 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('infra.webhooks.loading') }}
        </p>
      </div>

      <div
        v-else-if="filteredWebhooks.length === 0"
        class="p-6 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('infra.webhooks.empty') }}
        </p>
      </div>

      <table
        v-else
        class="min-w-full divide-y divide-border"
      >
        <thead class="bg-muted">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
              {{ t('infra.webhooks.table.name') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
              {{ t('infra.webhooks.table.url') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
              {{ t('infra.webhooks.table.events') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
              {{ t('infra.webhooks.table.calls') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
              {{ t('infra.webhooks.table.status') }}
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground tracking-wider">
              {{ t('infra.webhooks.table.actions') }}
            </th>
          </tr>
        </thead>
        <tbody class="bg-card divide-y divide-border">
          <tr
            v-for="webhook in filteredWebhooks"
            :key="webhook.id"
            class="hover:bg-muted"
          >
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-foreground">
                {{ webhook.name }}
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-foreground font-mono truncate max-w-xs">
                {{ webhook.url }}
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="event in (webhook.events || [])"
                  :key="event"
                  class="px-2 py-1 text-xs bg-secondary text-secondary-foreground rounded"
                >
                  {{ t('infra.webhooks.events.' + event) }}
                </span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
              {{ webhook.total_calls || 0 }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                :class="webhook.is_active ? 'bg-green-500/20 text-green-400' : 'bg-secondary text-secondary-foreground'"
              >
                {{ webhook.is_active ? t('infra.plugins.status.active') : t('infra.plugins.status.inactive') }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex justify-end space-x-2">
                <button
                  class="text-blue-600 hover:text-blue-900"
                  @click="testWebhook(webhook)"
                >
                  {{ t('infra.webhooks.actions.test') }}
                </button>
                <button
                  class="text-indigo-600 hover:text-indigo-900"
                  @click="editWebhook(webhook)"
                >
                  {{ t('infra.webhooks.actions.edit') }}
                </button>
                <button
                  class="text-red-600 hover:text-red-900"
                  @click="deleteWebhook(webhook)"
                >
                  {{ t('infra.webhooks.actions.delete') }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </ConsoleListCard>

    <!-- Create/Edit Modal -->
    <WebhookModal
      v-if="showCreateModal || showEditModal"
      :webhook="editingWebhook"
      @close="closeModal"
      @saved="handleWebhookSaved"
    />
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleListCard, ConsoleStatCard } from '@/shared/components/shell';
import { Input } from '@/shared/components/ui';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import WebhookModal from '@/modules/Core/Infra/components/webhooks/WebhookModal.vue';
import { parseResponse, ensureArray, parseSingleResponse } from '@/shared/utils/responseParser';
import {
  AlertCircle,
  BarChart3,
  CheckCircle,
  Search,
  Zap,
} from 'lucide-vue-next';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

interface Webhook {
    id: string;
    name: string;
    url: string;
    events: string[];
    total_calls: number;
    failed_calls: number;
    is_active: boolean;
    secret?: string;
    created_at?: string;
}

interface WebhookStats {
    total: number;
    active: number;
    total_calls: number;
    failed_calls: number;
}

const webhooks = ref<Webhook[]>([]);
const statistics = ref<WebhookStats | null>(null);
const loading = ref(false);
const search = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingWebhook = ref<Webhook | null>(null);

const filteredWebhooks = computed(() => {
    if (!search.value) return webhooks.value;
    
    const searchLower = search.value.toLowerCase();
    return webhooks.value.filter((webhook: Webhook) => 
        webhook.name.toLowerCase().includes(searchLower) ||
        webhook.url.toLowerCase().includes(searchLower)
    );
});

const fetchWebhooks = async () => {
    loading.value = true;
    try {
        const response = await api.get('/manage/webhooks');
        const { data } = parseResponse(response);
        webhooks.value = ensureArray(data);
        
        // Fetch statistics
        try {
            const statsResponse = await api.get('/manage/webhooks/statistics');
            statistics.value = parseSingleResponse<WebhookStats>(statsResponse);
        } catch {
            // Calculate from webhooks if endpoint doesn't exist
            statistics.value = {
                total: webhooks.value.length,
                active: webhooks.value.filter(w => w.is_active).length,
                total_calls: webhooks.value.reduce((sum, w) => sum + (w.total_calls || 0), 0),
                failed_calls: webhooks.value.reduce((sum, w) => sum + (w.failed_calls || 0), 0)};
        }
        } catch (err: unknown) {
            logger.error('Failed to fetch webhooks:', err);
    } finally {
        loading.value = false;
    }
};

const editWebhook = (webhook: Webhook) => {
    editingWebhook.value = webhook;
    showEditModal.value = true;
};

const testWebhook = async (webhook: Webhook) => {
    try {
        await api.post(`/manage/webhooks/${webhook.id}/test`);
        toast.success.action(t('infra.webhooks.messages.test_success'));
    } catch (error: unknown) {
        logger.error('Failed to test webhook:', error);
        toast.error.fromResponse(error);
    }
};

const deleteWebhook = async (webhook: Webhook) => {
    const confirmed = await confirm({
        title: t('infra.webhooks.actions.delete'),
        message: t('infra.webhooks.confirm.delete', { name: webhook.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete')});

    if (!confirmed) return;

    try {
        await api.delete(`/manage/webhooks/${webhook.id}`);
        toast.success.delete(t('infra.webhooks.title'));
        fetchWebhooks();
    } catch (error: unknown) {
        logger.error('Failed to delete webhook:', error);
        toast.error.delete(error, 'Webhook');
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingWebhook.value = null;
};

const handleWebhookSaved = () => {
    fetchWebhooks();
    closeModal();
};

onMounted(() => {
    fetchWebhooks();
});
</script>

