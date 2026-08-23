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
          :disabled="loading"
          class="bg-muted/40 border border-border/40 hover:bg-muted/60"
          :aria-label="$t('common.actions.refresh')"
          @click="refreshDashboard"
        >
          <RefreshCw
            class="w-4 h-4 mr-2"
            :class="{ 'animate-spin': loading }"
          />
          {{ $t('common.actions.refresh') }}
        </Button>
      </template>
    </PageHeader>

    <HubOnboardingWizard />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <StatCard
        :label="$t('system.dashboard.stats.creator.myContent')"
        :value="String(stats.myContents?.total || 0)"
        :hint="`${stats.myContents?.published || 0} ${$t('system.dashboard.stats.creator.published')}`"
        :hint-icon="FileText"
        :icon="PenTool"
        tone="primary"
      />
      <StatCard
        :label="$t('system.dashboard.stats.creator.pendingReview')"
        :value="String(stats.myContents?.pending || 0)"
        :hint="$t('system.dashboard.stats.creator.awaitingApproval')"
        :hint-icon="Clock3"
        :icon="Clock3"
        tone="warning"
      />
      <StatCard
        :label="$t('system.dashboard.stats.creator.myMedia')"
        :value="String(stats.myMedia?.total || 0)"
        :hint="$t('system.dashboard.stats.creator.uploadedFiles')"
        :hint-icon="Image"
        :icon="FolderOpen"
        tone="success"
      />
      <StatCard
        :label="$t('system.dashboard.stats.creator.drafts')"
        :value="String(stats.myContents?.draft || 0)"
        :hint="$t('system.dashboard.stats.creator.workInProgress')"
        :hint-icon="FileText"
        :icon="Edit3"
        tone="default"
      />
    </div>

    <Card class="border-border/40 bg-card shadow-none rounded-xl">
      <CardHeader class="flex flex-row items-center justify-between pb-2">
        <div class="space-y-1">
          <h2 class="text-lg font-semibold flex items-center gap-2">
            <Activity class="w-5 h-5 text-primary" />
            {{ $t('system.dashboard.traffic.visits') }}
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
            v-if="loading"
            class="absolute inset-0 flex items-center justify-center bg-card/50 z-20 backdrop-blur-[1px]"
            role="status"
            :aria-label="$t('common.labels.loading')"
          >
            <Loader2 class="h-8 w-8 text-primary animate-spin" />
          </div>
          <LineChart
            v-if="activityData.length > 0"
            :data="activityData"
            :label="$t('system.dashboard.traffic.visits')"
          />
          <EmptyState
            v-if="!loading && activityData.length === 0"
            :title="$t('system.dashboard.traffic.noData')"
            :icon="Activity"
            compact
          />
          <ChartAccessibleTable
            v-if="activityData.length > 0"
            :rows="trafficTableRows"
            :summary="$t('system.dashboard.traffic.dataTableSummary')"
            :label-header="$t('system.dashboard.traffic.dataTablePeriod')"
            :value-header="$t('system.dashboard.traffic.dataTableValue')"
          />
        </div>
      </CardContent>
    </Card>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
      <Card class="xl:col-span-3 border-border/40 bg-card shadow-none rounded-xl">
        <CardHeader>
          <CardTitle>{{ $t('system.dashboard.stats.creator.contentStatus') }}</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="h-[250px] w-full flex items-center justify-center">
            <DoughnutChart
              v-if="statusData.length > 0"
              :data="statusData"
              label-key="label"
              value-key="count"
            />
            <EmptyState
              v-else
              :title="$t('system.dashboard.traffic.noData')"
              :icon="PieChart"
              compact
            />
            <ChartAccessibleTable
              v-if="statusData.length > 0"
              :rows="statusTableRows"
              :summary="$t('system.dashboard.traffic.dataTableSummary')"
              :label-header="$t('system.dashboard.stats.creator.contentStatus')"
              :value-header="$t('system.dashboard.traffic.dataTableValue')"
            />
          </div>
        </CardContent>
      </Card>

      <Card class="xl:col-span-6 border-border/40 bg-card shadow-none rounded-xl">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Trophy class="w-5 h-5 text-warning" />
            {{ $t('system.dashboard.stats.creator.topContent') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div
            v-if="topContent.length > 0"
            class="overflow-x-auto rounded-lg border border-border/60"
          >
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>{{ $t('system.dashboard.table.content') }}</TableHead>
                  <TableHead class="text-right">
                    {{ $t('system.dashboard.table.views') }}
                  </TableHead>
                  <TableHead class="text-right">
                    {{ $t('system.dashboard.table.status') }}
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow
                  v-for="content in topContent"
                  :key="content.id"
                >
                  <TableCell class="font-medium">
                    <div class="flex flex-col">
                      <span class="truncate max-w-[200px]">{{ content.title }}</span>
                      <span class="text-xs text-muted-foreground capitalize">{{ content.type }}</span>
                    </div>
                  </TableCell>
                  <TableCell class="text-right tabular-nums">
                    {{ content.views }}
                  </TableCell>
                  <TableCell class="text-right">
                    <StatusBadge
                      :label="mapStatusToLabel(content.status)"
                      :tone="statusTone(content.status)"
                    />
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
          <EmptyState
            v-else
            :title="$t('system.dashboard.traffic.noData')"
            :icon="FileText"
            compact
          />
        </CardContent>
      </Card>

      <div class="xl:col-span-3">
        <QuickActions :show-recent="false" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { computed, ref, onMounted, watch } from 'vue';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { parseSingleResponse, ensureArray } from '@/shared/utils/responseParser';
import { PageHeader } from '@/shared/components/shell';
import HubOnboardingWizard from '@/modules/Core/System/components/onboarding/HubOnboardingWizard.vue';
import { StatCard } from '@/shared/components/dashboard';
import { ChartAccessibleTable, EmptyState, StatusBadge } from '@/shared/components/feedback';
import QuickActions from '@/modules/Core/System/components/console/QuickActions.vue';
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    Button,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/shared/components/ui';
import DoughnutChart from '@/modules/Core/System/components/charts/DoughnutChart.vue';
import LineChart from '@/modules/Core/System/components/charts/LineChart.vue';
import {
    Activity,
    Clock3,
    Edit3,
    FileText,
    FolderOpen,
    Image,
    Loader2,
    PenTool,
    PieChart,
    RefreshCw,
    Trophy,
} from 'lucide-vue-next';
import type { CreatorDashboardData, TrafficDataPoint, StatusDataPoint, TopContentItem } from '@/engine/types/dashboard';

const { t } = useI18n();
const authStore = useAuthStore();
const stats = ref<NonNullable<CreatorDashboardData['stats']>>({
    myContents: { total: 0, published: 0, pending: 0, draft: 0 },
    myMedia: { total: 0 },
});
const statusData = ref<StatusDataPoint[]>([]);
const activityData = ref<TrafficDataPoint[]>([]);
const topContent = ref<TopContentItem[]>([]);
const loading = ref(false);
const timeRange = ref('30');

const statusTableRows = computed(() =>
    statusData.value.map((row) => ({
        label: row.label,
        value: row.count,
    })),
);

const trafficTableRows = computed(() =>
    activityData.value.map((row) => ({
        label: row.period,
        value: row.visits,
    })),
);

const mapStatusToLabel = (status: string): string => {
    const map: Record<string, string> = {
        published: t('system.dashboard.stats.creator.published'),
        draft: t('system.dashboard.stats.creator.drafts'),
        pending: t('system.dashboard.stats.creator.pendingReview'),
    };
    return map[status] ?? status;
};

const statusTone = (status: string): 'default' | 'primary' | 'success' | 'warning' | 'destructive' => {
    if (status === 'published') return 'success';
    if (status === 'pending') return 'warning';
    if (status === 'draft') return 'primary';
    return 'default';
};

const fetchStats = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get('/dashboard/creator', {
            params: { days: timeRange.value },
        });
        const data = parseSingleResponse<CreatorDashboardData>(response);

        if (data?.stats) {
            stats.value = data.stats;
        }

        if (data?.charts?.myContentByStatus) {
            const rawStatus = ensureArray<{ status: string; count: number }>(data.charts.myContentByStatus);
            statusData.value = rawStatus.map((item) => ({
                label: mapStatusToLabel(item.status),
                count: item.count,
            }));
        } else {
            statusData.value = [];
        }

        const rawTraffic = data?.charts?.contentTraffic
            ? ensureArray<{ date: string; count: number }>(data.charts.contentTraffic)
            : data?.charts?.recentActivity
                ? ensureArray<{ date: string; count: number }>(data.charts.recentActivity)
                : [];

        activityData.value = rawTraffic.map((item) => ({
            period: item.date,
            visits: item.count,
        }));

        topContent.value = data?.topContent ? ensureArray<TopContentItem>(data.topContent) : [];
    } catch (error: unknown) {
        logger.error('Failed to fetch creator stats:', error);
    } finally {
        loading.value = false;
    }
};

const refreshDashboard = (): void => {
    void fetchStats();
};

watch(timeRange, () => {
    void fetchStats();
});

onMounted(() => {
    void fetchStats();
});
</script>
