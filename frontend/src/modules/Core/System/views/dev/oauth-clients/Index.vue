<template>
  <div class="space-y-6">
    <PageHeader
      :title="t('system.oauth.title')"
      :description="t('system.oauth.description')"
    >
      <template #actions>
        <Button @click="openCreate">
          {{ t('system.oauth.actions.create') }}
        </Button>
      </template>
    </PageHeader>

    <PageSkeleton v-if="loading" />

    <EmptyState
      v-else-if="clients.length === 0"
      :title="t('system.oauth.empty.title')"
      :description="t('system.oauth.empty.description')"
      :icon="Shield"
    />

    <ConsoleListCard v-else>
      <div class="overflow-x-auto min-w-0">
<table class="min-w-full divide-y divide-border">
        <thead class="bg-muted">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold text-foreground/80">{{ t('system.oauth.table.name') }}</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-foreground/80">{{ t('system.oauth.table.redirect') }}</th>
            <th class="px-6 py-3 text-right text-xs font-semibold text-foreground/80">{{ t('system.oauth.table.actions') }}</th>
          </tr>
        </thead>
        <tbody class="bg-card divide-y divide-border">
          <tr v-for="client in clients" :key="client.id" class="hover:bg-muted/40">
            <td class="px-6 py-4 text-sm font-medium">{{ client.name }}</td>
            <td class="px-6 py-4 text-sm text-muted-foreground font-mono text-xs">{{ client.redirect }}</td>
            <td class="px-6 py-4 text-right space-x-2">
              <Button variant="ghost" size="sm" @click="openEdit(client)">{{ t('common.actions.edit') }}</Button>
              <Button variant="ghost" size="sm" class="text-destructive" @click="removeClient(client)">{{ t('common.actions.delete') }}</Button>
            </td>
          </tr>
        </tbody>
      </table>
</div>
    </ConsoleListCard>

    <OAuthClientFormModal v-model:open="modalOpen" :client="editing" @saved="onSaved" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import PageHeader from '@/shared/components/shell/PageHeader.vue';
import EmptyState from '@/shared/components/feedback/EmptyState.vue';
import PageSkeleton from '@/shared/components/feedback/PageSkeleton.vue';
import ConsoleListCard from '@/shared/components/shell/ConsoleListCard.vue';
import { Button } from '@/shared/components/ui';
import { Shield } from 'lucide-vue-next';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import OAuthClientFormModal, { type OAuthClient } from './OAuthClientFormModal.vue';

const { t } = useI18n();
const toast = useToast();
const clients = ref<OAuthClient[]>([]);
const loading = ref(true);
const modalOpen = ref(false);
const editing = ref<OAuthClient | null>(null);

async function fetchClients() {
  loading.value = true;
  try {
    const response = await api.get('/manage/system/oauth-clients');
    const data = response.data;
    clients.value = Array.isArray(data) ? data : [];
  } catch {
    toast.error.load(new Error('oauth-clients'));
  } finally {
    loading.value = false;
  }
}

function openCreate() {
  editing.value = null;
  modalOpen.value = true;
}

function openEdit(client: OAuthClient) {
  editing.value = client;
  modalOpen.value = true;
}

async function removeClient(client: OAuthClient) {
  if (!window.confirm(t('system.oauth.confirmDelete', { name: client.name }))) return;
  try {
    await api.delete(`/manage/system/oauth-clients/${client.id}`);
    toast.success.delete('OAuth client');
    await fetchClients();
  } catch {
    toast.error.delete(new Error('oauth-clients'));
  }
}

function onSaved() {
  fetchClients();
}

onMounted(fetchClients);
</script>
