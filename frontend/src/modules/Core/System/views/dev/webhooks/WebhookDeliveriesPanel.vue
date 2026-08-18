<template>
  <ConsoleListCard>
    <div class="p-6 space-y-4">
      <div class="flex items-center justify-between gap-4">
        <div>
          <h3 class="text-lg font-medium">{{ t('system.webhooks.deliveries.title') }}</h3>
          <p class="text-sm text-muted-foreground">{{ t('system.webhooks.deliveries.subtitle') }}</p>
        </div>
        <Button variant="outline" size="sm" :disabled="loading" @click="fetchDeliveries">
          <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
          {{ t('common.actions.refresh') }}
        </Button>
      </div>

      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>{{ t('system.webhooks.deliveries.colWebhook') }}</TableHead>
            <TableHead>{{ t('system.webhooks.deliveries.colEvent') }}</TableHead>
            <TableHead>{{ t('system.webhooks.deliveries.colStatus') }}</TableHead>
            <TableHead>{{ t('system.webhooks.deliveries.colCode') }}</TableHead>
            <TableHead>{{ t('system.webhooks.deliveries.colWhen') }}</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="loading">
            <TableCell colspan="5" class="text-center py-8">
              <Loader2 class="w-6 h-6 animate-spin mx-auto text-muted-foreground" />
            </TableCell>
          </TableRow>
          <TableRow v-else-if="deliveries.length === 0">
            <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
              {{ t('system.webhooks.deliveries.empty') }}
            </TableCell>
          </TableRow>
          <TableRow v-else v-for="row in deliveries" :key="row.id">
            <TableCell class="font-medium">{{ row.webhook?.name ?? '—' }}</TableCell>
            <TableCell><Badge variant="outline">{{ row.event }}</Badge></TableCell>
            <TableCell>
              <Badge :variant="row.status === 'success' ? 'default' : 'destructive'">{{ row.status }}</Badge>
            </TableCell>
            <TableCell>{{ row.status_code ?? '—' }}</TableCell>
            <TableCell class="text-sm text-muted-foreground">{{ formatWhen(row.created_at) }}</TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </ConsoleListCard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { Loader2 } from 'lucide-vue-next';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { ConsoleListCard } from '@/shared/components/shell';
import {
  Button, Badge, Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/shared/components/ui';

const { t } = useI18n();
const toast = useToast();

interface DeliveryRow {
  id: string;
  event: string;
  status: string;
  status_code: number | null;
  created_at: string;
  webhook?: { id: string; name: string };
}

const deliveries = ref<DeliveryRow[]>([]);
const loading = ref(false);

const formatWhen = (iso: string) => new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(iso));

const fetchDeliveries = async () => {
  loading.value = true;
  try {
    const res = await api.get('/manage/infra/webhooks/deliveries/recent', { params: { limit: 50 } });
    deliveries.value = Array.isArray(res.data) ? res.data : [];
  } catch (e) {
    toast.error.load(e);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchDeliveries);
</script>
