<template>
  <div class="space-y-6">
    <PageHeader
      v-if="!isEmbedded"
      borderless
      :title="t('publishing.categories.title')"
      :subtitle="t('publishing.categories.description')"
    >
      <template #actions>
        <Button
          v-if="authStore.hasPermission('create categories')"
          size="sm"
          @click="openCreateModal"
        >
          <Plus data-icon="inline-start" class="size-4 shrink-0" />
          {{ t('publishing.categories.createNew') }}
        </Button>
      </template>
    </PageHeader>

    <ConsoleListCard>
      <template #toolbar>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-1 sm:min-w-0">
          <div class="relative w-full sm:max-w-xs shrink-0">
            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input
              v-model="search"
              :placeholder="t('publishing.categories.search')"
              :aria-label="t('publishing.categories.search')"
              class="h-10 w-full pl-9 bg-background"
            />
          </div>
          <Select
            v-model="statusFilter"
            @update:model-value="onFilterChange"
          >
            <SelectTrigger class="h-10 w-full sm:w-[160px] shrink-0 bg-background" :aria-label="t('common.labels.status')">
              <SelectValue :placeholder="t('common.labels.status')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">
                {{ t('publishing.categories.filters.all') }}
              </SelectItem>
              <SelectItem value="active">
                {{ t('publishing.categories.status.active') }}
              </SelectItem>
              <SelectItem value="inactive">
                {{ t('publishing.categories.status.inactive') }}
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
              @click="confirmBulkDelete"
            >
              <Trash2 data-icon="inline-start" class="size-4 shrink-0" />
              {{ t('common.actions.delete') }}
            </Button>
          </div>
          <Button
            v-if="isEmbedded && authStore.hasPermission('create categories')"
            class="h-10"
            @click="openCreateModal"
          >
            <Plus data-icon="inline-start" class="size-4 shrink-0" />
            {{ t('publishing.categories.createNew') }}
          </Button>
        </div>
      </template>

      <DataTable
        :table="table"
        :loading="loading"
        :empty-message="t('publishing.categories.empty')"
        variant="embedded"
      />

      <template
        v-if="pagination"
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

    <!-- Category Modal -->
    <CategoryFormModal
      v-model:open="showModal"
      :category="editingCategory"
      :categories="categories"
      @success="handleModalSuccess"
    />
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import {
  ChevronRight,
  Edit2,
  Plus,
  Search,
  Trash2,
} from 'lucide-vue-next';
import { debounce } from '@/shared/utils/debounce';
import CategoryFormModal from './CategoryFormModal.vue';

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
    SelectValue
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

// Stores & Composables
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useToast } from '@/shared/composables/useToast';
import type { Category } from '@/modules/Content/Publishing/types/taxonomy';
import { parseResponse, type PaginationData } from '@/shared/utils/responseParser';

interface FlatCategory extends Category {
    _depth: number;
}

defineProps<{
    isEmbedded?: boolean;
}>();

const { t } = useI18n();
const authStore = useAuthStore();
const toast = useToast();

const loading = ref(true);
const categories = ref<Category[]>([]); // Raw tree data
const search = ref('');
const statusFilter = ref('all');
const selectedIds = ref<string[]>([]);
const expandedIds = ref<(string)[]>([]); // Track expanded nodes
const pagination = ref<PaginationData>({
    current_page: 1,
    last_page: 1,
    per_page: 5,
    total: 0,
    from: 1,
    to: 1
});

// Modal State
const showModal = ref(false);
const editingCategory = ref<Category | null>(null);

const { confirm } = useConfirm();

// Toggle expand/collapse
const toggleExpand = (id: string) => {
    const index = expandedIds.value.indexOf(id);
    if (index > -1) {
        expandedIds.value.splice(index, 1);
    } else {
        expandedIds.value.push(id);
    }
};

// Helper to check if category has children
const hasChildren = (category: Category): boolean => {
    const children = category.all_children || category.children;
    return !!(children && Array.isArray(children) && children.length > 0);
};

// Helper to collect all parent IDs for auto-expansion
const getAllParentIds = (nodes: Category[]): (string)[] => {
    let ids: (string)[] = [];
    nodes.forEach(node => {
        const children = node.all_children || node.children;
        if (children && children.length > 0) {
            ids.push(node.id);
            ids = ids.concat(getAllParentIds(children));
        }
    });
    return ids;
};

// Flatten tree into array for Table display, calculating depth
const flattenTree = (nodes: Category[], depth = 0): FlatCategory[] => {
    if (!nodes) return [];
    let result: FlatCategory[] = [];
    nodes.forEach(node => {
        const flatNode: FlatCategory = { ...node, _depth: depth };
        result.push(flatNode);
        
        const children = node.all_children || node.children;
        const hasKids = children && Array.isArray(children) && children.length > 0;
        
        if (hasKids && (search.value || expandedIds.value.includes(node.id))) {
            result = result.concat(flattenTree(children, depth + 1));
        }
    });
    return result;
};

// Computed flat list for display
const flatCategories = computed(() => {
    return flattenTree(categories.value);
});

const columnHelper = createColumnHelper<FlatCategory>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h('div', { class: 'text-center' }, [
            authStore.hasPermission('delete categories') && h(Checkbox, {
                id: 'categories-select-all',
                checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
                'onUpdate:checked': (val) => table.toggleAllPageRowsSelected(!!val),
            }),
            authStore.hasPermission('delete categories') && h('label', {
                for: 'categories-select-all',
                class: 'sr-only',
            }, t('common.actions.selectAll')),
        ]),
        cell: ({ row }) => h('div', { class: 'text-center' }, [
            authStore.hasPermission('delete categories') && h(Checkbox, {
                id: `category-select-${row.original.id}`,
                checked: row.getIsSelected(),
                'onUpdate:checked': (val) => row.toggleSelected(!!val),
            }),
            authStore.hasPermission('delete categories') && h('label', {
                for: `category-select-${row.original.id}`,
                class: 'sr-only',
            }, t('common.actions.selectRow')),
        ]),
        size: 40,
    }),
    columnHelper.accessor('name', {
        header: t('publishing.categories.table.name'),
        cell: ({ row }) => {
            const category = row.original;
            return h('div', {
                style: { paddingLeft: `${category._depth * 24}px` },
                class: 'flex items-center'
            }, [
                hasChildren(category) ? h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    class: 'h-6 w-6 mr-2 shrink-0',
                    title: expandedIds.value.includes(category.id)
                        ? t('common.actions.collapse')
                        : t('common.actions.expand'),
                    onClick: (e: Event) => {
                        e.stopPropagation();
                        toggleExpand(category.id);
                    }
                }, [
                    h(ChevronRight, {
                        class: [
                            'w-4 h-4 transition-transform duration-200',
                            expandedIds.value.includes(category.id) ? 'rotate-90' : ''
                        ]
                    })
                ]) : h('span', { class: 'w-6 mr-2 shrink-0' }),
                h('span', { class: 'font-medium' }, category.name)
            ]);
        }
    }),
    columnHelper.accessor('slug', {
        header: t('publishing.categories.table.slug'),
        cell: ({ row }) => h('span', { class: 'text-muted-foreground font-mono text-xs' }, row.original.slug)
    }),
    columnHelper.accessor('is_active', {
        header: t('publishing.categories.table.status'),
        cell: ({ row }) => h(Badge, {
            variant: row.original.is_active ? 'default' : 'secondary'
        }, row.original.is_active ? t('publishing.categories.status.active') : t('publishing.categories.status.inactive'))
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-center' }, t('publishing.categories.table.actions')),
        cell: ({ row }) => h('div', { class: 'flex justify-center gap-1' }, [
            authStore.hasPermission('edit categories') && h(Button, {
                variant: 'ghost',
                size: 'icon',
                class: 'h-8 w-8 text-muted-foreground hover:text-foreground',
                title: t('common.actions.edit'),
                onClick: () => openEditModal(row.original)
            }, [h(Edit2, { class: 'w-4 h-4' })]),
            authStore.hasPermission('delete categories') && h(Button, {
                variant: 'ghost',
                size: 'icon',
                class: 'h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10',
                title: t('common.actions.delete'),
                onClick: () => deleteCategory(row.original)
            }, [h(Trash2, { class: 'w-4 h-4' })])
        ])
    })
];

const sorting = ref<SortingState>([]);
const rowSelection = ref<RowSelectionState>({});

const table = useVueTable({
    get data() { return flatCategories.value },
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

// Sync selectedIds with rowSelection for bulk actions
watch(rowSelection, (newSelection) => {
    selectedIds.value = Object.keys(newSelection)
        .filter(key => newSelection[key])
        .map(id => id);
}, { deep: true });

// Clear selection when categories change
watch(categories, () => {
    rowSelection.value = {};
});



const fetchCategories = async (page = 1) => {
    loading.value = true;
    try {
        const params: Record<string, string | number | boolean> = {
            page: page,
            per_page: Number(pagination.value.per_page),
            tree: true 
        };
        
        if (search.value) {
            params.tree = false;
            params.search = search.value;
        }

        if (statusFilter.value && statusFilter.value !== 'all') {
            params.is_active = statusFilter.value === 'active' ? 1 : 0;
        }

        const response = await api.get('/manage/library/categories', { params });
        const { data, pagination: paginationData } = parseResponse<Category>(response);
        
        categories.value = data || [];

        // Auto-expand all by default on first load or page change
        if (!search.value) {
            expandedIds.value = getAllParentIds(categories.value);
        } else {
            expandedIds.value = [];
        }

        if (paginationData) {
            pagination.value = paginationData;
        } else {
            pagination.value.total = categories.value.length;
            pagination.value.current_page = 1;
        }
        
        selectedIds.value = [];
    } catch (error: unknown) {
        logger.error('Failed to fetch categories:', error);
    } finally {
        loading.value = false;
    }
};

const debouncedSearch = debounce(() => {
    fetchCategories(1);
}, 300);

// Watch search input
watch(search, () => {
    debouncedSearch();
});

// Handle filter change
const onFilterChange = () => {
    fetchCategories(1);
};

const changePage = (page: number) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchCategories(page);
    }
};

const changePerPage = (perPage: number | string) => {
    pagination.value.per_page = typeof perPage === 'string' ? parseInt(perPage) : perPage;
    fetchCategories(1);
};

// Modal Actions
const openCreateModal = () => {
    editingCategory.value = null;
    showModal.value = true;
};

const openEditModal = (category: Category) => {
    // Clone to specific object to avoid binding issues
    editingCategory.value = { ...category };
    showModal.value = true;
};

const handleModalSuccess = () => {
    fetchCategories(pagination.value.current_page);
};

// const editCategory REMOVED - replaced by openEditModal

const deleteCategory = async (category: Category) => {
    const confirmed = await confirm({
        title: t('publishing.categories.actions.delete'),
        message: t('publishing.categories.messages.deleteConfirm', { name: category.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (confirmed) {
        try {
            await api.delete(`/manage/library/categories/${category.id}`);
            fetchCategories(pagination.value.current_page);
            toast.success.delete(t('publishing.categories.title_singular'));
        } catch (error: unknown) {
            logger.error('Failed to delete category:', error);
            toast.error.delete(error, t('publishing.categories.title_singular'));
        }
    }
};

// Bulk Actions

const confirmBulkDelete = async () => {
   const confirmed = await confirm({
       title: t('common.messages.confirm.bulkDelete'),
       message: t('common.messages.confirm.bulkDelete', { count: selectedIds.value.length }),
       variant: 'danger',
       confirmText: t('common.actions.delete'),
   });

   if (confirmed) {
        try {
            await api.post('/manage/library/categories/bulk-destroy', { ids: selectedIds.value });
            const count = selectedIds.value.length;
            selectedIds.value = [];
            fetchCategories(pagination.value.current_page);
            toast.success.delete(t('publishing.categories.title', { count: count }));
        } catch (error: unknown) {
           logger.error('Bulk delete failed:', error);
           toast.error.action(error as Record<string, unknown>);
        }
    }
}

onMounted(() => {
    fetchCategories();
});
</script>
