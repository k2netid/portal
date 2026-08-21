<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="t('system.access_journal.title')"
      :subtitle="t('system.access_journal.description')"
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
      class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6"
    >
      <div class="bg-card border border-border rounded-lg p-4">
        <p class="text-sm font-medium text-muted-foreground">
          {{ t('system.access_journal.stats.totalLogins') }}
        </p>
        <p class="text-2xl font-semibold text-foreground">
          {{ statistics.total_logins || 0 }}
        </p>
      </div>
      <div class="bg-card border border-border rounded-lg p-4">
        <p class="text-sm font-medium text-muted-foreground">
          {{ t('system.access_journal.stats.failedLogins') }}
        </p>
        <p class="text-2xl font-semibold text-red-700 dark:text-red-400">
          {{ statistics.failed_logins || 0 }}
        </p>
      </div>
      <div class="bg-card border border-border rounded-lg p-4">
        <p class="text-sm font-medium text-muted-foreground">
          {{ t('system.access_journal.stats.todayLogins') }}
        </p>
        <p class="text-2xl font-semibold text-foreground">
          {{ statistics.today_logins || 0 }}
        </p>
      </div>
      <div class="bg-card border border-border rounded-lg p-4">
        <p class="text-sm font-medium text-muted-foreground">
          {{ t('system.access_journal.stats.uniqueIps') }}
        </p>
        <p class="text-2xl font-semibold text-foreground">
          {{ statistics.unique_ips_today || 0 }}
        </p>
      </div>
      <div class="bg-card border border-border rounded-lg p-4">
        <p class="text-sm font-medium text-muted-foreground">
          {{ t('system.access_journal.stats.activeSessions') }}
        </p>
        <div class="flex items-center justify-between">
          <p class="text-2xl font-semibold text-emerald-700 dark:text-emerald-400">
            {{ statistics.active_sessions || 0 }}
          </p>
        </div>
      </div>
      <div class="bg-card border border-border rounded-lg p-4">
        <p class="text-sm font-medium text-muted-foreground">
          {{ t('system.access_journal.stats.suspiciousCount') }}
        </p>
        <p class="text-2xl font-semibold text-orange-700 dark:text-orange-400">
          {{ statistics.suspicious_count || 0 }}
        </p>
      </div>
    </div>

    <!-- Suspicious Activity Alerts -->
    <div
      v-if="suspiciousAlerts.length > 0"
      class="mb-6 space-y-4"
    >
      <Card class="border-orange-500/50 bg-orange-500/5">
        <CardHeader class="pb-2">
          <div class="flex items-center gap-2">
            <AlertTriangle class="w-5 h-5 text-orange-500" />
            <CardTitle class="text-lg text-orange-700 dark:text-orange-400">
              {{ t('system.access_journal.alerts.title') }}
            </CardTitle>
          </div>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="(alert, index) in suspiciousAlerts" 
              :key="index"
              class="p-3 rounded-md border flex flex-col gap-2"
              :class="[ alert.severity === 'high' ? 'bg-red-500/10 border-red-500/30' : alert.severity === 'medium' ? 'bg-orange-500/10 border-orange-500/30' : 'bg-yellow-500/10 border-yellow-500/30' ]"
            >
              <div class="flex items-center justify-between">
                <Badge
                  variant="outline"
                  :class="alert.severity === 'high' ? 'border-transparent bg-red-800 text-white' : 'border-border bg-muted text-foreground'"
                  class="text-[10px] h-4"
                >
                  {{ t(`system.access_journal.alerts.severity.${alert.severity}`) }}
                </Badge>
                <span class="text-[10px] text-foreground/80">{{ alert.detected_at ? formatDate(alert.detected_at) : '' }}</span>
              </div>
              <div>
                <h4 class="text-sm font-bold flex items-center gap-1">
                  {{ t(`system.access_journal.alerts.types.${alert.type}`) }}
                </h4>
                <p class="text-xs text-muted-foreground mt-1">
                  {{ alert.details }}
                </p>
              </div>
              <div class="flex items-center justify-between mt-auto pt-2 border-t border-border/50">
                <span class="text-xs font-medium">{{ alert.ip_address }}</span>
                <span
                  v-if="alert.user"
                  class="text-xs uppercase"
                >{{ alert.user.name }}</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <ConsoleListCard>
      <template #toolbar>
      <div class="px-0 pb-0 border-0">
        <!-- Filters Row -->
        <div class="flex flex-wrap items-center justify-between gap-4">
          <div class="flex flex-wrap items-center gap-3">
            <Select
              v-model="userFilter"
              @update:model-value="fetchHistory()"
            >
              <SelectTrigger
                class="w-[180px]"
                :aria-label="t('system.access_journal.filters.allUsers')"
              >
                <SelectValue :placeholder="t('system.access_journal.filters.allUsers')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('system.access_journal.filters.allUsers') }}
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
            <Select
              v-model="statusFilter"
              @update:model-value="fetchHistory()"
            >
              <SelectTrigger
                class="w-[180px]"
                :aria-label="t('system.access_journal.filters.allStatus')"
              >
                <SelectValue :placeholder="t('system.access_journal.filters.allStatus')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('system.access_journal.filters.allStatus') }}
                </SelectItem>
                <SelectItem value="success">
                  {{ t('system.access_journal.status.success') }}
                </SelectItem>
                <SelectItem value="failed">
                  {{ t('system.access_journal.status.failed') }}
                </SelectItem>
              </SelectContent>
            </Select>
            <div class="flex items-center gap-2">
              <label
                for="access-journal-date-from"
                class="text-sm text-foreground/80"
              >{{ t('system.access_journal.filters.dateFrom') }}:</label>
              <Input
                id="access-journal-date-from"
                v-model="dateFrom"
                type="date"
                class="w-36"
                @change="fetchHistory"
              />
            </div>
            <div class="flex items-center gap-2">
              <label
                for="access-journal-date-to"
                class="text-sm text-foreground/80"
              >{{ t('system.access_journal.filters.dateTo') }}:</label>
              <Input
                id="access-journal-date-to"
                v-model="dateTo"
                type="date"
                class="w-36"
                @change="fetchHistory"
              />
            </div>
          </div>
          <Button
            :disabled="exporting"
            @click="exportHistory"
          >
            <Download class="w-4 h-4 mr-2" />
            {{ exporting ? t('system.access_journal.export.exporting') : t('system.access_journal.export.button') }}
          </Button>
        </div>
      </div>
      </template>

      <div
        v-if="loading"
        class="p-6 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('system.access_journal.messages.loading') }}
        </p>
      </div>

      <div
        v-else-if="history.length === 0"
        class="p-6 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('system.access_journal.messages.empty') }}
        </p>
      </div>

      <div
        v-else
        class="divide-y divide-border"
      >
        <div
          v-for="entry in history"
          :key="entry.id"
          class="px-6 py-4 hover:bg-muted/50"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <!-- Status Icon -->
              <div
                :class="[ 'w-10 h-10 rounded-full flex items-center justify-center', entry.status === 'success' ? 'bg-emerald-800 text-white' : 'bg-red-800 text-white' ]"
              >
                <Check
                  v-if="entry.status === 'success'"
                  class="w-5 h-5"
                />
                <X
                  v-else
                  class="w-5 h-5"
                />
              </div>
              <!-- Details -->
              <div>
                <p class="font-medium text-foreground">
                  {{ entry.user?.name || t('system.users.unknown') }}
                </p>
                <p class="text-sm text-foreground/80">
                  {{ entry.user?.email || '' }}
                </p>
              </div>
            </div>
            <div class="flex items-center gap-6">
              <!-- Device Info (parsed UA) -->
              <div
                v-if="entry.user_agent"
                class="text-right hidden md:block"
              >
                <p class="text-xs text-foreground/80">
                  {{ parseUA(entry.user_agent).browser }}
                </p>
                <p class="text-xs text-foreground/80">
                  {{ parseUA(entry.user_agent).os }}
                </p>
              </div>
              <!-- Session Duration -->
              <div class="text-center min-w-[80px] hidden sm:block">
                <span
                  v-if="entry.status === 'success' && !entry.logout_at"
                  class="inline-flex items-center rounded-full border-transparent bg-emerald-800 px-2 py-0.5 text-xs font-medium text-white"
                >
                  Active
                </span>
                <span
                  v-else-if="entry.login_at && entry.logout_at"
                  class="text-xs text-foreground/80"
                >
                  {{ formatDuration(entry.login_at, entry.logout_at) }}
                </span>
                <span
                  v-else
                  class="text-xs text-foreground/80"
                >—</span>
              </div>
              <!-- IP + Time -->
              <div class="text-right">
                <p class="text-sm text-foreground">
                  {{ entry.ip_address }}
                </p>
                <p class="text-xs text-foreground/80">
                  {{ formatDate(entry.login_at) }}
                </p>
                <p
                  v-if="entry.failure_reason"
                  class="text-xs text-destructive"
                >
                  {{ entry.failure_reason }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
      <Pagination
        v-if="totalRecords > 0"
        :current-page="currentPage"
        :total-items="totalRecords"
        :per-page="perPage"
        embedded
        @page-change="fetchHistory"
        @update:per-page="(val) => { perPage = val; fetchHistory(1); }"
      />
      </template>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { getResponseList } from '@/shared/utils/responseParser';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import {
    Badge,
    Button,
    Pagination,
    Input,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/shared/components/ui';

import {
  AlertTriangle,
  Check,
  Download,
  X
} from 'lucide-vue-next';
import { Card, CardHeader, CardTitle, CardContent } from '@/shared/components/ui';

interface User {
    id: string;
    name: string;
    email: string;
}

interface LoginEntry {
    id: string;
    user?: User | null;
    ip_address: string;
    user_agent?: string | null;
    status: 'success' | 'failed';
    login_at: string;
    logout_at?: string | null;
    failure_reason: string | null;
}

interface LoginStatistics {
    total_logins: number;
    failed_logins: number;
    today_logins: number;
    unique_ips_today: number;
    active_sessions: number;
    suspicious_count: number;
}

interface SuspiciousAlert {
    type: 'brute_force' | 'new_ip' | 'shared_ip';
    severity: 'high' | 'medium' | 'low';
    user?: { id: string; name: string; email: string } | null;
    ip_address: string;
    details: string;
    count?: number;
    detected_at?: string;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const history = ref<LoginEntry[]>([]);
const users = ref<User[]>([]);
const statistics = ref<LoginStatistics | null>(null);
const suspiciousAlerts = ref<SuspiciousAlert[]>([]);
const loading = ref(false);
const exporting = ref(false);
const userFilter = ref('');
const statusFilter = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const perPage = ref(25);
const currentPage = ref(1);
const totalRecords = ref(0);

const fetchHistory = async (page: number = 1) : Promise<void> => {
    currentPage.value = page;
    loading.value = true;
    try {
        const params = new URLSearchParams();
        params.append('page', String(page));
        params.append('per_page', String(perPage.value));
        if (userFilter.value && userFilter.value !== 'all') params.append('user_id', userFilter.value);
        if (statusFilter.value && statusFilter.value !== 'all') params.append('status', statusFilter.value);
        if (dateFrom.value) params.append('date_from', dateFrom.value);
        if (dateTo.value) params.append('date_to', dateTo.value);

        const response = await api.get(`/manage/access-journal?${params.toString()}`);
        
        const payload = response.data;
        const data = getResponseList<LoginEntry>(payload);
        totalRecords.value = (payload && typeof payload === 'object' && 'total' in payload)
            ? Number((payload as { total?: number }).total ?? data.length)
            : data.length;
        history.value = data;
    } catch (error: unknown) {
        logger.error('Failed to fetch login history:', (error as Error).message);
    } finally {
        loading.value = false;
    }
};

const fetchStatistics = async () : Promise<void> => {
    try {
        const response = await api.get('/manage/access-journal/statistics');
        statistics.value = response.data;
    } catch (error: unknown) {
        logger.error('Failed to fetch statistics:', error);
    }
};

const fetchSuspicious = async () : Promise<void> => {
    try {
        const response = await api.get('/manage/access-journal/suspicious');
        const payload = response.data as { alerts?: SuspiciousAlert[] } | null;
        const alerts = payload?.alerts;
        suspiciousAlerts.value = Array.isArray(alerts) ? alerts : [];
    } catch (error: unknown) {
        logger.error('Failed to fetch suspicious alerts:', error);
    }
};

const fetchUsers = async () : Promise<void> => {
    try {
        const response = await api.get('/manage/system/users');
        users.value = getResponseList(response.data);
    } catch (error: unknown) {
        logger.error('Failed to fetch users:', error);
        users.value = [];
    }
};

const clearLogs = async () : Promise<void> => {
    const confirmed = await confirm({
        title: t('system.system.logs.actions.clear'),
        message: t('system.system.logs.confirm.clear'),
        variant: 'danger',
        confirmText: t('common.actions.clear')});

    if (!confirmed) return;

    try {
        await api.post('/manage/access-journal/clear');
        await fetchHistory();
        await fetchStatistics();
        toast.success.action(t('system.system.logs.messages.cleared'));
    } catch (error: unknown) {
        logger.error('Failed to clear logs:', (error as Error).message);
        toast.error.fromResponse(error);
    }
};

const exportHistory = async () : Promise<void> => {
    exporting.value = true;
    try {
        const params = new URLSearchParams();
        if (userFilter.value && userFilter.value !== 'all') params.append('user_id', userFilter.value);
        if (statusFilter.value && statusFilter.value !== 'all') params.append('status', statusFilter.value);
        if (dateFrom.value) params.append('date_from', dateFrom.value);
        if (dateTo.value) params.append('date_to', dateTo.value);

        const response = await api.get(`/manage/access-journal/export?${params.toString()}`, {
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `login-history-${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        toast.success.action(t('system.analytics.export.success'));
    } catch (error: unknown) {
        logger.error('Failed to export:', (error as Error).message);
        toast.error.fromResponse(error);
    } finally {
        exporting.value = false;
    }
};

const formatDate = (dateString?: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString();
};

const parseUA = (ua: string): { browser: string; os: string } => {
    let browser = 'Unknown';
    let os = 'Unknown';

    // Browser detection
    if (ua.includes('Firefox/')) browser = 'Firefox';
    else if (ua.includes('Edg/')) browser = 'Edge';
    else if (ua.includes('OPR/') || ua.includes('Opera/')) browser = 'Opera';
    else if (ua.includes('Chrome/') && ua.includes('Safari/')) browser = 'Chrome';
    else if (ua.includes('Safari/') && !ua.includes('Chrome')) browser = 'Safari';
    else if (ua.includes('bot') || ua.includes('Bot')) browser = 'Bot';

    // OS detection
    if (ua.includes('Windows NT 10')) os = 'Windows 10/11';
    else if (ua.includes('Windows')) os = 'Windows';
    else if (ua.includes('Mac OS,')) os = 'macOS';
    else if (ua.includes('Android')) os = 'Android';
    else if (ua.includes('iPhone') || ua.includes('iPad')) os = 'iOS';
    else if (ua.includes('Linux')) os = 'Linux';

    return { browser, os };
};

const formatDuration = (loginAt: string, logoutAt: string): string => {
    const login = new Date(loginAt).getTime();
    const logout = new Date(logoutAt).getTime();
    const diff = Math.max(0, logout - login);
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (days > 0) return `${days}d ${hours % 24}h`;
    if (hours > 0) return `${hours}h ${minutes % 60}m`;
    return `${minutes}m`;
};

onMounted(() => {
    fetchHistory();
    fetchStatistics();
    fetchSuspicious();
    fetchUsers();
});
</script>
