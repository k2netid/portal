<template>
  <div class="space-y-6">
    <PageHeader
borderless :title="t('system.activity_journal.title')"
      :subtitle="t('system.activity_journal.subtitle')"
    >
      <template #actions>
        <Button
          size="sm"
          variant="ghost"
          class="border border-red-800/40 bg-red-800 text-white hover:bg-red-900 hover:text-white"
          @click="clearLogs"
        >
          {{ t('system.system.logs.clear') }}
        </Button>
      </template>
    </PageHeader>

    <!-- Statistics -->
    <div
      v-if="statistics"
      class="grid grid-cols-1 gap-4 md:grid-cols-4"
    >
      <ConsoleStatCard
        :label="t('system.activity_journal.stats.total')"
        :value="statistics.total || 0"
        :icon="ClipboardList"
        tone="primary"
      />
      <ConsoleStatCard
        :label="t('system.activity_journal.stats.today')"
        :value="statistics.today || 0"
        :icon="Clock"
        tone="success"
      />
      <ConsoleStatCard
        :label="t('system.activity_journal.stats.activeUsers')"
        :value="statistics.active_users || 0"
        :icon="Users"
        tone="info"
      />
      <ConsoleStatCard
        :label="t('system.activity_journal.stats.thisWeek')"
        :value="statistics.this_week || 0"
        :icon="BarChart3"
        tone="muted"
      />
    </div>

    <ConsoleListCard>
      <template #toolbar>
      <div class="flex flex-col gap-4 w-full p-0">
        <!-- Row 1: Search, Filters, Export -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
          <div class="flex flex-wrap items-center gap-3">
            <Input
              v-model="search"
              type="text"
              :placeholder="t('system.activity_journal.filters.search')"
              class="w-48"
            />
            <Select v-model="typeFilter">
              <SelectTrigger
                class="w-[180px]"
                :aria-label="t('system.activity_journal.filters.allTypes')"
              >
                <SelectValue :placeholder="t('system.activity_journal.filters.allTypes')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('system.activity_journal.filters.allTypes') }}
                </SelectItem>
                <SelectItem value="created">
                  {{ t('system.activity_journal.filters.types.created') }}
                </SelectItem>
                <SelectItem value="updated">
                  {{ t('system.activity_journal.filters.types.updated') }}
                </SelectItem>
                <SelectItem value="deleted">
                  {{ t('system.activity_journal.filters.types.deleted') }}
                </SelectItem>
                <SelectItem value="login">
                  {{ t('system.activity_journal.filters.types.login') }}
                </SelectItem>
                <SelectItem value="logout">
                  {{ t('system.activity_journal.filters.types.logout') }}
                </SelectItem>
                <SelectItem value="viewed">
                  {{ t('system.activity_journal.filters.types.viewed') }}
                </SelectItem>
                <SelectItem value="published">
                  {{ t('system.activity_journal.filters.types.published') }}
                </SelectItem>
                <SelectItem value="unpublished">
                  {{ t('system.activity_journal.filters.types.unpublished') }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Select v-model="userFilter">
              <SelectTrigger
                class="w-[180px]"
                :aria-label="t('system.activity_journal.filters.allUsers')"
              >
                <SelectValue :placeholder="t('system.activity_journal.filters.allUsers')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('system.activity_journal.filters.allUsers') }}
                </SelectItem>
                <SelectItem
                  v-for="user in users"
                  :key="user.id"
                  :value="String(user.id)"
                >
                  {{ user.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <Button
            :disabled="exporting"
            @click="exportLogs"
          >
            <Download class="w-4 h-4 mr-2" />
            {{ exporting ? 'Exporting...' : 'Export CSV' }}
          </Button>
        </div>
                
        <!-- Row 2: Date Range & Per Page -->
        <div class="flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
              <label
                for="activity-journal-date-from"
                class="text-sm text-muted-foreground"
              >{{ t('system.activity_journal.filters.dateFrom') }}:</label>
              <Input
                id="activity-journal-date-from"
                v-model="dateFrom"
                type="date"
                class="w-36"
              />
            </div>
            <div class="flex items-center gap-2">
              <label
                for="activity-journal-date-to"
                class="text-sm text-muted-foreground"
              >{{ t('system.activity_journal.filters.dateTo') }}:</label>
              <Input
                id="activity-journal-date-to"
                v-model="dateTo"
                type="date"
                class="w-36"
              />
            </div>
            <button
              v-if="dateFrom || dateTo"
              class="text-sm text-muted-foreground hover:text-foreground"
              @click="clearDateFilter"
            >
              Clear
            </button>
          </div>
          <div class="flex items-center gap-2">
            <label
              for="activity-journal-per-page"
              class="text-sm text-muted-foreground"
            >{{ t('system.activity_journal.filters.perPage') }}:</label>
            <Select
              id="activity-journal-per-page"
              :model-value="String(perPage)"
              @update:model-value="perPage = Number($event); fetchLogs()"
            >
              <SelectTrigger
                class="w-[80px]"
                :aria-label="t('system.activity_journal.filters.perPage')"
              >
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="25">
                  25
                </SelectItem>
                <SelectItem value="50">
                  50
                </SelectItem>
                <SelectItem value="100">
                  100
                </SelectItem>
                <SelectItem value="200">
                  200
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>
      </template>

      <div
        v-if="loading"
        class="p-6 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('system.activity_journal.messages.loading') }}
        </p>
      </div>

      <div
        v-else-if="filteredLogs.length === 0"
        class="p-6 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('system.activity_journal.messages.empty') }}
        </p>
      </div>

      <div
        v-if="filteredLogs.length > 0"
        class="divide-y divide-border"
      >
        <div
          v-for="log in filteredLogs"
          :key="log.id"
          class="px-6 py-4 hover:bg-muted"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center space-x-2">
                <Badge
                  variant="outline"
                  :class="activityActionBadgeClass(log.action || log.type)"
                >
                  {{ t(`system.activity_journal.filters.types.${log.action || log.type}`) || (log.action || log.type || t('system.activity_journal.messages.unknown')) }}
                </Badge>
                <span class="text-sm font-medium text-foreground">{{ log.description }}</span>
              </div>
              <div class="mt-1 flex items-center space-x-4 text-sm text-muted-foreground">
                <span>{{ log.user?.name || t('system.activity_journal.messages.system') }}</span>
                <span>{{ log.model_type || '' }}</span>
                <span>{{ formatDate(log.created_at) }}</span>
              </div>
              <div
                v-if="log.properties || log.changes"
                class="mt-2 text-xs text-muted-foreground"
              >
                <details class="group">
                  <summary class="cursor-pointer hover:text-foreground flex items-center gap-1">
                    <Eye class="w-3 h-3 group-open:hidden" />
                    <EyeOff class="w-3 h-3 hidden group-open:block" />
                    {{ t('system.activity_journal.messages.viewDetails') }}
                  </summary>
                                    
                  <div class="mt-2 p-3 bg-muted/50 rounded-lg border border-border overflow-x-auto">
                    <!-- Visual Diff for Updated Action -->
                    <div
                      v-if="log.action === 'updated' && log.changes?.before"
                      class="space-y-2"
                    >
                      <div
                        v-for="(val, key) in log.changes.after"
                        :key="key"
                        class="grid grid-cols-1 sm:grid-cols-2 gap-2 pb-2 border-b border-border/50 last:border-0 last:pb-0"
                      >
                        <div class="flex flex-col gap-1">
                          <span class="text-[10px] uppercase font-bold text-muted-foreground">{{ key }}</span>
                          <div
                            class="p-1 px-2 rounded bg-red-500/10 text-red-700 dark:text-red-400 line-through truncate"
                            :title="String(getRecursiveValue(log.changes.before, key))"
                          >
                            {{ formatValue(getRecursiveValue(log.changes.before, key)) }}
                          </div>
                        </div>
                        <div class="flex flex-col gap-1 pt-4 sm:pt-0">
                          <span class="hidden sm:inline-block text-[10px]">&nbsp;</span>
                          <div
                            class="p-1 px-2 rounded bg-green-500/10 text-green-700 dark:text-green-400 truncate"
                            :title="String(val)"
                          >
                            {{ formatValue(val) }}
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Generic JSON for other actions -->
                    <pre
                      v-else
                      class="text-[10px] font-mono whitespace-pre-wrap"
                    >{{ JSON.stringify(log.properties || log.changes, null, 2) }}</pre>
                  </div>
                </details>
              </div>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
      <Pagination
        v-if="pagination && pagination.total > 0"
        :current-page="pagination.current_page"
        :total-items="pagination.total"
        :per-page="Number(perPage)"
        embedded class="mt-4"
        @page-change="fetchLogs"
        @update:per-page="(val) => { perPage = val; fetchLogs(1); }"
      />
      </template>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
function activityActionBadgeClass(action: string | undefined): string {
    const a = action ?? '';
    if (a === 'created') return 'border-transparent bg-emerald-800 text-white';
    if (a === 'updated') return 'border-transparent bg-blue-800 text-white';
    if (a === 'deleted') return 'border-transparent bg-red-800 text-white';
    return 'border-border bg-muted text-foreground';
}

import { PageHeader, ConsoleListCard, ConsoleStatCard } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { parseResponse, ensureArray, getResponseList, type PaginationData } from '@/shared/utils/responseParser';
import {
    Button,
    Pagination,
    Input,
    Badge,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/shared/components/ui';

import {
  BarChart3,
  ClipboardList,
  Clock,
  Download,
  Eye,
  EyeOff,
  Users} from 'lucide-vue-next';

interface User {
    id: string;
    name: string;
    email: string;
}

interface ActivityLog {
    id: string;
    action: string;
    type?: string;
    description: string;
    user?: User | null;
    model_type: string | null;
    properties?: Record<string, unknown>;
    changes?: {
        before?: Record<string, unknown>;
        after?: Record<string, unknown>;
    } | null;
    created_at: string;
}

interface ActivityStatistics {
    total: number;
    today: number;
    active_users: number;
    this_week: number;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const logs = ref<ActivityLog[]>([]);
const users = ref<User[]>([]);
const statistics = ref<ActivityStatistics | null>(null);
const loading = ref(false);
const search = ref('');
const typeFilter = ref('');
const userFilter = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const perPage = ref(25);
const pagination = ref<PaginationData | null>(null);
const exporting = ref(false);

const filteredLogs = computed(() => {
    if (!Array.isArray(logs.value)) {
        return [];
    }
    
    let filtered = logs.value;
    
    // Client-side filtering for search (server already filtered by action/user/date)
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(log => 
            log?.description?.toLowerCase().includes(searchLower) ||
            log?.model_type?.toLowerCase().includes(searchLower) ||
            log?.user?.name?.toLowerCase().includes(searchLower)
        );
    }
    
    return filtered;
});

const fetchLogs = async (page: number = 1) => {
    loading.value = true;
    try {
        // Build query params for server-side filtering
        const params: Record<string, string | number> = {
            page,
            per_page: perPage.value
        };
        
        if (typeFilter.value && typeFilter.value !== 'all') params.action = typeFilter.value;
        if (userFilter.value && userFilter.value !== 'all') params.user_id = userFilter.value;
        if (dateFrom.value) params.date_from = dateFrom.value;
        if (dateTo.value) params.date_to = dateTo.value;
        if (search.value) params.search = search.value;
        
        const response = await api.get('/manage/activity-journal', { params });
        const { data, pagination: pag } = parseResponse<ActivityLog[]>(response);
        
        logs.value = ensureArray(data);
        pagination.value = pag;
        
        // Fetch statistics (only on first load or if needed)
        try {
            const statsResponse = await api.get('/manage/activity-journal/statistics');
            statistics.value = statsResponse.data?.data || statsResponse.data;
        } catch {
            // Fallback stats if endpoint fails
            statistics.value = {
                total: pagination.value?.total || logs.value.length,
                today: 0,
                this_week: 0,
                active_users: 0
            };
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch activity logs:', (error as Error).message);
        toast.error.fromResponse(error);
    } finally {
        loading.value = false;
    }
};

const clearDateFilter = () => {
    dateFrom.value = '';
    dateTo.value = '';
    fetchLogs();
};

const clearLogs = async () => {
    const confirmed = await confirm({
        title: t('system.system.logs.actions.clear'),
        message: t('system.system.logs.confirm.clear'),
        variant: 'danger',
        confirmText: t('common.actions.clear')});

    if (!confirmed) return;

    try {
        await api.post('/manage/activity-journal/clear');
        toast.success.action(t('system.system.logs.messages.cleared'));
        fetchLogs();
    } catch (error: unknown) {
        logger.error('Failed to clear logs:', (error as Error).message);
        toast.error.fromResponse(error);
    }
};

const exportLogs = async () => {
    exporting.value = true;
    try {
        const params = new URLSearchParams();
        if (typeFilter.value && typeFilter.value !== 'all') params.append('action', typeFilter.value);
        if (userFilter.value && userFilter.value !== 'all') params.append('user_id', userFilter.value);
        if (dateFrom.value) params.append('date_from', dateFrom.value);
        if (dateTo.value) params.append('date_to', dateTo.value);
        
        const response = await api.get(`/manage/activity-journal/export?${params.toString()}`, {
            responseType: 'blob'
        });
        
        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `activity-logs-${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        toast.success.action(t('system.analytics.export.success'));
    } catch (error: unknown) {
        logger.error('Failed to export activity logs:', (error as Error).message);
        toast.error.fromResponse(error);
    } finally {
        exporting.value = false;
    }
};

const fetchUsers = async () => {
    try {
        const response = await api.get('/manage/system/users');
        users.value = getResponseList<User>(response.data);
    } catch (error: unknown) {
        logger.error('Failed to fetch users:', error);
        users.value = [];
    }
};

const formatDate = (date?: string) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
};

const formatValue = (val: unknown): string => {
    if (val === null || val === undefined) return 'null';
    if (typeof val === 'boolean') return val ? 'true' : 'false';
    if (typeof val === 'object') return JSON.stringify(val);
    return String(val);
};

const getRecursiveValue = (obj: Record<string, unknown> | null | undefined, key: string): unknown => {
    if (!obj) return undefined;
    if (key in obj) return obj[key];
    return undefined;
};

// Watch filters for auto-refresh
watch([typeFilter, userFilter, dateFrom, dateTo], () => {
    fetchLogs(1);
});

onMounted(() => {
    fetchLogs();
    fetchUsers();
});
</script>

