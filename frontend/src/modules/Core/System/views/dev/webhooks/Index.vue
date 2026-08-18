<template>
  <div class="space-y-6">
    <PageHeader :title="t('system.webhooks.title')" :description="t('system.webhooks.description')">
      <template #actions>
        <Button @click="openCreate">{{ t('common.actions.create') }}</Button>
      </template>
    </PageHeader>

    <PageSkeleton v-if="loading" />

    <EmptyState
      v-else-if="webhooks.length === 0"
      :title="t('system.webhooks.empty.title')"
      :description="t('system.webhooks.empty.description')"
      :icon="WebhookIcon"
    />

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card v-for="hook in webhooks" :key="hook.id" class="p-6">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <h3 class="font-semibold text-lg flex flex-wrap items-center gap-2">
              {{ hook.name }}
              <Badge v-if="hook.is_active" variant="default">{{ t('system.webhooks.form.active') }}</Badge>
              <Badge v-else variant="secondary">{{ t('common.status.inactive', 'Inactive') }}</Badge>
            </h3>
            <p class="text-sm text-muted-foreground mt-1 truncate">{{ hook.url }}</p>
          </div>
        </div>
        <div class="mt-4">
          <p class="text-xs font-semibold uppercase text-muted-foreground mb-1">{{ t('system.webhooks.form.events') }}</p>
          <div class="flex flex-wrap gap-1">
            <span v-for="ev in hook.events" :key="ev" class="text-xs bg-muted px-2 py-1 rounded">{{ ev }}</span>
          </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
          <Button variant="outline" size="sm" @click="triggerWebhook(hook.id)">{{ t('system.webhooks.actions.testPing') }}</Button>
          <Button variant="ghost" size="sm" @click="openEdit(hook)">{{ t('system.webhooks.actions.edit') }}</Button>
          <Button variant="ghost" size="sm" class="text-destructive" @click="removeWebhook(hook)">{{ t('system.webhooks.actions.delete') }}</Button>
        </div>
      </Card>
    </div>

    <WebhookDeliveriesPanel class="mt-8" />

    <WebhookFormModal v-model:open="modalOpen" :webhook="editing" @saved="onSaved" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import PageHeader from '@/shared/components/shell/PageHeader.vue';
import EmptyState from '@/shared/components/feedback/EmptyState.vue';
import PageSkeleton from '@/shared/components/feedback/PageSkeleton.vue';
import { Button, Card, Badge } from '@/shared/components/ui';
import { Webhook as WebhookIcon } from 'lucide-vue-next';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import WebhookFormModal, { type WebhookRow } from './WebhookFormModal.vue';
import WebhookDeliveriesPanel from './WebhookDeliveriesPanel.vue';
import { useConfirm } from '@/shared/composables/useConfirm';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const webhooks = ref<WebhookRow[]>([]);
const loading = ref(true);
const modalOpen = ref(false);
const editing = ref<WebhookRow | null>(null);

async function fetchWebhooks() {
  loading.value = true;
  try {
    const response = await api.get('/manage/infra/webhooks');
    const data = response.data;
    webhooks.value = Array.isArray(data) ? data : [];
  } catch (e) {
    toast.error.load(e);
  } finally {
    loading.value = false;
  }
}

function openCreate() {
  editing.value = null;
  modalOpen.value = true;
}

function openEdit(hook: WebhookRow) {
  editing.value = hook;
  modalOpen.value = true;
}

function onSaved() {
  toast.success.create('webhook');
  fetchWebhooks();
}

async function triggerWebhook(id: string) {
  try {
    await api.post(`/manage/infra/webhooks/${id}/trigger`, { payload: { message: 'Ping from Jejakawan Control Plane' } });
    toast.success.default(t('system.webhooks.messages.testDispatched'));
  } catch (e) {
    toast.error.default(t('system.webhooks.messages.testFailed'));
  }
}

async function removeWebhook(hook: WebhookRow) {
  const ok = await confirm({
    title: t('system.webhooks.actions.delete'),
    message: t('common.messages.confirm.delete', { item: hook.name }),
    confirmText: t('common.actions.delete', 'Delete'),
    variant: 'destructive',
  });
  if (!ok) return;
  try {
    await api.delete(`/manage/infra/webhooks/${hook.id}`);
    toast.success.delete('webhook');
    await fetchWebhooks();
  } catch (e) {
    toast.error.delete(e, 'webhook');
  }
}

onMounted(fetchWebhooks);
</script>
