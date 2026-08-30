<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="$t('system.analytics.title')"
      :subtitle="$t('system.analytics.subtitle')"
    >
      <template #actions>
        <div class="flex flex-wrap items-center gap-2">
          <div class="flex items-center gap-2 bg-card border border-border rounded-md px-1 py-0.5">
            <Input
              v-model="dateFrom"
              type="date"
              :aria-label="$t('system.analytics.dateFrom')"
              class="h-9 border-0 bg-transparent focus-visible:ring-0 w-[140px]"
            />
            <span class="text-muted-foreground text-xs uppercase font-medium">{{ $t('system.analytics.to') }}</span>
            <Input
              v-model="dateTo"
              type="date"
              :aria-label="$t('system.analytics.dateTo')"
              class="h-9 border-0 bg-transparent focus-visible:ring-0 w-[140px]"
            />
          </div>
          <Button size="sm" class="h-10 inline-flex items-center gap-2" @click="fetchAnalytics">
            {{ $t('system.analytics.apply') }}
          </Button>
          <div
            ref="exportDropdownRef"
            class="relative"
          >
            <Button
              size="sm"
              variant="outline"
              :disabled="exporting"
              @click="toggleExportMenu"
            >
              <Download
                v-if="!exporting"
                class="w-4 h-4 mr-2"
              />
              <Loader2
                v-else
                class="w-4 h-4 mr-2 animate-spin"
              />
              {{ exporting ? $t('system.analytics.export.exporting') : $t('system.analytics.export.button') }}
            </Button>
            <div
              v-if="showExportMenu"
              class="absolute right-0 mt-2 w-48 bg-background border border-border rounded-md shadow-lg z-50 py-1"
            >
              <button
                class="w-full px-4 py-2 text-left text-sm text-popover-foreground hover:bg-accent transition-colors"
                @click="exportData('visits')"
              >
                {{ $t('system.analytics.export.visits') }}
              </button>
              <button
                class="w-full px-4 py-2 text-left text-sm text-popover-foreground hover:bg-accent transition-colors"
                @click="exportData('events')"
              >
                {{ $t('system.analytics.export.events') }}
              </button>
              <button
                class="w-full px-4 py-2 text-left text-sm text-popover-foreground hover:bg-accent transition-colors"
                @click="exportData('sessions')"
              >
                {{ $t('system.analytics.export.sessions') }}
              </button>
            </div>
          </div>
        </div>
      </template>
    </PageHeader>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="text-center py-12"
    >
      <p class="text-muted-foreground">
        {{ $t('system.analytics.loading') }}
      </p>
    </div>

    <template v-else>
      <!-- Overview Stats - Compact Row -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center gap-4">
              <div class="p-2.5 bg-indigo-500/10 dark:bg-indigo-500/20 rounded-xl">
                <Eye class="h-5 w-5 text-indigo-500 dark:text-indigo-400" />
              </div>
              <div>
                <p class="text-sm font-medium text-muted-foreground">
                  {{ $t('system.analytics.overview.totalVisits') }}
                </p>
                <p class="text-2xl font-bold tracking-tight text-foreground">
                  {{ formatNumber(overview.total_visits || 0) }}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center gap-4">
              <div class="p-2.5 bg-emerald-500/10 dark:bg-emerald-500/20 rounded-xl">
                <Users class="h-5 w-5 text-emerald-500 dark:text-emerald-400" />
              </div>
              <div>
                <p class="text-sm font-medium text-muted-foreground">
                  {{ $t('system.analytics.overview.uniqueVisitors') }}
                </p>
                <p class="text-2xl font-bold tracking-tight text-foreground">
                  {{ formatNumber(overview.unique_visitors || 0) }}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center gap-4">
              <div class="p-2.5 bg-blue-500/10 dark:bg-blue-500/20 rounded-xl">
                <BarChart3 class="h-5 w-5 text-blue-500 dark:text-blue-400" />
              </div>
              <div>
                <p class="text-sm font-medium text-muted-foreground">
                  {{ $t('system.analytics.overview.totalSessions') }}
                </p>
                <p class="text-2xl font-bold tracking-tight text-foreground">
                  {{ formatNumber(overview.total_sessions || 0) }}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center gap-4">
              <div class="p-2.5 bg-purple-500/10 dark:bg-purple-500/20 rounded-xl">
                <TrendingUp class="h-5 w-5 text-purple-500 dark:text-purple-400" />
              </div>
              <div>
                <p class="text-sm font-medium text-muted-foreground">
                  {{ $t('system.analytics.overview.bounceRate') }}
                </p>
                <p class="text-2xl font-bold tracking-tight text-foreground">
                  {{ overview.bounce_rate || 0 }}%
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Real-time Activity - Compact -->
      <Card class="bg-gradient-to-r from-indigo-500/5 via-emerald-500/5 to-blue-500/5 border border-indigo-500/10 dark:border-indigo-500/20 shadow-none">
        <CardContent class="p-5">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
              <Badge
                variant="outline"
                class="bg-background/50 border-emerald-500/20 text-emerald-600 dark:text-emerald-400 gap-1.5 py-1"
              >
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse" />
                {{ $t('system.analytics.charts.realtime') }}
              </Badge>
            </div>
            <div class="flex items-center gap-8">
              <div class="text-center">
                <span class="text-xl font-bold text-indigo-500 dark:text-indigo-400">{{ realtime.active_sessions || 0 }}</span>
                <span class="text-xs font-medium text-muted-foreground ml-1.5 uppercase tracking-wider">{{ $t('system.analytics.realtime.activeSessions') }}</span>
              </div>
              <div class="text-center">
                <span class="text-xl font-bold text-emerald-500 dark:text-emerald-400">{{ formatNumber(realtime.visits_last_hour || 0) }}</span>
                <span class="text-xs font-medium text-muted-foreground ml-1.5 uppercase tracking-wider">{{ $t('system.analytics.realtime.visitsLastHour') }}</span>
              </div>
              <div class="text-center">
                <span class="text-xl font-bold text-blue-500 dark:text-blue-400">{{ realtime.top_pages_now?.length || 0 }}</span>
                <span class="text-xs font-medium text-muted-foreground ml-1.5 uppercase tracking-wider">{{ $t('system.analytics.realtime.activePages') }}</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Traffic Section -->
      <ConsoleListCard>
        <div class="p-4 border-b border-border/50">
          <h3 class="text-base font-semibold flex items-center gap-2 text-foreground">
            <TrendingUp class="w-4 h-4 text-primary" />
            {{ $t('system.analytics.sections.traffic') }}
          </h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Visits Chart -->
            <div class="lg:col-span-2">
              <h4 class="text-xs font-semibold text-muted-foreground capitalize tracking-wide mb-4">
                {{ $t('system.analytics.charts.visitsOverTime') }}
              </h4>
              <div class="h-[240px]">
                <LineChart
                  v-if="visits.length > 0"
                  :data="visits"
                  :label="$t('system.analytics.charts.visits')"
                  :accessibility-label="$t('system.analytics.charts.visitsOverTime')"
                />
                <div
                  v-else
                  class="h-full flex flex-col items-center justify-center text-muted-foreground bg-muted/30 rounded-lg"
                >
                  <BarChart3 class="w-8 h-8 mb-2 opacity-20" />
                  <p class="text-sm">
                    {{ $t('system.analytics.noData') }}
                  </p>
                </div>
              </div>
            </div>
            <!-- Top Pages -->
            <div>
              <h4 class="text-xs font-semibold text-muted-foreground capitalize tracking-wide mb-4">
                {{ $t('system.analytics.charts.topPages') }}
              </h4>
              <div
class="space-y-3 max-h-[240px] overflow-y-auto pr-2 custom-scrollbar focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                tabindex="0"
                role="region"
                :aria-label="$t('system.analytics.charts.topPages')"
>
                <div
                  v-for="(page, i) in filteredTopPages.slice(0, 10)"
                  :key="i"
                  class="flex items-center justify-between group"
                >
                  <span class="text-sm truncate flex-1 text-foreground/90 group-hover:text-foreground transition-colors">{{ formatUrl(page.url) }}</span>
                  <Badge
                    variant="secondary"
                    class="ml-2 tabular-nums"
                  >
                    {{ page.visits }}
                  </Badge>
                </div>
                <div
                  v-if="filteredTopPages.length === 0"
                  class="h-full flex flex-col items-center justify-center py-12 text-muted-foreground bg-muted/30 rounded-lg"
                >
                  <p class="text-xs italic">
                    {{ $t('system.analytics.noData') }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </ConsoleListCard>

      <!-- Technology & Geography Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Technology -->
        <Card>
          <CardHeader class="pb-3 border-b border-border/50">
            <CardTitle class="text-base font-semibold flex items-center gap-2">
              <Monitor class="w-4 h-4 text-primary" />
              {{ $t('system.analytics.sections.technology') }}
            </CardTitle>
          </CardHeader>
          <CardContent class="p-6">
            <div class="grid grid-cols-2 gap-8">
              <!-- Devices -->
              <div>
                <h4 class="text-xs font-semibold text-muted-foreground capitalize tracking-wide mb-4">
                  {{ $t('system.analytics.charts.devices') }}
                </h4>
                <div class="h-[160px]">
                  <DoughnutChart
                    v-if="devices.length > 0"
                    :data="devices"
                    label-key="device_type"
                    value-key="count"
                    :accessibility-label="$t('system.analytics.charts.devices')"
                  />
                  <div
                    v-else
                    class="h-full flex items-center justify-center text-muted-foreground bg-muted/30 rounded-lg text-xs"
                  >
                    {{ $t('system.analytics.noData') }}
                  </div>
                </div>
              </div>
              <!-- Browsers -->
              <div>
                <h4 class="text-xs font-semibold text-muted-foreground capitalize tracking-wide mb-4">
                  {{ $t('system.analytics.charts.browsers') }}
                </h4>
                <div class="h-[160px]">
                  <DoughnutChart
                    v-if="browsers.length > 0"
                    :data="browsers"
                    label-key="browser"
                    value-key="count"
                    :accessibility-label="$t('system.analytics.charts.browsers')"
                  />
                  <div
                    v-else
                    class="h-full flex items-center justify-center text-muted-foreground bg-muted/30 rounded-lg text-xs"
                  >
                    {{ $t('system.analytics.noData') }}
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Geography -->
        <Card>
          <CardHeader class="pb-3 border-b border-border/50">
            <CardTitle class="text-base font-semibold flex items-center gap-2">
              <Globe class="w-4 h-4 text-primary" />
              {{ $t('system.analytics.sections.geography') }}
            </CardTitle>
          </CardHeader>
          <CardContent class="p-6">
            <div class="grid grid-cols-2 gap-8">
              <!-- Countries -->
              <div>
                <h4 class="text-xs font-semibold text-muted-foreground capitalize tracking-wide mb-4">
                  {{ $t('system.analytics.charts.topCountries') }}
                </h4>
                <div class="h-[160px]">
                  <BarChart
                    v-if="countries.length > 0"
                    :data="countries.slice(0, 5)"
                    label-key="country"
                    value-key="count"
                    :horizontal="true"
                    :accessibility-label="$t('system.analytics.charts.topCountries')"
                  />
                  <div
                    v-else
                    class="h-full flex items-center justify-center text-muted-foreground bg-muted/30 rounded-lg text-xs"
                  >
                    {{ $t('system.analytics.noData') }}
                  </div>
                </div>
              </div>
              <!-- Referrers -->
              <div>
                <h4 class="text-xs font-semibold text-muted-foreground capitalize tracking-wide mb-4">
                  {{ $t('system.analytics.charts.topReferrers') }}
                </h4>
                <div class="space-y-2.5 max-h-[160px] overflow-y-auto pr-1 flex flex-col justify-center">
                  <div
                    v-for="(referrerItem, i) in referrers.slice(0, 5)"
                    :key="i"
                    class="flex items-center justify-between group"
                  >
                    <span class="text-xs truncate flex-1 text-foreground/80 group-hover:text-foreground transition-colors">{{ formatUrl(referrerItem.referer) }}</span>
                    <Badge
                      variant="outline"
                      class="ml-2 h-5 px-1.5 text-[10px] tabular-nums"
                    >
                      {{ referrerItem.count }}
                    </Badge>
                  </div>
                  <div
                    v-if="referrers.length === 0"
                    class="flex flex-col items-center justify-center py-8 text-muted-foreground bg-muted/30 rounded-lg"
                  >
                    <p class="text-[10px] italic">
                      {{ $t('system.analytics.noData') }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Content Section -->
      <Card>
        <CardHeader class="pb-3 border-b border-border/50">
          <CardTitle class="text-base font-semibold flex items-center gap-2">
            <FileText class="w-4 h-4 text-primary" />
            {{ $t('system.analytics.sections.content') }}
          </CardTitle>
        </CardHeader>
        <CardContent class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            <div
              v-for="content in topContent.slice(0, 10)"
              :key="content.id"
              class="flex flex-col justify-between p-4 rounded-xl border border-border bg-muted/20 hover:bg-muted/30 transition-colors group"
            >
              <div>
                <p
                  class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors truncate mb-1"
                  :title="content.title"
                >
                  {{ content.title }}
                </p>
                <p class="text-xs text-muted-foreground truncate">
                  {{ content.author?.name || $t('common.labels.system') }}
                </p>
              </div>
              <div class="mt-4 flex items-end justify-between">
                <span class="text-2xl font-bold tracking-tight text-foreground tabular-nums">{{ content.visits_count || 0 }}</span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-1">{{ $t('system.analytics.labels.visits') }}</span>
              </div>
            </div>
            <div
              v-if="topContent.length === 0"
              class="col-span-full flex flex-col items-center justify-center py-12 text-muted-foreground bg-muted/30 rounded-lg"
            >
              <FileText class="w-10 h-10 mb-2 opacity-10" />
              <p class="text-sm italic">
                {{ $t('system.analytics.noData') }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      <RetentionPanel />
    </template>
  </div>
</template>

<script setup lang="ts">
import {PageHeader, ConsoleListCard} from '@/shared/components/shell';
import RetentionPanel from '@/modules/Analytics/components/RetentionPanel.vue';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { AnalyticsService } from '@/shared/services/analyticsService';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse, parseSingleResponse, ensureArray } from '@/shared/utils/responseParser';
import LineChart from '@/modules/Core/System/components/charts/LineChart.vue';
import DoughnutChart from '@/modules/Core/System/components/charts/DoughnutChart.vue';
import BarChart from '@/modules/Core/System/components/charts/BarChart.vue';
import { Badge, Button, Card, CardContent, CardHeader, CardTitle, Input } from '@/shared/components/ui';

import {
  BarChart3,
  Download,
  Eye,
  FileText,
  Globe,
  Loader2,
  Monitor,
  TrendingUp,
  Users,
} from 'lucide-vue-next';

const { t } = useI18n();
const toast = useToast();
const loading = ref(false);
const exporting = ref(false);
const showExportMenu = ref(false);
const exportDropdownRef = ref<HTMLElement | null>(null);
const dateFrom = ref(new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]);
const dateTo = ref(new Date().toISOString().split('T')[0]);

interface AnalyticsOverview {
    total_visits?: number;
    unique_visitors?: number;
    total_sessions?: number;
    bounce_rate?: number;
}

const overview = ref<AnalyticsOverview>({});

interface VisitData {
    period: string;
    visits: number;
}

interface PageData {
    url: string;
    visits: number;
}

interface ContentData {
    id: string;
    title: string;
    visits_count: number;
    author?: { name: string };
}

interface RealtimeData {
    active_sessions?: number;
    visits_last_hour?: number;
    top_pages_now?: unknown[];
}

interface StatItem {
    count: number;
    [key: string]: unknown;
}

const visits = ref<VisitData[]>([]);
const topPages = ref<PageData[]>([]);
const topContent = ref<ContentData[]>([]);
const devices = ref<StatItem[]>([]);
const browsers = ref<StatItem[]>([]);
const countries = ref<StatItem[]>([]);
const referrers = ref<{ referer: string; count: number }[]>([]);
const realtime = ref<RealtimeData>({});
let refreshInterval: ReturnType<typeof setInterval> | null = null;

// Filter out API routes and error pages from top pages
const filteredTopPages = computed(() => {
    const errorPatterns = ['/403', '/404', '/419', '/500', '/503'];
    return topPages.value.filter((page: PageData) => {
        if (!page.url) return true;
        // Exclude /api/ URLs
        if (page.url.includes('/api/')) return false;
        // Exclude error pages
        const path = page.url.replace(/https?:\/\/[^/]+/, ''); // Get path only
        if (errorPatterns.some(pattern => path === pattern || path.startsWith(pattern + '?'))) return false;
        return true;
    });
});

// Utility functions
const formatNumber = (num: number) => {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num.toString();
};

const formatUrl = (url: string) => {
    if (!url) return '-';
    try {
        const parsed = new URL(url);
        return parsed.pathname === '/' ? parsed.hostname : parsed.pathname;
    } catch {
        return url.substring(0, 30);
    }
};

const fetchAnalytics = async () => {
    loading.value = true;
    try {
        const params = { date_from: dateFrom.value, date_to: dateTo.value };
        const [overviewRes, visitsRes, topPagesRes, topContentRes, devicesRes, browsersRes, countriesRes, referrersRes, realtimeRes] = await Promise.all([
            AnalyticsService.overview(params),
            AnalyticsService.visits(params),
            AnalyticsService.topPages(params),
            AnalyticsService.topContent(params),
            AnalyticsService.devices(params),
            AnalyticsService.browsers(params),
            AnalyticsService.countries(params),
            AnalyticsService.referrers(params),
            AnalyticsService.realtime(),
        ]);

        overview.value = parseSingleResponse(overviewRes) || {};
        visits.value = ensureArray(parseResponse(visitsRes).data);
        topPages.value = ensureArray(parseResponse(topPagesRes).data);
        topContent.value = ensureArray(parseResponse(topContentRes).data);
        devices.value = ensureArray(parseResponse(devicesRes).data);
        browsers.value = ensureArray(parseResponse(browsersRes).data);
        countries.value = ensureArray(parseResponse(countriesRes).data);
        referrers.value = ensureArray(parseResponse(referrersRes).data);
        realtime.value = parseSingleResponse(realtimeRes) || {};
    } catch (error: unknown) {
        logger.error('Failed to fetch analytics:', error);
    } finally {
        loading.value = false;
    }
};

const toggleExportMenu = () => showExportMenu.value = !showExportMenu.value;

const handleClickOutside = (event: Event) => {
    if (exportDropdownRef.value && !exportDropdownRef.value.contains(event.target as Node)) {
        showExportMenu.value = false;
    }
};

const exportData = async (type: string) => {
    showExportMenu.value = false;
    exporting.value = true;
    try {
        const response = await AnalyticsService.export({
            date_from: dateFrom.value,
            date_to: dateTo.value,
            type,
        });
        const blob = new Blob([response.data], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `analytics-${type}-${dateFrom.value}-to-${dateTo.value}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        toast.success.action(t('system.analytics.export.success'));
    } catch (error: unknown) {
        logger.error('Failed to export:', error);
        toast.error.fromResponse(error);
    } finally {
        exporting.value = false;
    }
};

onMounted(() => {
    fetchAnalytics();
    refreshInterval = setInterval(() => {
        AnalyticsService.realtime().then(res => {
            realtime.value = parseSingleResponse(res) || {};
        }).catch(err => {
            logger.error('Realtime fetch failed:', err);
            if (err.response?.status === 401 || err.response?.status === 419) {
                if (refreshInterval) clearInterval(refreshInterval);
            }
        });
    }, 60000); // Increased from 30s to 1m
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
    document.removeEventListener('click', handleClickOutside);
});
</script>
