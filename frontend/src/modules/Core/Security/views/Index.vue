<template>
  <div class="space-y-6">
    <PageHeader
borderless
      :title="$t('system.security.title')"
    :subtitle="$t('system.security.subtitle')"
    >
      <template #actions>
        <div class="flex items-center space-x-2">
                <Button 
                  v-if="showClearButton" 
                  variant="ghost"
                                    class="bg-red-800 text-white hover:bg-red-900 border border-red-800"
                  @click="handleClearLogs"
                >
                  <Trash2 class="w-4 h-4 mr-2" />
                  {{ $t('system.system.logs.clear') }}
                </Button>
                <Button
                  :disabled="loading"
                  @click="refreshAll"
                >
                  <Loader2
                    v-if="loading"
                    class="w-4 h-4 mr-2"
                  />
                  <RefreshCw
                    v-else
                    class="w-4 h-4 mr-2"
                  />
                  <span>{{ $t('common.actions.refresh') }}</span>
                </Button>
              </div>
      </template>
    </PageHeader>

    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <ConsoleListCard>
        <template #toolbar>
          <div class="flex items-center justify-between w-full">
            <TabsList class="bg-transparent p-0 h-auto gap-0 flex-wrap border-none">
              <TabsTrigger
                value="overview"
                class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
              >
                <BarChart3 class="w-4 h-4 mr-2" />
                {{ $t('system.security.tabs.overview') }}
              </TabsTrigger>
              <TabsTrigger
                value="blocklist"
                class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
              >
                <Shield class="w-4 h-4 mr-2" />
                {{ $t('system.security.tabs.blocklist') }}
              </TabsTrigger>
              <TabsTrigger
                value="whitelist"
                class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
              >
                <ShieldCheck class="w-4 h-4 mr-2" />
                {{ $t('system.security.tabs.whitelist') }}
              </TabsTrigger>
              <TabsTrigger
                value="csp-reports"
                class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
              >
                <FileWarning class="w-4 h-4 mr-2" />
                {{ $t('system.security.tabs.cspReports') }}
              </TabsTrigger>
              <TabsTrigger
                value="slow-queries"
                class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
              >
                <Timer class="w-4 h-4 mr-2" />
                {{ $t('system.security.tabs.slowQueries') }}
              </TabsTrigger>
              <TabsTrigger
                value="vulnerabilities"
                class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
              >
                <ShieldAlert class="w-4 h-4 mr-2" />
                {{ $t('system.security.tabs.vulnerabilities') }}
              </TabsTrigger>
              <TabsTrigger
                value="shield-journal"
                class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
              >
                <ShieldCheck class="w-4 h-4 mr-2" />
                {{ $t('system.security.tabs.shieldJournal') }}
              </TabsTrigger>
              <TabsTrigger
                value="threat-analysis"
                class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
              >
                <Activity class="w-4 h-4 mr-2" />
                {{ $t('system.security.tabs.threatAnalysis') }}
              </TabsTrigger>
              <TabsTrigger
                value="file-integrity"
                class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
              >
                <FileCheck class="w-4 h-4 mr-2" />
                {{ $t('system.security.tabs.fileIntegrity') }}
              </TabsTrigger>
              <TabsTrigger
                value="abac-policies"
                class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
              >
                <Key class="w-4 h-4 mr-2" />
                {{ $t('system.security.abac.title') }}
              </TabsTrigger>
              <TabsTrigger
                value="siem-exports"
                class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
              >
                <Server class="w-4 h-4 mr-2" />
                {{ $t('system.security.siem.tab') }}
              </TabsTrigger>
              <TabsTrigger
                value="settings"
                class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
              >
                <SettingsIcon class="w-4 h-4 mr-2" />
                {{ $t('system.security.tabs.settings') }}
              </TabsTrigger>
            </TabsList>
          </div>
        </template>

        <div class="w-full">
<!-- Overview Tab -->
        <TabsContent v-if="activeTab === 'overview'" value="overview">
          <OverviewTab
            ref="overviewTabRef"
            :logs="logs"
            :statistics="statistics"
            :security-kpi="securityKpi"
            :loading="loading"
            :blocklist-count="blocklist.length"
            :whitelist-count="whitelist.length"
            :maintenance-status="maintenanceStatus"
            :maintenance-loading="maintenanceLoading"
            :maintenance-activating="maintenanceActivating"
            :security-health="securityHealth"
            @block-ip="blockIP"
            @check-ip="checkIPStatus"
            @block-from-log="blockIPFromLog"
            @bulk-block="bulkBlockFromLogs"
            @activate-maintenance="activateMaintenance"
            @deactivate-maintenance="deactivateMaintenance"
            @test-notification="testNotification"
          />
        </TabsContent>

        <!-- Blocklist Tab -->
        <TabsContent v-if="activeTab === 'blocklist'" value="blocklist">
          <BlocklistTab
            ref="blocklistTabRef"
            :blocklist="blocklist"
            :loading="loading"
            @remove="removeFromBlocklist"
            @move-to-whitelist="moveToWhitelist"
            @bulk-unblock="bulkUnblock"
          />
        </TabsContent>

        <!-- Whitelist Tab -->
        <TabsContent v-if="activeTab === 'whitelist'" value="whitelist">
          <WhitelistTab
            ref="whitelistTabRef"
            :whitelist="whitelist"
            :loading="loading"
            @add="addToWhitelist"
            @remove="removeFromWhitelist"
            @bulk-remove="bulkRemoveWhitelist"
          />
        </TabsContent>

        <!-- CSP Reports Tab -->
        <TabsContent v-if="activeTab === 'csp-reports'" value="csp-reports">
          <CspReportsTab
            :reports="cspReports"
            :stats="cspStats"
            :loading="cspLoading"
            :pagination="cspPagination"
            :filters="cspFilters"
            @refresh="fetchCspReports"
            @apply-filters="applyCspFilters"
            @reset-filters="resetCspFilters"
            @bulk-action="cspBulkAction"
            @page-change="(val: number) => { cspFilters.page = val; fetchCspReports(); }"
            @per-page-change="(val: number) => { cspFilters.per_page = val; cspFilters.page = 1; fetchCspReports(); }"
          />
        </TabsContent>

        <!-- Slow Queries Tab -->
        <TabsContent v-if="activeTab === 'slow-queries'" value="slow-queries">
          <SlowQueriesTab
            :queries="slowQueries"
            :stats="slowQueryStats"
            :loading="slowQueryLoading"
            :pagination="slowQueryPagination"
            :filters="slowQueryFilters"
            @refresh="fetchSlowQueries"
            @apply-filters="applySlowQueryFilters"
            @reset-filters="resetSlowQueryFilters"
            @page-change="(val: number) => { slowQueryFilters.page = val; fetchSlowQueries(); }"
            @per-page-change="(val: number) => { slowQueryFilters.per_page = val; slowQueryFilters.page = 1; fetchSlowQueries(); }"
          />
        </TabsContent>

        <!-- Vulnerabilities Tab -->
        <TabsContent v-if="activeTab === 'vulnerabilities'" value="vulnerabilities">
          <VulnerabilitiesTab
            :vulnerabilities="vulnerabilities"
            :stats="vulnStats"
            :loading="vulnLoading"
            :audit-running="auditRunning"
            :pagination="vulnPagination"
            :filters="vulnFilters"
            @refresh="fetchVulnerabilities"
            @run-audit="runDependencyAudit"
            @apply-filters="applyVulnFilters"
            @reset-filters="resetVulnFilters"
            @update-status="updateVulnStatus"
            @page-change="(val: number) => { vulnFilters.page = val; fetchVulnerabilities(); }"
            @per-page-change="(val: number) => { vulnFilters.per_page = val; vulnFilters.page = 1; fetchVulnerabilities(); }"
          />
        </TabsContent>

        <!-- Shield Journal Tab -->
        <TabsContent v-if="activeTab === 'shield-journal'" value="shield-journal">
          <ShieldJournalTab
            :logs="shieldLogs"
            :stats="shieldStats"
            :loading="shieldLoading"
            :pagination="shieldPagination"
            @refresh="fetchShieldLogs"
            @page-change="(val: number) => { shieldPage = val; fetchShieldLogs(); }"
            @block-ip="blockIP"
          />
        </TabsContent>

        <!-- Threat Analysis Tab -->
        <TabsContent v-if="activeTab === 'threat-analysis'" value="threat-analysis">
          <ThreatAnalysisTab
            :analysis="threatAnalysisData"
            :auto-tune-logs="autoTuneLogsData"
            :loading="threatLoading"
            @refresh="fetchThreatAnalysis"
          />
        </TabsContent>

        <!-- File Integrity Tab -->
        <TabsContent v-if="activeTab === 'file-integrity'" value="file-integrity">
          <FileIntegrityTab
            :integrity="integrityData"
            :loading="integrityLoading"
            :resync-submitting="resyncSubmitting"
            :resync-cooldown-seconds="resyncCooldownSeconds"
            @refresh="fetchFileIntegrity"
            @run-check="runIntegrityCheck"
            @resync="resyncIntegrityBaseline"
          />
        </TabsContent>

        <!-- ABAC Policies Tab -->
        <TabsContent v-if="activeTab === 'abac-policies'" value="abac-policies">
          <AbacPoliciesTab />
        </TabsContent>

        <TabsContent v-if="activeTab === 'siem-exports'" value="siem-exports">
          <SiemExportsTab />
        </TabsContent>

        <!-- Settings Tab -->
        <TabsContent v-if="activeTab === 'settings'" value="settings">
          <SettingsTab
            :settings="securitySettings"
            :saving="settingsSaving"
            @save="saveSecuritySettings"
          />
        </TabsContent>
        </div>
      </ConsoleListCard>
    </Tabs>

    <Dialog
      :open="resyncDialogOpen"
      @update:open="(v) => { resyncDialogOpen = v; }"
    >
      <DialogContent class="console-dialog-sm">
        <DialogHeader>
          <DialogTitle>{{ t('system.security.fileIntegrity.resync.dialogTitle') }}</DialogTitle>
          <DialogDescription>
            {{ t('system.security.fileIntegrity.resync.dialogDescription') }}
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4 py-2">
          <div class="space-y-2">
            <Label for="integrity-reason">{{ t('system.security.fileIntegrity.resync.reasonLabel') }}</Label>
            <Textarea
              id="integrity-reason"
              v-model="resyncReason"
              rows="4"
              :placeholder="t('system.security.fileIntegrity.resync.reasonPlaceholder')"
            />
          </div>

          <label class="flex items-start gap-3 rounded-md border border-border/50 p-3">
            <Checkbox
              :checked="resyncClearHistory"
              @update:checked="(v) => { resyncClearHistory = v === true; }"
            />
            <div class="space-y-1">
              <p class="text-sm font-medium">
                {{ t('system.security.fileIntegrity.resync.clearHistoryLabel') }}
              </p>
              <p class="text-xs text-muted-foreground">
                {{ t('system.security.fileIntegrity.resync.clearHistoryDesc') }}
              </p>
            </div>
          </label>
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            :disabled="resyncSubmitting"
            @click="resyncDialogOpen = false"
          >
            {{ t('system.security.fileIntegrity.resync.cancel') }}
          </Button>
          <Button
            :disabled="resyncSubmitting"
            @click="submitIntegrityResync"
          >
            <Loader2
              v-if="resyncSubmitting"
              class="w-4 h-4 mr-2"
            />
            {{ t('system.security.fileIntegrity.resync.submit') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, reactive, onMounted, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { lazyTab } from '@/shared/utils/lazyTab';
import { useConfirm } from '@/shared/composables/useConfirm';
import { parseResponse, ensureArray, parseSingleResponse, getResponseList, getResponseObject } from '@/shared/utils/responseParser';
import {
    Tabs, TabsList, TabsTrigger, TabsContent, Button,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
    Label, Textarea, Checkbox
} from '@/shared/components/ui';

// Tab components (lazy — loaded when tab is first opened)
const OverviewTab = lazyTab(() => import('./components/OverviewTab.vue'));
const BlocklistTab = lazyTab(() => import('./components/BlocklistTab.vue'));
const WhitelistTab = lazyTab(() => import('./components/WhitelistTab.vue'));
const CspReportsTab = lazyTab(() => import('./components/CspReportsTab.vue'));
const SlowQueriesTab = lazyTab(() => import('./components/SlowQueriesTab.vue'));
const VulnerabilitiesTab = lazyTab(() => import('./components/VulnerabilitiesTab.vue'));
const ShieldJournalTab = lazyTab(() => import('./components/ShieldJournalTab.vue'));
const ThreatAnalysisTab = lazyTab(() => import('./components/ThreatAnalysisTab.vue'));
const FileIntegrityTab = lazyTab(() => import('./components/FileIntegrityTab.vue'));
const AbacPoliciesTab = lazyTab(() => import('./components/AbacPoliciesTab.vue'));
const SiemExportsTab = lazyTab(() => import('./components/SiemExportsTab.vue'));
const SettingsTab = lazyTab(() => import('./components/SettingsTab.vue'));

// Icons
import { Activity,
  BarChart3,
  FileCheck,
  FileWarning,
  Loader2,
  RefreshCw,
  SettingsIcon,
  ShieldAlert,
  ShieldCheck,
  Shield,
  Timer,
  Key,
  Trash2, Server } from 'lucide-vue-next';

import type { ShieldLog, ShieldStats, PaginationInfo as SecurityPaginationInfo } from '@/engine/types/security';

// Types
interface User {
    id: string;
    name: string;
    email: string;
}

interface Log {
    id: string;
    event_type: string;
    ip_address: string;
    user_id?: string | null;
    user?: User | null;
    details: string;
    created_at: string;
}

interface Statistics {
    total_events: number;
    failed_logins: number;
    blocked_ips: number;
}

interface SecurityKpi {
    period_days: number;
    drills: {
        count: number;
        pass_count: number;
        pass_rate_percent: number;
        avg_rto_seconds: number;
        avg_rpo_minutes: number;
    };
    detection: {
        info_signals: number;
        critical_signals: number;
        noise_rate_percent: number;
    };
}

interface IpManagementItem {
    id: string | string;
    ip_address: string;
    reason?: string | null;
    creator?: User | null;
    created_at: string;
}

interface IpStatus {
    is_blocked: boolean;
    reason?: string | null;
}

interface CspReport {
    id: string;
    violated_directive: string;
    blocked_uri: string | null;
    document_uri: string;
    ip_address: string;
    status: string;
    created_at: string;
}

interface CspStats {
    total?: number;
    new?: number;
    by_directive?: { violated_directive: string; count: number }[];
    recent_trend?: { date: string; count: number }[];
}

interface SlowQuery {
    id: string;
    route: string | null;
    duration: number;
    user_id?: string | null;
    user?: User | null;
    query: string;
    created_at: string;
}

interface SlowQueryStats {
    total?: number;
    avg_duration?: number;
    max_duration?: number;
    today?: number;
}

interface Vulnerability {
    id: string;
    package_name: string;
    version: string;
    severity: string;
    cve: string | null;
    source: string;
    status: string;
}

interface VulnStats {
    total: number;
    critical: number;
    high: number;
    medium: number;
    low: number;
    patched?: number;
}

interface PaginationInfo {
    total: number;
    current_page: number;
    last_page: number;
}

const route = useRoute();
const router = useRouter();
const { t } = useI18n();

const SECURITY_TABS = ['overview', 'blocklist', 'whitelist', 'csp-reports', 'slow-queries', 'vulnerabilities', 'threat-analysis', 'shield-journal', 'file-integrity', 'abac-policies', 'siem-exports', 'settings'];
const { confirm } = useConfirm();
const toast = useToast();

// Refs for child components
const overviewTabRef = ref<{
    refresh?: () => void;
    setIpStatus?: (status: IpStatus | null) => void;
    clearSelection?: () => void;
} | null>(null);
const blocklistTabRef = ref<{ refresh?: () => void; clearSelection?: () => void } | null>(null);
const whitelistTabRef = ref<{ refresh?: () => void; clearSelection?: () => void } | null>(null);

// Core Data
const logs = ref<Log[]>([]);
const statistics = ref<Statistics | null>(null);
const securityKpi = ref<SecurityKpi | null>(null);
const blocklist = ref<IpManagementItem[]>([]);
const whitelist = ref<IpManagementItem[]>([]);
const loading = ref(false);
const activeTab = ref('overview');

// CSP Reports
const cspReports = ref<CspReport[]>([]);
const cspStats = ref<CspStats | null>(null);
const cspLoading = ref(false);
const cspPagination = ref<PaginationInfo>({ total: 0, current_page: 1, last_page: 1 });

// Slow Queries
const slowQueries = ref<SlowQuery[]>([]);
const slowQueryStats = ref<SlowQueryStats | null>(null);
const slowQueryLoading = ref(false);
const slowQueryPagination = ref<PaginationInfo>({ total: 0, current_page: 1, last_page: 1 });

// Vulnerabilities
const vulnerabilities = ref<Vulnerability[]>([]);
const vulnStats = ref<VulnStats>({ total: 0, critical: 0, high: 0, medium: 0, low: 0, patched: 0 });
const vulnLoading = ref(false);
const auditRunning = ref(false);
const vulnPagination = ref<PaginationInfo>({ total: 0, current_page: 1, last_page: 1 });

// Shield Journal
const shieldLogs = ref<ShieldLog[]>([]);
const shieldStats = ref<ShieldStats>({ verifications: 0, failures: 0, honeypot: 0, scannersBlocked: 0, extensionsBlocked: 0, currentDifficulty: 4, isScaling: false });
const shieldLoading = ref(false);
const shieldPagination = ref<SecurityPaginationInfo>({ total: 0, current_page: 1, last_page: 1 });
const shieldPage = ref(1);

// Threat Analysis
const threatAnalysisData = ref(null);
const autoTuneLogsData = ref([]);
const threatLoading = ref(false);

// File Integrity
const integrityData = ref(null);
const integrityLoading = ref(false);
const resyncDialogOpen = ref(false);
const resyncSubmitting = ref(false);
const resyncReason = ref('');
const resyncClearHistory = ref(true);
const resyncCooldownUntil = ref(0);
const resyncCooldownSeconds = computed(() => Math.max(0, Math.ceil((resyncCooldownUntil.value - Date.now()) / 1000)));

// Security Settings
const securitySettings = ref({ 
    security_log_retention_days: 90, 
    activity_log_retention_days: 90,
    login_history_retention_days: 180,
    security_autotune_frequency: 'weekly' 
});
const settingsSaving = ref(false);
const settingsLoaded = ref(false);

// Maintenance Mode
const maintenanceStatus = ref({
    active: false,
    modules: [],
    started_at: null,
    expires_at: null,
    remaining_seconds: 0
});
const maintenanceLoading = ref(false);
const maintenanceActivating = ref(false);

// Security health and trend
const securityHealth = ref({
    assessment: { score: 100, level: 'safe', status: 'Healthy', details: [] },
    trend: { labels: [] as string[], datasets: [] as { label: string; data: number[]; borderColor?: string; backgroundColor?: string }[] }
});

// Filters state
const cspFilters = reactive({ status: 'new', directive: '', date_from: '', date_to: '', page: 1, per_page: 50 });
const slowQueryFilters = reactive({ route: '', min_duration: '', date_from: '', date_to: '', page: 1, per_page: 50 });
const vulnFilters = reactive({ source: 'all', severity: 'all', status: 'all', package: '', page: 1, per_page: 50 });

// ========================================
// CORE FETCH FUNCTIONS
// ========================================
const fetchShieldLogs = async (): Promise<void> => {
    shieldLoading.value = true;
    try {
        const response = await api.get('/manage/security/shield/journal', { params: { page: shieldPage.value } });
        const result = response.data;
        shieldLogs.value = (result.data as ShieldLog[]) || [];
        shieldPagination.value = { total: result.total || 0, current_page: result.current_page || 1, last_page: result.last_page || 1 };
    } catch (_error: unknown) {
        logger.error('Failed to fetch shield logs:', _error);
    } finally {
        shieldLoading.value = false;
    }
};

const fetchShieldStats = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/shield/stats');
        shieldStats.value = (response.data as ShieldStats) || { verifications: 0, failures: 0, honeypot: 0, scannersBlocked: 0, extensionsBlocked: 0, currentDifficulty: 4, isScaling: false };
    } catch (_error: unknown) {
        logger.error('Failed to fetch shield stats:', _error);
    }
};
const fetchLogs = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get('/manage/security/journal', { params: { per_page: 100 } });
        const { data } = parseResponse<Log[]>(response);
        logs.value = ensureArray(data);
    } catch (_error: unknown) {
        logger.error('Failed to fetch logs:', _error);
    } finally {
        loading.value = false;
    }
};

const clearLogs = async (): Promise<void> => {
    const confirmed = await confirm({
        title: t('system.system.logs.actions.clear'),
        message: t('system.system.logs.confirm.clear'),
        variant: 'danger',
        confirmText: t('common.actions.clear')});
    if (!confirmed) return;
    try {
        await api.delete('/manage/security/journal');
        toast.success.action(t('system.system.logs.messages.cleared'));
        fetchLogs();
    } catch (_error: unknown) {
        logger.error('Failed to clear logs:', _error);
        toast.error.fromResponse(_error);
    }
};

const clearShieldLogs = async (): Promise<void> => {
    const confirmed = await confirm({
        title: t('common.actions.clear'),
        message: t('system.security.logs.clearShieldConfirm'),
        variant: 'danger',
        confirmText: t('common.actions.clear')});
    if (!confirmed) return;

    try {
        await api.post('/manage/security/shield/clear');
        toast.success.default(t('system.security.logs.shieldCleared'));
        fetchShieldLogs();
    } catch (_error) {
        toast.error.default(t('common.errors.generic')); // _error
    }
};

const handleClearLogs = () => {
    if (activeTab.value === 'shield-journal') {
        clearShieldLogs();
    } else {
        clearLogs();
    }
};

const showClearButton = computed(() => {
    return ['overview', 'shield-journal'].includes(activeTab.value);
});

const fetchStats = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/stats');
        statistics.value = parseSingleResponse<Statistics>(response) as Statistics || null;
    } catch (_error: unknown) {
        logger.error('Failed to fetch stats:', _error);
    }
};

const fetchSecurityKpi = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/kpi', { params: { days: 30 } });
        securityKpi.value = parseSingleResponse<SecurityKpi>(response) as SecurityKpi || null;
    } catch (_error: unknown) {
        logger.error('Failed to fetch security KPI:', _error);
    }
};

const fetchBlocklist = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/blocklist');
        blocklist.value = ensureArray(parseSingleResponse<IpManagementItem[]>(response)) as IpManagementItem[];
    } catch (_error: unknown) {
        logger.error('Failed to fetch blocklist:', _error);
    }
};

const fetchWhitelist = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/whitelist');
        whitelist.value = ensureArray(parseSingleResponse<IpManagementItem[]>(response)) as IpManagementItem[];
    } catch (_error: unknown) {
        logger.error('Failed to fetch whitelist:', _error);
    }
};

// ========================================
// IP ACTIONS (from OverviewTab)
// ========================================
const blockIP = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('system.security.ipManagement.block.button'),
        message: t('system.security.messages.confirmBlock', { ip }),
        variant: 'danger',
        confirmText: t('system.security.ipManagement.block.button')});
    if (!confirmed) return;
    try {
        await api.post('/manage/security/block-ip', { ip_address: ip });
        toast.success.action(t('system.security.messages.blockSuccess'));
        await fetchBlocklist();
        await fetchLogs();
    } catch (_error: unknown) {
        logger.error('Failed to block IP:', _error);
        toast.error.fromResponse(_error);
    }
};

const checkIPStatus = async (ip: string): Promise<void> => {
    try {
        const response = await api.get('/manage/security/check-ip', { params: { ip_address: ip } });
        const status = parseSingleResponse<IpStatus>(response) as IpStatus || null;
        overviewTabRef.value?.setIpStatus?.(status);
    } catch (_error: unknown) {
        logger.error('Failed to check IP status:', _error);
        toast.error.fromResponse(_error);
    }
};

const blockIPFromLog = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('system.security.logs.actions.blockIp'),
        message: t('system.security.messages.confirmBlock', { ip }),
        variant: 'danger',
        confirmText: t('system.security.logs.actions.blockIp')});
    if (!confirmed) return;
    try {
        await api.post('/manage/security/block-ip', { ip_address: ip });
        toast.success.action(t('system.security.messages.blockSuccess'));
        await fetchBlocklist();
        await fetchLogs();
    } catch (_error: unknown) {
        logger.error('Failed to block IP:', _error);
        toast.error.fromResponse(_error);
    }
};

const bulkBlockFromLogs = async (ips: string[]): Promise<void> => {
    if (ips.length === 0) return;
    const confirmed = await confirm({
        title: t('system.security.bulkActions.blockSelected'),
        message: t('system.security.messages.confirmBulkBlock', { count: ips.length }),
        variant: 'danger',
        confirmText: t('system.security.bulkActions.blockSelected')});
    if (!confirmed) return;
    try {
        await api.post('/manage/security/bulk-block', { ip_addresses: ips });
        toast.success.action(t('system.security.messages.bulkBlockSuccess'));
        overviewTabRef.value?.clearSelection?.();
        await fetchBlocklist();
        await fetchLogs();
    } catch (_error: unknown) {
        logger.error('Failed to bulk block:', _error);
        toast.error.fromResponse(_error);
    }
};

// ========================================
// BLOCKLIST ACTIONS
// ========================================
const removeFromBlocklist = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('system.security.blocklist.actions.unblock'),
        message: t('system.security.messages.confirmUnblock', { ip }),
        variant: 'warning',
        confirmText: t('system.security.blocklist.actions.unblock')});
    if (!confirmed) return;
    try {
        await api.post('/manage/security/unblock-ip', { ip_address: ip });
        toast.success.action(t('system.security.messages.unblockSuccess'));
        await fetchBlocklist();
    } catch (_error: unknown) {
        logger.error('Failed to remove from blocklist:', _error);
        toast.error.fromResponse(_error);
    }
};

const moveToWhitelist = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('system.security.blocklist.actions.moveToWhitelist'),
        message: t('system.security.messages.confirmMoveToWhitelist', { ip }),
        variant: 'info',
        confirmText: t('common.actions.move')});
    if (!confirmed) return;
    try {
        await api.post('/manage/security/unblock-ip', { ip_address: ip });
        await api.post('/manage/security/whitelist', { ip_address: ip });
        toast.success.action(t('system.security.messages.movedToWhitelist'));
        await fetchBlocklist();
        await fetchWhitelist();
    } catch (_error: unknown) {
        logger.error('Failed to move to whitelist:', _error);
        toast.error.fromResponse(_error);
    }
};

const bulkUnblock = async (ips: string[]): Promise<void> => {
    if (ips.length === 0) return;
    const confirmed = await confirm({
        title: t('system.security.bulkActions.unblockSelected'),
        message: t('system.security.messages.confirmBulkUnblock', { count: ips.length }),
        variant: 'warning',
        confirmText: t('system.security.bulkActions.unblockSelected')});
    if (!confirmed) return;
    try {
        await api.post('/manage/security/bulk-unblock', { ip_addresses: ips });
        toast.success.action(t('system.security.messages.bulkUnblockSuccess'));
        blocklistTabRef.value?.clearSelection?.();
        await fetchBlocklist();
    } catch (_error: unknown) {
        logger.error('Failed to bulk unblock:', _error);
        toast.error.fromResponse(_error);
    }
};

// ========================================
// WHITELIST ACTIONS
// ========================================
const addToWhitelist = async (ip: string): Promise<void> => {
    try {
        await api.post('/manage/security/whitelist', { ip_address: ip });
        toast.success.action(t('system.security.messages.whitelistSuccess'));
        await fetchWhitelist();
    } catch (_error: unknown) {
        logger.error('Failed to add to whitelist:', _error);
        toast.error.fromResponse(_error);
    }
};

const removeFromWhitelist = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('system.security.whitelist.actions.remove'),
        message: t('system.security.messages.confirmRemoveWhitelist', { ip }),
        variant: 'danger',
        confirmText: t('common.actions.remove')});
    if (!confirmed) return;
    try {
        await api.post('/manage/security/remove-whitelist', { ip_address: ip });
        toast.success.action(t('system.security.messages.whitelistRemoveSuccess'));
        await fetchWhitelist();
    } catch (_error: unknown) {
        logger.error('Failed to remove from whitelist:', _error);
        toast.error.fromResponse(_error);
    }
};

const bulkRemoveWhitelist = async (ips: string[]): Promise<void> => {
    if (ips.length === 0) return;
    const confirmed = await confirm({
        title: t('system.security.bulkActions.removeSelected'),
        message: t('system.security.messages.confirmBulkRemoveWhitelist', { count: ips.length }),
        variant: 'danger',
        confirmText: t('common.actions.remove')});
    if (!confirmed) return;
    try {
        await api.post('/manage/security/bulk-remove-whitelist', { ip_addresses: ips });
        toast.success.action(t('system.security.messages.bulkWhitelistRemoveSuccess'));
        whitelistTabRef.value?.clearSelection?.();
        await fetchWhitelist();
    } catch (_error: unknown) {
        logger.error('Failed to bulk remove whitelist:', _error);
        toast.error.fromResponse(_error);
    }
};

// ========================================
// CSP REPORTS
// ========================================
const fetchCspReports = async (): Promise<void> => {
    cspLoading.value = true;
    try {
        const params: Record<string, string | number> = { ...cspFilters };
        if (params.status === 'all') params.status = '';
        const response = await api.get('/manage/security/csp-reports', { params });
        const result = response.data;
        cspReports.value = (result.data as CspReport[]) || [];
        cspPagination.value = { total: result.total || 0, current_page: result.current_page || 1, last_page: result.last_page || 1 };
    } catch (_error: unknown) {
        logger.error('Failed to fetch CSP reports:', _error);
    } finally {
        cspLoading.value = false;
    }
};

const fetchCspStats = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/csp-reports/statistics');
        cspStats.value = (response.data as CspStats) || {};
    } catch (_error: unknown) {
        logger.error('Failed to fetch CSP stats:', _error);
    }
};

const applyCspFilters = (): void => { cspFilters.page = 1; fetchCspReports(); };
const resetCspFilters = (): void => {
    cspFilters.status = 'all';
    cspFilters.directive = '';
    cspFilters.page = 1;
    fetchCspReports();
};

const cspBulkAction = async (action: string, ids: string[]): Promise<void> => {
    if (ids.length === 0) return;
    const confirmed = await confirm({
        title: t('common.actions.confirm'),
        message: t('system.security.cspReports.confirmBulkAction', { count: ids.length, action: action.replace('_', ' ') }),
        variant: 'danger',
        confirmText: t('common.actions.confirm')});
    if (!confirmed) return;
    try {
        await api.post('/manage/security/csp-reports/bulk-action', { ids, action });
        toast.success.action(t('system.security.cspReports.bulkActionSuccess', { count: ids.length }));
        fetchCspReports();
        fetchCspStats();
    } catch (_error: unknown) {
        toast.error.fromResponse(_error);
    }
};

// ========================================
// SLOW QUERIES
// ========================================
const fetchSlowQueries = async (): Promise<void> => {
    slowQueryLoading.value = true;
    try {
        const response = await api.get('/manage/security/slow-queries', { params: slowQueryFilters });
        const payload = response.data as { data?: SlowQuery[]; total?: number; current_page?: number; last_page?: number };
        slowQueries.value = getResponseList<SlowQuery>(payload);
        slowQueryPagination.value = { total: payload.total || 0, current_page: payload.current_page || 1, last_page: payload.last_page || 1 };
    } catch (_error: unknown) {
        logger.error('Failed to fetch slow queries:', _error);
    } finally {
        slowQueryLoading.value = false;
    }
};

const fetchSlowQueryStats = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/slow-queries/statistics');
        slowQueryStats.value = (response.data as SlowQueryStats) || {};
    } catch (_error: unknown) {
        logger.error('Failed to fetch slow query stats:', _error);
    }
};

const applySlowQueryFilters = (): void => {
    slowQueryFilters.page = 1;
    fetchSlowQueries();
};
const resetSlowQueryFilters = (): void => {
    Object.assign(slowQueryFilters, { route: '', min_duration: '', date_from: '', date_to: '', page: 1 });
    fetchSlowQueries();
};

// ========================================
// VULNERABILITIES
// ========================================
const fetchVulnerabilities = async (): Promise<void> => {
    vulnLoading.value = true;
    try {
        const params: Record<string, string | number> = { ...vulnFilters };
        if (params.source === 'all') params.source = '';
        if (params.severity === 'all') params.severity = '';
        if (params.status === 'all') params.status = '';
        const response = await api.get('/manage/security/dependency-vulnerabilities', { params });
        const result = response.data;
        vulnerabilities.value = (result.data as Vulnerability[]) || [];
        vulnPagination.value = { total: result.total || 0, current_page: result.current_page || 1, last_page: result.last_page || 1 };
    } catch (_error: unknown) {
        logger.error('Failed to fetch vulnerabilities:', _error);
    } finally {
        vulnLoading.value = false;
    }
};

const fetchVulnStats = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/dependency-vulnerabilities/statistics');
        vulnStats.value = ((response.data?.data || response.data) as VulnStats) || { total: 0, critical: 0, high: 0, medium: 0, low: 0 };
    } catch (_error: unknown) {
        logger.error('Failed to fetch vulnerability stats:', _error);
    }
};

const runDependencyAudit = async (): Promise<void> => {
    auditRunning.value = true;
    try {
        await api.post('/manage/security/run-dependency-audit');
        toast.success.action(t('system.security.vulnerabilities.auditCompleted'));
        fetchVulnerabilities();
        fetchVulnStats();
    } catch (_error: unknown) {
        toast.error.fromResponse(_error);
    } finally {
        auditRunning.value = false;
    }
};

const updateVulnStatus = async (vuln: Vulnerability, status: string): Promise<void> => {
    try {
        await api.put(`/manage/security/dependency-vulnerabilities/${vuln.id}`, { status });
        vuln.status = status;
        toast.success.action(t('common.messages.success.updated', { item: 'Status' }));
    } catch (_error: unknown) {
        toast.error.fromResponse(_error);
    }
};

const applyVulnFilters = (): void => {
    vulnFilters.page = 1;
    fetchVulnerabilities();
};
const resetVulnFilters = (): void => {
    Object.assign(vulnFilters, { source: 'all', severity: 'all', status: 'all', package: '', page: 1 });
    fetchVulnerabilities();
};

// ========================================
// THREAT ANALYSIS & AUTO-TUNE
// ========================================
const fetchThreatAnalysis = async (): Promise<void> => {
    threatLoading.value = true;
    try {
        const analysisRes = await api.get('/manage/security/threat-analysis');
        threatAnalysisData.value = getResponseObject(analysisRes.data);
    } catch (_error: unknown) {
        logger.error('Failed to fetch threat analysis:', _error);
        threatAnalysisData.value = null;
        toast.error.fromResponse(_error);
    }

    try {
        const logsRes = await api.get('/manage/security/auto-tune/logs');
        autoTuneLogsData.value = getResponseList(logsRes.data);
    } catch (_error: unknown) {
        logger.error('Failed to fetch auto-tune logs:', _error);
        autoTuneLogsData.value = [];
    } finally {
        threatLoading.value = false;
    }
};

// ========================================
// FILE INTEGRITY
// ========================================
const fetchFileIntegrity = async (): Promise<void> => {
    integrityLoading.value = true;
    try {
        const response = await api.get('/manage/security/file-integrity');
        integrityData.value = getResponseObject(response.data);
    } catch (_error: unknown) {
        logger.error('Failed to fetch file integrity:', _error);
    } finally {
        integrityLoading.value = false;
    }
};

const runIntegrityCheck = async (): Promise<void> => {
    integrityLoading.value = true;
    try {
        await api.post('/manage/security/run-integrity-check'); 
        toast.success.action(t('system.security.fileIntegrity.resync.auditStarted'));
        fetchFileIntegrity();
    } catch (_error: unknown) {
        toast.error.fromResponse(_error);
    } finally {
        integrityLoading.value = false;
    }
};

const resyncIntegrityBaseline = async (): Promise<void> => {
    if (resyncCooldownSeconds.value > 0) {
        toast.error.default(t('system.security.fileIntegrity.resync.rateLimitActive', { seconds: resyncCooldownSeconds.value }));
        return;
    }

    const now = new Date();
    const timestamp = now.toLocaleString('id-ID', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'});

    resyncReason.value = t('system.security.fileIntegrity.resync.defaultReason', { timestamp });
    resyncClearHistory.value = true;
    resyncDialogOpen.value = true;
};

const submitIntegrityResync = async (): Promise<void> => {
    const reason = resyncReason.value.trim();
    if (reason.length < 8) {
        toast.error.default(t('system.security.fileIntegrity.resync.reasonMinLength'));
        return;
    }

    resyncSubmitting.value = true;
    integrityLoading.value = true;
    try {
        await api.post('/manage/security/file-integrity/resync', {
            reason,
            clear_history: resyncClearHistory.value});
        toast.success.action(t('system.security.fileIntegrity.resync.success'));
        resyncDialogOpen.value = false;
        await Promise.all([fetchFileIntegrity(), fetchSecurityHealth()]);
    } catch (_error: unknown) {
        const errorObj = _error as {
            response?: {
                status?: number;
                headers?: Record<string, string | number | undefined>;
                data?: { retry_after?: number };
            };
        };
        const status = errorObj?.response?.status;
        if (status === 429) {
            const fromBody = errorObj?.response?.data?.retry_after;
            const retryAfterRaw = errorObj?.response?.headers?.['retry-after'];
            const retryAfter = Number.isFinite(Number(fromBody)) && Number(fromBody) > 0
                ? Number(fromBody)
                : Number(retryAfterRaw);
            const waitSeconds = Number.isFinite(retryAfter) && retryAfter > 0 ? Math.ceil(retryAfter) : 60;
            resyncCooldownUntil.value = Date.now() + (waitSeconds * 1000);
            toast.error.default(t('system.security.fileIntegrity.resync.tooManyRequests', { seconds: waitSeconds }));
        } else {
            toast.error.fromResponse(_error);
        }
    } finally {
        resyncSubmitting.value = false;
        integrityLoading.value = false;
    }
};

// ========================================
// SECURITY SETTINGS
// ========================================
const fetchSecuritySettings = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/settings');
        const data = (response.data || {}) as Partial<typeof securitySettings.value>;
        securitySettings.value = {
            security_log_retention_days: data.security_log_retention_days ?? 90,
            activity_log_retention_days: data.activity_log_retention_days ?? 90,
            login_history_retention_days: data.login_history_retention_days ?? 180,
            security_autotune_frequency: data.security_autotune_frequency ?? 'weekly'};
        settingsLoaded.value = true;
    } catch (_error: unknown) {
        logger.error('Failed to fetch security settings:', _error);
    }
};

const saveSecuritySettings = async (settings: { 
    security_log_retention_days: number; 
    activity_log_retention_days: number;
    login_history_retention_days: number;
    security_autotune_frequency: string 
}): Promise<void> => {
    settingsSaving.value = true;
    try {
        await api.put('/manage/security/settings', settings);
        securitySettings.value = { ...settings };
        toast.success.default(t('system.security.settings.saved'));
    } catch (_error: unknown) {
        logger.error('Failed to save security settings:', _error);
        toast.error.fromResponse(_error);
    } finally {
        settingsSaving.value = false;
    }
};

// ========================================
// MAINTENANCE MODE
// ========================================
const fetchMaintenanceStatus = async (): Promise<void> => {
    maintenanceLoading.value = true;
    try {
        const response = await api.get('/manage/security/maintenance');
        const parsed = parseSingleResponse<typeof maintenanceStatus.value>(response);
        if (parsed) {
            maintenanceStatus.value = parsed;
        }
    } catch (_error: unknown) {
        logger.error('Failed to fetch maintenance status:', _error);
    } finally {
        maintenanceLoading.value = false;
    }
};

const activateMaintenance = async (modules: string[], duration: number): Promise<void> => {
    maintenanceActivating.value = true;
    try {
        await api.post('/manage/security/maintenance/activate', { modules, duration });
        toast.success.default(t('system.security.maintenance.notifications.activated', { duration }));
        await fetchMaintenanceStatus();
    } catch (_error: unknown) {
        logger.error('Failed to activate maintenance mode:', _error);
        toast.error.fromResponse(_error);
    } finally {
        maintenanceActivating.value = false;
    }
};

const deactivateMaintenance = async (): Promise<void> => {
    maintenanceActivating.value = true;
    try {
        await api.post('/manage/security/maintenance/deactivate');
        toast.success.default(t('system.security.maintenance.notifications.deactivated'));
        await fetchMaintenanceStatus();
        // Refresh integrity after maintenance ends as it triggers re-baseline
        setTimeout(() => fetchFileIntegrity(), 1000);
    } catch (_error: unknown) {
        logger.error('Failed to deactivate maintenance mode:', _error);
        toast.error.fromResponse(_error);
    } finally {
        maintenanceActivating.value = false;
    }
};

// ========================================
// SECURITY DASHBOARD ACTIONS
// ========================================
async function fetchSecurityHealth(): Promise<void> {
    try {
        const response = await api.get('/manage/security/health');
        securityHealth.value = (response.data || securityHealth.value) as typeof securityHealth.value;
    } catch (_error: unknown) {
        logger.error('Failed to fetch security health:', _error);
    }
}

async function testNotification(): Promise<void> {
    try {
        await api.post('/manage/security/test-notification');
        toast.success.default(t('system.security.notifications.test_sent'));
    } catch (_error: unknown) {
        toast.error.fromResponse(_error);
    }
}

// ========================================
// LIFECYCLE & WATCHERS
// ========================================
const refreshAll = async (): Promise<void> => {
    await Promise.all([fetchLogs(), fetchStats(), fetchSecurityKpi(), fetchBlocklist(), fetchWhitelist(), fetchSecurityHealth()]);
    if (activeTab.value === 'shield-journal') {
        await Promise.all([fetchShieldLogs(), fetchShieldStats()]);
    }
};

watch(activeTab, (newTab: string) => {
    const q = { ...route.query };
    if (newTab === 'overview') {
        delete q.tab;
    } else {
        q.tab = newTab;
    }
    router.replace({ query: q });
    if (newTab === 'csp-reports' && cspReports.value.length === 0) {
        fetchCspReports();
        fetchCspStats();
    } else if (newTab === 'slow-queries' && slowQueries.value.length === 0) {
        fetchSlowQueries();
        fetchSlowQueryStats();
    } else if (newTab === 'vulnerabilities' && vulnerabilities.value.length === 0) {
        fetchVulnerabilities();
        fetchVulnStats();
    } else if (newTab === 'shield-journal' && shieldLogs.value.length === 0) {
        fetchShieldLogs();
        fetchShieldStats();
    } else if (newTab === 'threat-analysis') {
        fetchThreatAnalysis();
    } else if (newTab === 'file-integrity' && !integrityData.value) {
        fetchFileIntegrity();
    } else if (newTab === 'settings' && !settingsLoaded.value) {
        fetchSecuritySettings();
    }
});

const overviewDataLoaded = ref(false);
const loadOverviewExtras = async (): Promise<void> => {
    if (overviewDataLoaded.value) return;
    overviewDataLoaded.value = true;
    await Promise.all([fetchBlocklist(), fetchWhitelist(), fetchSecurityHealth()]);
};

onMounted(async () => {
    const tab = route.query.tab;
    if (typeof tab === 'string' && SECURITY_TABS.includes(tab)) {
        activeTab.value = tab;
    }
    await Promise.all([
        fetchLogs(),
        fetchStats(),
        fetchSecurityKpi(),
        fetchMaintenanceStatus(),
    ]);
    if (activeTab.value === 'overview') {
        await loadOverviewExtras();
    }
});
</script>
