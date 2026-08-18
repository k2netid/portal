<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between pb-4">
      <div>
        <CardTitle class="text-lg">
          {{ $t('system.security.cspReports.title') }}
        </CardTitle>
        <CardDescription>{{ $t('system.security.cspReports.description') }}</CardDescription>
      </div>
      <div class="flex gap-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="loading"
          @click="$emit('refresh')"
        >
          <RefreshCw class="w-4 h-4 mr-2" />
          {{ $t('common.actions.refresh') }}
        </Button>
      </div>
    </CardHeader>

    <!-- Statistics -->
    <div class="px-6 pb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-muted/30 rounded-lg p-4">
        <p class="text-sm text-muted-foreground">
          {{ $t('system.security.cspReports.stats.total') }}
        </p>
        <p class="text-2xl font-bold text-foreground">
          {{ stats?.total || 0 }}
        </p>
      </div>
      <div class="bg-muted/30 rounded-lg p-4">
        <p class="text-sm text-muted-foreground">
          {{ $t('system.security.cspReports.stats.new') }}
        </p>
        <p class="text-2xl font-bold text-orange-600">
          {{ stats?.new || 0 }}
        </p>
      </div>
      <div class="bg-muted/30 rounded-lg p-4">
        <p class="text-sm text-muted-foreground">
          {{ $t('system.security.cspReports.stats.topViolation') }}
        </p>
        <p class="text-sm font-medium text-foreground truncate">
          {{ topViolation }}
        </p>
      </div>
      <div class="bg-muted/30 rounded-lg p-4">
        <p class="text-sm text-muted-foreground">
          {{ $t('system.security.cspReports.stats.last24h') }}
        </p>
        <p class="text-2xl font-bold text-blue-600">
          {{ recentCount }}
        </p>
      </div>
    </div>

    <!-- Filters -->
    <div class="px-6 pb-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <Label class="mb-2 block">{{ $t('system.security.cspReports.filters.status') }}</Label>
          <Select v-model="localFilters.status">
            <SelectTrigger>
              <SelectValue :placeholder="$t('common.labels.all')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">
                {{ $t('common.labels.all') }}
              </SelectItem>
              <SelectItem value="new">
                {{ $t('system.security.cspReports.status.new') }}
              </SelectItem>
              <SelectItem value="reviewed">
                {{ $t('system.security.cspReports.status.reviewed') }}
              </SelectItem>
              <SelectItem value="false_positive">
                {{ $t('system.security.cspReports.status.falsePositive') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div>
          <Label class="mb-2 block">{{ $t('system.security.cspReports.filters.directive') }}</Label>
          <Input
            v-model="localFilters.directive"
            :placeholder="t('system.security.cspReports.filters.directivePlaceholder')"
          />
        </div>
        <div>
          <Label class="mb-2 block">{{ $t('system.security.cspReports.filters.dateFrom') }}</Label>
          <Input
            v-model="localFilters.date_from"
            type="date"
          />
        </div>
        <div>
          <Label class="mb-2 block">{{ $t('system.security.cspReports.filters.dateTo') }}</Label>
          <Input
            v-model="localFilters.date_to"
            type="date"
          />
        </div>
      </div>
      <div class="flex justify-end gap-2 mt-4">
        <Button
          size="sm"
          @click="$emit('apply-filters')"
        >
          {{ $t('common.actions.apply') }}
        </Button>
        <Button
          variant="outline"
          size="sm"
          @click="$emit('reset-filters')"
        >
          {{ $t('common.actions.reset') }}
        </Button>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div
      v-if="selectedIds.length > 0"
      class="px-6 pb-4"
    >
      <div class="bg-muted/50 border border-border rounded-lg p-4 flex items-center justify-between">
        <span class="text-sm font-medium">{{ $t('system.security.bulkActions.selected', { count: selectedIds.length }) }}</span>
        <div class="flex gap-2">
          <Button
            variant="outline"
            size="sm"
            @click="handleBulkAction('mark_reviewed')"
          >
            {{ $t('system.security.cspReports.actions.markReviewed') }}
          </Button>
          <Button
            variant="outline"
            size="sm"
            @click="handleBulkAction('mark_false_positive')"
          >
            {{ $t('system.security.cspReports.actions.markFalsePositive') }}
          </Button>
          <Button
            variant="destructive"
            size="sm"
            @click="handleBulkAction('delete')"
          >
            {{ $t('common.actions.delete') }}
          </Button>
        </div>
      </div>
    </div>

    <CardContent class="p-0">
      <DataTable
        :table="table"
        :loading="loading"
        :empty-message="t('system.security.cspReports.empty')"
      />
    </CardContent>
    <Pagination
      v-if="pagination.total > 0"
      :current-page="pagination.current_page"
      :total-items="pagination.total"
      :per-page="filters.per_page"
      embedded
      @page-change="(val: number) => $emit('page-change', val)"
      @per-page-change="(val: number) => $emit('per-page-change', val)"
    />
  </Card>
</template>

<script setup lang="ts">
import { ref, computed, h, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';
import {
    Card, CardHeader, CardTitle, CardDescription, CardContent,
    Button, Badge, Input, Label, Checkbox, DataTable, Pagination,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/shared/components/ui';

import {
  RefreshCw,
} from 'lucide-vue-next';

interface CspReport {
    id: string;
    violated_directive: string;
    blocked_uri: string | null;
    document_uri: string;
    ip_address: string;
    status: string;
    created_at: string;
}

interface CspStats {
    total?: number;
    new?: number;
    by_directive?: { violated_directive: string; count: number }[];
    recent_trend?: { date: string; count: number }[];
}

interface PaginationInfo {
    total: number;
    current_page: number;
    last_page: number;
}

interface CspFilters {
    status: string;
    directive: string;
    date_from: string;
    date_to: string;
    page: number;
    per_page: number;
}

const props = defineProps<{
    reports: CspReport[];
    stats: CspStats | null;
    loading: boolean;
    pagination: PaginationInfo;
    filters: CspFilters;
}>();

const emit = defineEmits<{
    'refresh': [];
    'apply-filters': [];
    'reset-filters': [];
    'bulk-action': [action: string, ids: string[]];
    'page-change': [page: number];
    'per-page-change': [perPage: number];
}>();

const { t } = useI18n();

// Local state
const selectedIds = ref<string[]>([]);
const sorting = ref<SortingState>([]);
const localFilters = ref({ ...props.filters });

// Sync filters with parent
watch(() => props.filters, (newFilters) => {
    localFilters.value = { ...newFilters };
}, { deep: true });

const topViolation = computed(() => {
    if (!props.stats?.by_directive || props.stats.by_directive.length === 0) return t('common.labels.none');
    return props.stats.by_directive[0]?.violated_directive || t('common.labels.none');
});

const recentCount = computed(() => {
    if (!props.stats?.recent_trend) return 0;
    const lastDay = props.stats.recent_trend[props.stats.recent_trend.length - 1];
    return lastDay ? lastDay.count : 0;
});

const handleBulkAction = (action: string) => {
    emit('bulk-action', action, selectedIds.value);
    selectedIds.value = [];
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleString(undefined, {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const getStatusVariant = (status: string): "warning" | "secondary" | "outline" => {
    const variants: Record<string, "warning" | "secondary" | "outline"> = {
        new: 'warning',
        reviewed: 'secondary',
        false_positive: 'outline'
    };
    return variants[status] || 'secondary';
};

// TanStack Table
const columnHelper = createColumnHelper<CspReport>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: () => h(Checkbox, {
            checked: selectedIds.value.length === props.reports.length && props.reports.length > 0,
            'onUpdate:checked': (val: boolean) => {
                selectedIds.value = val ? props.reports.map(r => r.id) : [];
            }
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: selectedIds.value.includes(row.original.id),
            'onUpdate:checked': (val: boolean) => {
                if (val) selectedIds.value.push(row.original.id);
                else selectedIds.value = selectedIds.value.filter(id => id !== row.original.id);
            }
        })
    }),
    columnHelper.accessor('violated_directive', {
        header: t('system.security.cspReports.table.directive'),
        cell: ({ row }) => h(Badge, { variant: 'destructive' }, () => row.original.violated_directive)
    }),
    columnHelper.accessor('blocked_uri', {
        header: t('system.security.cspReports.table.blockedUri'),
        cell: ({ row }) => h('div', { class: 'truncate max-w-xs text-sm', title: row.original.blocked_uri ?? undefined }, row.original.blocked_uri || t('common.labels.notAvailable'))
    }),
    columnHelper.accessor('document_uri', {
        header: t('system.security.cspReports.table.documentUri'),
        cell: ({ row }) => h('div', { class: 'truncate max-w-xs text-sm text-muted-foreground', title: row.original.document_uri }, row.original.document_uri)
    }),
    columnHelper.accessor('ip_address', {
        header: t('system.security.cspReports.table.ip'),
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.original.ip_address)
    }),
    columnHelper.accessor('status', {
        header: t('system.security.cspReports.table.status'),
        cell: ({ row }) => h(Badge, { variant: getStatusVariant(row.original.status) }, () => row.original.status.replace('_', ' '))
    }),
    columnHelper.accessor('created_at', {
        header: t('system.security.cspReports.table.date'),
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground whitespace-nowrap' }, formatDate(row.original.created_at))
    }),
];

const table = useVueTable({
    get data() { return props.reports },
    columns,
    state: { get sorting() { return sorting.value } },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
});
</script>
