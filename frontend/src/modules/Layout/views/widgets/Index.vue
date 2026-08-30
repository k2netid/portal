<template>
  <div class="console-page min-w-0 max-w-full">
    <PageHeader
      borderless
      :title="t('layout.widgets.title')"
      :subtitle="t('layout.widgets.subtitle')"
    />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
      <ConsoleStatCard
        :label="t('layout.widgets.statistics.total')"
        :value="widgets.length"
        :icon="Layout"
        tone="primary"
      />
      <ConsoleStatCard
        :label="t('layout.widgets.statistics.active')"
        :value="activeCount"
        :icon="CheckCircle2"
        tone="success"
        :active="statusFilter === 'active'"
        clickable
        @click="statusFilter = 'active'"
      />
      <ConsoleStatCard
        :label="t('layout.widgets.statistics.inactive')"
        :value="inactiveCount"
        :icon="CircleOff"
        tone="muted"
        :active="statusFilter === 'inactive'"
        clickable
        @click="statusFilter = 'inactive'"
      />
    </div>

    <ConsoleListCard>
      <template #toolbar>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-1 sm:min-w-0">
          <div class="relative w-full sm:max-w-xs shrink-0">
            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input
              v-model="search"
              :placeholder="t('layout.widgets.search')"
              class="h-10 w-full pl-9 bg-background"
            />
          </div>
          <Select v-model="statusFilter">
            <SelectTrigger class="h-10 w-full sm:w-[180px] shrink-0 bg-background" :aria-label="t('common.labels.status')">
              <SelectValue :placeholder="t('common.labels.status')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">
                {{ t('layout.widgets.filters.all') }}
              </SelectItem>
              <SelectItem value="active">
                {{ t('layout.widgets.filters.active') }}
              </SelectItem>
              <SelectItem value="inactive">
                {{ t('layout.widgets.filters.inactive') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="flex shrink-0 justify-end">
          <Button
            size="sm"
            class="h-10 inline-flex items-center gap-2"
            @click="showCreateModal = true"
          >
            <Plus data-icon="inline-start" class="size-4 shrink-0" />
            {{ t('layout.widgets.new') }}
          </Button>
        </div>
      </template>

      <DataTable
        :table="table"
        :loading="loading"
        :empty-message="t('layout.widgets.empty')"
        variant="embedded"
      />
    </ConsoleListCard>

    <WidgetModal
      v-if="showCreateModal || showEditModal"
      :widget="editingWidget"
      @close="closeModal"
      @saved="handleWidgetSaved"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch, h } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  useVueTable,
  getCoreRowModel,
  createColumnHelper,
  getSortedRowModel,
  type SortingState,
} from '@tanstack/vue-table';
import {
  CheckCircle2,
  CircleOff,
  Layout,
  Pencil,
  Plus,
  Search,
  Trash2,
} from 'lucide-vue-next';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { PageHeader, ConsoleStatCard, ConsoleListCard } from '@/shared/components/shell';
import {
  Badge,
  Button,
  Input,
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  DataTable,
} from '@/shared/components/ui';
import WidgetModal from '@/modules/Layout/components/widgets/WidgetModal.vue';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { logger } from '@/shared/utils/logger';

interface Widget {
  id: string;
  title: string;
  type: string;
  location?: string;
  is_active?: boolean;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const widgets = ref<Widget[]>([]);
const loading = ref(false);
const search = ref('');
const statusFilter = ref<'all' | 'active' | 'inactive'>('all');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingWidget = ref<Widget | null>(null);

const activeCount = computed(() => widgets.value.filter((w) => w.is_active !== false).length);
const inactiveCount = computed(() => widgets.value.filter((w) => w.is_active === false).length);

const filteredWidgets = computed(() => {
  let list = widgets.value;
  const q = search.value.trim().toLowerCase();
  if (q) {
    list = list.filter(
      (w) =>
        w.title.toLowerCase().includes(q) ||
        w.type.toLowerCase().includes(q) ||
        (w.location || '').toLowerCase().includes(q),
    );
  }
  if (statusFilter.value === 'active') {
    list = list.filter((w) => w.is_active !== false);
  } else if (statusFilter.value === 'inactive') {
    list = list.filter((w) => w.is_active === false);
  }
  return list;
});

const columnHelper = createColumnHelper<Widget>();

const columns = [
  columnHelper.accessor('title', {
    header: () => t('layout.widgets.table.title'),
    cell: ({ row }) => {
      const widget = row.original;
      return h('div', { class: 'flex items-center gap-2 font-medium' }, [
        widget.title,
        widget.is_active === false
          ? h(Badge, { variant: 'destructive', class: 'h-4 px-1.5 text-[10px]' }, () =>
              t('layout.widgets.table.status.inactive'),
            )
          : null,
      ]);
    },
  }),
  columnHelper.accessor('type', {
    header: () => t('layout.widgets.table.type'),
    cell: ({ row }) =>
      h(Badge, { variant: 'outline', class: 'capitalize' }, () => row.original.type),
  }),
  columnHelper.accessor('location', {
    header: () => t('layout.widgets.table.location'),
    cell: ({ row }) => {
      const loc = row.original.location;
      return h(
        'code',
        { class: 'rounded border border-border bg-muted px-1.5 py-0.5 font-mono text-xs' },
        loc || '-',
      );
    },
  }),
  columnHelper.display({
    id: 'actions',
    header: () => h('div', { class: 'text-right' }, t('layout.widgets.table.actions')),
    cell: ({ row }) => {
      const widget = row.original;
      return h('div', { class: 'flex justify-end gap-1' }, [
        h(
          Button,
          {
            variant: 'ghost',
            size: 'icon',
            class: 'h-8 w-8',
            'aria-label': t('layout.widgets.actions.edit'),
            title: t('layout.widgets.actions.edit'),
            onClick: () => editWidget(widget),
          },
          () => h(Pencil, { class: 'h-4 w-4' }),
        ),
        h(
          Button,
          {
            variant: 'ghost',
            size: 'icon',
            class: 'h-8 w-8 text-destructive',
            'aria-label': t('layout.widgets.actions.delete'),
            title: t('layout.widgets.actions.delete'),
            onClick: () => deleteWidget(widget),
          },
          () => h(Trash2, { class: 'h-4 w-4' }),
        ),
      ]);
    },
  }),
];

const sorting = ref<SortingState>([]);

const table = useVueTable({
  get data() {
    return filteredWidgets.value;
  },
  columns,
  state: {
    get sorting() {
      return sorting.value;
    },
  },
  onSortingChange: (updater) => {
    sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater;
  },
  getCoreRowModel: getCoreRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getRowId: (row) => String(row.id),
});

const fetchWidgets = async () => {
  loading.value = true;
  try {
    const response = await api.get('/manage/layout/widgets');
    const { data } = parseResponse(response);
    widgets.value = ensureArray(data);
  } catch (error: unknown) {
    logger.error('Failed to fetch widgets:', error);
    toast.error.action(error);
  } finally {
    loading.value = false;
  }
};

const editWidget = (widget: Widget) => {
  editingWidget.value = widget;
  showEditModal.value = true;
};

const deleteWidget = async (widget: Widget) => {
  const confirmed = await confirm({
    title: t('layout.widgets.actions.delete'),
    message: t('layout.widgets.messages.deleteConfirm', { title: widget.title }),
    variant: 'danger',
    confirmText: t('common.actions.delete'),
  });
  if (!confirmed) return;
  try {
    await api.delete(`/manage/layout/widgets/${widget.id}`);
    toast.success.delete(t('layout.widgets.title'));
    fetchWidgets();
  } catch (error: unknown) {
    logger.error('Failed to delete widget:', error);
    toast.error.delete(error as Error, t('layout.widgets.title'));
  }
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  editingWidget.value = null;
};

const handleWidgetSaved = () => {
  fetchWidgets();
  closeModal();
};

watch(search, () => {
  /* client filter via computed */
});

onMounted(() => fetchWidgets());
</script>
