<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { useHead } from '@unhead/vue';
import {
  Archive,
  Calendar,
  CheckCircle2,
  Clock3,
  FileEdit,
  FileText,
  LayoutGrid,
  Pencil,
  Plus,
  RotateCcw,
  Search,
  Trash2,
} from 'lucide-vue-next';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useToast } from '@/shared/composables/useToast';
import api from '@/engine/api/client';
import { parseResponse, parseSingleResponse, ensureArray, type PaginationData } from '@/shared/utils/responseParser';
import { cn } from '@/shared/utils/lib-utils';
import { PageHeader, ConsoleStatCard, ConsoleListCard } from '@/shared/components/shell';
import type { Content } from '@/modules/Content/Publishing/types/content';

// UI Components
import {
    Button,
    Input,
    Badge,
    Checkbox,
    Switch,
    Pagination,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    DataTable
} from '@/shared/components/ui';
import { h } from 'vue';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
    getSortedRowModel,
    type SortingState,
    type RowSelectionState
} from '@tanstack/vue-table';

interface ContentStats {
    total: number;
    published: number;
    draft: number;
    pending: number;
    archived: number;
    trashed: number;
}

interface ContentFilter {
    page: number;
    per_page: string;
    sort: string;
    order: string;
    search?: string;
    status?: string;
    [key: string]: string | number | undefined;
}

interface ConfirmOptions {
    title: string;
    message: string;
    variant?: 'danger' | 'warning' | 'info' | 'success';
    confirmText?: string;
}

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const systemStore = useSystemStore();
const { confirm } = useConfirm();
const toast = useToast();

const props = defineProps<{
    isEmbedded?: boolean;
}>();

if (!props.isEmbedded) {
    useHead({
        title: computed(() => `${systemStore.siteSettings?.site_name || t('app.name')} | ${t('publishing.content.list.title')}`)
    });
}

const loading = ref(true);
const contents = ref<Content[]>([]);
const pagination = ref<PaginationData | null>(null);
const search = ref('');
const statusFilter = ref('all');
const perPage = ref('10');
const selectedContents = ref<string[]>([]);
const bulkAction = ref('');

const isBuilderContent = (content: Content): boolean => {
    return !!(
        (content.meta?.builder_blocks && Array.isArray(content.meta.builder_blocks) && content.meta.builder_blocks.length > 0) ||
        content.meta?.editor_type === 'builder' ||
        (content as any).editor_type === 'builder'
    );
};

const columnHelper = createColumnHelper<Content>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h('div', { class: 'flex items-center justify-center' }, [
            h(Checkbox, {
                id: 'contents-select-all',
                checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
                'onUpdate:checked': (val) => table.toggleAllPageRowsSelected(!!val),
            }),
            h('label', { for: 'contents-select-all', class: 'sr-only' }, t('common.actions.selectAll')),
        ]),
        cell: ({ row }) => h('div', { class: 'flex justify-center' }, [
            h(Checkbox, {
                id: `content-select-${row.original.id}`,
                checked: row.getIsSelected(),
                'onUpdate:checked': (val) => row.toggleSelected(!!val),
            }),
            h('label', {
                for: `content-select-${row.original.id}`,
                class: 'sr-only',
            }, t('common.actions.selectRow')),
        ]),
        size: 50,
    }),
    columnHelper.accessor('title', {
        header: t('common.labels.title'),
        cell: ({ row }) => {
            const content = row.original;
            return h('div', { class: 'flex flex-col gap-0.5' }, [
                h('div', { class: 'flex items-center gap-2' }, [
                    h('span', { class: 'text-sm font-semibold text-foreground group-hover:text-primary transition-colors' }, content.title),
                    content.deleted_at ? h(Badge, { variant: 'destructive', class: 'h-4.5 text-[9px] px-1.5 font-bold tracking-wider' }, t('publishing.content.status.trashed')) : null
                ]),
                h('span', { class: 'text-xs text-muted-foreground font-mono' }, content.slug)
            ]);
        }
    }),
    columnHelper.accessor('author', {
        header: t('common.labels.author'),
        cell: ({ row }) => {
            const author = row.original.author;
            return h('div', { class: 'flex items-center gap-2' }, [
                h('div', { class: 'w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary' }, getUserInitials(author?.name)),
                h('span', { class: 'text-sm text-foreground/80' }, author?.name)
            ]);
        }
    }),
    columnHelper.accessor('status', {
        header: t('common.labels.status'),
        cell: ({ row }) => {
            const status = row.original.status || '';
            return h(Badge, {
                variant: 'outline',
                class: cn('capitalize border-none px-2 py-0.5', getStatusBadgeClass(status))
            }, t(`publishing.content.status.${status}`));
        }
    }),
    columnHelper.display({
        id: 'editor_type',
        header: t('publishing.content.form.editor', 'Editor'),
        cell: ({ row }) => {
            const isBuilder = isBuilderContent(row.original);
            if (isBuilder) {
                return h(Badge, {
                    variant: 'outline',
                    class: 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-200/50 dark:border-indigo-800/50 font-medium px-2 py-0.5 flex items-center gap-1.5 w-fit text-xs'
                }, [
                    h(LayoutGrid, { class: 'w-3 h-3 text-indigo-500 flex-shrink-0' }),
                    h('span', 'Visual Builder')
                ]);
            }
            return h(Badge, {
                variant: 'outline',
                class: 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-200/50 dark:border-slate-800/50 font-medium px-2 py-0.5 flex items-center gap-1.5 w-fit text-xs'
            }, [
                h(FileText, { class: 'w-3 h-3 text-slate-500 flex-shrink-0' }),
                h('span', 'Classic')
            ]);
        }
    }),
    columnHelper.accessor('is_featured', {
        header: t('publishing.content.form.featured'),
        cell: ({ row }) => h('div', { class: 'flex items-center gap-2' }, [
            h(Switch, {
                checked: !!row.original.is_featured,
                'onUpdate:checked': () => toggleFeatured(row.original),
                title: `${t('publishing.content.form.featured')}: ${row.original.title}`,
                'aria-label': `${t('publishing.content.form.featured')}: ${row.original.title}`,
            }),
            h('span', { class: 'sr-only' }, `${t('publishing.content.form.featured')}: ${row.original.title}`),
        ])
    }),
    columnHelper.accessor('created_at', {
        header: t('common.labels.date'),
        cell: ({ row }) => h('div', { class: 'flex items-center gap-1.5 text-xs text-muted-foreground' }, [
            h(Calendar, { class: 'w-3.5 h-3.5' }),
            formatDate(row.original.created_at)
        ])
    }),

    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.actions.title')),
        cell: ({ row }) => {
            const content = row.original;
            return h('div', { class: 'flex justify-end items-center gap-1' }, [
                content.deleted_at 
                    ? [
                        authStore.hasPermission('delete content') && h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-success',
                            onClick: () => handleRestore(content), title: t('common.actions.restore')
                        }, [h(RotateCcw, { class: 'w-4 h-4' })]),
                        authStore.hasPermission('delete content') && h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive',
                            onClick: () => handleForceDelete(content), title: t('common.actions.deletePermanently')
                        }, [h(Trash2, { class: 'w-4 h-4' })])
                    ]
                    : [
                        authStore.hasPermission('edit content') && h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8',
                            onClick: () => handleEdit(content), title: t('common.actions.edit')
                        }, [h(Pencil, { class: 'w-4 h-4' })]),
                        authStore.hasPermission('delete content') && h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive',
                            onClick: () => handleDelete(content), title: t('common.actions.delete')
                        }, [h(Trash2, { class: 'w-4 h-4' })])
                    ]
            ]);
        }
    })
];

const sorting = ref<SortingState>([]);
const rowSelection = ref<RowSelectionState>({});

const table = useVueTable({
    get data() { return contents.value },
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

// Sync selectedContents with rowSelection for bulk actions
watch(rowSelection, (newSelection) => {
    selectedContents.value = Object.keys(newSelection)
        .filter(key => newSelection[key]);
}, { deep: true });

// Clear selection when contents change
watch(contents, () => {
    rowSelection.value = {};
});

const stats = ref<ContentStats>({
    total: 0,
    published: 0,
    draft: 0,
    pending: 0,
    archived: 0,
    trashed: 0
});

const fetchContents = async (page: number = 1) => {
    loading.value = true;
    try {
        const params: ContentFilter = {
            page,
            per_page: perPage.value,
            sort: 'created_at',
            order: 'desc',
        };

        if (search.value) params.search = search.value;
        if (statusFilter.value && statusFilter.value !== 'all') {
            params.status = statusFilter.value;
        }

        const response = await api.get('/manage/publishing/contents', { params });
        const { data, pagination: meta } = parseResponse<Content[]>(response);
        
        contents.value = ensureArray(data);
        pagination.value = meta;
        
    } catch (error: unknown) {
        logger.error('Failed to fetch contents:', error);
        toast.error.action(error);
        contents.value = [];
        pagination.value = null;
    } finally {
        loading.value = false;
    }
};

const fetchStats = async () => {
    try {
        const response = await api.get('/manage/publishing/contents/stats');
        const data = parseSingleResponse<ContentStats>(response);
        stats.value = data || {
            total: 0,
            published: 0,
            draft: 0,
            pending: 0,
            archived: 0,
            trashed: 0
        };
    } catch (error: unknown) {
        logger.error('Failed to fetch stats:', error);
    }
};


const toggleFeatured = async (content: Content) => {
    const previousState = !!content.is_featured;
    content.is_featured = !previousState;

    try {
        await api.patch(`/manage/publishing/contents/${content.id}/toggle-featured`);
        toast.success.action(t('common.messages.success.updated'));
    } catch (error: unknown) {
        content.is_featured = previousState;
        toast.error.action(error);
    }
};

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'published': return 'bg-emerald-600/15 text-emerald-950 dark:text-emerald-100 border-emerald-600/30';
        case 'draft': return 'bg-muted text-foreground/80 border-border/40';
        case 'pending': return 'bg-amber-600/15 text-amber-950 dark:text-amber-100 border-amber-600/30';
        case 'archived': return 'bg-slate-600/15 text-slate-900 dark:text-slate-100 border-slate-500/30';
        case 'trashed': return 'bg-destructive/10 text-destructive border-destructive/20';
        default: return 'bg-muted text-foreground/80 border-border/40';
    }
};

const getUserInitials = (name?: string) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

const handleBulkAction = async (action: string) => {
    if (!action) return;
    
    const confirmConfig: ConfirmOptions = {
        title: t('publishing.content.list.bulkActions'),
        message: t('common.messages.confirm.bulkAction', { action: action, count: selectedContents.value.length }),
    };
    
    if (action === 'delete') {
        confirmConfig.variant = 'danger';
        confirmConfig.confirmText = t('common.actions.delete');
    } else if (action === 'approve') {
        confirmConfig.variant = 'success';
        confirmConfig.confirmText = t('publishing.content.actions.approve');
    } else if (action === 'reject') {
        confirmConfig.variant = 'danger';
        confirmConfig.confirmText = t('publishing.content.actions.reject');
    } else if (action === 'restore') {
        confirmConfig.variant = 'info';
        confirmConfig.confirmText = t('common.actions.restore');
    } else if (action === 'force_delete') {
        confirmConfig.variant = 'danger';
        confirmConfig.confirmText = t('common.actions.deletePermanently');
    }

    const confirmed = await confirm(confirmConfig);

    if (!confirmed) {
        bulkAction.value = '';
        return;
    }

    try {
        await api.post('/manage/publishing/contents/bulk-action', {
            action: action,
            content_ids: selectedContents.value,
        });
        selectedContents.value = []; // Clear selection
        await fetchContents();
        await fetchStats();
        toast.success.update();
        bulkAction.value = '';
    } catch (error: unknown) {
        logger.error('Failed to perform bulk action:', error);
        toast.error.action(error);
    } finally {
        bulkAction.value = '';
    }
};

const handleEmptyTrash = async () => {
    const confirmed = await confirm({
        title: t('publishing.content.actions.emptyTrash'),
        message: t('common.messages.confirm.emptyTrash'),
        confirmText: t('common.actions.deletePermanently'),
        variant: 'danger'
    });

    if (!confirmed) return;

    try {
        await api.delete('/manage/publishing/contents/trash/empty');
        await fetchContents();
        await fetchStats();
        toast.success.action(t('common.messages.success.deleted'));
    } catch (error: unknown) {
        logger.error('Failed to empty trash:', error);
        toast.error.action(error);
    }
};

const handleDelete = async (content: Content) => {
    const confirmed = await confirm({
        title: t('common.actions.delete'),
        message: t('common.messages.confirm.delete', { item: content.title }),
        confirmText: t('common.actions.delete'),
        variant: 'danger'
    });
    if (!confirmed) return;
    try {
        await api.delete(`/manage/publishing/contents/${content.id}`);
        await fetchContents();
        await fetchStats();
    } catch (error: unknown) {
        logger.error('Failed to delete content:', error);
        toast.error.delete(error, content.title);
    }
};

const handleRestore = async (content: Content) => {
    const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: t('common.messages.confirm.restore', { item: content.title }),
        confirmText: t('common.actions.restore'),
        variant: 'info'
    });

    if (!confirmed) return;

    try {
        await api.put(`/manage/publishing/contents/${content.id}/restore`);
        await fetchContents();
        await fetchStats();
        toast.success.action(t('common.messages.success.restored'));
    } catch (error: unknown) {
        logger.error('Failed to restore content:', error);
        toast.error.action(error);
    }
};

const handleForceDelete = async (content: Content) => {
    const confirmed = await confirm({
        title: t('common.actions.deletePermanently'),
        message: t('common.messages.confirm.deletePermanently', { item: content.title }),
        confirmText: t('common.actions.delete'),
        variant: 'danger'
    });
    if (!confirmed) return;
    try {
        await api.delete(`/manage/publishing/contents/${content.id}/force-delete`);
        await fetchContents();
        await fetchStats();
    } catch (error: unknown) {
        logger.error('Failed to force delete content:', error);
        toast.error.delete(error, content.title);
    }
};

const handleEdit = (content: Content) => {
    router.push({ name: 'contents.edit', params: { id: content.id } });
};


const formatDate = (date: string | undefined) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
};

watch([search, statusFilter], () => {
    fetchContents();
});

onMounted(() => {
    if (route.query.q) {
        search.value = route.query.q as string;
    }
    fetchContents();
    fetchStats();
});
</script>

<template>
  <div :class="isEmbedded ? 'space-y-6' : 'space-y-6'">
    <PageHeader
      v-if="!isEmbedded"
      borderless
      :title="t('publishing.content.list.title')"
      :subtitle="t('publishing.content.list.subtitle')"
    >
      <template #actions>
        <Button
          v-if="authStore.hasPermission('create content')"
          size="sm"
          @click="router.push({ name: 'contents.create' })"
        >
          <Plus data-icon="inline-start" class="size-4 shrink-0" />
          {{ t('publishing.content.list.createNew') }}
        </Button>
      </template>
    </PageHeader>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
      <ConsoleStatCard
        :label="t('system.dashboard.stats.totalContents')"
        :value="stats.total || 0"
        :icon="FileText"
        tone="primary"
        :active="statusFilter === 'all'"
        clickable
        @click="statusFilter = 'all'"
      />
      <ConsoleStatCard
        :label="t('publishing.content.status.published')"
        :value="stats.published || 0"
        :icon="CheckCircle2"
        tone="success"
        :active="statusFilter === 'published'"
        clickable
        @click="statusFilter = 'published'"
      />
      <ConsoleStatCard
        :label="t('publishing.content.status.draft')"
        :value="stats.draft || 0"
        :icon="FileEdit"
        tone="muted"
        :active="statusFilter === 'draft'"
        clickable
        @click="statusFilter = 'draft'"
      />
      <ConsoleStatCard
        :label="t('publishing.content.status.pending')"
        :value="stats.pending || 0"
        :icon="Clock3"
        tone="warning"
        :active="statusFilter === 'pending'"
        clickable
        @click="statusFilter = 'pending'"
      />
      <ConsoleStatCard
        :label="t('publishing.content.status.archived')"
        :value="stats.archived || 0"
        :icon="Archive"
        tone="muted"
        :active="statusFilter === 'archived'"
        clickable
        @click="statusFilter = 'archived'"
      />
      <ConsoleStatCard
        :label="t('publishing.content.status.trashed')"
        :value="stats.trashed || 0"
        :icon="Trash2"
        tone="destructive"
        :active="statusFilter === 'trashed'"
        clickable
        @click="statusFilter = 'trashed'"
      />
    </div>

    <ConsoleListCard>
      <template #toolbar>
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:flex-1 lg:min-w-0 w-full">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-1 sm:min-w-0">
            <div class="relative w-full sm:max-w-xs shrink-0">
              <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                v-model="search"
                type="text"
                :placeholder="t('common.actions.search') + '...'"
                class="h-10 w-full pl-9 bg-background"
                :aria-label="t('common.actions.search')"
              />
            </div>
            <Select v-model="statusFilter">
              <SelectTrigger
                class="h-10 w-full sm:w-[180px] shrink-0 bg-background"
                :aria-label="t('publishing.content.list.filterByStatus')"
              >
                <SelectValue :placeholder="t('publishing.content.list.filterByStatus')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('publishing.content.filters.allStatus') }}
                </SelectItem>
                <SelectItem value="published">
                  {{ t('publishing.content.status.published') }}
                </SelectItem>
                <SelectItem value="draft">
                  {{ t('publishing.content.status.draft') }}
                </SelectItem>
                <SelectItem value="pending">
                  {{ t('publishing.content.status.pending') }}
                </SelectItem>
                <SelectItem value="archived">
                  {{ t('publishing.content.status.archived') }}
                </SelectItem>
                <SelectItem value="trashed">
                  {{ t('publishing.content.status.trashed') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex flex-wrap items-center justify-end gap-2 shrink-0">
            <Button
              v-if="statusFilter === 'trashed' && stats.trashed > 0 && selectedContents.length === 0 && authStore.hasPermission('delete content')"
              variant="ghost"
              size="sm"
              class="h-10 border border-red-800/40 bg-red-800 text-white hover:bg-red-900 hover:text-white"
              @click="handleEmptyTrash"
            >
              <Trash2 data-icon="inline-start" class="size-4 shrink-0" />
              {{ t('publishing.content.actions.emptyTrash') }}
            </Button>
            <div
              v-if="selectedContents.length > 0"
              class="flex items-center gap-2 rounded-lg border border-border/50 bg-muted/30 px-3 py-1.5"
            >
              <span class="text-sm font-medium text-foreground whitespace-nowrap">
                {{ t('publishing.content.list.selected', { count: selectedContents.length }) }}
              </span>
              <div class="h-4 w-px bg-border" />
              <Select
                v-model="bulkAction"
                @update:model-value="(val: string) => handleBulkAction(val)"
              >
                <SelectTrigger class="h-8 w-[140px]">
                  <SelectValue :placeholder="t('publishing.content.list.bulkActions')" />
                </SelectTrigger>
                <SelectContent>
                  <template v-if="statusFilter !== 'trashed'">
                    <SelectItem
                      v-if="authStore.hasPermission('approve content')"
                      value="approve"
                      class="text-success"
                    >
                      {{ t('publishing.content.actions.approve') }}
                    </SelectItem>
                    <SelectItem
                      v-if="authStore.hasPermission('approve content')"
                      value="reject"
                      class="text-destructive"
                    >
                      {{ t('publishing.content.actions.reject') }}
                    </SelectItem>
                    <SelectItem
                      v-if="authStore.hasPermission('delete content')"
                      value="delete"
                      class="text-destructive"
                    >
                      {{ t('common.actions.delete') }}
                    </SelectItem>
                  </template>
                  <template v-else>
                    <SelectItem
                      v-if="authStore.hasPermission('delete content')"
                      value="restore"
                      class="text-success"
                    >
                      {{ t('common.actions.restore') }}
                    </SelectItem>
                    <SelectItem
                      v-if="authStore.hasPermission('delete content')"
                      value="force_delete"
                      class="text-destructive"
                    >
                      {{ t('common.actions.deletePermanently') }}
                    </SelectItem>
                  </template>
                </SelectContent>
              </Select>
            </div>
            <Button
              v-if="isEmbedded && authStore.hasPermission('create content')"
              size="sm"
              class="h-10"
              @click="router.push({ name: 'contents.create' })"
            >
              <Plus data-icon="inline-start" class="size-4 shrink-0" />
              {{ t('publishing.content.list.createNew') }}
            </Button>
          </div>
        </div>
      </template>

      <DataTable
        :table="table"
        :loading="loading"
        :empty-message="t('common.messages.empty.default')"
        variant="embedded"
      />

      <template
        v-if="pagination"
        #footer
      >
        <Pagination
          :total-items="pagination.total || 0"
          :per-page="parseInt(perPage) || 10"
          :current-page="pagination.current_page || 1"
          embedded
          @page-change="fetchContents"
          @update:per-page="(val: number) => { perPage = String(val); fetchContents(1); }"
        />
      </template>
    </ConsoleListCard>
  </div>
</template>
