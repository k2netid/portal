<template>
  <Card>
    <!-- Statistics -->
    <div class="px-6 pt-6 pb-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <div class="bg-muted/30 rounded-lg p-4">
        <p class="text-sm text-muted-foreground">
          {{ $t('system.security.shield.stats.scannersBlocked') }}
        </p>
        <p class="text-2xl font-bold text-rose-600">
          {{ stats.scannersBlocked }}
        </p>
      </div>
      <div class="bg-muted/30 rounded-lg p-4">
        <p class="text-sm text-muted-foreground">
          {{ $t('system.security.shield.stats.extensionsBlocked') }}
        </p>
        <p class="text-2xl font-bold text-pink-600">
          {{ stats.extensionsBlocked }}
        </p>
      </div>
      <div class="bg-muted/30 rounded-lg p-4">
        <p class="text-sm text-muted-foreground">
          {{ $t('system.security.shield.stats.verifications') }}
        </p>
        <p class="text-2xl font-bold text-foreground">
          {{ stats.verifications }}
        </p>
      </div>
      <div class="bg-muted/30 rounded-lg p-4">
        <p class="text-sm text-muted-foreground">
          {{ $t('system.security.shield.stats.failures') }}
        </p>
        <p class="text-2xl font-bold text-orange-600">
          {{ stats.failures }}
        </p>
      </div>
      <div class="bg-muted/30 rounded-lg p-4">
        <p class="text-sm text-muted-foreground">
          {{ $t('system.security.shield.stats.honeypot') }}
        </p>
        <p class="text-2xl font-bold text-red-600">
          {{ stats.honeypot }}
        </p>
      </div>
      <div class="bg-muted/30 rounded-lg p-4 flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">
            {{ $t('system.security.shield.stats.difficulty') }}
          </p>
          <p class="text-2xl font-bold text-blue-600">
            {{ stats.currentDifficulty }}
          </p>
        </div>
        <div
          v-if="stats.isScaling"
          class="h-2 w-2 rounded-full bg-red-500"
          title="Dynamic scaling active"
        />
      </div>
    </div>

    <CardContent class="p-0">
      <DataTable
        :table="table"
        :loading="loading"
        :empty-message="t('system.security.logs.empty')"
      />
    </CardContent>

    <Pagination
      v-if="pagination.total > 0"
      :current-page="pagination.current_page"
      :total-items="pagination.total"
      :per-page="50"
      embedded
      @page-change="(val: number) => $emit('page-change', val)"
    />
  </Card>
</template>

<script setup lang="ts">
import { ref, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { formatSecurityEventLabel } from '@/modules/Core/Security/utils/securityEventLabel';
// import api from '@/engine/api/client';
// import { useToast } from '@/shared/composables/useToast';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';
import {
    Card, CardContent,
    Button, DataTable, Pagination
} from '@/shared/components/ui';

import {
  BotIcon,
  ShieldAlertIcon,
  ShieldBanIcon,
  ShieldIcon,
} from 'lucide-vue-next';

import type { ShieldLog, ShieldStats, PaginationInfo } from '@/engine/types';


const props = defineProps<{
    logs: ShieldLog[];
    stats: ShieldStats;
    loading: boolean;
    pagination: PaginationInfo;
}>();

const emit = defineEmits<{
    'refresh': [];
    'page-change': [page: number];
    'block-ip': [ip: string];
}>();

const { t } = useI18n();
// const toast = useToast();
const sorting = ref<SortingState>([]);

const formatDate = (date: string): string => {
    return new Date(date).toLocaleString(undefined, {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const getEventIcon = (type: string) => {
    if (type === 'shield_verified') return ShieldIcon;
    if (type === 'shield_honeypot') return BotIcon;
    if (type === 'malicious_scanner_blocked' || type === 'malicious_extension_blocked') return ShieldBanIcon;
    return ShieldAlertIcon;
};

const getEventColor = (type: string) => {
    if (type === 'shield_verified') return 'text-emerald-500';
    if (type === 'shield_honeypot') return 'text-red-500';
    if (type === 'malicious_scanner_blocked') return 'text-rose-600';
    if (type === 'malicious_extension_blocked') return 'text-pink-600';
    return 'text-amber-500';
};

// TanStack Table
const columnHelper = createColumnHelper<ShieldLog>();

const columns = [
    columnHelper.accessor('event_type', {
        header: t('system.security.logs.table.event'),
        cell: ({ row }) => h('div', { class: 'flex items-center gap-2' }, [
            h(getEventIcon(row.original.event_type), { class: `w-4 h-4 ${getEventColor(row.original.event_type)}` }),
            h('span', { class: 'text-sm font-medium' }, formatSecurityEventLabel(t, row.original.event_type))
        ])
    }),
    columnHelper.accessor('ip_address', {
        header: t('system.security.shield.table.ip'),
        cell: ({ row }) => h('div', { class: 'flex flex-col' }, [
            h('span', { class: 'font-mono text-sm' }, row.original.ip_address),
            row.original.event_type === 'shield_honeypot' ? h('span', { class: 'text-[10px] text-red-500 uppercase font-bold' }, 'BANNED') : null
        ])
    }),
    columnHelper.accessor('details', {
        header: t('system.security.logs.table.details'),
        cell: ({ row }) => h('div', { class: 'max-w-sm' }, [
            h('span', { class: 'font-mono text-sm text-foreground block truncate', title: row.original.details }, row.original.details || t('common.labels.emptyCell')),
            row.original.user_agent ? h('span', { class: 'text-xs text-muted-foreground block truncate mt-0.5', title: row.original.user_agent }, row.original.user_agent) : null
        ])
    }),
    columnHelper.accessor('created_at', {
        header: t('system.security.shield.table.date'),
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground whitespace-nowrap' }, formatDate(row.original.created_at))
    }),
    columnHelper.display({
        id: 'actions',
        header: t('system.security.logs.table.actions'),
        cell: ({ row }) => h(Button, {
            variant: 'ghost',
            size: 'sm',
            onClick: () => emit('block-ip', row.original.ip_address)
        }, () => t('system.security.logs.actions.blockIp'))
    })
];

const table = useVueTable({
    get data() { return props.logs },
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
