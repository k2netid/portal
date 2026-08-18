<template>
  <div class="space-y-6">
    <PageHeader
      v-if="!isEmbedded"
      :title="t('library.tags.title')"
      :subtitle="t('library.tags.description')"
      borderless
    >
      <template #actions>
        <Button
          v-if="authStore.hasPermission('create tags')"
          size="sm"
          class="h-10 inline-flex items-center gap-2"
          @click="openCreateModal"
        >
          <Plus
            data-icon="inline-start"
            class="size-4 shrink-0"
          />
          {{ t('library.tags.createNew') }}
        </Button>
      </template>
    </PageHeader>

    <div
      v-if="statistics && !isEmbedded"
      class="grid grid-cols-1 gap-4 md:grid-cols-3"
    >
      <ConsoleStatCard
        :label="t('library.tags.stats.total')"
        :value="statistics.total_tags || 0"
        :icon="TagIcon"
        tone="primary"
      />
      <ConsoleStatCard
        :label="t('library.tags.stats.used')"
        :value="statistics.used_tags || 0"
        :icon="BarChart3"
        tone="success"
      />
      <ConsoleStatCard
        :label="t('library.tags.stats.usage')"
        :value="statistics.total_usage || 0"
        :icon="MousePointer2"
        tone="info"
      />
    </div>

    <ConsoleListCard>
      <template #toolbar>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-1 sm:min-w-0">
          <div class="relative w-full sm:max-w-xs shrink-0">
            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input
              v-model="search"
              type="text"
              :placeholder="t('library.tags.search')"
              :aria-label="t('library.tags.search')"
              class="h-10 w-full pl-9 bg-background"
              @input="onSearchInput"
            />
          </div>
          <Select
            v-model="filterUsage"
            @update:model-value="fetchTags(1)"
          >
            <SelectTrigger class="h-10 w-full sm:w-[150px] shrink-0 bg-background" :aria-label="t('common.labels.status')">
              <SelectValue :placeholder="t('library.tags.filters.usage')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">
                {{ t('library.tags.filters.all') }}
              </SelectItem>
              <SelectItem value="used">
                {{ t('library.tags.filters.used') }}
              </SelectItem>
              <SelectItem value="unused">
                {{ t('library.tags.filters.unused') }}
              </SelectItem>
            </SelectContent>
          </Select>
          <Select
            v-if="!isEmbedded && !scope"
            v-model="filterType"
            @update:model-value="fetchTags(1)"
          >
            <SelectTrigger
              class="h-10 w-full sm:w-[150px] shrink-0 bg-background"
              :aria-label="t('library.tags.filters.type')"
            >
              <SelectValue :placeholder="t('library.tags.filters.allTypes')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">
                {{ t('library.tags.filters.allTypes') }}
              </SelectItem>
              <SelectItem value="content">
                {{ t('library.tags.filters.content') }}
              </SelectItem>
              <SelectItem value="media">
                {{ t('library.tags.filters.media') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2 shrink-0">
          <div
            v-if="selectedIds.length > 0"
            class="flex items-center gap-2 rounded-lg border border-border/50 bg-muted/30 px-3 py-1.5"
          >
            <span class="text-sm font-medium text-foreground whitespace-nowrap">
              {{ selectedIds.length }} {{ t('common.labels.selected') }}
            </span>
            <div class="h-4 w-px bg-border" />
            <Button
              variant="ghost"
              size="sm"
              class="h-8 text-destructive hover:bg-destructive/10"
              @click="bulkDelete"
            >
              <Trash2 class="h-4 w-4 mr-1.5" />
              {{ t('common.actions.delete') }}
            </Button>
          </div>
          <Button
            v-if="isEmbedded && authStore.hasPermission('create tags')"
            size="sm"
            class="h-10 inline-flex items-center gap-2"
            @click="openCreateModal"
          >
            <Plus
              data-icon="inline-start"
              class="size-4 shrink-0"
            />
            {{ t('library.tags.createNew') }}
          </Button>
        </div>
      </template>

      <DataTable
        :table="table"
        :loading="loading"
        :empty-message="t('library.tags.empty')"
        variant="embedded"
      />

      <template
        v-if="pagination && pagination.total > 0"
        #footer
      >
        <Pagination
          :current-page="pagination.current_page"
          :total-items="pagination.total"
          :per-page="Number(pagination.per_page)"
          :show-page-numbers="true"
          embedded
          @page-change="changePage"
          @update:per-page="changePerPage"
        />
      </template>
    </ConsoleListCard>

    <TagFormModal
      v-model:open="showModal"
      :tag="editingTag"
      :scope="scope"
      @success="handleModalSuccess"
    />
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleStatCard, ConsoleListCard } from '@/shared/components/shell';
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
// import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import {
  BarChart3,
  Edit,
  MousePointer2,
  Plus,
  Search,
  Tag as TagIcon,
  Trash2,
} from 'lucide-vue-next';
import { useLibraryStore } from '@/modules/Content/Library/stores/library';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useToast } from '@/shared/composables/useToast';
import { debounce } from '@/shared/utils/debounce';
import TagFormModal from './TagFormModal.vue';

// UI Components
import {
    Button,
    Input,
    Badge,
    Checkbox,
    Pagination,
    DataTable,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
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

import { useAuthStore } from '@/modules/Core/System/stores/auth';
import type { Tag } from '@/modules/Content/Library/types/taxonomy';

const props = defineProps<{
    isEmbedded?: boolean;
    scope?: string;
}>();

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const authStore = useAuthStore();
const libraryStore = useLibraryStore();

const search = ref('');
const filterUsage = ref('all');
const filterType = ref('all');
const selectedIds = ref<string[]>([]);

// Computed
const tags = computed(() => libraryStore.tags);
const loading = computed(() => libraryStore.loading);
const statistics = computed(() => libraryStore.statistics);
const pagination = computed(() => libraryStore.pagination || {
    current_page: 1,
    per_page: 20,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0
});

// Modal State
const showModal = ref(false);
const editingTag = ref<Tag | null>(null);

const columnHelper = createColumnHelper<Tag>();

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
        header: t('library.tags.table.name'),
        cell: ({ row }) => h('div', { class: 'font-medium' }, [
            row.original.name,
            h('div', { class: 'md:hidden text-xs text-muted-foreground mt-1' }, row.original.slug)
        ])
    }),
    columnHelper.accessor('slug', {
        header: t('library.tags.table.slug'),
        cell: ({ row }) => h('span', { class: 'text-muted-foreground' }, row.original.slug),
        meta: { className: 'hidden md:table-cell' }
    }),
    columnHelper.accessor('type', {
        id: 'type',
        header: () => t('library.tags.table.type'),
        cell: ({ row }) => h(Badge, { variant: 'outline', class: 'capitalize' }, row.original.type || 'global'),
        meta: { className: 'hidden md:table-cell' }
    }),
    columnHelper.accessor('description', {
        header: t('library.tags.table.description'),
        cell: ({ row }) => h('div', { class: 'max-w-[300px] truncate', title: row.original.description }, row.original.description || '-'),
        meta: { className: 'hidden lg:table-cell' }
    }),
    columnHelper.accessor('contents_count', {
        header: () => t('library.tags.table.usage'),
        cell: ({ row }) => h(Badge, { variant: 'secondary', class: 'font-mono' }, String(row.original.contents_count || 0))
    }),
    columnHelper.display({
        id: 'actions',
        header: () => t('library.tags.table.actions'),
        cell: ({ row }) => h('div', { class: 'flex justify-start gap-1' }, [
            authStore.hasPermission('edit tags') && h(Button, {
                variant: 'ghost', size: 'icon', class: 'h-8 w-8',
                'aria-label': t('common.actions.edit'),
                onClick: () => openEditModal(row.original)
            }, [h(Edit, { class: 'w-4 h-4' })]),
            authStore.hasPermission('delete tags') && h(Button, {
                variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10',
                'aria-label': t('common.actions.delete'),
                onClick: () => deleteTag(row.original)
            }, [h(Trash2, { class: 'w-4 h-4' })])
        ])
    })
];

const sorting = ref<SortingState>([]);
const rowSelection = ref<RowSelectionState>({});

const table = useVueTable({
    get data() { return tags.value },
    columns,
    state: {
        get sorting() { return sorting.value },
        get rowSelection() { return rowSelection.value },
        columnVisibility: { type: !props.isEmbedded && !props.scope },
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

// Sync selectedIds with rowSelection for bulk actions
watch(rowSelection, (newSelection) => {
    selectedIds.value = Object.keys(newSelection)
        .filter(key => newSelection[key]);
}, { deep: true });

// Clear selection when tags change
watch(tags, () => {
    rowSelection.value = {};
});

const onSearchInput = debounce(() => {
    fetchTags(1);
}, 500);

const fetchTags = async (page = 1) => {
    try {
        const params: Record<string, unknown> = {
            page: page,
            per_page: pagination.value.per_page,
            search: search.value,
        };

        if (filterUsage.value !== 'all') {
            params.usage = filterUsage.value;
        }

        if (props.scope) {
            params.type = props.scope;
        } else if (filterType.value !== 'all') {
            params.type = filterType.value;
        }

        await libraryStore.fetchTags(params);
        await libraryStore.fetchStatistics();
    } catch (error: unknown) {
        logger.error('Failed to fetch tags:', error);
    }
};

const changePage = (page: number) => {
    if (page >= 1 && page <= (pagination.value.last_page || 1)) {
        fetchTags(page);
    }
};

const changePerPage = (perPage: number) => {
    pagination.value.per_page = perPage;
    fetchTags(1);
};


const bulkDelete = async () => {
    if (selectedIds.value.length === 0) return;
    
    const confirmed = await confirm({
        title: t('library.tags.actions.bulkDelete'),
        message: t('common.messages.confirm.bulkDelete', { count: selectedIds.value.length }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.post('/manage/library/tags/bulk-delete', { ids: selectedIds.value });
        selectedIds.value = [];
        await fetchTags(pagination.value.current_page);
        toast.success.delete(t('library.tags.title', { count: 2 }));
    } catch (error: unknown) {
        logger.error('Bulk delete failed:', error);
        toast.error.fromResponse(error);
    }
};

// Modal Actions
const openCreateModal = () => {
    editingTag.value = null;
    showModal.value = true;
};

const openEditModal = (tag: Tag) => {
    editingTag.value = { ...tag };
    showModal.value = true;
};

const handleModalSuccess = () => {
    fetchTags(pagination.value.current_page);
};

const deleteTag = async (tag: Tag) => {
    const confirmed = await confirm({
        title: t('library.tags.actions.deleteTitle'),
        message: t('library.tags.messages.deleteConfirm', { name: tag.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await libraryStore.deleteTag(tag.id);
        await fetchTags();
        toast.success.delete(t('library.tags.title_singular'));
    } catch (error: unknown) {
        logger.error('Failed to delete tag:', error);
        toast.error.delete(error, t('library.tags.title_singular'));
    }
};

onMounted(() => {
    fetchTags();
});
</script>
