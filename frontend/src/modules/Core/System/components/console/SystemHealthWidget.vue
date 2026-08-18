<template>
  <Card class="system-health-widget h-full flex flex-col overflow-hidden border-border/40">
    <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
      <CardTitle class="text-xl font-bold flex items-center gap-2">
        <Activity
          class="w-5 h-5"
          :class="overallStatusClass"
        />
        {{ $t('system.dashboard.widgets.systemHealth.title') }}
      </CardTitle>
      <Button
        variant="ghost"
        size="icon"
        :disabled="loading"
        class="h-8 w-8 text-muted-foreground hover:text-foreground"
        title="Refresh"
        :aria-label="$t('common.actions.refresh')"
        @click="refresh"
      >
        <RefreshCw
          class="w-4 h-4"
          :class="{ '': loading }"
        />
      </Button>
    </CardHeader>

    <CardContent class="flex-1 space-y-6 pt-2">
      <!-- Overall Status -->
      <div class="flex items-center justify-between p-3 rounded-xl bg-muted/20 border border-border/40">
        <span class="text-sm font-semibold text-foreground">{{ $t('system.dashboard.widgets.systemHealth.overallStatus') }}</span>
        <Badge
          :class="overallStatusBadgeClass"
          variant="outline"
          class="border-none font-bold text-[10px]"
        >
          {{ overallStatusText }}
        </Badge>
      </div>

      <!-- Health Metrics -->
      <div class="space-y-4">
        <!-- CPU -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-muted-foreground flex items-center gap-1.5">
              <Cpu class="w-3.5 h-3.5" />
              {{ $t('system.dashboard.widgets.systemHealth.cpu') }}
            </span>
            <span
              :class="getStatusClass(health.cpu?.status)"
              class="text-xs font-bold tabular-nums"
            >
              {{ health.cpu?.percent || 0 }}%
            </span>
          </div>
          <div class="h-1.5 w-full bg-muted rounded-full overflow-hidden">
            <div
              :class="getProgressBarClass(health.cpu?.status)"
              :style="{ width: `${health.cpu?.percent || 0}%` }"
              class="h-full rounded-full shadow-[0_0_10px_rgba(0,0,0,0.1)]"
            />
          </div>
          <p
            v-if="health.cpu?.load"
            class="text-[10px] text-muted-foreground line-clamp-1 italic"
          >
            Load: {{ health.cpu.load }}
          </p>
        </div>

        <!-- Memory -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-muted-foreground flex items-center gap-1.5">
              <Layers class="w-3.5 h-3.5" />
              {{ $t('system.dashboard.widgets.systemHealth.memory') }}
            </span>
            <span
              :class="getStatusClass(health.memory?.status)"
              class="text-xs font-bold tabular-nums"
            >
              {{ health.memory?.percent || 0 }}%
            </span>
          </div>
          <div class="h-1.5 w-full bg-muted rounded-full overflow-hidden">
            <div
              :class="getProgressBarClass(health.memory?.status)"
              :style="{ width: `${health.memory?.percent || 0}%` }"
              class="h-full rounded-full shadow-[0_0_10px_rgba(0,0,0,0.1)]"
            />
          </div>
          <p class="text-[10px] text-muted-foreground line-clamp-1 italic">
            {{ health.memory?.used || '0 B' }} / {{ health.memory?.total || '0 B' }}
          </p>
        </div>

        <!-- System Components Row -->
        <div class="grid grid-cols-2 gap-3 pt-2">
          <!-- Disk -->
          <div class="p-3 rounded-xl bg-muted/20 border border-border/40 space-y-2">
            <div class="flex items-center justify-between">
              <HardDrive class="w-4 h-4 text-muted-foreground" />
              <span
                :class="getStatusClass(health.disk?.status)"
                class="text-[10px] font-bold"
              >
                {{ health.disk?.percent || 0 }}%
              </span>
            </div>
            <p class="text-[10px] font-bold text-foreground truncate opacity-60">
              {{ $t('system.dashboard.widgets.systemHealth.disk') }}
            </p>
            <div class="h-1 w-full bg-muted rounded-full overflow-hidden">
              <div
                :class="getProgressBarClass(health.disk?.status)"
                :style="{ width: `${health.disk?.percent || 0}%` }"
                class="h-full"
              />
            </div>
          </div>

          <!-- Database -->
          <div class="p-3 rounded-xl bg-muted/20 border border-border/40 space-y-2">
            <div class="flex items-center justify-between">
              <Database class="w-4 h-4 text-muted-foreground" />
              <Badge
                variant="outline"
                class="border-none p-0 h-auto"
                :class="getStatusClass(health.database?.status)"
              >
                <CheckCircle2
                  v-if="health.database?.status === 'ok'"
                  class="w-3.5 h-3.5"
                />
                <AlertCircle
                  v-else
                  class="w-3.5 h-3.5"
                />
              </Badge>
            </div>
            <p class="text-[10px] font-bold text-foreground truncate opacity-60">
              {{ $t('system.dashboard.widgets.systemHealth.database') }}
            </p>
            <p class="text-[10px] text-muted-foreground truncate italic">
              {{ health.database?.status === 'ok' ? $t('system.dashboard.widgets.systemHealth.status.ok') : $t('system.dashboard.widgets.systemHealth.status.error') }}
            </p>
          </div>
        </div>

        <!-- Redis -->
        <div class="p-3 rounded-xl bg-muted/20 border border-border/40 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-warning/10 text-warning">
              <Zap class="w-4 h-4" />
            </div>
            <div>
              <p class="text-[10px] font-bold text-foreground opacity-60">
                {{ $t('system.dashboard.widgets.systemHealth.redis') }}
              </p>
              <p class="text-[10px] text-muted-foreground truncate italic max-w-[120px]">
                {{ health.redis?.message || $t('system.dashboard.widgets.systemHealth.status.unknown') }}
              </p>
            </div>
          </div>
          <Badge
            :class="getStatusClass(health.redis?.status)"
            variant="outline"
            class="border-none font-bold text-[10px]"
          >
            {{ health.redis?.status === 'ok' ? $t('system.dashboard.widgets.systemHealth.status.ok') : health.redis?.status === 'disabled' ? $t('system.dashboard.widgets.systemHealth.status.disabled') : $t('system.dashboard.widgets.systemHealth.status.error') }}
          </Badge>
        </div>
      </div>
    </CardContent>

    <CardFooter
      v-if="lastUpdated"
      class="pb-4 pt-0 justify-center"
    >
      <div class="flex items-center gap-1.5 text-[10px] text-muted-foreground font-medium opacity-60">
        <Clock class="w-3 h-3" />
        {{ $t('system.dashboard.widgets.systemHealth.lastUpdated', { time: formatTime(lastUpdated) }) }}
      </div>
    </CardFooter>
  </Card>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import { 
    Card, 
    CardHeader, 
    CardTitle, 
    CardContent, 
    CardFooter, 
    Button, 
    Badge 
} from '@/shared/components/ui';
import {
  Activity,
  AlertCircle,
  CheckCircle2,
  Clock,
  Cpu,
  Database,
  HardDrive,
  Layers,
  RefreshCw,
  Zap,
} from 'lucide-vue-next';

interface HealthMetric {
    percent?: number;
    used?: string;
    total?: string;
    status: string;
    load?: string;
    message?: string;
}

interface SystemHealth {
    cpu: HealthMetric;
    memory: HealthMetric;
    disk: HealthMetric;
    database: HealthMetric;
    redis: HealthMetric;
    overall: string;
}

const { t } = useI18n();
const authStore = useAuthStore();

const health = ref<SystemHealth>({
  cpu: { percent: 0, status: 'unknown' },
  memory: { percent: 0, used: '0 B', total: '0 B', status: 'unknown' },
  disk: { percent: 0, used: '0 B', total: '0 B', status: 'unknown' },
  database: { status: 'unknown', message: 'Unknown' },
  redis: { status: 'unknown', message: 'Unknown' },
  overall: 'unknown',
});

const loading = ref(false);
const lastUpdated = ref<Date | null>(null);
const refreshInterval = ref<ReturnType<typeof setInterval> | null>(null);

const stopRefresh = () => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
        refreshInterval.value = null;
    }
};

const overallStatus = computed(() => health.value.overall || 'unknown');

const overallStatusText = computed(() => {
  const status = overallStatus.value;
  return t(`system.dashboard.widgets.systemHealth.status.${status}`);
});

const overallStatusClass = computed(() => {
  const status = overallStatus.value;
  if (status === 'healthy') return 'text-success';
  if (status === 'warning') return 'text-warning';
  if (status === 'critical') return 'text-destructive';
  return 'text-muted-foreground';
});

const overallStatusBadgeClass = computed(() => {
  const status = overallStatus.value;
  if (status === 'healthy') return 'bg-success/10 text-foreground';
  if (status === 'warning') return 'bg-warning/10 text-foreground';
  if (status === 'critical') return 'bg-destructive/10 text-foreground';
  return 'bg-muted text-muted-foreground';
});

const getStatusClass = (status?: string) => {
  if (status === 'ok') return 'text-foreground';
  if (status === 'warning') return 'text-foreground';
  if (status === 'critical' || status === 'error') return 'text-foreground';
  return 'text-muted-foreground';
};

const getProgressBarClass = (status?: string) => {
  if (status === 'ok') return 'bg-success';
  if (status === 'warning') return 'bg-warning';
  if (status === 'critical' || status === 'error') return 'bg-destructive';
  return 'bg-muted-foreground/30';
};

const fetchHealth = async () => {
    if ((window as unknown as { __isSessionTerminated?: boolean }).__isSessionTerminated) {
        stopRefresh();
        return;
    }

    if (!authStore.hasPermission('manage system')) return;

    loading.value = true;
    try {
        const response = await api.get('/manage/system/health/detailed');
        const data = parseSingleResponse(response);
        if (data) {
            health.value = data as SystemHealth;
            lastUpdated.value = new Date();
        }
    } catch (error: unknown) {
        if (error && typeof error === 'object' && 'code' in error && 'response' in error) {
            const err = error as { code: string; response?: { status: number } };
            if (err.response?.status === 401 || err.response?.status === 403) {
                stopRefresh();
                return;
            }
            if (err.code !== 'ERR_CANCELED') {
                logger.error('Failed to fetch system health:', error);
            }
        }
    } finally {
        loading.value = false;
    }
};

const refresh = () => {
  fetchHealth();
};

const formatTime = (date: Date) => {
  if (!date) return '';
  return date.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
};

onMounted(() => {
    fetchHealth();
    // Refresh every 3 minutes, but only if the tab is visible
    refreshInterval.value = setInterval(() => {
        if (document.visibilityState === 'visible') {
            fetchHealth();
        }
    }, 180000);
});

onUnmounted(() => {
    stopRefresh();
});
</script>

<style scoped>
/* Scoped styles removed as we are using Tailwind utilities and Shadcn-aligned structure */
</style>

