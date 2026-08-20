<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="t('system.system.info.title')"
      :subtitle="t('system.system.info.subtitle')"
    >
      <template #actions>
        <div class="flex items-center gap-2">
          <!-- Active Tab Navigation Pills -->
          <div class="bg-muted p-1 rounded-lg flex items-center gap-1 border border-border">
            <button
              type="button"
              @click="activeTab = 'overview'"
              :class="activeTab === 'overview' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
              class="px-3.5 py-1.5 rounded-md text-xs font-semibold transition flex items-center gap-2"
            >
              <Activity class="h-3.5 w-3.5" />
              <span>{{ t('system.system.info.tabs.overview') }}</span>
            </button>
            <button
              type="button"
              @click="activeTab = 'requirements'"
              :class="activeTab === 'requirements' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
              class="px-3.5 py-1.5 rounded-md text-xs font-semibold transition flex items-center gap-2"
            >
              <ShieldCheck class="h-3.5 w-3.5 text-primary" />
              <span>{{ t('system.system.info.tabs.requirements') }}</span>
              <span
                v-if="requirementsData?.overview"
                :class="requirementsData.overview.is_ready ? 'bg-green-500/15 text-green-600 dark:text-green-400 border-green-500/30' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/30'"
                class="px-1.5 py-0.5 text-[10px] font-bold rounded border leading-none"
              >
                {{ requirementsData.overview.score_percent }}%
              </span>
            </button>
          </div>
        </div>
      </template>
    </PageHeader>

    <ConsoleListCard>
      <div class="p-6 space-y-6">
        <!-- Loading Skeleton -->
        <div v-if="loading" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-card border border-border rounded-lg p-6 h-24 animate-pulse" />
            <div class="bg-card border border-border rounded-lg p-6 h-24 animate-pulse" />
            <div class="bg-card border border-border rounded-lg p-6 h-24 animate-pulse" />
          </div>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-card border border-border rounded-lg p-6 h-64 animate-pulse" />
            <div class="bg-card border border-border rounded-lg p-6 h-64 animate-pulse" />
          </div>
        </div>

        <!-- Main Tab Content -->
        <template v-else>
          <!-- Tab 1: Overview & Maintenance -->
          <SystemOverviewTab
            v-if="activeTab === 'overview'"
            :system-info="systemInfo"
            :system-health="systemHealth"
            :cache-status-label="cacheStatusLabel"
            :display-memory="displayMemory"
            :display-disk="displayDisk"
          />

          <!-- Tab 2: System Requirements Matrix -->
          <SystemRequirementsTab
            v-else-if="activeTab === 'requirements'"
            :requirements-data="requirementsData"
            :req-loading="reqLoading"
            @refresh="fetchRequirements"
          />
        </template>
      </div>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';
import { logger } from '@/shared/utils/logger';
import api from '@/engine/api/client';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import type { SystemInfo, CacheData, RequirementsData } from '@/modules/Core/System/components/system-info/types';
import SystemOverviewTab from '@/modules/Core/System/components/system-info/SystemOverviewTab.vue';
import SystemRequirementsTab from '@/modules/Core/System/components/system-info/SystemRequirementsTab.vue';
import { Activity, ShieldCheck } from 'lucide-vue-next';

const { t } = useI18n();

const activeTab = ref<'overview' | 'requirements'>('overview');
const loading = ref(true);
const reqLoading = ref(false);

const systemInfo = ref<Partial<SystemInfo>>({});
const requirementsData = ref<RequirementsData | null>(null);
const CACHE_STATUS_ACTIVE = 'active';
const cacheStatus = ref(CACHE_STATUS_ACTIVE);

const cacheStatusLabel = computed(() => {
    const s = (cacheStatus.value || '').toLowerCase();
    if (s === 'active' || s === 'aktif') return t('system.system.info.cache.active');
    return t('system.system.info.cache.inactive');
});

const systemHealth = computed(() => {
    if (requirementsData.value?.overview) {
        if (requirementsData.value.overview.errors > 0) return 'critical';
        if (requirementsData.value.overview.warnings > 0) return 'warning';
    }
    
    const memoryUsage = systemInfo.value.memory_usage_percent || 0;
    const diskUsage = systemInfo.value.disk_usage_percent || 0;
    
    if (memoryUsage > 90 || diskUsage > 90) return 'critical';
    if (memoryUsage > 75 || diskUsage > 75) return 'warning';
    return 'healthy';
});

const displayMemory = computed(() => {
    if (!systemInfo.value.memory_usage) return '-';
    if (typeof systemInfo.value.memory_usage === 'string') {
        return systemInfo.value.memory_usage;
    }
    return formatBytes(systemInfo.value.memory_usage as number);
});

const displayDisk = computed(() => {
    const usage = systemInfo.value.disk_usage;
    if (!usage) return '-';
    if (typeof usage === 'object') {
        return `${usage.used} / ${usage.total} (${usage.percent || 0}%)`;
    }
    return usage;
});

const formatBytes = (bytes: number): string => {
    if (!bytes || typeof bytes !== 'number') return '-';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const fetchSystemInfo = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get('/manage/system/info');
        systemInfo.value = parseSingleResponse<SystemInfo>(response) || {};

        try {
            const cacheResponse = await api.get('/manage/system/cache-status');
            cacheStatus.value = parseSingleResponse<CacheData>(cacheResponse)?.status || CACHE_STATUS_ACTIVE;
        } catch (error: unknown) {
            logger.warning('Failed to fetch cache status:', error);
            cacheStatus.value = CACHE_STATUS_ACTIVE;
        }

        // Fetch requirements in background
        await fetchRequirements();
    } catch (error: unknown) {
        logger.error('Failed to fetch system info:', error);
    } finally {
        loading.value = false;
    }
};

const fetchRequirements = async (): Promise<void> => {
    reqLoading.value = true;
    try {
        const res = await api.get('/manage/system/requirements');
        requirementsData.value = parseSingleResponse<RequirementsData>(res) || null;
    } catch (error: unknown) {
        logger.error('Failed to fetch system requirements:', error);
    } finally {
        reqLoading.value = false;
    }
};

onMounted(() => {
    fetchSystemInfo();
});
</script>
