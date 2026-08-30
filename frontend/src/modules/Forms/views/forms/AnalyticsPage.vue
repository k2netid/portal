<template>
  <div class="space-y-6 print:p-0">
    <PageHeader
      borderless
      class="print:hidden"
      :title="$t('forms.submissions.analytics.title')"
      :subtitle="analyticsSubtitle"
    >
      <template #actions>
        <div class="flex items-center gap-2 flex-wrap">
        <Button
          variant="outline"
          size="sm"
          class="h-10 inline-flex items-center gap-2"
          @click="$router.push({ name: 'forms.submissions', params: { id: formId } })"
        >
          <ArrowLeft class="w-4 h-4 mr-2" />
          {{ $t('common.actions.back') }}
        </Button>
        <Button
          variant="outline"
          size="sm"
          class="h-10 inline-flex items-center gap-2"
          @click="printReport"
        >
          <Printer class="w-4 h-4 mr-2" />
          {{ $t('forms.submissions.analytics.print') }}
        </Button>
        <div class="flex items-center bg-muted/50 rounded-md p-1">
          <Button
            variant="ghost"
            size="sm"
            @click="exportData('xlsx')"
          >
            <Download class="w-4 h-4 mr-2" />
            {{ $t('forms.submissions.analytics.excel') }}
          </Button>
          <Button
            variant="ghost"
            size="sm"
            @click="exportData('csv')"
          >
            <Download class="w-4 h-4 mr-2" />
            {{ $t('forms.submissions.analytics.csv') }}
          </Button>
        </div>
        </div>
      </template>
    </PageHeader>

    <!-- Print Header (Visible only when printing) -->
    <div class="hidden print:block text-center border-b pb-6 mb-8">
      <h1 class="text-3xl font-bold mb-2">
        {{ $t('forms.submissions.analytics.title') }}
      </h1>
      <p class="text-lg text-muted-foreground">
        {{ form?.name || '-' }}
      </p>
      <p class="text-sm mt-4 text-muted-foreground">
        {{ new Date().toLocaleString() }}
      </p>
    </div>

    <!-- Filters -->
    <Card class="p-4 print:hidden">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-4">
          <div class="flex items-center gap-2">
            <span class="text-sm font-medium">{{ $t('common.actions.filter') }}:</span>
            <Select
              v-model="analyticsDays"
              @update:model-value="handlePresetChange"
            >
              <SelectTrigger class="w-[150px]" :aria-label="$t('forms.submissions.analytics.range')">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="7">
                  {{ $t('forms.submissions.analytics.last7Days') }}
                </SelectItem>
                <SelectItem value="30">
                  {{ $t('forms.submissions.analytics.last30Days') }}
                </SelectItem>
                <SelectItem value="90">
                  {{ $t('forms.submissions.analytics.last90Days') }}
                </SelectItem>
                <SelectItem value="custom">
                  {{ $t('forms.submissions.analytics.customRange') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
                    
          <Transition 
            enter-active-class="transition-all duration-200" 
            enter-from-class="opacity-0 -translate-x-2"
            enter-to-class="opacity-100 translate-x-0"
          >
            <div
              v-if="analyticsDays === 'custom'"
              class="flex items-center gap-2"
            >
              <Input
                v-model="dateFrom"
                type="date"
                class="w-40 h-10"
                @change="fetchStatistics"
              />
              <span class="text-muted-foreground">-</span>
              <Input
                v-model="dateTo"
                type="date"
                class="w-40 h-10"
                @change="fetchStatistics"
              />
            </div>
          </Transition>

          <div class="h-6 w-px bg-border mx-2 hidden sm:block" />
        </div>

        <div class="flex flex-wrap items-center gap-4">
          <div class="flex items-center gap-2">
            <span class="text-sm font-medium">{{ $t('forms.submissions.analytics.chartType') }}:</span>
            <div class="flex items-center bg-muted/50 rounded-md p-1">
              <Button 
                variant="ghost" 
                size="sm" 
                :class="{ 'bg-background shadow-sm': chartType === 'line' }"
                @click="chartType = 'line'"
              >
                <LineChartIcon class="w-4 h-4 mr-2" />
                {{ $t('forms.submissions.analytics.line') }}
              </Button>
              <Button 
                variant="ghost" 
                size="sm" 
                :class="{ 'bg-background shadow-sm': chartType === 'bar' }"
                @click="chartType = 'bar'"
              >
                <BarChart3 class="w-4 h-4 mr-2" />
                {{ $t('forms.submissions.analytics.bar') }}
              </Button>
            </div>
          </div>
        </div>
      </div>
    </Card>

    <!-- Stats Overview -->
    <div
      v-if="statistics"
      class="grid grid-cols-2 md:grid-cols-4 gap-4"
    >
      <Card class="p-6">
        <p class="text-sm font-medium text-muted-foreground mb-1">
          {{ $t('forms.stats.total') }}
        </p>
        <div class="flex items-end gap-2">
          <h3 class="text-3xl font-bold text-primary">
            {{ statistics.total || 0 }}
          </h3>
          <div 
            v-if="statistics?.growth !== undefined && statistics?.growth !== null" 
            class="text-xs font-medium px-1.5 py-0.5 rounded-sm flex items-center mb-1"
            :class="Number(statistics.growth) >= 0 ? 'bg-green-500/10 text-green-800' : 'bg-red-500/10 text-red-600'"
          >
            <ArrowUp
              v-if="Number(statistics.growth) >= 0"
              class="w-3 h-3 mr-0.5"
            />
            <ArrowDown
              v-else
              class="w-3 h-3 mr-0.5"
            />
            {{ Math.abs(Number(statistics.growth)) }}%
          </div>
        </div>
        <p class="text-xs text-foreground/70 mt-1">
          {{ $t('forms.submissions.analytics.vsPrevious') }} ({{ statistics.previous_total || 0 }})
        </p>
      </Card>
      <Card class="p-6">
        <p class="text-sm font-medium text-muted-foreground mb-1">
          {{ $t('forms.stats.new') }}
        </p>
        <h3 class="text-3xl font-bold text-green-800">
          {{ statistics.new || 0 }}
        </h3>
        <p class="text-xs text-foreground/70 mt-1">
          {{ $t('forms.submissions.analytics.unreadSubmissions') }}
        </p>
      </Card>
      <Card class="p-6">
        <p class="text-sm font-medium text-muted-foreground mb-1">
          {{ $t('forms.stats.read') }}
        </p>
        <h3 class="text-3xl font-bold text-amber-800">
          {{ statistics.read || 0 }}
        </h3>
        <p class="text-xs text-foreground/70 mt-1">
          {{ $t('forms.submissions.analytics.reviewedByTeam') }}
        </p>
      </Card>
      <Card class="p-6">
        <p class="text-sm font-medium text-muted-foreground mb-1">
          {{ $t('forms.submissions.analytics.lifetimeData') }}
        </p>
        <h3 class="text-3xl font-bold text-muted-foreground">
          {{ statistics.all_time_total || 0 }}
        </h3>
        <p class="text-xs text-foreground/70 mt-1">
          {{ $t('forms.submissions.analytics.lifetimeData') }}
        </p>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Trend Chart -->
      <Card class="lg:col-span-2 p-6 flex flex-col h-[450px]">
        <div class="flex flex-col gap-2 mb-4">
          <div class="flex items-center justify-between">
            <h3 class="font-semibold flex items-center gap-2">
              <TrendingUp class="w-5 h-5 text-primary" />
              {{ $t('forms.submissions.analytics.trend') }}
            </h3>
          </div>
          <p
            v-if="statistics?.daily_views_stats?.length"
            class="text-xs text-muted-foreground leading-relaxed max-w-3xl"
          >
            {{ $t('forms.submissions.analytics.funnelSummary') }}
          </p>
          <div
            v-if="statistics && (statistics.range_views_total !== undefined || statistics.range_starts_total !== undefined)"
            class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-muted-foreground"
          >
            <span>
              {{ $t('forms.submissions.analytics.rangeViews') }}:
              <strong class="text-foreground tabular-nums">{{ statistics.range_views_total ?? 0 }}</strong>
            </span>
            <span>
              {{ $t('forms.submissions.analytics.rangeStarts') }}:
              <strong class="text-foreground tabular-nums">{{ statistics.range_starts_total ?? 0 }}</strong>
            </span>
          </div>
        </div>
        <div class="flex-1 w-full relative">
          <LineChart
            v-if="chartType === 'line' && statistics?.daily_stats?.length"
            :data="(statistics.daily_stats as any)"
            :label="$t('forms.submissions.analytics.chartSubmissions')"
            :accessibility-label="$t('forms.submissions.analytics.chartSubmissions')"
            label-key="period"
            value-key="visits"
            :compare-data="(statistics.daily_views_stats as any) || []"
            :compare-label="$t('forms.submissions.analytics.chartPageViews')"
          />
          <BarChart
            v-else-if="chartType === 'bar' && statistics?.daily_stats?.length"
            :data="(statistics.daily_stats as any)"
            label-key="period"
            value-key="visits"
            :horizontal="false"
            :accessibility-label="$t('forms.submissions.analytics.chartSubmissions')"
          />
          <div
            v-else
            class="h-full flex items-center justify-center text-muted-foreground italic border border-dashed rounded-lg bg-muted/5"
          >
            {{ $t('forms.messages.empty') }}
          </div>
        </div>
      </Card>

      <!-- Data Breakdown -->
      <Card class="p-6 flex flex-col h-[450px]">
        <div class="flex items-center justify-between mb-6 print:hidden">
          <h3 class="font-semibold flex items-center gap-2">
            <PieChart class="w-5 h-5 text-primary" />
            {{ $t('forms.submissions.analytics.dataBreakdown') }}
          </h3>
          <Select
            v-model="selectedAggregateField"
            @update:model-value="fetchStatistics"
          >
            <SelectTrigger class="w-[140px] h-8 text-xs" :aria-label="$t('forms.submissions.analytics.dataBreakdown')">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem 
                v-for="field in statistics?.chartable_fields" 
                :key="field.id" 
                :value="field.name"
              >
                {{ field.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <!-- Print Title for Breakdown -->
        <div class="hidden print:block mb-4 text-center">
          <h3 class="font-bold underline">
            {{ $t('forms.submissions.analytics.dataBreakdown') }}: {{ getSelectedFieldLabel }}
          </h3>
        </div>

        <div class="flex-1 w-full flex items-center justify-center overflow-hidden">
          <DoughnutChart 
            v-if="selectedAggregateField && statistics?.field_distribution"
            :data="(statistics.field_distribution as any)" 
            label-key="label"
            value-key="count"
            :accessibility-label="getSelectedFieldLabel"
          />
          <div
            v-else
            class="text-center p-8 bg-muted/10 rounded-xl border border-dashed text-muted-foreground space-y-3"
          >
            <PieChart class="w-10 h-10 mx-auto opacity-20" />
            <p class="text-sm italic px-4">
              {{ statistics?.chartable_fields?.length ? $t('forms.submissions.analytics.selectField') : $t('forms.submissions.analytics.noChartableFields') }}
            </p>
          </div>
        </div>
      </Card>
    </div>

    <!-- Peak Activity Insights -->
    <div
      v-if="statistics?.hourly_stats?.length || statistics?.weekly_stats?.length"
      class="grid grid-cols-1 md:grid-cols-2 gap-6"
    >
      <Card class="p-6 h-[350px] flex flex-col">
        <h3 class="font-semibold mb-6 flex items-center gap-2">
          <Clock class="w-5 h-5 text-primary" />
          {{ $t('forms.submissions.analytics.peakHours') }}
        </h3>
        <div class="flex-1 relative">
          <LineChart 
            :data="(statistics?.hourly_stats as any) || []" 
            label-key="hour" 
            value-key="count" 
            :label="$t('forms.submissions.analytics.submissionsPerHour')"
            :accessibility-label="$t('forms.submissions.analytics.submissionsPerHour')"
          />
        </div>
      </Card>

      <Card class="p-6 h-[350px] flex flex-col">
        <h3 class="font-semibold mb-6 flex items-center gap-2">
          <CalendarDays class="w-5 h-5 text-primary" />
          {{ $t('forms.submissions.analytics.peakDays') }}
        </h3>
        <div class="flex-1 relative">
          <BarChart 
            :data="(statistics?.weekly_stats as any) || []" 
            label-key="day" 
            value-key="count" 
            :horizontal="false"
            :accessibility-label="$t('forms.submissions.analytics.peakDays')"
          />
        </div>
      </Card>
    </div>

    <!-- Data Table (Integrated with TanStack Table for Analysis) -->
    <Card class="p-6 overflow-hidden print:shadow-none print:border-none">
      <h3 class="font-semibold mb-4 text-foreground flex items-center gap-2">
        <FileText class="w-5 h-5 text-primary" />
        {{ $t('forms.submissions.formData') }} ({{ $t('forms.submissions.analytics.samples') }})
      </h3>
            
      <div class="relative overflow-x-auto border rounded-lg">
        <Table>
          <TableHeader class="bg-muted/50 border-b border-border">
            <TableRow
              v-for="headerGroup in table.getHeaderGroups()"
              :key="headerGroup.id"
            >
              <TableHead 
                v-for="header in headerGroup.headers" 
                :key="header.id"
                class="px-3 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider"
              >
                <FlexRender 
                  v-if="!header.isPlaceholder"
                  :render="header.column.columnDef.header"
                  :props="header.getContext()"
                />
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow 
              v-for="row in table.getRowModel().rows" 
              :key="row.id" 
              class="hover:bg-muted/30 border-b border-border/50 last:border-0"
            >
              <TableCell 
                v-for="cell in row.getVisibleCells()" 
                :key="cell.id"
                class="px-3 py-3"
              >
                <FlexRender 
                  :render="cell.column.columnDef.cell"
                  :props="cell.getContext()"
                />
              </TableCell>
            </TableRow>
            <!-- Empty State in Table -->
            <TableRow v-if="rawSamples.length === 0">
              <TableCell
                :colspan="columns.length"
                class="h-32 text-center text-muted-foreground italic"
              >
                {{ $t('forms.submissions.empty') }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
            
      <p
        v-if="statistics && statistics.total > 20"
        class="mt-4 text-xs italic text-muted-foreground text-center print:hidden"
      >
        {{ $t('forms.submissions.analytics.exportForFull', { count: statistics.total - 20 }) }}
      </p>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { PageHeader } from '@/shared/components/shell';
import { ref, computed, onMounted, h } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { logger } from '@/shared/utils/logger';
import { apiConfig } from '@/config';
import { FormsService } from '@/modules/Forms/services/formsService';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import { 
    useVueTable, 
    getCoreRowModel, 
    getSortedRowModel,
    createColumnHelper,
    FlexRender,
    type ColumnDef,
    type CellContext 
} from '@tanstack/vue-table';
import { 
    Button, 
    Card, 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue,
    Input,
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell
} from '@/shared/components/ui';
import {
  ArrowDown,
  ArrowLeft,
  ArrowUp,
  ArrowUpDown,
  BarChart3,
  CalendarDays,
  Clock,
  Download,
  FileText,
  LineChartIcon,
  PieChart,
  Printer,
  TrendingUp,
} from 'lucide-vue-next';

import LineChart from '@/modules/Core/System/components/charts/LineChart.vue';
import BarChart from '@/modules/Core/System/components/charts/BarChart.vue';
import DoughnutChart from '@/modules/Core/System/components/charts/DoughnutChart.vue';

interface AnalyticsStatistics {
    total: number;
    growth?: number;
    previous_total?: number;
    new?: number;
    read?: number;
    all_time_total?: number;
    daily_stats?: Record<string, unknown>[];
    daily_views_stats?: Record<string, unknown>[];
    range_views_total?: number;
    range_starts_total?: number;
    field_distribution?: Record<string, unknown>[];
    chartable_fields?: { name: string, label: string, id?: string }[];
    hourly_stats?: Record<string, unknown>[];
    weekly_stats?: Record<string, unknown>[];
}

const route = useRoute();
const { t } = useI18n();
const formId = computed(() => route.params.id);
const form = ref<{ name: string } | null>(null);
const analyticsSubtitle = computed(() => {
  const base = t('forms.submissions.analytics.subtitle');
  const n = form.value?.name;
  return n ? `${base} — ${n}` : base;
});
const statistics = ref<AnalyticsStatistics | null>(null);
const analyticsDays = ref('30');
const dateFrom = ref('');
const dateTo = ref('');
const chartType = ref<'line' | 'bar'>('line');
const selectedAggregateField = ref('');
const rawSamples = ref<Record<string, unknown>[]>([]);
const sorting = ref([{ id: 'created_at', desc: true }]);

const fetchForm = async () => {
    try {
        const response = await FormsService.get(String(formId.value));
        form.value = parseSingleResponse(response);
    } catch (error) {
        logger.error('Failed to fetch form:', error);
    }
};

const fetchStatistics = async () => {
    try {
        const params: Record<string, unknown> = {
            aggregate_field: selectedAggregateField.value
        };

        if (analyticsDays.value === 'custom') {
            if (!dateFrom.value || !dateTo.value) return;
            params.date_from = dateFrom.value;
            params.date_to = dateTo.value;
        } else {
            params.days = analyticsDays.value;
        }

        const response = await FormsService.submissionStatistics(String(formId.value), params);
        statistics.value = parseSingleResponse(response);
        
        if (!selectedAggregateField.value && statistics.value && (statistics.value.chartable_fields?.length ?? 0) > 0) {
            const firstField = statistics.value.chartable_fields?.[0];
            if (firstField) {
                selectedAggregateField.value = firstField.name;
                fetchStatistics();
            }
        }
    } catch (error) {
        logger.error('Failed to fetch statistics:', error);
    }
};

const handlePresetChange = () => {
    if (analyticsDays.value !== 'custom') {
        dateFrom.value = '';
        dateTo.value = '';
        fetchStatistics();
    }
};

const fetchSamples = async () => {
    try {
        const response = await FormsService.listSubmissions(String(formId.value), {
            per_page: 20, sort_by: 'created_at', sort_order: 'desc'
        });
        const data = parseSingleResponse(response) as { data: Record<string, unknown>[] };
        rawSamples.value = (data?.data as Record<string, unknown>[]) || [];
    } catch (error) {
        logger.error('Failed to fetch samples:', error);
    }
};

const reportFields = computed(() => {
    if (!rawSamples.value?.length || !rawSamples.value[0]?.data) return [];
    return Object.keys(rawSamples.value[0].data).slice(0, 4); // Show first 4 fields in report
});

const getSelectedFieldLabel = computed(() => {
    return statistics.value?.chartable_fields?.find((f: { name: string, label: string }) => f.name === selectedAggregateField.value)?.label || '';
});

// --- TanStack Table Setup ---
const columnHelper = createColumnHelper<Record<string, unknown>>();

const renderSortIcon = (isSorted: string | boolean) => {
    if (isSorted === 'asc') return ArrowUp;
    if (isSorted === 'desc') return ArrowDown;
    return ArrowUpDown;
};

const columns = computed(() => {
    const cols: ColumnDef<Record<string, unknown>, unknown>[] = [
        columnHelper.accessor('created_at', {
            header: ({ column }) => h(Button, {
                variant: 'ghost',
                size: 'sm',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
                class: '-ml-3 h-8 text-slate-700 font-medium print:hidden',
            }, () => [
                'Date',
                h(renderSortIcon(column.getIsSorted()), { class: 'ml-2 h-4 w-4' })
            ]),
            cell: (info: CellContext<Record<string, unknown>, unknown>) => h('span', { class: 'text-sm font-mono whitespace-nowrap' }, formatDate(info.getValue() as string)),
        }),
    ];

    // Add dynamic data fields
    reportFields.value.forEach((field: string) => {
        cols.push(columnHelper.accessor(row => (row.data as Record<string, unknown>)?.[field], {
            id: field,
            header: ({ column }) => h(Button, {
                variant: 'ghost',
                size: 'sm',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
                class: '-ml-3 h-8 text-slate-700 font-medium print:hidden',
            }, () => [
                field,
                h(renderSortIcon(column.getIsSorted()), { class: 'ml-2 h-4 w-4' })
            ]),
            cell: (info: CellContext<Record<string, unknown>, unknown>) => h('span', { class: 'text-sm' }, String(info.getValue() || '-')),
        }));
    });

    cols.push(columnHelper.accessor('ip_address', {
        header: ({ column }) => h(Button, {
            variant: 'ghost',
            size: 'sm',
            onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            class: '-ml-3 h-8 text-slate-700 font-medium print:hidden',
        }, () => [
            'IP Address',
            h(renderSortIcon(column.getIsSorted()), { class: 'ml-2 h-4 w-4' })
        ]),
        cell: (info: CellContext<Record<string, unknown>, unknown>) => h('span', { class: 'text-sm font-mono opacity-60' }, (info.getValue() as string) || '-'),
    }));

    return cols;
});

const table = useVueTable({
    get data() { return rawSamples.value },
    get columns() { return columns.value },
    state: {
        get sorting() { return sorting.value },
    },
    onSortingChange: (updaterOrValue) => {
        sorting.value = typeof updaterOrValue === 'function' 
            ? updaterOrValue(sorting.value) 
            : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
});

const formatDate = (date: string) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const printReport = () => {
    window.print();
};

const exportData = (format = 'xlsx') => {
    const params = new URLSearchParams({
        format,
        ...(analyticsDays.value === 'custom' ? {
            date_from: dateFrom.value,
            date_to: dateTo.value
        } : {
            days: analyticsDays.value
        })
    });
    const baseUrl = apiConfig.externalUrl;
    const url = FormsService.submissionsExportUrl(String(formId.value), params.toString(), baseUrl);
    window.open(url, '_blank');
};



onMounted(() => {
    fetchForm();
    fetchStatistics();
    fetchSamples();
});
</script>

<style scoped>
@media print {
    /* Hide scrollbars and ensure charts are visible */
    body {
        background: white;
    }
    .print\:hidden {
        display: none !important;
    }
    .print\:block {
        display: block !important;
    }
    .print\:p-0 {
        padding: 0 !important;
    }
    .print\:shadow-none {
        box-shadow: none !important;
    }
    .print\:border-none {
        border: none !important;
    }
    /* Ensure charts have enough space */
    .h-\[450px\] {
        height: 600px !important;
    }
    /* Page breaks */
    .lg\:col-span-2 {
        page-break-after: always;
    }
}
</style>
