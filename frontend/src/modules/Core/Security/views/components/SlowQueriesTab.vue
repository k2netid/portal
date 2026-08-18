<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between pb-4">
      <div>
        <CardTitle class="text-lg">
          {{ $t('system.security.slowQueries.title') }}
        </CardTitle>
        <CardDescription>{{ $t('system.security.slowQueries.description') }}</CardDescription>
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
          {{ $t('system.security.slowQueries.stats.total') }}
        </p>
        <p class="text-2xl font-bold text-foreground">
          {{ stats?.total || 0 }}
        </p>
      </div>
      <div class="bg-muted/30 rounded-lg p-4">
        <p class="text-sm text-muted-foreground">
          {{ $t('system.security.slowQueries.stats.avgDuration') }}
        </p>
        <p class="text-2xl font-bold text-yellow-600">
          {{ formatDuration(stats?.avg_duration || 0) }}
        </p>
      </div>
      <div class="bg-muted/30 rounded-lg p-4">
        <p class="text-sm text-muted-foreground">
          {{ $t('system.security.slowQueries.stats.maxDuration') }}
        </p>
        <p class="text-2xl font-bold text-red-600">
          {{ formatDuration(stats?.max_duration || 0) }}
        </p>
      </div>
      <div class="bg-muted/30 rounded-lg p-4">
        <p class="text-sm text-muted-foreground">
          {{ $t('system.security.slowQueries.stats.today') }}
        </p>
        <p class="text-2xl font-bold text-blue-600">
          {{ stats?.today || 0 }}
        </p>
      </div>
    </div>

    <!-- Filters -->
    <div class="px-6 pb-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <Label class="mb-2 block">{{ $t('system.security.slowQueries.filters.route') }}</Label>
          <Input
            v-model="localFilters.route"
            :placeholder="$t('system.security.slowQueries.filters.routePlaceholder')"
          />
        </div>
        <div>
          <Label class="mb-2 block">{{ $t('system.security.slowQueries.filters.minDuration') }}</Label>
          <Input
            v-model="localFilters.min_duration"
            type="number"
            :placeholder="t('system.security.slowQueries.filters.minDurationPlaceholder')"
          />
        </div>
        <div>
          <Label class="mb-2 block">{{ $t('system.security.slowQueries.filters.dateFrom') }}</Label>
          <Input
            v-model="localFilters.date_from"
            type="date"
          />
        </div>
        <div>
          <Label class="mb-2 block">{{ $t('system.security.slowQueries.filters.dateTo') }}</Label>
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

    <CardContent class="p-0">
      <DataTable
        :table="table"
        :loading="loading"
        :empty-message="t('system.security.slowQueries.empty')"
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
import { ref, h, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';
import {
    Card, CardHeader, CardTitle, CardDescription, CardContent,
    Button, Badge, Input, Label, DataTable, Pagination
} from '@/shared/components/ui';

import {
  RefreshCw,
} from 'lucide-vue-next';

interface User {
    id: string;
    name: string;
}

interface SlowQuery {
    id: string;
    route: string | null;
    duration: number;
    user_id?: string | null;
    user?: User | null;
    query: string;
    created_at: string;
}

interface SlowQueryStats {
    total?: number;
    avg_duration?: number;
    max_duration?: number;
    today?: number;
}

interface PaginationInfo {
    total: number;
    current_page: number;
    last_page: number;
}

interface SlowQueryFilters {
    route: string;
    min_duration: string;
    date_from: string;
    date_to: string;
    page: number;
    per_page: number;
}

const props = defineProps<{
    queries: SlowQuery[];
    stats: SlowQueryStats | null;
    loading: boolean;
    pagination: PaginationInfo;
    filters: SlowQueryFilters;
}>();

defineEmits<{
    'refresh': [];
    'apply-filters': [];
    'reset-filters': [];
    'page-change': [page: number];
    'per-page-change': [perPage: number];
}>();

const { t } = useI18n();

// Local state
const sorting = ref<SortingState>([]);
const localFilters = ref({ ...props.filters });

// Sync filters with parent
watch(() => props.filters, (newFilters) => {
    localFilters.value = { ...newFilters };
}, { deep: true });

const formatDuration = (ms: number): string => {
    if (ms >= 1000) return `${(ms / 1000).toFixed(2)}s`;
    return `${ms}ms`;
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleString(undefined, {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const getDurationVariant = (duration: number): "destructive" | "warning" | "secondary" => {
    if (duration >= 5000) return 'destructive';
    if (duration >= 2000) return 'warning';
    return 'secondary';
};

// TanStack Table
const columnHelper = createColumnHelper<SlowQuery>();

const columns = [
    columnHelper.accessor('route', {
        header: t('system.security.slowQueries.table.route'),
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.original.route || t('common.labels.emptyCell'))
    }),
    columnHelper.accessor('duration', {
        header: t('system.security.slowQueries.table.duration'),
        cell: ({ row }) => h(Badge, { variant: getDurationVariant(row.original.duration) }, () => formatDuration(row.original.duration))
    }),
    columnHelper.accessor('query', {
        header: t('system.security.slowQueries.table.query'),
        cell: ({ row }) => h('div', {
            class: 'max-w-md truncate font-mono text-xs text-muted-foreground',
            title: row.original.query
        }, row.original.query)
    }),
    columnHelper.accessor(row => row.user?.name, {
        id: 'user',
        header: t('system.security.slowQueries.table.user'),
        cell: ({ row }) => h('span', { class: 'text-sm' }, row.original.user?.name || t('common.labels.emptyCell'))
    }),
    columnHelper.accessor('created_at', {
        header: t('system.security.slowQueries.table.date'),
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground whitespace-nowrap' }, formatDate(row.original.created_at))
    }),
];

const table = useVueTable({
    get data() { return props.queries },
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
