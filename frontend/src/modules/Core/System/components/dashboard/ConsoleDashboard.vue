<template>
  <div class="space-y-6">
    <PageHeader
      :title="$t('system.dashboard.title')"
      :subtitle="$t('system.dashboard.welcome', { name: authStore.user?.name })"
      display-size
      borderless
    >
      <template #actions>
        <Button
          variant="ghost"
          size="sm"
          :disabled="loadingDashboard"
          class="bg-muted/40 border border-border/40 hover:bg-muted/60"
          :aria-label="$t('common.actions.refresh')"
          @click="refreshDashboard"
        >
          <RefreshCw
            class="w-4 h-4"
            data-icon="inline-start"
            :class="{ 'animate-spin': loadingDashboard }"
          />
          {{ $t('common.actions.refresh') }}
        </Button>
      </template>
    </PageHeader>

    <PostResetWizard />
    <HubOnboardingWizard />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <ConsoleStatCard
        :label="$t('system.dashboard.stats.totalContents')"
        :value="String(stats.contents?.total || 0)"
        :hint="`${stats.contents?.published || 0} ${$t('system.dashboard.stats.published')}`"
        :hint-icon="FileText"
        :icon="Library"
        tone="primary"
      />
      <ConsoleStatCard
        :label="$t('system.dashboard.stats.mediaFiles')"
        :value="String(stats.media?.total || 0)"
        :hint="$t('common.status.online')"
        :hint-icon="Image"
        :icon="FolderOpen"
        tone="success"
      />
      <ConsoleStatCard
        v-if="authStore.hasPermission('manage users')"
        :label="$t('system.dashboard.stats.totalUsers')"
        :value="String(stats.users?.total || 0)"
        :hint="$t('system.dashboard.stats.activeUsers')"
        :hint-icon="Users"
        :icon="UserCheck"
        tone="primary"
      />
      <ConsoleStatCard
        v-if="authStore.hasPermission('approve content')"
        :label="$t('system.dashboard.stats.pendingContent')"
        :value="String(stats.contents?.pending || 0)"
        :hint="$t('system.dashboard.stats.requiresReview')"
        :hint-icon="AlertCircle"
        :icon="Clock3"
        tone="warning"
      />
    </div>

    <div
      v-if="authStore.hasPermission('view analytics')"
      class="w-full"
    >
      <Card class="border-border/40 bg-card shadow-none rounded-xl">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <div class="space-y-1">
            <h2 class="text-lg font-semibold flex items-center gap-2">
              <BarChart3 class="w-5 h-5 text-primary" />
              {{ $t('system.dashboard.traffic.title') }}
            </h2>
            <CardDescription>{{ $t('system.dashboard.traffic.overview') }}</CardDescription>
          </div>
          <div class="w-[180px]">
            <Select v-model="timeRange">
              <SelectTrigger
                class="w-full"
                :aria-label="$t('system.dashboard.traffic.filters.last7Days')"
              >
                <SelectValue :placeholder="$t('system.dashboard.traffic.filters.last7Days')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="7">
                  {{ $t('system.dashboard.traffic.filters.last7Days') }}
                </SelectItem>
                <SelectItem value="30">
                  {{ $t('system.dashboard.traffic.filters.last30Days') }}
                </SelectItem>
                <SelectItem value="90">
                  {{ $t('system.dashboard.traffic.filters.last90Days') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </CardHeader>
        <CardContent>
          <div class="h-[250px] mt-4 relative">
            <div
              v-if="loadingVisits"
              class="absolute inset-0 flex items-center justify-center bg-card/50 z-20 backdrop-blur-[1px]"
            >
              <Loader2 class="h-8 w-8 text-primary animate-spin" />
            </div>

            <AsyncLineChart
              v-if="visitsDesktop.length > 0"
              :data="visitsDesktop"
              :label="$t('system.dashboard.traffic.visits')"
            />

            <EmptyState
              v-if="!loadingVisits && visitsDesktop.length === 0"
              :title="$t('system.dashboard.traffic.noData')"
              :icon="AreaChart"
              compact
            />
            <ChartAccessibleTable
              v-if="visitsDesktop.length > 0"
              :rows="trafficTableRows"
              :summary="$t('system.dashboard.traffic.dataTableSummary')"
              :label-header="$t('system.dashboard.traffic.dataTablePeriod')"
              :value-header="$t('system.dashboard.traffic.dataTableValue')"
            />
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
      <div
        v-if="authStore.hasPermission('view users')"
        class="xl:col-span-6"
      >
        <AsyncRecentActivityWidget ref="recentActivityWidget" />
      </div>

      <div
        v-if="authStore.hasPermission('manage settings')"
        class="xl:col-span-3"
      >
        <AsyncEmailStatusWidget />
      </div>

      <div
        v-if="authStore.hasPermission('manage system')"
        class="xl:col-span-3"
      >
        <AsyncSystemHealthWidget class="h-full" />
      </div>

      <div class="md:col-span-6 xl:col-span-12">
        <AsyncQuickActions :show-recent="false" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { defineAsyncComponent, computed, onMounted, ref, watch } from 'vue';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import api from '@/engine/api/client';
import { parseSingleResponse, ensureArray } from '@/shared/utils/responseParser';
import type { SystemStats, TrafficItem, TrafficDataPoint, DashboardData } from '@/engine/types/dashboard';
import { PageHeader } from '@/shared/components/shell';
import HubOnboardingWizard from '@/modules/Core/System/components/onboarding/HubOnboardingWizard.vue';
import PostResetWizard from '@/modules/Core/System/components/onboarding/PostResetWizard.vue';
import { ConsoleStatCard } from '@/shared/components/shell';
import { ChartAccessibleTable, EmptyState } from '@/shared/components/feedback';
import {
    Card,
    CardHeader,
    CardDescription,
    CardContent,
    Button,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/shared/components/ui';
import {
  AlertCircle,
  AreaChart,
  BarChart3,
  Clock3,
  FileText,
  FolderOpen,
  Image,
  Library,
  Loader2,
  RefreshCw,
  UserCheck,
  Users,
} from 'lucide-vue-next';

const authStore = useAuthStore();
const AsyncQuickActions = defineAsyncComponent(() => import('@/modules/Core/System/components/console/QuickActions.vue'));
const AsyncSystemHealthWidget = defineAsyncComponent(() => import('@/modules/Core/System/components/console/SystemHealthWidget.vue'));
const AsyncRecentActivityWidget = defineAsyncComponent(() => import('@/modules/Core/System/components/console/RecentActivityWidget.vue'));
const AsyncEmailStatusWidget = defineAsyncComponent(() => import('@/modules/Core/System/components/console/EmailStatusWidget.vue'));
const AsyncLineChart = defineAsyncComponent(() => import('@/modules/Core/System/components/charts/LineChart.vue'));

const stats = ref<SystemStats>({
    contents: { total: 0, published: 0, pending: 0 },
    media: { total: 0 },
    users: { total: 0 },
});

const visitsDesktop = ref<TrafficDataPoint[]>([]);
const loadingVisits = ref(false);
const loadingStats = ref(false);
const timeRange = ref('7');
const recentActivityWidget = ref<{ fetchActivities?: () => Promise<void> } | null>(null);

const trafficTableRows = computed(() =>
    visitsDesktop.value.map((row) => ({
        label: row.period,
        value: row.visits,
    })),
);

const loadingDashboard = computed(() => loadingVisits.value || loadingStats.value);

const refreshDashboard = async () => {
    if (loadingDashboard.value) return;
    loadingVisits.value = true;
    loadingStats.value = true;
    try {
        await Promise.allSettled([
            fetchDashboardData(true),
            recentActivityWidget.value?.fetchActivities?.() ?? Promise.resolve(),
        ]);
    } catch (error: unknown) {
        if (typeof error === 'object' && error !== null && 'code' in error && 'response' in error) {
            const err = error as { code?: string; response?: { status?: number } };
            if (err.code !== 'ERR_CANCELED' && err.response?.status !== 401) {
                logger.error('Failed to refresh dashboard:', error);
            }
        } else {
            logger.error('Failed to refresh dashboard:', error);
        }
    } finally {
        loadingVisits.value = false;
        loadingStats.value = false;
    }
};

const fetchDashboardData = async (skipLoading = false) => {
    if (!skipLoading) {
        loadingVisits.value = true;
        loadingStats.value = true;
    }
    try {
        let endpoint = '/dashboard/viewer';
        if (authStore.hasPermission('manage users') || authStore.hasPermission('manage settings')) {
            endpoint = '/dashboard/admin';
        } else if (authStore.hasPermission('create content') || authStore.hasPermission('edit content')) {
            endpoint = '/dashboard/creator';
        }

        const response = await api.get(endpoint, {
            params: { days: timeRange.value },
        });
        const rawData = parseSingleResponse<Record<string, unknown>>(response);
        const data = (rawData?.data as DashboardData) || (rawData as DashboardData);

        if (data) {
            if (data.stats) {
                stats.value = {
                    contents: {
                        total: data.stats.contents?.total ?? data.stats.myContents?.total ?? 0,
                        published: data.stats.contents?.published ?? data.stats.myContents?.published ?? 0,
                        pending: data.stats.contents?.pending ?? data.stats.myContents?.pending ?? 0,
                    },
                    media: {
                        total: data.stats.media?.total ?? data.stats.myMedia?.total ?? 0,
                    },
                    users: {
                        total: data.stats.users?.total ?? 0,
                    },
                };
            }

            const chartTraffic = data.charts?.contentTraffic;
            const legacyUsers = data.charts?.userActivity;
            const trafficRaw =
                Array.isArray(chartTraffic) && chartTraffic.length > 0
                    ? chartTraffic
                    : Array.isArray(legacyUsers) && legacyUsers.length > 0
                        ? legacyUsers
                        : chartTraffic ?? legacyUsers;

            if (Array.isArray(trafficRaw) && trafficRaw.length > 0) {
                const traffic = ensureArray<TrafficItem | { date: string; count: number }>(trafficRaw);
                visitsDesktop.value = traffic
                    .map((item) => {
                        const period =
                            'period' in item && item.period != null && item.period !== ''
                                ? String(item.period)
                                : 'date' in item && item.date != null
                                    ? String(item.date)
                                    : '';
                        const rawVisits =
                            'visits' in item && item.visits != null ? item.visits : 'count' in item ? item.count : 0;
                        const visits = typeof rawVisits === 'number' ? rawVisits : Number(rawVisits) || 0;
                        return { period, visits };
                    })
                    .filter((v) => v.period !== '');
            } else {
                visitsDesktop.value = [];
            }
        }
    } catch (error: unknown) {
        if (typeof error === 'object' && error !== null && 'code' in error && 'response' in error) {
            const err = error as { code?: string; response?: { status?: number } };
            if (err.code !== 'ERR_CANCELED' && err.response?.status !== 401) {
                logger.error('Failed to fetch dashboard data:', error);
            }
        } else {
            logger.error('Failed to fetch dashboard data:', error);
        }
    } finally {
        if (!skipLoading) {
            loadingVisits.value = false;
            loadingStats.value = false;
        }
    }
};

watch(timeRange, () => {
    fetchDashboardData();
});

onMounted(() => {
    fetchDashboardData();
});
</script>
