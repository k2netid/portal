<script setup lang="ts">
import { ref, onMounted, watch, h } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  CheckCircle2,
  FileText,
  Pencil,
  Plus,
  RotateCcw,
  Search,
  Trash2,
} from 'lucide-vue-next';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useMenuDeleteConfirm } from '@/modules/Content/Layout/composables/useMenuDeleteConfirm';
import { parseResponse, ensureArray, type PaginationData } from '@/shared/utils/responseParser';
import { cn } from '@/shared/utils/lib-utils';

// UI Components
import { ConsoleStatCard, ConsoleListCard } from '@/shared/components/shell';
import {
    Button,
    Input,
    Badge,
    Checkbox,
    Pagination,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    DataTable
} from '@/shared/components/ui';

import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
    getSortedRowModel,
    type SortingState,
    type RowSelectionState
} from '@tanstack/vue-table';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const { confirmMenuDelete } = useMenuDeleteConfirm();

const emit = defineEmits<{
    (e: 'select-menu', id: string): void;
    (e: 'create-menu'): void;
}>();

interface Menu {
    id: string;
    name: string;
    slug: string;
    location: string;
    is_active: boolean;
    items_count?: number;
    deleted_at?: string | null;
    created_at: string;
}

const loading = ref(true);
const menus = ref<Menu[]>([]);
const pagination = ref<PaginationData | null>(null);
const search = ref('');
const statusFilter = ref('without'); // without, only
const perPage = ref('10');
const rowSelection = ref<RowSelectionState>({});
const bulkAction = ref('');
const trashedCount = ref(0);

const columnHelper = createColumnHelper<Menu>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            'aria-label': t('common.actions.selectAll'),
            checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:checked': (val) => table.toggleAllPageRowsSelected(!!val),
        }),
        cell: ({ row }) => h(Checkbox, {
            'aria-label': t('common.actions.selectRow'),
            checked: row.getIsSelected(),
            'onUpdate:checked': (val) => row.toggleSelected(!!val),
        }),
        size: 50,
    }),
    columnHelper.accessor('name', {
        header: t('layout.menus.form.name'),
        cell: ({ row }) => {
            const menu = row.original;
            return h('div', { class: 'flex flex-col gap-0.5' }, [
                h('div', { class: 'flex items-center gap-2 font-medium cursor-pointer hover:text-primary transition-colors', onClick: () => emit('select-menu', menu.id) }, [
                    menu.name,
                    menu.deleted_at ? h(Badge, { variant: 'destructive', class: 'h-4 text-[9px] px-1' }, t('common.labels.trashed')) : null
                ]),
                h('span', { class: 'text-[10px] text-muted-foreground font-mono' }, menu.slug)
            ]);
        }
    }),
    columnHelper.accessor('location', {
        header: t('layout.menus.form.location'),
        cell: ({ row }) => {
            const loc = row.original.location;
            if (!loc || loc === 'none') return h('span', { class: 'text-muted-foreground italic' }, t('layout.menus.form.placeholders.none'));
            return h(Badge, { variant: 'secondary', class: 'capitalize' }, loc.replace(/_/g, ' '));
        }
    }),
    columnHelper.accessor('items_count', {
        header: t('layout.menus.headers.items'),
        cell: ({ row }) => h(Badge, { variant: 'outline' }, `${row.original.items_count ?? 0} ${t('layout.menus.headers.items').toLowerCase()}`)
    }),
    columnHelper.accessor('is_active', {
        header: t('common.labels.status'),
        cell: ({ row }) => {
            const active = row.original.is_active;
            return h('div', { class: 'flex items-center gap-1.5' }, [
                h('div', { class: cn('w-2 h-2 rounded-full', active ? 'bg-success' : 'bg-muted-foreground/30') }),
                h('span', { class: 'text-xs' }, active ? t('common.labels.active') : t('common.labels.inactive'))
            ]);
        }
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.actions.title')),
        cell: ({ row }) => {
            const menu = row.original;
            return h('div', { class: 'flex justify-end gap-1' }, [
                menu.deleted_at 
                    ? [
                        h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-success',
                            onClick: () => handleRestore(menu), 'aria-label': t('common.actions.restore')
                        }, [h(RotateCcw, { class: 'w-4 h-4' })]),
                        h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive',
                            onClick: () => handleForceDelete(menu), 'aria-label': t('common.actions.deletePermanently')
                        }, [h(Trash2, { class: 'w-4 h-4' })])
                    ]
                    : [
                        h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8',
                            onClick: () => emit('select-menu', menu.id), 'aria-label': t('common.actions.edit')
                        }, [h(Pencil, { class: 'w-4 h-4' })]),
                        h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive',
                            onClick: () => handleDelete(menu), 'aria-label': t('common.actions.delete')
                        }, [h(Trash2, { class: 'w-4 h-4' })])
                    ]
            ]);
        }
    })
];

const sorting = ref<SortingState>([]);

const table = useVueTable({
    get data() { return menus.value },
    columns,
    state: {
        get sorting() { return sorting.value },
        get rowSelection() { return rowSelection.value },
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    onRowSelectionChange: updaterOrValue => {
        rowSelection.value = typeof updaterOrValue === 'function' ? updaterOrValue(rowSelection.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
    enableRowSelection: true,
});

const fetchMenus = async (page: number = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/manage/layout/menus', {
            params: {
                page,
                per_page: perPage.value,
                search: search.value,
                trashed: statusFilter.value
            }
        });
        
        const { data, pagination: meta } = parseResponse<Menu[]>(response);
        menus.value = ensureArray(data);
        pagination.value = meta;
        trashedCount.value = response.data?.meta?.trashed_count ?? 0;
        rowSelection.value = {}; // Reset selection
    } catch (error) {
        toast.error.action(error);
    } finally {
        loading.value = false;
    }
};

const handleDelete = async (menu: Menu) => {
    const { confirmed } = await confirmMenuDelete(menu.id, menu.name, 'soft');
    if (!confirmed) return;
    try {
        await api.delete(`/manage/layout/menus/${menu.id}`);
        toast.success.delete(t('layout.menus.title'));
        fetchMenus();
    } catch (error) { toast.error.action(error); }
};

const handleRestore = async (menu: Menu) => {
    const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: t('layout.menus.messages.restoreConfirm', { name: menu.name }),
        variant: 'info'
    });
    if (confirmed) {
        try {
            await api.post(`/manage/layout/menus/${menu.id}/restore`);
            toast.success.restore(t('layout.menus.title'));
            fetchMenus();
        } catch (error) { toast.error.action(error); }
    }
};

const handleForceDelete = async (menu: Menu) => {
    const { confirmed, blocked } = await confirmMenuDelete(menu.id, menu.name, 'force');
    if (!confirmed) return;
    try {
        await api.delete(`/manage/layout/menus/${menu.id}/force-delete`);
        toast.success.delete(t('layout.menus.title'));
        fetchMenus();
    } catch (error) {
        if (blocked) return;
        toast.error.action(error);
    }
};

const handleBulkAction = async () => {
    if (!bulkAction.value) return;
    const selectedIds = Object.keys(rowSelection.value).map(Number);
    if (selectedIds.length === 0) return;

    const confirmed = await confirm({
        title: t('common.actions.bulkAction'),
        message: t('common.messages.confirm.bulkAction', { action: bulkAction.value, count: selectedIds.length }),
        variant: bulkAction.value.includes('delete') ? 'danger' : 'info'
    });

    if (confirmed) {
        try {
            await api.post('/manage/layout/menus/bulk-action', {
                action: bulkAction.value,
                menu_ids: selectedIds
            });
            toast.success.action(t('common.messages.success.action'));
            bulkAction.value = '';
            fetchMenus();
        } catch (error) { toast.error.action(error); }
    }
};

let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(statusFilter, () => fetchMenus(1));

watch(search, () => {
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => fetchMenus(1), 300);
});

onMounted(() => fetchMenus());
</script>

<template>
  <div class="flex flex-col gap-6">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
      <ConsoleStatCard
        :label="t('layout.menus.headers.totalMenus')"
        :value="pagination?.total || 0"
        :icon="FileText"
        tone="primary"
        :active="statusFilter === 'without'"
        clickable
        @click="statusFilter = 'without'"
      />
      <ConsoleStatCard
        :label="t('layout.menus.headers.activeLocations')"
        :value="menus.filter(m => m.location && m.location !== 'none').length"
        :icon="CheckCircle2"
        tone="success"
      />
      <ConsoleStatCard
        :label="t('common.labels.trashed')"
        :value="trashedCount"
        :icon="Trash2"
        tone="destructive"
        :active="statusFilter === 'only'"
        clickable
        @click="statusFilter = 'only'"
      />
    </div>

    <ConsoleListCard>
      <template #toolbar>
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between w-full">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-1 sm:min-w-0">
          <div class="relative w-full sm:max-w-xs shrink-0">
            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input
              v-model="search"
              :placeholder="t('common.actions.search')"
              class="h-10 w-full pl-9 bg-background"
            />
          </div>
          <Select v-model="statusFilter">
            <SelectTrigger class="h-10 w-full sm:w-[200px] shrink-0 bg-background" :aria-label="t('common.labels.status')">
              <SelectValue :placeholder="t('common.labels.status')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="without">
                {{ t('layout.menus.filters.notTrashed') }}
              </SelectItem>
              <SelectItem value="only">
                {{ t('layout.menus.filters.trashedOnly') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 shrink-0">
          <div
            v-if="Object.keys(rowSelection).length > 0"
            class="flex items-center gap-2 rounded-lg border border-border/50 bg-muted/30 px-3 py-1.5"
          >
            <span class="text-sm font-medium text-foreground">{{ t('common.messages.selectedItems', { count: Object.keys(rowSelection).length }) }}</span>
            <div class="h-4 w-px bg-border" />
            <Select
              v-model="bulkAction"
              @update:model-value="handleBulkAction"
            >
              <SelectTrigger class="h-8 w-[140px]" :aria-label="t('common.actions.bulkAction')">
                <SelectValue :placeholder="t('common.actions.bulkAction')" />
              </SelectTrigger>
              <SelectContent>
                <template v-if="statusFilter !== 'only'">
                  <SelectItem value="delete" class="text-destructive">
                    {{ t('common.actions.delete') }}
                  </SelectItem>
                </template>
                <template v-else>
                  <SelectItem value="restore" class="text-success">
                    {{ t('common.actions.restore') }}
                  </SelectItem>
                  <SelectItem value="force_delete" class="text-destructive">
                    {{ t('common.actions.deletePermanently') }}
                  </SelectItem>
                </template>
              </SelectContent>
            </Select>
          </div>

          <Button size="sm" class="h-10 shrink-0 inline-flex items-center gap-2" @click="emit('create-menu')">
            <Plus data-icon="inline-start" class="size-4 shrink-0" />
            {{ t('layout.menus.actions.create') }}
          </Button>
        </div>
      </div>
      </template>

      <DataTable
          :table="table"
          :loading="loading"
          :empty-message="t('layout.menus.messages.empty')"
          variant="embedded"
        />

      <template
        v-if="pagination"
        #footer
      >
        <Pagination
          :total-items="pagination.total"
          :per-page="parseInt(perPage)"
          :current-page="pagination.current_page"
          embedded
          @page-change="fetchMenus"
          @update:per-page="(val) => { perPage = String(val); fetchMenus(1); }"
        />
      </template>
    </ConsoleListCard>
  </div>
</template>

