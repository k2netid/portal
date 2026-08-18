<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-medium">{{ t('system.security.siem.title') }}</h3>
        <p class="text-sm text-muted-foreground">{{ t('system.security.siem.subtitle') }}</p>
      </div>
      <Button variant="outline" size="sm" :disabled="loading" @click="fetchEvents">
        <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
        {{ t('common.actions.refresh') }}
      </Button>
    </div>

    <Card>
      <div v-if="loading" class="p-8 flex justify-center">
        <Loader2 class="w-6 h-6 animate-spin text-muted-foreground" />
      </div>
      <div v-else-if="events.length === 0" class="p-8 text-center text-muted-foreground text-sm">
        {{ t('system.security.siem.empty') }}
      </div>
      <ul v-else class="divide-y max-h-[480px] overflow-y-auto">
        <li v-for="(ev, idx) in events" :key="idx" class="p-4 font-mono text-xs break-all">
          <span v-if="ev.recorded_at" class="text-muted-foreground block mb-1">{{ ev.recorded_at }}</span>
          {{ ev.raw }}
        </li>
      </ul>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { Loader2 } from 'lucide-vue-next';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { Button, Card } from '@/shared/components/ui';

const { t } = useI18n();
const toast = useToast();

interface SiemEvent { raw: string; recorded_at: string | null }

const events = ref<SiemEvent[]>([]);
const loading = ref(false);

const fetchEvents = async () => {
  loading.value = true;
  try {
    const res = await api.get('/manage/security/siem/exports', { params: { limit: 50 } });
    events.value = Array.isArray(res.data) ? res.data : [];
  } catch (e) {
    toast.error.load(e);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchEvents);
</script>
