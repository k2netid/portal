<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="t('system.users.title')"
      :subtitle="t('system.users.subtitle')"
    >
      <template #actions>
        <router-link
          v-if="authStore.hasPermission('create users')"
          :to="{ name: 'users.create' }"
        >
          <Button size="sm" class="gap-2">
            <Plus class="w-4 h-4" />
            {{ t('system.users.createNew') }}
          </Button>
        </router-link>
      </template>
    </PageHeader>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
      <ConsoleStatCard
        :label="t('system.users.stats.total')"
        :value="stats.total"
        :icon="Users"
        tone="primary"
        :active="verificationFilter === 'all' && !activeStatFilter"
        clickable
        @click="clearFilters"
      />
      <ConsoleStatCard
        :label="t('system.users.stats.verified')"
        :value="stats.verified"
        :icon="CheckCircle"
        tone="primary"
        :active="verificationFilter === 'verified'"
        clickable
        @click="setVerificationFilter('verified')"
      />
      <ConsoleStatCard
        :label="t('system.users.stats.unverified')"
        :value="stats.unverified"
        :icon="AlertCircle"
        tone="warning"
        :active="verificationFilter === 'unverified'"
        clickable
        @click="setVerificationFilter('unverified')"
      />
      <ConsoleStatCard
        :label="t('system.users.stats.recent')"
        :value="stats.recent"
        :icon="UserPlus"
        tone="success"
        :active="activeStatFilter === 'recent'"
        clickable
        @click="setStatFilter('recent')"
      />
      <ConsoleStatCard
        :label="t('system.users.stats.active')"
        :value="stats.active"
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
                :placeholder="t('system.users.search')"
                class="h-10 w-full pl-9 bg-background"
              />
            </div>
            <Select v-model="roleFilter">
              <SelectTrigger class="h-10 w-full sm:w-[160px] shrink-0 bg-background" :aria-label="t('system.users.allRoles')">
                <SelectValue :placeholder="t('system.users.allRoles')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('system.users.allRoles') }}
                </SelectItem>
                <SelectItem
                  v-for="role in roles"
                  :key="role.id"
                  :value="role.name"
                >
                  {{ role.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Select v-model="verificationFilter">
              <SelectTrigger class="h-10 w-full sm:w-[160px] shrink-0 bg-background" :aria-label="t('system.users.filters.verification')">
                <SelectValue :placeholder="t('system.users.filters.all')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('system.users.filters.all') }}
                </SelectItem>
                <SelectItem value="verified">
                  {{ t('system.users.filters.verifiedOnly') }}
                </SelectItem>
                <SelectItem value="unverified">
                  {{ t('system.users.filters.unverifiedOnly') }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Select v-model="trashedFilter">
              <SelectTrigger class="h-10 w-full sm:w-[160px] shrink-0 bg-background" :aria-label="t('common.labels.status')">
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
            v-if="selectedIds.length > 0"
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
              <SelectTrigger class="h-8 w-[140px]" :aria-label="t('publishing.content.list.bulkActions')">
                <SelectValue :placeholder="t('publishing.content.list.bulkActions')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  value="force_logout"
                  class="text-warning"
                >
                  {{ t('system.users.actions.forceLogout') }}
                </SelectItem>
                <SelectItem
                  value="verify"
                >
                  {{ t('system.users.actions.verify') }}
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
                  class="text-success"
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
        :empty-message="t('system.users.empty')"
        variant="embedded"
      />

      <template
        v-if="pagination && pagination.total > 0"
        #footer
      >
        <Pagination
          :current-page="pagination.current_page"
          :total-items="pagination.total"
          :per-page="Number(pagination.per_page || 10)"
          embedded
          @page-change="changePage"
          @update:per-page="changePerPage"
        />
      </template>
    </ConsoleListCard>

        <!-- Create/Edit Modal Removed -->
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleStatCard, ConsoleListCard } from '@/shared/components/shell';
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import api from '@/engine/api/client';
import { parseResponse, ensureArray, type PaginationData } from '@/shared/utils/responseParser';
import { useToast } from '@/shared/composables/useToast';
import {
    Pagination,
    Button,
    Input,
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
    Checkbox,
    Badge,
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
import {
  Activity,
  AlertCircle,
  CheckCheck,
  CheckCircle,
  LogOut,
  Pencil,
  Plus,
  RotateCcw,
  Search,
  Trash2,
  UserPlus,
  Users,
} from 'lucide-vue-next';
import { useAuthStore, ROLE_RANKS } from '@/modules/Core/System/stores/auth';
import { useConfirm } from '@/shared/composables/useConfirm';
import type { User, Role } from '@/engine/types/auth';

const { t } = useI18n();
const toast = useToast();
const router = useRouter();
const loading = ref(false);
const users = ref<User[]>([]);
const roles = ref<Role[]>([]);
const search = ref('');
const roleFilter = ref('all');
const verificationFilter = ref('all');
const trashedFilter = ref('without');
const activeStatFilter = ref<string | null>(null);
const pagination = ref<PaginationData | null>(null);
const authStore = useAuthStore();

const { confirm } = useConfirm();
const columnHelper = createColumnHelper<User>();

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
        header: t('system.users.table.user'),
        cell: ({ row }) => {
            const user = row.original;
            const avatarUrl = user.avatar ? (typeof user.avatar === 'string' ? user.avatar : user.avatar.url) : null;
            
            return h('div', { class: 'flex items-center' }, [
                h('div', { class: 'flex-shrink-0 h-9 w-9' }, [
                    avatarUrl 
                        ? h('img', { src: avatarUrl, class: 'h-9 w-9 rounded-full object-cover' })
                        : h('div', { class: 'h-9 w-9 rounded-full bg-muted flex items-center justify-center border border-border/40' }, [
                            h('span', { class: 'text-muted-foreground font-medium text-xs' }, user?.name?.charAt(0)?.toUpperCase() || 'U')
                        ])
                ]),
                h('div', { class: 'ml-4' }, [
                    h('div', { class: 'text-sm font-medium text-foreground flex items-center gap-2' }, [
                        user.name,
                        user.deleted_at ? h(Badge, { variant: 'destructive', class: 'text-[10px] h-4 px-1' }, () => t('common.labels.deleted')) : null
                    ]),
                    user.phone ? h('div', { class: 'text-[10px] text-muted-foreground font-mono uppercase tracking-tight' }, user.phone) : null
                ])
            ]);
        }
    }),
    columnHelper.accessor('email', {
        header: t('system.users.table.email'),
        cell: ({ row }) => {
            const user = row.original;
            return h('div', [
                h('div', { class: 'text-sm text-foreground' }, user.email),
                user.email_verified_at 
                    ? h('div', { class: 'text-[10px] text-primary font-bold uppercase tracking-wider' }, t('system.users.status.verified'))
                    : h('div', { class: 'text-[10px] text-muted-foreground italic uppercase tracking-wider' }, t('system.users.status.unverified'))
            ]);
        }
    }),
    columnHelper.accessor('roles', {
        header: t('system.users.table.roles'),
        cell: ({ row }) => {
            const roles = row.original.roles || [];
            if (roles.length === 0) return h('span', { class: 'text-xs text-muted-foreground italic' }, t('system.users.status.noRoles'));
            
            return h('div', { class: 'flex flex-wrap gap-1.5' }, roles.map(role => h(Badge, {
                variant: 'secondary',
                class: 'h-5 text-[10px] px-2 font-semibold uppercase tracking-wider'
            }, () => role.name)));
        }
    }),
    columnHelper.accessor('last_login_at', {
        header: t('system.users.table.lastLogin'),
        cell: ({ row }) => {
            const date = row.original.last_login_at;
            if (!date) return h('div', { class: 'text-xs text-muted-foreground' }, t('system.users.status.never'));
            return h('div', { class: 'text-xs' }, formatDate(date));
        }
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-center' }, t('system.users.table.actions')),
        cell: ({ row }) => {
            const user = row.original;
            const canManageUser = canManage(user);
            const canDeleteUser = canDelete(user);
            
            return h('div', { class: 'flex justify-center items-center gap-1' }, [
                !user.email_verified_at && authStore.hasPermission('edit users') && h(Button, {
                    variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-primary hover:bg-primary/10',
                    disabled: !canManageUser, onClick: () => verifyUser(user), 'aria-label': t('system.users.actions.verify')
                }, () => h(CheckCheck, { class: 'w-4 h-4' })),
                
                authStore.hasPermission('edit users') && h(Button, {
                    variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-warning hover:bg-warning/10',
                    disabled: !canManageUser, onClick: () => forceLogoutUser(user), 'aria-label': t('system.users.actions.forceLogout')
                }, () => h(LogOut, { class: 'w-4 h-4' })),
                
                authStore.hasPermission('edit users') && h(Button, {
                    variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-primary hover:bg-primary/10',
                    disabled: !canManageUser, onClick: () => editUser(user), 'aria-label': t('common.actions.edit')
                }, () => h(Pencil, { class: 'w-4 h-4' })),
                
                user.deleted_at 
                    ? [
                        authStore.hasPermission('delete users') && h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-success hover:bg-success/10',
                            onClick: () => restoreUser(user), 'aria-label': t('common.actions.restore')
                        }, () => h(RotateCcw, { class: 'w-4 h-4' })),
                        authStore.hasPermission('delete users') && h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive hover:bg-destructive/10',
                            onClick: () => forceDeleteUser(user), 'aria-label': t('common.actions.forceDelete')
                        }, () => h(Trash2, { class: 'w-4 h-4' }))
                    ]
                    : [
                        authStore.hasPermission('delete users') && h(Button, {
                            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive hover:bg-destructive/10',
                            disabled: !canDeleteUser, onClick: () => deleteUser(user), 'aria-label': t('common.actions.delete')
                        }, () => h(Trash2, { class: 'w-4 h-4' }))
                    ]
            ]);
        }
    })
];

const sorting = ref<SortingState>([]);
const rowSelection = ref<RowSelectionState>({});

const table = useVueTable({
    get data() { return users.value },
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
        .map(id => String(id));
}, { deep: true });

// Clear selection when users change (pagination/filter)
watch(users, () => {
    rowSelection.value = {};
});

const stats = ref<{
    total: number;
    verified: number;
    unverified: number;
    recent: number;
    active: number;
    by_role: Record<string, number>;
}>({
    total: 0,
    verified: 0,
    unverified: 0,
    recent: 0,
    active: 0,
    by_role: {},
});

const isSuperAdmin = (u: User) => u.roles?.some(r => (ROLE_RANKS[r.name] || 0) >= 100);

const canManage = (targetUser: User) => {
    // Self management is always allowed (for basic edits)
    if (targetUser.id === authStore.user?.id) return true;
    
    // Super Admin (Rank 100) can manage anyone
    if (authStore.getRoleRank() >= 100) return true;

    // Others must strictly be higher rank
    return authStore.isHigherThan(targetUser);
};

const canDelete = (targetUser: User) => {
    // Cannot delete self
    if (targetUser.id === authStore.user?.id) return false;
    
    const myRank = authStore.getRoleRank();
    
    // Non-Super Admins can only delete users with strictly lower rank
    if (myRank < 100) {
        if (!authStore.isHigherThan(targetUser)) return false;
    }
    
    // Super Admin protection for last one
    if (isSuperAdmin(targetUser)) {
        const superAdminCount = users.value.filter(u => isSuperAdmin(u)).length;
        // This is only a frontend check, backend will re-verify
        if ((pagination.value?.total || 0) <= 1 || superAdminCount <= 1) return false;
    }
    
    return true;
};

const fetchUsers = async () => {
    loading.value = true;
    try {
        const params: Record<string, string | number | boolean | undefined> = {
            page: pagination.value?.current_page || 1,
            per_page: pagination.value?.per_page || 10,
        };

        if (search.value) {
            params.search = search.value;
        }

        // Don't send 'all' role to API
        if (roleFilter.value && roleFilter.value !== 'all') {
            params.role = roleFilter.value;
        }

        // Add verification filter
        if (verificationFilter.value === 'verified') {
            params.verified = 1;
        } else if (verificationFilter.value === 'unverified') {
            params.verified = 0;
        }

        // Add trashed filter
        if (trashedFilter.value && trashedFilter.value !== 'without') {
            params.trashed = trashedFilter.value;
        }

        // Add stat filters
        if (activeStatFilter.value === 'recent') {
            params.recent = 1;
        } else if (activeStatFilter.value === 'active') {
            params.active = 1;
        }

        const response = await api.get('/manage/system/users', { params });
        const { data, pagination: paginationData } = parseResponse(response);
        // Ensure each user has roles array
        users.value = (ensureArray(data) as User[]).map((user: User) => ({
            ...user,
            roles: user.roles || [],
        }));
        if (paginationData) {
            pagination.value = paginationData;
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch users:', error);
        toast.error.action(error as Record<string, unknown>);
    } finally {
        loading.value = false;
    }
};

const fetchStats = async () => {
    try {
        const response = await api.get('/manage/system/users/stats');
        // The BaseApiController returns { success: true, data: { ... }, message: ... }
        // parseResponse returns { data: [...], pagination: ... } which is for lists.
        // We just need the raw data object here.
        if (response.data && response.data) {
            stats.value = response.data;
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch stats:', error);
    }
};

const setVerificationFilter = (filter: string) => {
    activeStatFilter.value = null;
    verificationFilter.value = filter;
};

const setStatFilter = (filter: string) => {
    verificationFilter.value = 'all';
    activeStatFilter.value = activeStatFilter.value === filter ? null : filter;
    fetchUsers();
};

const clearFilters = () => {
    verificationFilter.value = 'all';
    activeStatFilter.value = null;
    roleFilter.value = 'all';
    trashedFilter.value = 'without';
    search.value = '';
};

const fetchRoles = async () => {
    try {
        const response = await api.get('/manage/system/roles').catch(() => null);
        if (response) {
            const { data: rolesData } = parseResponse(response);
            roles.value = ensureArray(rolesData);
        } else {
            // Fallback: Extract unique roles from users
            const uniqueRoles = new Map();
            users.value.forEach(user => {
                user.roles?.forEach(role => {
                    if (!uniqueRoles.has(role.id)) {
                        uniqueRoles.set(role.id, role);
                    }
                });
            });
            roles.value = Array.from(uniqueRoles.values());
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch roles:', error);
    }
};

const changePage = (page: number) => {
    if (pagination.value) {
        pagination.value.current_page = page;
        fetchUsers();
    }
};

const changePerPage = (perPage: number | string) => {
    if (pagination.value) {
        pagination.value.per_page = Number(perPage);
        pagination.value.current_page = 1;
        fetchUsers();
    }
};

const editUser = (user: User) => {
    router.push({ name: 'users.edit', params: { id: user.id } });
};

const deleteUser = async (user: User) => {
    const confirmed = await confirm({
        title: t('common.messages.confirm.title'),
        message: t('system.users.messages.deleteConfirm', { name: user.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) {
        return;
    }

    try {
        await api.delete(`/manage/system/users/${user.id}`);
        await fetchUsers();
        toast.success.delete(t('system.users.title_singular'));
    } catch (error: unknown) {
        logger.error('Failed to delete user:', error);
        toast.error.delete(error as Record<string, unknown>, 'User');
    }
};

const forceLogoutUser = async (user: User) => {
    const confirmed = await confirm({
        title: t('system.users.actions.forceLogout'),
        message: t('system.users.messages.forceLogoutConfirm', { name: user.name }),
        variant: 'warning',
        confirmText: t('system.users.actions.forceLogout'),
    });

    if (!confirmed) {
        return;
    }

    try {
        await api.post(`/manage/system/users/${user.id}/force-logout`);
        
        toast.success.action(t('system.users.messages.forceLogoutSuccess'));
    } catch (error: unknown) {
        logger.error('Failed to force logout user:', error);
        toast.error.action(error as Record<string, unknown>);
    }
};

const verifyUser = async (user: User) => {
    try {
        await api.post(`/manage/system/users/${user.id}/verify`);
        toast.success.action(t('system.users.messages.verifySuccess'));
        await fetchUsers();
    } catch (error: unknown) {
        logger.error('Failed to verify user:', error);
        toast.error.action(error as Record<string, unknown>);
    }
};

const restoreUser = async (user: User) => {
    const confirmed = await confirm({
        title: t('system.users.confirm.restoreTitle'),
        message: t('system.users.confirm.restoreMessage', { name: user.name }),
        variant: 'info',
        confirmText: t('system.users.confirm.restoreConfirm'),
    });

    if (!confirmed) return;

    try {
        await api.post(`/manage/system/users/${user.id}/restore`);
        toast.success.action(t('system.users.messages.restoreSuccess'));
        await fetchUsers();
    } catch (error: unknown) {
        logger.error('Failed to restore user:', error);
        toast.error.action(error as Record<string, unknown>);
    }
};

const forceDeleteUser = async (user: User) => {
    const confirmed = await confirm({
        title: t('system.users.confirm.forceDeleteTitle'),
        message: t('system.users.confirm.forceDeleteMessage', { name: user.name }),
        variant: 'danger',
        confirmText: t('system.users.confirm.forceDeleteConfirm'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/manage/system/users/${user.id}/force-delete`);
        toast.success.action(t('system.users.messages.forceDeleteSuccess'));
        await fetchUsers();
    } catch (error: unknown) {
        logger.error('Failed to force delete user:', error);
        toast.error.action(error as Record<string, unknown>);
    }
};

const selectedIds = ref<string[]>([]);


const bulkActionSelection = ref('');

const handleBulkAction = async (value: string) => {
    if (!value) return;
    await bulkAction(value);
    bulkActionSelection.value = '';
};

const bulkAction = async (action: string) => {
    if (selectedIds.value.length === 0) return;
    
    let confirmMessage = '';
    let confirmVariant = 'warning';
    let confirmTitle = t('publishing.content.list.bulkActions');

    if (action === 'delete') {
        confirmMessage = t('common.messages.confirm.bulkDelete', { count: selectedIds.value.length });
        confirmVariant = 'danger';
        confirmTitle = t('common.actions.delete');
    } else if (action === 'force_logout') {
        confirmMessage = t('system.users.messages.bulkForceLogoutConfirm', { count: selectedIds.value.length }) || `Force logout ${selectedIds.value.length} users?`;
        confirmTitle = t('system.users.actions.forceLogout');
    } else if (action === 'verify') {
        confirmMessage = t('system.users.messages.bulkVerifyConfirm', { count: selectedIds.value.length }) || `Verify ${selectedIds.value.length} users?`;
        confirmTitle = t('system.users.actions.verify');
    } else if (action === 'restore') {
        confirmMessage = `Restore ${selectedIds.value.length} users?`;
        confirmTitle = 'Restore Users';
        confirmVariant = 'info';
    } else if (action === 'force_delete') {
         confirmMessage = `Permanently delete ${selectedIds.value.length} users? This cannot be undone.`;
         confirmTitle = 'Force Delete Users';
        confirmVariant = 'danger';
    }

    const confirmed = await confirm({
        title: confirmTitle,
        message: confirmMessage,
        variant: confirmVariant as 'success' | 'warning' | 'info' | 'danger',
        confirmText: t('common.actions.confirm'),
    });

    if (!confirmed) {
        bulkActionSelection.value = '';
        return;
    }

    try {
        await api.post('/manage/system/users/bulk-action', {
            ids: selectedIds.value,
            action: action
        });
        
        selectedIds.value = [];
        await fetchUsers();
        
        toast.success.action(t('system.users.messages.bulkActionSuccess'));
    } catch (error: unknown) {
        logger.error('Bulk action failed:', error);
        toast.error.action(error as Record<string, unknown>);
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

watch([search, roleFilter, verificationFilter, trashedFilter, activeStatFilter], () => {
    if (pagination.value) {
        pagination.value.current_page = 1;
    }
    fetchUsers();
});

const route = useRoute();

onMounted(() => {
    // Check for search query param from Global Search
    if (route.query.q) {
        search.value = route.query.q as string;
    }
    fetchUsers();
    fetchRoles();
    fetchStats();
});
</script>
