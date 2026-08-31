<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="t('member.title')"
      :subtitle="t('member.subtitle')"
    >
      <template #actions>
        <Button
          variant="outline"
          size="sm"
          @click="exportCsv"
        >
          <Download class="w-4 h-4 mr-2" />
          {{ t('member.actions.export') }}
        </Button>
        <router-link
          v-if="canManage"
          :to="{ name: 'members.create' }"
        >
          <Button size="sm">
            <Plus class="w-4 h-4 mr-2" />
            {{ t('member.form.createTitle') }}
          </Button>
        </router-link>
      </template>
    </PageHeader>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
      <ConsoleStatCard
        :label="t('member.stats.total')"
        :value="String(stats.total)"
        :icon="Users"
        tone="primary"
        :active="verificationFilter === 'all' && statusFilter === 'all' && !activeStatFilter"
        clickable
        @click="clearFilters"
      />
      <ConsoleStatCard
        :label="t('member.stats.verified')"
        :value="String(stats.verified)"
        :icon="CheckCircle"
        tone="primary"
        :active="verificationFilter === 'verified'"
        clickable
        @click="setVerificationFilter('verified')"
      />
      <ConsoleStatCard
        :label="t('member.stats.unverified')"
        :value="String(stats.unverified)"
        :icon="AlertCircle"
        tone="warning"
        :active="verificationFilter === 'unverified'"
        clickable
        @click="setVerificationFilter('unverified')"
      />
      <ConsoleStatCard
        :label="t('member.stats.recent')"
        :value="String(stats.recent)"
        :icon="UserPlus"
        tone="success"
        :active="activeStatFilter === 'recent'"
        clickable
        @click="setStatFilter('recent')"
      />
      <ConsoleStatCard
        :label="t('member.stats.active')"
        :value="String(stats.active)"
        :icon="Activity"
        tone="info"
        :active="activeStatFilter === 'active'"
        clickable
        @click="setStatFilter('active')"
      />
    </div>

    <ConsoleListCard>
      <template #toolbar>
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:flex-1 lg:min-w-0 w-full">
          <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:flex-1 sm:min-w-0">
            <div class="relative w-full sm:max-w-xs shrink-0">
              <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                v-model="search"
                type="text"
                :placeholder="t('member.filters.search')"
                class="h-10 w-full pl-9 bg-background"
              />
            </div>
            <Select v-model="statusFilter">
              <SelectTrigger class="h-10 w-full sm:w-[160px] shrink-0 bg-background">
                <SelectValue :placeholder="t('member.filters.allStatus')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('member.filters.allStatus') }}
                </SelectItem>
                <SelectItem value="active">
                  {{ t('member.status.active') }}
                </SelectItem>
                <SelectItem value="inactive">
                  {{ t('member.status.inactive') }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Select v-model="verificationFilter">
              <SelectTrigger class="h-10 w-full sm:w-[160px] shrink-0 bg-background">
                <SelectValue :placeholder="t('member.filters.allVerified')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('member.filters.allVerified') }}
                </SelectItem>
                <SelectItem value="verified">
                  {{ t('member.verified.yes') }}
                </SelectItem>
                <SelectItem value="unverified">
                  {{ t('member.verified.no') }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Select v-model="trashedFilter">
              <SelectTrigger class="h-10 w-full sm:w-[160px] shrink-0 bg-background">
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
          </div>

          <div
            v-if="canManage && selectedIds.length > 0"
            class="flex items-center gap-2 rounded-lg border border-border/50 bg-muted/30 px-3 py-1.5 shrink-0"
          >
            <span class="text-sm font-medium text-foreground whitespace-nowrap">
              {{ t('common.messages.selectedItems', { count: selectedIds.length }) }}
            </span>
            <div class="h-4 w-px bg-border" />
            <Select
              v-model="bulkActionSelection"
              @update:model-value="handleBulkAction"
            >
              <SelectTrigger class="h-8 w-[160px]">
                <SelectValue :placeholder="t('common.actions.bulkAction')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-if="trashedFilter !== 'only'"
                  value="activate"
                >
                  {{ t('member.actions.activate') }}
                </SelectItem>
                <SelectItem
                  v-if="trashedFilter !== 'only'"
                  value="deactivate"
                >
                  {{ t('member.actions.deactivate') }}
                </SelectItem>
                <SelectItem
                  v-if="trashedFilter !== 'only'"
                  value="verify"
                >
                  {{ t('member.actions.verify') }}
                </SelectItem>
                <SelectItem
                  v-if="trashedFilter !== 'only'"
                  value="delete"
                  class="text-destructive"
                >
                  {{ t('common.actions.delete') }}
                </SelectItem>
                <SelectItem
                  v-if="trashedFilter === 'only'"
                  value="restore"
                  class="text-emerald-600"
                >
                  {{ t('common.actions.restore') }}
                </SelectItem>
                <SelectItem
                  v-if="trashedFilter === 'only'"
                  value="force_delete"
                  class="text-destructive"
                >
                  {{ t('common.actions.forceDelete') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </template>

      <DataTable
        :table="table"
        :loading="loading"
        :empty-message="t('member.messages.empty')"
        variant="embedded"
      />

      <template
        v-if="pagination && pagination.total > 0"
        #footer
      >
        <Pagination
          embedded
          :current-page="pagination.current_page"
          :total-items="pagination.total"
          :per-page="Number(pagination.per_page || 15)"
          @page-change="changePage"
          @update:per-page="changePerPage"
        />
      </template>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { computed, h, onMounted, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import {
    Activity,
    AlertCircle,
    CheckCheck,
    CheckCircle,
    Download,
    Eye,
    Pencil,
    Plus,
    RotateCcw,
    Search,
    Trash2,
    UserPlus,
    Users,
} from 'lucide-vue-next';
import {
    useVueTable,
    getCoreRowModel,
    createColumnHelper,
    getSortedRowModel,
    type SortingState,
    type RowSelectionState,
} from '@tanstack/vue-table';
import { PageHeader, ConsoleStatCard, ConsoleListCard } from '@/shared/components/shell';
import {
    Badge,
    Button,
    Checkbox,
    DataTable,
    Input,
    Pagination,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/shared/components/ui';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useToast } from '@/shared/composables/useToast';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import MemberDirectoryService, {
    type MemberDirectoryRow,
    type MemberDirectoryStats,
} from '@/modules/Member/services/memberDirectoryService';
import { parseResponse, parseSingleResponse, type PaginationData } from '@/shared/utils/responseParser';

const { t } = useI18n();
const toast = useToast();
const router = useRouter();
const authStore = useAuthStore();
const { confirm } = useConfirm();

const loading = ref(false);
const members = ref<MemberDirectoryRow[]>([]);
const pagination = ref<PaginationData | null>(null);
const search = ref('');
const statusFilter = ref('all');
const verificationFilter = ref('all');
const trashedFilter = ref('without');
const activeStatFilter = ref<string | null>(null);
const page = ref(1);
const perPage = ref(15);
const selectedIds = ref<string[]>([]);
const bulkActionSelection = ref('');

const stats = ref<MemberDirectoryStats>({
    total: 0,
    verified: 0,
    unverified: 0,
    active_status: 0,
    inactive_status: 0,
    recent: 0,
    active: 0,
    trashed: 0,
});

const canManage = computed(() => authStore.hasPermission('manage members'));

const formatDate = (value?: string | null): string => {
    if (!value) {
        return '—';
    }
    return new Date(value).toLocaleDateString();
};

const columnHelper = createColumnHelper<MemberDirectoryRow>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            'aria-label': t('common.actions.selectAll'),
            checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:checked': (val: boolean) => table.toggleAllPageRowsSelected(!!val),
        }),
        cell: ({ row }) => h(Checkbox, {
            'aria-label': t('common.actions.selectRow'),
            checked: row.getIsSelected(),
            'onUpdate:checked': (val: boolean) => row.toggleSelected(!!val),
            disabled: !canManage.value,
        }),
        size: 50,
    }),
    columnHelper.accessor('name', {
        header: t('member.table.member'),
        cell: ({ row }) => {
            const member = row.original;
            return h('div', { class: 'min-w-0' }, [
                h('div', { class: 'text-sm font-medium text-foreground flex items-center gap-2' }, [
                    member.name || member.email,
                    member.deleted_at
                        ? h(Badge, { variant: 'destructive', class: 'text-[10px] h-4 px-1' }, () => t('common.labels.deleted'))
                        : null,
                ]),
                h('div', { class: 'text-xs text-muted-foreground truncate' }, member.email),
            ]);
        },
    }),
    columnHelper.accessor('phone', {
        header: t('member.table.phone'),
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, row.original.phone || '—'),
    }),
    columnHelper.accessor('status', {
        header: t('member.table.status'),
        cell: ({ row }) => h(Badge, {
            variant: 'outline',
            class: row.original.status === 'active'
                ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20'
                : 'bg-muted text-muted-foreground',
        }, () => (
            row.original.status === 'active'
                ? t('member.status.active')
                : t('member.status.inactive')
        )),
    }),
    columnHelper.accessor('email_verified_at', {
        header: t('member.table.verified'),
        cell: ({ row }) => h(Badge, {
            variant: 'outline',
            class: row.original.email_verified_at
                ? 'bg-primary/10 text-primary border-primary/20'
                : 'bg-amber-500/10 text-amber-600 border-amber-500/20',
        }, () => (
            row.original.email_verified_at
                ? t('member.verified.yes')
                : t('member.verified.no')
        )),
    }),
    columnHelper.accessor('last_login_at', {
        header: t('member.table.lastLogin'),
        cell: ({ row }) => h('span', { class: 'text-xs text-muted-foreground' }, formatDate(row.original.last_login_at)),
    }),
    columnHelper.accessor('created_at', {
        header: t('member.table.joinedAt'),
        cell: ({ row }) => h('span', { class: 'text-xs text-muted-foreground' }, formatDate(row.original.created_at)),
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-center' }, t('member.table.actions')),
        cell: ({ row }) => {
            const member = row.original;
            const actions = [
                h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    class: 'h-8 w-8 text-primary hover:bg-primary/10',
                    onClick: () => viewMember(member),
                    'aria-label': t('member.actions.view'),
                }, () => h(Eye, { class: 'w-4 h-4' })),
            ];

            if (canManage.value) {
                if (!member.deleted_at) {
                    actions.push(h(Button, {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-primary hover:bg-primary/10',
                        onClick: () => editMember(member),
                        'aria-label': t('common.actions.edit'),
                    }, () => h(Pencil, { class: 'w-4 h-4' })));
                }

                if (!member.email_verified_at && !member.deleted_at) {
                    actions.push(h(Button, {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-primary hover:bg-primary/10',
                        onClick: () => verifyMember(member),
                        'aria-label': t('member.actions.verify'),
                    }, () => h(CheckCheck, { class: 'w-4 h-4' })));
                }

                if (member.deleted_at) {
                    actions.push(
                        h(Button, {
                            variant: 'ghost',
                            size: 'icon',
                            class: 'h-8 w-8 text-emerald-600 hover:bg-emerald-500/10',
                            onClick: () => restoreMember(member),
                            'aria-label': t('common.actions.restore'),
                        }, () => h(RotateCcw, { class: 'w-4 h-4' })),
                        h(Button, {
                            variant: 'ghost',
                            size: 'icon',
                            class: 'h-8 w-8 text-destructive hover:bg-destructive/10',
                            onClick: () => deleteMember(member, true),
                            'aria-label': t('common.actions.forceDelete'),
                        }, () => h(Trash2, { class: 'w-4 h-4' })),
                    );
                } else {
                    actions.push(
                        h(Button, {
                            variant: 'ghost',
                            size: 'icon',
                            class: 'h-8 w-8 text-muted-foreground hover:bg-muted',
                            onClick: () => toggleStatus(member),
                            'aria-label': member.status === 'active'
                                ? t('member.actions.deactivate')
                                : t('member.actions.activate'),
                        }, () => h('span', { class: 'text-[10px] font-bold uppercase' }, member.status === 'active' ? 'OFF' : 'ON')),
                        h(Button, {
                            variant: 'ghost',
                            size: 'icon',
                            class: 'h-8 w-8 text-destructive hover:bg-destructive/10',
                            onClick: () => deleteMember(member, false),
                            'aria-label': t('common.actions.delete'),
                        }, () => h(Trash2, { class: 'w-4 h-4' })),
                    );
                }
            }

            return h('div', { class: 'flex justify-center items-center gap-1' }, actions);
        },
    }),
];

const sorting = ref<SortingState>([]);
const rowSelection = ref<RowSelectionState>({});

const table = useVueTable({
    get data() { return members.value; },
    columns,
    state: {
        get sorting() { return sorting.value; },
        get rowSelection() { return rowSelection.value; },
    },
    onSortingChange: (updaterOrValue) => {
        sorting.value = typeof updaterOrValue === 'function'
            ? updaterOrValue(sorting.value)
            : updaterOrValue;
    },
    onRowSelectionChange: (updaterOrValue) => {
        rowSelection.value = typeof updaterOrValue === 'function'
            ? updaterOrValue(rowSelection.value)
            : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: (row) => String(row.id),
    enableRowSelection: () => canManage.value,
});

watch(rowSelection, (selection) => {
    selectedIds.value = Object.keys(selection).filter((key) => selection[key]);
}, { deep: true });

watch(members, () => {
    rowSelection.value = {};
});

const buildParams = (): Record<string, string | number> => {
    const params: Record<string, string | number> = {
        page: page.value,
        per_page: perPage.value,
    };
    if (search.value.trim()) {
        params.search = search.value.trim();
    }
    if (statusFilter.value !== 'all') {
        params.status = statusFilter.value;
    }
    if (verificationFilter.value !== 'all') {
        params.verified = verificationFilter.value;
    }
    if (trashedFilter.value !== 'without') {
        params.trashed = trashedFilter.value;
    }
    if (activeStatFilter.value) {
        params.stat = activeStatFilter.value;
    }
    return params;
};

const fetchStats = async (): Promise<void> => {
    try {
        const response = await MemberDirectoryService.stats();
        const data = parseSingleResponse<MemberDirectoryStats>(response);
        if (data) {
            stats.value = data;
        }
    } catch {
        // non-blocking
    }
};

const fetchMembers = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await MemberDirectoryService.list(buildParams());
        const { data, pagination: pag } = parseResponse<MemberDirectoryRow>(response);
        members.value = data;
        pagination.value = pag ?? null;
    } catch (error: unknown) {
        toast.error.fromResponse(error);
        members.value = [];
    } finally {
        loading.value = false;
    }
};

const debouncedSearch = useDebounceFn(() => {
    page.value = 1;
    void fetchMembers();
}, 300);

watch(search, () => {
    void debouncedSearch();
});

watch([statusFilter, verificationFilter, trashedFilter], () => {
    page.value = 1;
    void fetchMembers();
});

const changePage = (nextPage: number): void => {
    page.value = nextPage;
    void fetchMembers();
};

const changePerPage = (nextPerPage: number): void => {
    perPage.value = nextPerPage;
    page.value = 1;
    void fetchMembers();
};

const clearFilters = (): void => {
    verificationFilter.value = 'all';
    statusFilter.value = 'all';
    activeStatFilter.value = null;
    trashedFilter.value = 'without';
    page.value = 1;
    void fetchMembers();
};

const setVerificationFilter = (value: string): void => {
    verificationFilter.value = value;
    activeStatFilter.value = null;
    page.value = 1;
    void fetchMembers();
};

const setStatFilter = (value: string): void => {
    activeStatFilter.value = value;
    verificationFilter.value = 'all';
    statusFilter.value = 'all';
    page.value = 1;
    void fetchMembers();
};

const viewMember = (member: MemberDirectoryRow): void => {
    void router.push({ name: 'members.show', params: { id: member.id } });
};

const editMember = (member: MemberDirectoryRow): void => {
    void router.push({ name: 'members.edit', params: { id: member.id } });
};

const toggleStatus = async (member: MemberDirectoryRow): Promise<void> => {
    const nextStatus = member.status === 'active' ? 'inactive' : 'active';
    const confirmed = await confirm({
        title: nextStatus === 'active' ? t('member.actions.activate') : t('member.actions.deactivate'),
        message: t('member.confirm.status', { email: member.email, status: t(`member.status.${nextStatus}`) }),
        variant: 'warning',
        confirmText: t('common.actions.confirm'),
    });
    if (!confirmed) {
        return;
    }
    try {
        await MemberDirectoryService.update(member.id, { status: nextStatus });
        toast.success.action(t('member.actions.updated'));
        await Promise.all([fetchMembers(), fetchStats()]);
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    }
};

const verifyMember = async (member: MemberDirectoryRow): Promise<void> => {
    const confirmed = await confirm({
        title: t('member.actions.verify'),
        message: t('member.confirm.verify', { email: member.email }),
        variant: 'info',
        confirmText: t('member.actions.verify'),
    });
    if (!confirmed) {
        return;
    }
    try {
        await MemberDirectoryService.update(member.id, { verify_email: true });
        toast.success.action(t('member.messages.verified'));
        await Promise.all([fetchMembers(), fetchStats()]);
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    }
};

const deleteMember = async (member: MemberDirectoryRow, force: boolean): Promise<void> => {
    const confirmed = await confirm({
        title: force ? t('common.actions.forceDelete') : t('common.actions.delete'),
        message: force
            ? t('member.confirm.forceDelete', { email: member.email })
            : t('member.confirm.delete', { email: member.email }),
        variant: 'danger',
        confirmText: force ? t('common.actions.forceDelete') : t('common.actions.delete'),
    });
    if (!confirmed) {
        return;
    }
    try {
        if (force) {
            await MemberDirectoryService.forceDelete(member.id);
        } else {
            await MemberDirectoryService.destroy(member.id);
        }
        toast.success.delete(t('member.title_singular', 'Member'));
        await Promise.all([fetchMembers(), fetchStats()]);
    } catch (error: unknown) {
        toast.error.delete(error, t('member.title_singular', 'Member'));
    }
};

const restoreMember = async (member: MemberDirectoryRow): Promise<void> => {
    const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: t('member.confirm.restore', { email: member.email }),
        variant: 'info',
        confirmText: t('common.actions.restore'),
    });
    if (!confirmed) {
        return;
    }
    try {
        await MemberDirectoryService.restore(member.id);
        toast.success.restore(t('member.title_singular', 'Member'));
        await Promise.all([fetchMembers(), fetchStats()]);
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    }
};

const handleBulkAction = async (action: string): Promise<void> => {
    bulkActionSelection.value = '';
    if (!action || selectedIds.value.length === 0) {
        return;
    }
    const confirmed = await confirm({
        title: t('common.actions.bulkAction'),
        message: t('member.confirm.bulk', { count: selectedIds.value.length, action }),
        variant: action.includes('delete') ? 'danger' : 'warning',
        confirmText: t('common.actions.confirm'),
    });
    if (!confirmed) {
        return;
    }
    try {
        await MemberDirectoryService.bulkAction({ ids: selectedIds.value, action });
        toast.success.action(t('member.messages.bulkSuccess'));
        rowSelection.value = {};
        await Promise.all([fetchMembers(), fetchStats()]);
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    }
};

const exportCsv = async (): Promise<void> => {
    try {
        const response = await MemberDirectoryService.export(buildParams());
        const url = window.URL.createObjectURL(new Blob([response.data as Blob]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `members-${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    }
};

onMounted(() => {
    void Promise.all([fetchMembers(), fetchStats()]);
});
</script>
