<template>
  <div class="space-y-6">
        <PageHeader
      borderless
      :title="t('newsletter.title')"
      :subtitle="t('newsletter.subtitle')"
    >
      <template #actions>
        <div class="flex gap-2">
                <Button
                  variant="outline"
                  @click="exportCsv"
                >
                  <Download class="w-4 h-4 mr-2" />
                  {{ $t('newsletter.actions.export') }}
                </Button>
              </div>
      </template>
    </PageHeader>

    <ConsoleListCard>
      <template #toolbar>
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:flex-wrap w-full">
        <div class="relative flex-1">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <Input
            v-model="filters.q"
            :placeholder="$t('newsletter.filters.search')"
            class="pl-9"
            @input="debounceSearch"
          />
        </div>
        <Select
          v-model="filters.status"
          @update:model-value="fetchSubscribers"
        >
          <SelectTrigger class="w-full md:w-[200px]">
            <SelectValue :placeholder="$t('newsletter.filters.allStatus')" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">
              {{ $t('newsletter.filters.allStatus') }}
            </SelectItem>
            <SelectItem value="subscribed">
              {{ $t('newsletter.filters.subscribed') }}
            </SelectItem>
            <SelectItem value="unsubscribed">
              {{ $t('newsletter.filters.unsubscribed') }}
            </SelectItem>
          </SelectContent>
        </Select>
        <Select
          v-model="filters.trashed"
          @update:model-value="fetchSubscribers"
        >
          <SelectTrigger class="w-full md:w-[160px]">
            <SelectValue :placeholder="t('common.labels.status')" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="without">
              {{ t('common.labels.activeOnly') }}
            </SelectItem>
            <SelectItem value="with">
              {{ t('common.labels.includesTrashed') }}
            </SelectItem>
            <SelectItem value="only">
              {{ t('common.labels.trashedOnly') }}
            </SelectItem>
          </SelectContent>
        </Select>

        <!-- Bulk Actions -->
        <div
          v-if="selectedIds.length > 0"
          class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10 transition-colors animate-in fade-in slide-in-from-top-1 ml-auto"
        >
          <span class="text-sm font-medium text-primary">
            {{ $t('newsletter.messages.selected', { count: selectedIds.length }) }}
          </span>
          <div class="h-4 w-px bg-primary/20" />
          <Select
            v-model="bulkActionSelection"
            @update:model-value="handleBulkAction"
          >
            <SelectTrigger class="w-[160px] h-8 border-primary/20">
              <SelectValue :placeholder="$t('publishing.content.list.bulkActions')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                value="delete"
                class="text-destructive focus:text-destructive"
              >
                {{ $t('common.actions.delete') }}
              </SelectItem>
              <SelectItem
                value="restore"
                class="text-emerald-600 focus:text-emerald-600"
              >
                {{ $t('common.actions.restore') }}
              </SelectItem>
              <SelectItem
                value="force_delete"
                class="text-destructive focus:text-destructive"
              >
                {{ $t('common.actions.forceDelete') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
      </template>

        <DataTable
          :table="table"
          :loading="loading"
          :empty-message="$t('newsletter.messages.empty')"
          variant="embedded"
        />

      <template #footer>
        <Pagination
          embedded
          v-if="pagination && pagination.total > 0"
          :current-page="pagination.current_page"
          :total-items="pagination.total"
          :per-page="Number(pagination.per_page || 15)"
          :show-page-numbers="true"
          @page-change="changePage"
          @update:per-page="changePerPage"
        />
      </template>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { PageHeader } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { NewsletterService } from '@/modules/Intelligence/Newsletter/services/newsletterService';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { parseResponse } from '@/shared/utils/responseParser';
import { Badge, Button, Checkbox, Input, Pagination, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, DataTable } from '@/shared/components/ui';

import { h } from 'vue';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
    getSortedRowModel,
    type SortingState
} from '@tanstack/vue-table';

import {
  Download,
  RotateCcw,
  Search,
  Trash2,
} from 'lucide-vue-next';
import { debounce } from '@/shared/utils/debounce';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();

interface Subscriber {
    id: string | string;
    name: string | null;
    email: string;
    status: 'subscribed' | 'unsubscribed';
    created_at: string;
    source: string | null;
    deleted_at: string | null;
}

interface NewsletterPagination {
    current_page: number;
    total: number;
    per_page: number | string;
    last_page: number;
}

const loading = ref(false);
const subscribers = ref<Subscriber[]>([]);
const pagination = ref<NewsletterPagination | Record<string, never>>({});
const filters = ref({
    status: 'all',
    q: '',
    trashed: 'without',
    page: 1,
    per_page: 15,
});

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const columnHelper = createColumnHelper<Subscriber>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:checked': (val) => table.toggleAllPageRowsSelected(!!val),
            'aria-label': t('common.actions.selectAll'),
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: row.getIsSelected(),
            'onUpdate:checked': (val) => row.toggleSelected(!!val),
            'aria-label': t('common.actions.selectRow') + ': ' + row.original.email || row.original.name,
        }),
        size: 50,
    }),
    columnHelper.accessor('name', {
        header: t('newsletter.table.subscriber'),
        cell: ({ row }) => {
            const subscriber = row.original;
            const initial = (subscriber.name || subscriber.email).charAt(0).toUpperCase();
            
            return h('div', { class: 'flex items-center' }, [
                h('div', { class: 'flex-shrink-0 h-9 w-9' }, [
                    h('div', { class: 'h-9 w-9 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold text-xs border border-primary/20' }, initial)
                ]),
                h('div', { class: 'ml-4' }, [
                    h('div', { class: 'text-sm font-medium text-foreground flex items-center gap-2' }, [
                        subscriber.name || t('newsletter.messages.noName'),
                        subscriber.deleted_at ? h(Badge, { variant: 'destructive', class: 'h-4.5 text-[10px] px-1.5 uppercase font-bold tracking-wider' }, t('common.labels.deleted')) : null
                    ]),
                    h('div', { class: 'text-xs text-muted-foreground' }, subscriber.email)
                ])
            ]);
        }
    }),
    columnHelper.accessor('status', {
        header: t('newsletter.table.status'),
        cell: ({ row }) => {
            const status = row.original.status;
            return h(Badge, {
                variant: 'outline',
                class: status === 'subscribed' ? 'bg-green-500/10 text-green-500 border-green-500/20' : 'bg-red-500/10 text-red-500 border-red-500/20'
            }, t(`newsletter.filters.${status}`));
        }
    }),
    columnHelper.accessor('created_at', {
        header: t('newsletter.table.joinedAt'),
        cell: ({ row }) => h('span', { class: 'text-xs text-muted-foreground' }, formatDate(row.original.created_at))
    }),
    columnHelper.accessor('source', {
        header: t('newsletter.table.source'),
        cell: ({ row }) => h('span', { class: 'text-xs text-muted-foreground truncated max-w-[150px]', title: row.original.source || '' }, row.original.source || '-')
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-center' }, t('newsletter.table.actions')),
        cell: ({ row }) => {
            const subscriber = row.original;
            return h('div', { class: 'flex items-center justify-center gap-1' }, [
                subscriber.deleted_at && h(Button, {
                    variant: 'ghost', size: 'icon', onClick: () => restoreSubscriber(subscriber),
                    class: 'h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-500/10',
                    title: t('common.actions.restore')
                }, [h(RotateCcw, { class: 'w-4 h-4' })]),
                h(Button, {
                    variant: 'ghost', size: 'icon', onClick: () => deleteSubscriber(subscriber),
                    class: 'h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10',
                    title: subscriber.deleted_at ? t('common.actions.forceDelete') : t('newsletter.actions.delete')
                }, [h(Trash2, { class: 'w-4 h-4' })])
            ]);
        }
    })
];

const sorting = ref<SortingState>([]);

const table = useVueTable({
    get data() { return subscribers.value },
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

const fetchSubscribers = async () => {
    loading.value = true;
    try {
        const params: Record<string, string | number> = {};
        
        Object.entries(filters.value).forEach(([key, value]) => {
            if (key === 'status' && value === 'all') return;
            if (key === 'trashed' && value === 'without') return;
            params[key] = value;
        });

        const response = await NewsletterService.listSubscribers(params);
        const { data, pagination: pag } = parseResponse(response);
        subscribers.value = data as Subscriber[];
        if (pag) {
            pagination.value = pag as NewsletterPagination;
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch subscribers:', error);
    } finally {
        loading.value = false;
    }
};

const debounceSearch = debounce(() => {
    filters.value.page = 1;
    fetchSubscribers();
}, 300);

const changePage = (page: number) => {
    if (page < 1 || (pagination.value as NewsletterPagination).last_page && page > (pagination.value as NewsletterPagination).last_page) return;
    filters.value.page = page;
    fetchSubscribers();
};

const changePerPage = (perPage: number) => {
    filters.value.per_page = perPage;
    filters.value.page = 1;
    fetchSubscribers();
};

const deleteSubscriber = async (subscriber: Subscriber) => {
    const isTrashed = !!subscriber.deleted_at;
    const confirmed = await confirm({
        title: isTrashed ? t('common.actions.forceDelete') : t('newsletter.actions.delete'),
        message: isTrashed 
            ? t('newsletter.confirm.forceDelete', { email: subscriber.email })
            : t('newsletter.confirm.delete', { email: subscriber.email }),
        variant: 'danger',
        confirmText: isTrashed ? t('common.actions.forceDelete') : t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        if (isTrashed) {
             await NewsletterService.forceDeleteSubscriber(String(subscriber.id));
             toast.success.action(t('common.messages.success.deleted', { item: t('newsletter.title_singular') }));
        } else {
            await NewsletterService.deleteSubscriber(String(subscriber.id));
            toast.success.delete(t('newsletter.title_singular'));
        }
        fetchSubscribers();
    } catch (error: unknown) {
        logger.error('Failed to delete subscriber:', error);
        toast.error.delete(error, t('newsletter.title_singular'));
    }
};

const restoreSubscriber = async (subscriber: Subscriber) => {
    const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: t('newsletter.confirm.restore', { email: subscriber.email }),
        variant: 'info',
        confirmText: t('common.actions.restore'),
    });

    if (!confirmed) return;

    try {
        await NewsletterService.restoreSubscriber(String(subscriber.id));
        toast.success.restore('Subscriber');
        fetchSubscribers();
    } catch (error: unknown) {
         logger.error('Failed to restore subscriber:', error);
        toast.error.fromResponse(error);
    }
};

const exportCsv = async () => {
    try {
        const response = await NewsletterService.exportSubscribers({
            params: { status: filters.value.status },
            responseType: 'blob',
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data as any]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `subscribers-${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        toast.success.action(t('common.messages.success.exported'));
    } catch (error: unknown) {
        logger.error('Failed to export subscribers:', error);
        toast.error.fromResponse(error);
    }
};


const bulkActionSelection = ref('');

const handleBulkAction = async (value: string) => {
    if (!value) return;
    await bulkAction(value);
    bulkActionSelection.value = '';
};

const selectedIds = ref<(string | number)[]>([]);


const bulkAction = async (action: string) => {
    if (selectedIds.value.length === 0) return;
    
    if (action === 'delete' || action === 'force_delete') {
         const isForce = action === 'force_delete';
         const confirmed = await confirm({
             title: isForce ? t('common.actions.forceDelete') : t('newsletter.actions.delete'),
             message: isForce
                ? t('newsletter.confirm.bulkForceDelete', { count: selectedIds.value.length })
                : t('common.messages.confirm.bulkDelete', { count: selectedIds.value.length }),
             variant: 'danger',
             confirmText: isForce ? t('common.actions.forceDelete') : t('common.actions.delete'),
         });

         if (!confirmed) {
             bulkActionSelection.value = '';
             return;
         }

         try {
             await NewsletterService.bulkSubscribers({
                 ids: selectedIds.value,
                 action: action
             });
             selectedIds.value = [];
             await fetchSubscribers();
             toast.success.action(t('common.messages.success.deleted', { item: 'Subscribers' }));
         } catch (error: unknown) {
             toast.error.action(error);
         }
    } else if (action === 'restore') {
         try {
             await NewsletterService.bulkSubscribers({
                 ids: selectedIds.value,
                 action: 'restore'
             });
             selectedIds.value = [];
             await fetchSubscribers();
             toast.success.restore('Subscribers');
         } catch (error: unknown) {
             toast.error.action(error);
         }
    } else if (action === 'unsubscribe' || action === 'subscribe') {
         try {
             await NewsletterService.bulkSubscribers({
                 ids: selectedIds.value,
                 action: action
             });
             selectedIds.value = [];
             await fetchSubscribers();
             toast.success.action(t('common.messages.success.updated'));
         } catch (error: unknown) {
             toast.error.action(error);
         }
    }
};

onMounted(() => {
    fetchSubscribers();
});
</script>
