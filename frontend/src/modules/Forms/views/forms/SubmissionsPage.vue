<template>
  <div class="relative min-h-[500px]">
    <PageHeader
      borderless
      :title="$t('forms.submissions.title')"
      :subtitle="submissionsSubtitle"
    >
      <template #actions>
        <div class="flex items-center gap-2 flex-wrap">
        <Button
          variant="outline"
          size="sm"
          class="h-10 inline-flex items-center gap-2"
          @click="$router.push({ name: 'forms' })"
        >
          <ArrowLeft class="w-4 h-4 mr-2" />
          {{ $t('common.actions.back') }}
        </Button>
        <Button
          variant="outline"
          size="sm"
          class="h-10 inline-flex items-center gap-2"
          @click="$router.push({ name: 'forms.analytics', params: { id: formId } })"
        >
          <TrendingUp class="w-4 h-4 mr-2" />
          {{ $t('forms.actions.analysis') }}
        </Button>
        <Popover>
          <PopoverTrigger class="inline-flex h-10 items-center gap-2 rounded-lg border border-border/60 bg-background px-2.5 text-sm font-medium hover:bg-accent/50">
            <Download class="w-4 h-4 mr-2" />
            {{ $t('forms.actions.export') }}
          </PopoverTrigger>
          <PopoverContent
            class="w-48 p-2"
            align="end"
          >
            <div class="grid gap-1">
              <Button
                variant="ghost"
                class="w-full justify-start text-left h-9 px-3"
                @click="exportSubmissions('xlsx')"
              >
                <span class="mr-2 text-lg">📊</span> {{ $t('forms.submissions.analytics.excel') }}
              </Button>
              <Button
                variant="ghost"
                class="w-full justify-start text-left h-9 px-3"
                @click="exportSubmissions('csv')"
              >
                <span class="mr-2 text-lg">📝</span> {{ $t('forms.submissions.analytics.csv') }}
              </Button>
              <Button
                variant="ghost"
                class="w-full justify-start text-left h-9 px-3"
                @click="exportSubmissions('pdf')"
              >
                <span class="mr-2 text-lg">📕</span> {{ $t('forms.submissions.analytics.pdf') }}
              </Button>
            </div>
          </PopoverContent>
        </Popover>
        </div>
      </template>
    </PageHeader>

    <!-- Statistics Cards -->
    <div
      v-if="statistics"
      class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6"
    >
      <Card 
        class="p-4 cursor-pointer hover:bg-primary/5 transition-all duration-75" 
        :class="{ 'ring-2 ring-primary/50': statusFilter === 'all' }"
        @click="statusFilter = 'all'"
      >
        <div class="flex flex-col">
          <span class="text-2xl font-bold text-primary">{{ statistics.total || 0 }}</span>
          <span class="text-sm text-muted-foreground">{{ $t('forms.stats.total') }}</span>
        </div>
      </Card>
      <Card 
        class="p-4 cursor-pointer hover:bg-green-500/5 transition-all duration-75 border-green-500/20" 
        :class="{ 'ring-2 ring-green-500/50': statusFilter === 'new' }"
        @click="statusFilter = 'new'"
      >
        <div class="flex flex-col">
          <span class="text-2xl font-bold text-green-800">{{ statistics.new || 0 }}</span>
          <span class="text-sm text-green-800/80">{{ $t('forms.stats.new') }}</span>
        </div>
      </Card>
      <Card 
        class="p-4 cursor-pointer hover:bg-yellow-500/5 transition-all duration-75 border-yellow-500/20" 
        :class="{ 'ring-2 ring-yellow-500/50': statusFilter === 'read' }"
        @click="statusFilter = 'read'"
      >
        <div class="flex flex-col">
          <span class="text-2xl font-bold text-amber-800">{{ statistics.read || 0 }}</span>
          <span class="text-sm text-amber-800/80">{{ $t('forms.stats.read') }}</span>
        </div>
      </Card>
      <Card 
        class="p-4 cursor-pointer hover:bg-muted/50 transition-all duration-75" 
        :class="{ 'ring-2 ring-muted-foreground/50': statusFilter === 'archived' }"
        @click="statusFilter = 'archived'"
      >
        <div class="flex flex-col">
          <span class="text-2xl font-bold text-muted-foreground">{{ statistics.archived || 0 }}</span>
          <span class="text-sm text-muted-foreground">{{ $t('forms.stats.archived') }}</span>
        </div>
      </Card>
    </div>

    <ConsoleListCard>
      <div class="p-6 space-y-6">
    <!-- Filters -->
    <div class="p-0 mb-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <Input
            v-model="search"
            type="text"
            :placeholder="$t('forms.submissions.search')"
            :aria-label="$t('forms.submissions.search')"
            class="pl-9"
          />
        </div>
        <Select v-model="statusFilter">
          <SelectTrigger :aria-label="$t('forms.filters.status')">
            <SelectValue :placeholder="$t('forms.filters.status')" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">
              {{ $t('forms.filters.status') }}
            </SelectItem>
            <SelectItem value="new">
              {{ $t('forms.stats.new') }}
            </SelectItem>
            <SelectItem value="read">
              {{ $t('forms.stats.read') }}
            </SelectItem>
            <SelectItem value="archived">
              {{ $t('forms.stats.archived') }}
            </SelectItem>
          </SelectContent>
        </Select>
        <Input
          v-model="dateFrom"
          type="date"
          :aria-label="$t('forms.submissions.dateFrom')"
        />
        <Input
          v-model="dateTo"
          type="date"
          :aria-label="$t('forms.submissions.dateTo')"
        />
      </div>
    </div>

    <!-- Bulk Actions Toolbar -->
    <Transition
      enter-active-class="transition-[opacity,transform] duration-100"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-[opacity,transform] duration-75"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-2"
    >
      <div
        v-show="selectedRowsCount > 0"
        class="fixed bottom-10 left-1/2 -translate-x-1/2 z-50 flex items-center gap-4 bg-background border border-border px-6 py-3 rounded-full shadow-2xl ring-4 ring-primary/5"
      >
        <span class="text-sm font-medium text-foreground whitespace-nowrap">
          {{ selectedRowsCount }} {{ $t('forms.bulk.selected') }}
        </span>
        <div class="h-4 w-px bg-border mx-2" />
        <div class="flex items-center gap-1">
          <Button
            variant="ghost"
            size="sm"
            class="text-amber-500 hover:text-amber-600 hover:bg-amber-500/10"
            @click="handleBulkMarkRead"
          >
            <Check class="w-4 h-4 mr-2" />
            {{ $t('forms.submissions.actions.markRead') }}
          </Button>
          <Button
            variant="ghost"
            size="sm"
            class="text-purple-500 hover:text-purple-600 hover:bg-purple-500/10"
            @click="handleBulkArchive"
          >
            <Archive class="w-4 h-4 mr-2" />
            {{ $t('forms.submissions.actions.archive') }}
          </Button>
          <Button
            variant="ghost"
            size="sm"
            class="text-destructive hover:bg-destructive/10"
            @click="handleBulkDelete"
          >
            <Trash2 class="w-4 h-4 mr-2" />
            {{ $t('common.actions.delete') }}
          </Button>
          <Button
            variant="ghost"
            size="sm"
            @click="table.resetRowSelection()"
          >
            {{ $t('common.actions.cancel') }}
          </Button>
        </div>
      </div>
    </Transition>

    <!-- Main Content Area -->
    <div class="relative">
      <!-- Loading Overlay (Satset style: non-blocking) -->
      <div
        v-if="loading && submissions.length > 0"
        class="absolute inset-x-0 -top-1 z-20"
      >
        <div class="h-1 w-full bg-primary/10 overflow-hidden">
          <div class="h-full bg-primary animate-progress-loading w-1/3" />
        </div>
      </div>

      <!-- Loading Spinner (Initial load) -->
      <div
        v-if="loading && submissions.length === 0"
        class="bg-card border border-border rounded-lg p-12 text-center"
      >
        <Loader2 class="w-8 h-8 mx-auto animate-spin text-muted-foreground" />
        <p class="text-muted-foreground mt-2">
          {{ $t('forms.messages.loading') }}
        </p>
      </div>

      <!-- Empty State -->
      <Card
        v-else-if="submissions.length === 0"
        class="p-12 text-center"
      >
        <FileText class="mx-auto h-12 w-12 text-muted-foreground opacity-50" />
        <p class="mt-4 text-muted-foreground">
          {{ $t('forms.submissions.empty') }}
        </p>
      </Card>

      <!-- Submissions Table -->
      <Card
        v-else
        class="overflow-hidden border-border bg-card"
        :class="{ 'opacity-50 pointer-events-none': loading }"
      >
        <div class="overflow-x-auto">
          <Table>
            <TableHeader class="bg-muted/50 border-b border-border">
              <TableRow
                v-for="headerGroup in table.getHeaderGroups()"
                :key="headerGroup.id"
              >
                <TableHead 
                  v-for="header in headerGroup.headers" 
                  :key="header.id"
                  class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider"
                >
                  <FlexRender 
                    v-if="!header.isPlaceholder"
                    :render="header.column.columnDef.header"
                    :props="header.getContext()"
                  />
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow 
                v-for="row in table.getRowModel().rows" 
                :key="row.id" 
                class="group hover:bg-muted/30 cursor-pointer border-b border-border/50 last:border-0"
                :class="{ 'bg-primary/5': row.getIsSelected() }"
                @click="viewSubmission(row.original)"
              >
                <TableCell 
                  v-for="cell in row.getVisibleCells()" 
                  :key="cell.id"
                  class="px-4 py-4"
                >
                  <FlexRender 
                    :render="cell.column.columnDef.cell"
                    :props="cell.getContext()"
                  />
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </Card>
    </div>

    <!-- Pagination -->
    <div class="py-4 border-t border-border mt-auto">
      <Pagination
          embedded
        v-if="pagination && (pagination.total as number) > 0"
        :current-page="(pagination.current_page as number)"
        :total-items="(pagination.total as number)"
        :per-page="Number(pagination.per_page || 15)"
        :show-page-numbers="true"
        @page-change="loadPage"
        @update:per-page="(val: number) => { if (pagination) { pagination.per_page = val; loadPage(1); } }"
      />
    </div>

    <!-- Detail Dialog -->
    <Dialog v-model:open="showDetail">
      <DialogContent class="console-dialog-xl-scroll duration-100">
        <DialogHeader class="flex flex-row items-center justify-between space-y-0">
          <DialogTitle>{{ $t('forms.submissions.detailTitle') }}</DialogTitle>
          <div class="flex items-center gap-2 mr-6">
            <Button 
              v-if="selectedSubmission"
              variant="outline" 
              size="sm" 
              class="h-8" 
              @click="exportPdf(selectedSubmission)"
            >
              <Download class="w-4 h-4 mr-2" />
              PDF
            </Button>
          </div>
        </DialogHeader>
        <div
          v-if="selectedSubmission"
          class="space-y-4"
        >
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="flex items-center gap-2">
              <span class="font-medium text-foreground">{{ $t('forms.submissions.status') }}:</span>
              <Badge
                :class="{
                  'bg-green-500/10 text-green-500 border-green-500/20': selectedSubmission.status === 'new',
                  'bg-yellow-500/10 text-yellow-500 border-yellow-500/20': selectedSubmission.status === 'read',
                  'bg-muted text-muted-foreground': selectedSubmission.status === 'archived'
                }"
              >
                {{ getStatusLabel(selectedSubmission.status) }}
              </Badge>
            </div>
            <div>
              <span class="font-medium text-foreground">{{ $t('forms.submissions.submitted') }}:</span>
              <span class="ml-2 text-muted-foreground">{{ formatDate(selectedSubmission.created_at) }}</span>
            </div>
            <div>
              <span class="font-medium text-foreground">{{ $t('forms.submissions.ipAddress') }}:</span>
              <span class="ml-2 text-muted-foreground font-mono">{{ selectedSubmission.ip_address || '-' }}</span>
            </div>
            <div v-if="selectedSubmission.user">
              <span class="font-medium text-foreground">{{ $t('forms.submissions.user') }}:</span>
              <span class="ml-2 text-muted-foreground">{{ selectedSubmission.user.name || selectedSubmission.user.email }}</span>
            </div>
          </div>
          <div class="border-t border-border pt-4">
            <h4 class="font-semibold text-foreground mb-3">
              {{ $t('forms.submissions.formData') }}
            </h4>
            <div class="bg-muted/50 rounded-lg border border-border overflow-hidden">
              <dl class="divide-y divide-border/50">
                <div
                  v-for="(value, key) in selectedSubmission.data"
                  :key="key"
                  class="p-3 grid grid-cols-3 gap-4 hover:bg-muted/80 duration-75"
                >
                  <dt class="text-sm font-medium text-foreground flex items-center">
                    {{ key }}
                  </dt>
                  <dd class="text-sm text-muted-foreground col-span-2 break-words">
                    {{ formatValue(value) }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
</div>
    </ConsoleListCard>

    <!-- Back to Top -->
    <BackToTop
      :show="scrolled"
      class="fixed bottom-6 right-6"
      @click="scrollToTop"
    />
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';
import { logger } from '@/shared/utils/logger';
import { h, ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
    FlexRender
} from '@tanstack/vue-table';
import { apiConfig } from '@/config';
import { FormsService } from '@/modules/Forms/services/formsService';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { BackToTop, Badge, Button, Card, Checkbox, Dialog, DialogContent, DialogHeader, DialogTitle, Input, Pagination, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, Table, TableBody, TableCell, TableHead, TableHeader, TableRow, Popover, PopoverTrigger, PopoverContent } from '@/shared/components/ui';

import {
  Archive,
  ArrowDown,
  ArrowLeft,
  ArrowUp,
  ArrowUpDown,
  Check,
  Download,
  FileText,
  Loader2,
  Search,
  Trash2,
  TrendingUp,
} from 'lucide-vue-next';

interface SubmissionUser {
    name?: string;
    email?: string;
}

interface Submission {
    id: string | string;
    status: 'new' | 'read' | 'archived';
    created_at: string;
    ip_address?: string;
    user?: SubmissionUser;
    data: Record<string, unknown>;
}

interface Form {
    id: string | string;
    name: string;
}

interface SubmissionStatistics {
    total: number;
    new: number;
    read: number;
    archived: number;
}

interface SubmissionsPagination {
    current_page: number;
    total: number;
    per_page: number | string;
    last_page: number;
}

const { t } = useI18n()
const submissionsSubtitle = computed(() => {
  const base = t('forms.submissions.subtitle');
  const n = form.value?.name;
  return n ? `${base} — ${n}` : base;
});
;
const route = useRoute();
const { confirm } = useConfirm();
const toast = useToast();

const form = ref<Form | null>(null);
const submissions = ref<Submission[]>([]);
const loading = ref(true);
const statistics = ref<SubmissionStatistics | null>(null);
const pagination = ref<SubmissionsPagination | null>(null);
const search = ref('');
const statusFilter = ref('all');
const dateFrom = ref('');
const dateTo = ref('');
const selectedSubmission = ref<Submission | null>(null);
const showDetail = ref(false);
const rowSelection = ref({});
const sorting = ref([{ id: 'created_at', desc: true }]);

const formId = computed(() => route.params.id);

const renderSortIcon = (isSorted: string | boolean) => {
    if (isSorted === 'asc') return ArrowUp;
    if (isSorted === 'desc') return ArrowDown;
    return ArrowUpDown;
};

// --- TanStack Table Setup ---
const columnHelper = createColumnHelper<Submission>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            checked: table.getIsAllPageRowsSelected(),
            'onUpdate:checked': (value) => table.toggleAllPageRowsSelected(!!value),
            'aria-label': 'Select all',
            onClick: (e: MouseEvent) => e.stopPropagation(),
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: row.getIsSelected(),
            'onUpdate:checked': (value) => row.toggleSelected(!!value),
            'aria-label': 'Select row',
            onClick: (e: MouseEvent) => e.stopPropagation(),
        }),
        size: 40,
    }),
    columnHelper.accessor('status', {
        header: ({ column }) => h(Button, {
            variant: 'ghost',
            size: 'sm',
            onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            class: '-ml-3 h-8 text-slate-700 font-medium data-[state=open]:bg-accent',
        }, () => [
            'Status',
            h(renderSortIcon(column.getIsSorted()), { class: 'ml-2 h-4 w-4' })
        ]),
        cell: info => h(Badge, {
            class: {
                'bg-green-500/10 text-green-800 border-green-500/20': info.getValue() === 'new',
                'bg-yellow-500/10 text-amber-800 border-yellow-500/20': info.getValue() === 'read',
                'bg-muted text-muted-foreground': info.getValue() === 'archived'
            }
        }, () => getStatusLabel(info.getValue())),
    }),
    columnHelper.accessor('created_at', {
        id: 'created_at',
        header: ({ column }) => h(Button, {
            variant: 'ghost',
            size: 'sm',
            onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            class: '-ml-3 h-8 text-slate-700 font-medium data-[state=open]:bg-accent',
        }, () => [
            t('forms.submissions.submitted'),
            h(renderSortIcon(column.getIsSorted()), { class: 'ml-2 h-4 w-4' })
        ]),
        cell: info => h('span', { class: 'text-sm text-muted-foreground font-mono' }, formatDate(info.getValue())),
    }),
    columnHelper.accessor('data', {
        header: () => t('forms.submissions.formData'),
        cell: info => h('div', { class: 'flex flex-wrap gap-2 max-w-md' }, 
            Object.entries(getFirstFields(info.getValue())).map(([key, value]) => 
                h('span', { class: 'text-xs bg-muted/80 text-foreground border border-border/40 px-2 py-0.5 rounded-sm' }, [
                    h('span', { class: 'font-semibold text-muted-foreground' }, `${key}: `),
                    formatValue(value)
                ])
            )
        ),
        enableSorting: false,
    }),
    columnHelper.accessor('ip_address', {
        header: ({ column }) => h(Button, {
            variant: 'ghost',
            size: 'sm',
            onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            class: '-ml-3 h-8 text-slate-700 font-medium data-[state=open]:bg-accent',
        }, () => [
            t('forms.submissions.ipAddress'),
            h(renderSortIcon(column.getIsSorted()), { class: 'ml-2 h-4 w-4' })
        ]),
        cell: info => h('span', { class: 'text-sm text-muted-foreground font-mono' }, info.getValue() || '-'),
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-center' }, t('common.actions.title')),
        cell: info => {
            const submission = info.row.original;
            return h('div', { class: 'flex items-center justify-center space-x-1' }, [
                submission.status === 'new' ? h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    class: 'h-8 w-8 text-amber-600 hover:text-amber-700 hover:bg-amber-500/10 transition-none',
                    title: t('forms.stats.read'),
                    'aria-label': t('forms.submissions.actions.markRead'),
                    onClick: (e: MouseEvent) => {
                        e.stopPropagation();
                        markAsRead(submission);
                    }
                }, () => h(Check, { class: 'w-4 h-4' })) : null,
                submission.status !== 'archived' ? h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    class: 'h-8 w-8 text-purple-700 hover:text-purple-800 hover:bg-purple-500/10 transition-none',
                    title: t('common.actions.archive'),
                    'aria-label': t('forms.submissions.actions.archive'),
                    onClick: (e: MouseEvent) => {
                        e.stopPropagation();
                        archiveSubmission(submission);
                    }
                }, () => h(Archive, { class: 'w-4 h-4' })) : null,
                h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    class: 'h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10 transition-none',
                    title: t('common.actions.delete'),
                    'aria-label': t('common.actions.delete'),
                    onClick: (e: MouseEvent) => {
                        e.stopPropagation();
                        deleteSubmission(submission);
                    }
                }, () => h(Trash2, { class: 'w-4 h-4' }))
            ]);
        }
    })
];

const table = useVueTable({
    get data() { return submissions.value },
    columns,
    state: {
        get rowSelection() { return rowSelection.value },
        get sorting() { return sorting.value },
    },
    onRowSelectionChange: (updaterOrValue) => {
        rowSelection.value = typeof updaterOrValue === 'function' 
            ? updaterOrValue(rowSelection.value) 
            : updaterOrValue;
    },
    onSortingChange: (updaterOrValue) => {
        sorting.value = typeof updaterOrValue === 'function' 
            ? updaterOrValue(sorting.value) 
            : updaterOrValue;
        fetchSubmissions(1);
    },
    getCoreRowModel: getCoreRowModel(),
    manualSorting: true,
});

const selectedRowsCount = computed(() => Object.keys(rowSelection.value).length);

// --- API Methods ---
const fetchForm = async () => {
    try {
        const response = await FormsService.get(String(formId.value));
        form.value = (response.data) as Form;
    } catch (error: unknown) {
        logger.error('Error fetching form:', error);
    }
};

const fetchSubmissions = async (page = 1) => {
    try {
        loading.value = true;
        
        let sortBy = 'created_at';
        let sortOrder = 'desc';
        
        if (sorting.value.length > 0) {
            const currentSort = sorting.value[0];
            if (currentSort) {
                sortBy = currentSort.id;
                sortOrder = currentSort.desc ? "desc" : "asc";
            }
        }

        const params = {
            page,
            per_page: pagination.value?.per_page || 15,
            search: search.value,
            sort_by: sortBy,
            sort_order: sortOrder,
            ...(statusFilter.value && statusFilter.value !== 'all' && { status: statusFilter.value }),
            ...(dateFrom.value && { date_from: dateFrom.value }),
            ...(dateTo.value && { date_to: dateTo.value })
        };
        const response = await FormsService.listSubmissions(String(formId.value), params);
        const paginatedData = response.data;
        submissions.value = paginatedData?.data || [];
        pagination.value = {
            current_page: paginatedData?.current_page || 1,
            total: paginatedData?.total || 0,
            per_page: paginatedData?.per_page || 15,
            last_page: paginatedData?.last_page || 1
        };
        table.resetRowSelection();
    } catch (error: unknown) {
        logger.error('Error fetching submissions:', error);
    } finally {
        loading.value = false;
    }
};

const fetchStatistics = async () => {
    try {
        const response = await FormsService.submissionStatistics(String(formId.value));
        statistics.value = parseSingleResponse(response) as SubmissionStatistics;
    } catch (error) {
        logger.error('Failed to fetch statistics:', error);
    }
};

const loadPage = (page: number) => {
    fetchSubmissions(page);
};

const viewSubmission = async (submission: Submission) => {
    selectedSubmission.value = submission;
    showDetail.value = true;
    if (submission.status === 'new') {
        await markAsRead(submission, false);
    }
};

const markAsRead = async (submission: Submission, refresh = true) => {
    try {
        await FormsService.markSubmissionRead(String(submission.id));
        if (refresh) {
            fetchSubmissions(pagination.value?.current_page || 1);
            fetchStatistics();
        } else {
            submission.status = 'read';
        }
    } catch (error: unknown) {
        logger.error('Error marking as read:', error);
    }
};

const archiveSubmission = async (submission: Submission) => {
    try {
        await FormsService.archiveSubmission(String(submission.id));
        fetchSubmissions(pagination.value?.current_page || 1);
        fetchStatistics();
    } catch (error: unknown) {
        logger.error('Error archiving submission:', error);
    }
};

const deleteSubmission = async (submission: Submission) => {
    const confirmed = await confirm({
        title: t('forms.submissions.actions.delete'),
        message: t('forms.submissions.messages.deleteConfirm'),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await FormsService.deleteSubmission(String(submission.id));
        submissions.value = submissions.value.filter(s => s.id !== submission.id);
        toast.success.default(t('forms.submissions.messages.deleteSuccess'));
        fetchStatistics();
        table.resetRowSelection();
    } catch (error: unknown) {
        logger.error('Error deleting submission:', error);
        toast.error.fromResponse(error);
    }
};

// --- Bulk Actions ---
const handleBulkMarkRead = async () => {
    const selectedRows = table.getSelectedRowModel().rows;
    const selectedIds = selectedRows.map(row => row.original.id);
    
    try {
        await Promise.all(selectedIds.map(id => FormsService.markSubmissionRead(String(id))));
        toast.success.default(t('forms.submissions.messages.bulkReadSuccess', { count: selectedIds.length }));
        fetchSubmissions(pagination.value?.current_page || 1);
        fetchStatistics();
    } catch (error: unknown) {
        logger.error('Error in bulk mark read:', error);
        toast.error.fromResponse(error);
    }
};

const handleBulkArchive = async () => {
    const selectedRows = table.getSelectedRowModel().rows;
    const selectedIds = selectedRows.map(row => row.original.id);
    
    const confirmed = await confirm({
        title: t('forms.submissions.actions.archive'),
        message: t('forms.submissions.messages.bulkArchiveConfirm', { count: selectedIds.length }),
        variant: 'warning',
        confirmText: t('forms.submissions.actions.archive'),
    });

    if (!confirmed) return;

    try {
        await Promise.all(selectedIds.map(id => FormsService.archiveSubmission(String(id))));
        toast.success.default(t('forms.submissions.messages.bulkArchiveSuccess', { count: selectedIds.length }));
        fetchSubmissions(pagination.value?.current_page || 1);
        fetchStatistics();
    } catch (error: unknown) {
        logger.error('Error in bulk archive:', error);
        toast.error.fromResponse(error);
    }
};

const handleBulkDelete = async () => {
    const selectedRows = table.getSelectedRowModel().rows;
    const selectedIds = selectedRows.map(row => row.original.id);
    
    const confirmed = await confirm({
        title: t('common.actions.delete'),
        message: t('forms.submissions.messages.bulkDeleteConfirm', { count: selectedIds.length }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await Promise.all(selectedIds.map(id => FormsService.deleteSubmission(String(id))));
        toast.success.default(t('forms.submissions.messages.bulkDeleteSuccess', { count: selectedIds.length }));
        fetchSubmissions(pagination.value?.current_page || 1);
        fetchStatistics();
    } catch (error: unknown) {
        logger.error('Error in bulk delete:', error);
        toast.error.fromResponse(error);
    }
};

const exportSubmissions = async (format = 'xlsx') => {
    try {
        let sortBy = 'created_at';
        let sortOrder = 'desc';
        
        if (sorting.value.length > 0) {
            const currentSort = sorting.value[0];
            if (currentSort) {
                sortBy = currentSort.id;
                sortOrder = currentSort.desc ? "desc" : "asc";
            }
        }

        const params = new URLSearchParams({
            format,
            search: search.value,
            sort_by: sortBy,
            sort_order: sortOrder,
            ...(statusFilter.value && statusFilter.value !== 'all' && { status: statusFilter.value }),
            ...(dateFrom.value && { date_from: dateFrom.value }),
            ...(dateTo.value && { date_to: dateTo.value })
        });
        const baseUrl = apiConfig.externalUrl;
        const url = FormsService.submissionsExportUrl(String(formId.value), params.toString(), baseUrl);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `submissions-${formId.value}.${format}`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        toast.success.default(t('forms.submissions.messages.exportSuccess'));
    } catch (error: unknown) {
        logger.error('Error exporting submissions:', error);
        toast.error.fromResponse(error);
    }
};

const exportPdf = (submission: Submission) => {
    if (!submission) return;
    try {
        const baseUrl = apiConfig.externalUrl;
        const exportUrl = FormsService.exportSubmissionPdfUrl(String(submission.id), baseUrl);
        window.open(exportUrl, '_blank');
    } catch (error: unknown) {
        logger.error('Failed to export PDF:', error);
        toast.error.default(t('forms.submissions.messages.exportFailed'));
    }
};

// --- Helper Functions ---
const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        new: t('forms.stats.new'),
        read: t('forms.stats.read'),
        archived: t('forms.stats.archived')
    };
    return labels[status] || status;
};

const formatDate = (date: string | null | undefined) => {
    if (!date) return '-';
    const parsed = new Date(date);
    if (isNaN(parsed.getTime())) return '-';
    return parsed.toLocaleString();
};

const formatValue = (value: unknown) => {
    if (Array.isArray(value)) return value.join(', ');
    if (typeof value === 'object') return JSON.stringify(value);
    return String(value || '-');
};

const getFirstFields = (data: Record<string, unknown>) => {
    if (!data) return {};
    const entries = Object.entries(data);
    return Object.fromEntries(entries.slice(0, 3));
};

// --- Scroll Handling ---
const scrolled = ref(false);
const handleScroll = () => {
    scrolled.value = window.scrollY > 300;
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'auto' });
};

// --- Watchers with Debounce ---
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;
watch(search, () => {
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        fetchSubmissions(1);
    }, 300);
});

watch([statusFilter, dateFrom, dateTo], () => {
    fetchSubmissions(1);
});

onMounted(() => {
    fetchForm();
    fetchSubmissions();
    fetchStatistics();
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
.animate-progress-loading {
    animation: progress-loading 1s infinite linear;
    transform-origin: 0% 50%;
}

@keyframes progress-loading {
    0% { transform: translateX(-100%) scaleX(0.2); }
    50% { transform: translateX(0%) scaleX(0.5); }
    100% { transform: translateX(100%) scaleX(0.2); }
}

[dir="rtl"] .fixed.bottom-6.right-6 {
    right: auto;
    left: 24px;
}
</style>
