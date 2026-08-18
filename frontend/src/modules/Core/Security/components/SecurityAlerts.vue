<template>
  <div
    v-if="alerts.length > 0"
    class="bg-card border border-border rounded-lg mb-6"
  >
    <div class="px-6 py-4 border-b border-border flex items-center justify-between">
      <div class="flex items-center gap-2">
        <AlertTriangle class="w-5 h-5 text-destructive" />
        <h3 class="font-semibold text-foreground">
          {{ t('system.security.alerts.title') }}
        </h3>
        <span class="bg-destructive/20 text-destructive text-xs px-2 py-0.5 rounded-full">
          {{ alerts.length }}
        </span>
      </div>
      <button 
        class="text-sm text-primary hover:underline" 
        @click="toggleExpanded"
      >
        {{ expanded ? t('system.security.alerts.hide') : t('system.security.alerts.show') }}
      </button>
    </div>
        
    <div
      v-if="expanded"
      class="divide-y divide-border max-h-80 overflow-y-auto"
    >
      <div
        v-for="alert in alerts"
        :key="alert.id"
        class="px-6 py-3 flex items-center justify-between hover:bg-muted/50"
      >
        <div class="flex items-center gap-3">
          <span
            :class="[ 'w-2 h-2 rounded-full flex-shrink-0', alert.severity === 'critical' ? 'bg-destructive' : alert.severity === 'warning' ? 'bg-warning' : 'bg-info' ]"
          />
          <div>
            <p class="text-sm font-medium text-foreground">
              {{ alert.title }}
            </p>
            <p class="text-xs text-muted-foreground">
              {{ alert.message }}
            </p>
          </div>
        </div>
        <div class="text-right flex-shrink-0">
          <span
            :class="[ 'text-xs px-2 py-1 rounded-full', alert.severity === 'critical' ? 'bg-destructive/20 text-destructive' : alert.severity === 'warning' ? 'bg-warning/20 text-warning' : 'bg-info/20 text-info' ]"
          >
            {{ getSeverityLabel(alert.severity) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { getResponseObject } from '@/shared/utils/responseParser';
import {
  AlertTriangle,
} from 'lucide-vue-next';

interface Alert {
    id: string;
    title: string;
    message: string;
    severity: 'critical' | 'warning' | 'info';
    [key: string]: unknown;
}

const { t } = useI18n();

const alerts = ref<Alert[]>([]);
const expanded = ref(true);
const pollInterval = ref<ReturnType<typeof setInterval> | null>(null);

const fetchAlerts = async () => {
    try {
        const response = await api.get('/manage/security/alerts');
        const payload = getResponseObject<{ alerts?: Alert[] }>(response.data);
        const alertItems = payload?.alerts;
        alerts.value = Array.isArray(alertItems) ? alertItems : [];
    } catch (error: unknown) {
        console.warn('Failed to fetch security alerts:', error);
    }
};

const toggleExpanded = () => {
    expanded.value = !expanded.value;
};

const getSeverityLabel = (severity: string) => {
    const labels: Record<string, string> = {
        critical: t('common.labels.severity.critical'),
        warning: t('common.labels.severity.warning'),
        info: t('common.labels.severity.info'),
    };
    return labels[severity] || severity;
};

onMounted(() => {
    fetchAlerts();
    pollInterval.value = setInterval(fetchAlerts, 60000);
});

onUnmounted(() => {
    if (pollInterval.value) {
        clearInterval(pollInterval.value);
    }
});
</script>
