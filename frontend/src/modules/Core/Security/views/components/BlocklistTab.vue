<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between pb-4">
      <div>
        <CardTitle class="text-lg">
          {{ $t('system.security.blocklist.title') }}
        </CardTitle>
        <CardDescription>{{ $t('system.security.blocklist.description') }}</CardDescription>
      </div>
      <div class="flex items-center space-x-2">
        <Badge
          v-if="selectedIds.length > 0"
          variant="secondary"
        >
          {{ $t('system.security.bulkActions.selected', { count: selectedIds.length }) }}
        </Badge>
        <Button
          v-if="selectedIds.length > 0"
          variant="outline"
          size="sm"
          @click="$emit('bulk-unblock', selectedIds)"
        >
          <ShieldCheck class="w-4 h-4 mr-2 text-green-500" />
          {{ $t('system.security.bulkActions.unblockSelected') }}
        </Button>
      </div>
    </CardHeader>
    <CardContent class="p-0">
      <DataTable
        :table="table"
        :loading="loading"
        :empty-message="t('system.security.blocklist.empty')"
      />
    </CardContent>
    <Pagination
      v-if="blocklist.length > 0"
      v-model:per-page="perPage"
      :current-page="currentPage"
      :total-items="blocklist.length"
      embedded
      @page-change="(val: number) => currentPage = val"
    />
  </Card>
</template>

<script setup lang="ts">
import { ref, computed, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';
import {
    Card, CardHeader, CardTitle, CardDescription, CardContent,
    Button, Badge, Checkbox, DataTable, Pagination
} from '@/shared/components/ui';

import {
  ShieldCheck,
  Trash2,
} from 'lucide-vue-next';

interface User {
    id: string;
    name: string;
}

interface IpManagementItem {
    id: string | string;
    ip_address: string;
    reason?: string | null;
    creator?: User | null;
    created_at: string;
}

const props = defineProps<{
    blocklist: IpManagementItem[];
    loading: boolean;
}>();

const emit = defineEmits<{
    'remove': [ip: string];
    'move-to-whitelist': [ip: string];
    'bulk-unblock': [ips: string[]];
}>();

const { t } = useI18n();

// Local state
const selectedIds = ref<string[]>([]);
const currentPage = ref(1);
const perPage = ref(10);
const sorting = ref<SortingState>([]);

// Paginated data
const paginatedBlocklist = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = Math.min(start + perPage.value, props.blocklist.length);
    return props.blocklist.slice(start, end);
});

const formatDate = (date: string | null | undefined): string => {
    if (!date) return '-';
    return new Date(date).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// TanStack Table
const columnHelper = createColumnHelper<IpManagementItem>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: () => h(Checkbox, {
            checked: selectedIds.value.length === paginatedBlocklist.value.length && paginatedBlocklist.value.length > 0,
            'onUpdate:checked': (val: boolean) => {
                selectedIds.value = val ? paginatedBlocklist.value.map(item => item.ip_address) : [];
            }
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: selectedIds.value.includes(row.original.ip_address),
            'onUpdate:checked': (val: boolean) => {
                if (val) {
                    selectedIds.value.push(row.original.ip_address);
                } else {
                    selectedIds.value = selectedIds.value.filter(id => id !== row.original.ip_address);
                }
            }
        })
    }),
    columnHelper.accessor('ip_address', {
        header: t('system.security.blocklist.table.ip'),
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.original.ip_address)
    }),
    columnHelper.accessor('reason', {
        header: t('system.security.blocklist.table.reason'),
        cell: ({ row }) => {
            const reason = row.original.reason;
            if (!reason) return h('span', { class: 'text-muted-foreground italic' }, '-');

            try {
                // Check if it's JSON
                if (reason.startsWith('{')) {
                    const data = JSON.parse(reason);
                    if (data.key) {
                        return h('span', { class: 'text-sm' }, t(data.key, data.params || {}));
                    }
                }
            } catch (_e) {
                // Fallback to plain text if not valid JSON or parsing fails
            }

            return h('span', { class: 'max-w-xs truncate text-muted-foreground text-sm' }, reason);
        }
    }),
    columnHelper.accessor(row => row.creator?.name, {
        id: 'creator',
        header: t('system.security.blocklist.table.createdBy'),
        cell: ({ row }) => h('span', { class: 'text-sm' }, row.original.creator?.name || t('common.labels.emptyCell'))
    }),
    columnHelper.accessor('created_at', {
        header: t('system.security.blocklist.table.date'),
        cell: ({ row }) => h('span', { class: 'text-muted-foreground whitespace-nowrap text-sm' }, formatDate(row.original.created_at))
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('system.security.blocklist.table.actions')),
        cell: ({ row }) => h('div', { class: 'flex justify-end gap-2' }, [
            h(Button, {
                variant: 'outline',
                size: 'sm',
                onClick: () => emit('move-to-whitelist', row.original.ip_address),
                class: 'h-8'
            }, () => [
                h(ShieldCheck, { class: 'w-4 h-4 mr-1 text-green-500' }),
                t('system.security.blocklist.actions.moveToWhitelist')
            ]),
            h(Button, {
                variant: 'ghost',
                size: 'icon',
                onClick: () => emit('remove', row.original.ip_address),
                class: 'h-8 w-8 border-destructive/60 text-destructive hover:bg-red-800 hover:text-white'
            }, () => h(Trash2, { class: 'w-4 h-4' }))
        ])
    })
];

const table = useVueTable({
    get data() { return paginatedBlocklist.value },
    columns,
    state: {
        get sorting() { return sorting.value }
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
});

// Expose methods
defineExpose({
    clearSelection: () => { selectedIds.value = []; }
});
</script>
