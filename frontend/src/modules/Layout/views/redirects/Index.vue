<template>
  <div class="console-page min-w-0 max-w-full">
    <PageHeader
      borderless
      :title="t('layout.redirects.title')"
      :subtitle="t('layout.redirects.subtitle')"
    />

    <div
      v-if="statistics"
      class="grid grid-cols-1 gap-4 md:grid-cols-3"
    >
      <ConsoleStatCard
        :label="t('layout.redirects.statistics.total')"
        :value="statistics.total || 0"
        :icon="ArrowRightLeft"
        tone="primary"
      />
      <ConsoleStatCard
        :label="t('layout.redirects.statistics.active')"
        :value="statistics.active || 0"
        :icon="CheckCircle2"
        tone="success"
      />
      <ConsoleStatCard
        :label="t('layout.redirects.statistics.hits')"
        :value="statistics.total_hits || 0"
        :icon="BarChart3"
        tone="muted"
      />
    </div>

    <ConsoleListCard>
      <template #toolbar>
        <div class="relative w-full sm:max-w-xs shrink-0">
          <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            v-model="search"
            type="text"
            :placeholder="t('layout.redirects.search')"
            class="h-10 w-full pl-9 bg-background"
          />
        </div>
        <div class="flex shrink-0 justify-end">
          <Button
            class="h-10"
            @click="showCreateModal = true"
          >
            <Plus class="mr-2 h-4 w-4" />
            {{ t('layout.redirects.new') }}
          </Button>
        </div>
      </template>

      <DataTable
        :table="table"
        :loading="loading"
        :empty-message="t('layout.redirects.empty')"
        variant="embedded"
      />
    </ConsoleListCard>

    <RedirectModal
      v-if="showCreateModal || showEditModal"
      :redirect="editingRedirect"
      @close="closeModal"
      @saved="handleRedirectSaved"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, h } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  useVueTable,
  getCoreRowModel,
  createColumnHelper,
  getSortedRowModel,
  type SortingState,
} from '@tanstack/vue-table';
import {
  ArrowRightLeft,
  BarChart3,
  CheckCircle2,
  Pencil,
  Plus,
  Search,
  Trash2,
} from 'lucide-vue-next';
import api from '@/engine/api/client';
import { layoutPaths } from '@/engine/api/paths';
import { fromApiRedirect, type RedirectRow } from '@/modules/Layout/utils/redirect';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { PageHeader, ConsoleStatCard, ConsoleListCard } from '@/shared/components/shell';
import {
  Badge,
  Button,
  Input,
  DataTable,
} from '@/shared/components/ui';
import RedirectModal from '@/modules/Layout/components/redirects/RedirectModal.vue';
import { parseResponse, ensureArray, parseSingleResponse } from '@/shared/utils/responseParser';
import { logger } from '@/shared/utils/logger';
import { cn } from '@/shared/utils/lib-utils';

interface RedirectStatistics {
  total: number;
  active: number;
  total_hits: number;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const redirects = ref<RedirectRow[]>([]);
const statistics = ref<RedirectStatistics | null>(null);
const loading = ref(false);
const search = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingRedirect = ref<RedirectRow | null>(null);

const filteredRedirects = computed(() => {
  const q = search.value.trim().toLowerCase();
  if (!q) return redirects.value;
  return redirects.value.filter(
    (r) =>
      r.from_url.toLowerCase().includes(q) ||
      r.to_url.toLowerCase().includes(q),
  );
});

const columnHelper = createColumnHelper<RedirectRow>();

const columns = [
  columnHelper.accessor('from_url', {
    header: () => t('layout.redirects.table.from'),
    cell: ({ row }) =>
      h('span', { class: 'text-sm font-medium text-foreground' }, row.original.from_url),
  }),
  columnHelper.accessor('to_url', {
    header: () => t('layout.redirects.table.to'),
    cell: ({ row }) => h('span', { class: 'text-sm text-foreground' }, row.original.to_url),
  }),
  columnHelper.accessor('status_code', {
    header: () => t('layout.redirects.table.code'),
    cell: ({ row }) =>
      h(Badge, { variant: 'outline', class: 'font-mono' }, () => String(row.original.status_code || 301)),
  }),
  columnHelper.accessor('hits', {
    header: () => t('layout.redirects.table.hits'),
    cell: ({ row }) =>
      h('span', { class: 'text-sm text-muted-foreground' }, String(row.original.hits || 0)),
  }),
  columnHelper.accessor('is_active', {
    header: () => t('layout.redirects.table.status'),
    cell: ({ row }) => {
      const active = row.original.is_active;
      return h(
        Badge,
        { variant: active ? 'default' : 'secondary' },
        () =>
          active
            ? t('layout.redirects.status.active')
            : t('layout.redirects.status.inactive'),
      );
    },
  }),
  columnHelper.display({
    id: 'actions',
    header: () => h('div', { class: 'text-right' }, t('layout.redirects.table.actions')),
    cell: ({ row }) => {
      const redirect = row.original;
      return h('div', { class: 'flex justify-end gap-1' }, [
        h(
          Button,
          {
            variant: 'ghost',
            size: 'icon',
            class: 'h-8 w-8',
            'aria-label': t('common.actions.edit'),
            onClick: () => editRedirect(redirect),
          },
          () => h(Pencil, { class: 'h-4 w-4' }),
        ),
        h(
          Button,
          {
            variant: 'ghost',
            size: 'icon',
            class: cn('h-8 w-8 text-destructive'),
            'aria-label': t('common.actions.delete'),
            onClick: () => deleteRedirect(redirect),
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
    return filteredRedirects.value;
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

const fetchRedirects = async () => {
  loading.value = true;
  try {
    const response = await api.get(layoutPaths.redirects);
    const { data } = parseResponse(response);
    redirects.value = ensureArray(data).map((row) => fromApiRedirect(row as Parameters<typeof fromApiRedirect>[0]));

    try {
      const statsResponse = await api.get(layoutPaths.redirectStatistics);
      statistics.value = parseSingleResponse(statsResponse) as RedirectStatistics;
    } catch (error: unknown) {
      statistics.value = {
        total: redirects.value.length,
        active: redirects.value.filter((r) => r.is_active).length,
        total_hits: redirects.value.reduce((sum, r) => sum + (r.hits || 0), 0),
      };
      logger.error('Failed to fetch statistics:', error);
    }
  } catch (error: unknown) {
    logger.error('Failed to fetch redirects:', error);
    toast.error.action(error);
  } finally {
    loading.value = false;
  }
};

const editRedirect = (redirect: RedirectRow) => {
  editingRedirect.value = redirect;
  showEditModal.value = true;
};

const deleteRedirect = async (redirect: RedirectRow) => {
  const confirmed = await confirm({
    title: t('layout.redirects.actions.delete'),
    message: t('layout.redirects.messages.deleteConfirm', { from: redirect.from_url }),
    variant: 'danger',
    confirmText: t('common.actions.delete'),
  });
  if (!confirmed) return;
  try {
    await api.delete(layoutPaths.redirect(String(redirect.id)));
    toast.success.delete(t('layout.redirects.title'));
    fetchRedirects();
  } catch (error: unknown) {
    logger.error('Failed to delete redirect:', error);
    toast.error.delete(error, t('layout.redirects.title'));
  }
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  editingRedirect.value = null;
};

const handleRedirectSaved = () => {
  fetchRedirects();
  closeModal();
};

onMounted(() => fetchRedirects());
</script>
