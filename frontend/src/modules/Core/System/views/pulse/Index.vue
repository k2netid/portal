<template>
  <div class="space-y-6">
    <PageHeader
borderless
      :title="t('system.journal_dashboard.title')"
      :subtitle="t('system.journal_dashboard.description')"
    >
    </PageHeader>

<ConsoleListCard>
      <div class="p-6 space-y-6">
<!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <!-- Activity Logs Card -->
      <router-link 
        :to="consolePath('/activity-journal')" 
        class="bg-card border border-border rounded-lg p-6 hover:border-primary/50 hover:shadow-md group"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-full bg-indigo-500/20 dark:bg-indigo-500/10 flex items-center justify-center">
            <ClipboardList class="w-6 h-6 text-indigo-500 dark:text-indigo-400" />
          </div>
          <ChevronRight class="w-5 h-5 text-muted-foreground group-hover:text-primary" />
        </div>
        <h3 class="font-semibold text-foreground mb-1">
          {{ t('system.journal_dashboard.cards.activity.title') }}
        </h3>
        <p class="text-sm text-muted-foreground mb-3">
          {{ t('system.journal_dashboard.cards.activity.description') }}
        </p>
        <div class="flex items-center gap-4 text-sm">
          <span class="text-foreground font-medium">{{ stats.activity?.total || 0 }}</span>
          <span class="text-muted-foreground">{{ t('system.journal_dashboard.cards.activity.total') }}</span>
        </div>
      </router-link>

      <!-- Security Logs Card -->
      <router-link 
        :to="consolePath('/security-journal')" 
        class="bg-card border border-border rounded-lg p-6 hover:border-primary/50 hover:shadow-md group"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-full bg-red-500/20 dark:bg-red-500/10 flex items-center justify-center">
            <ShieldAlert class="w-6 h-6 text-red-500 dark:text-red-400" />
          </div>
          <ChevronRight class="w-5 h-5 text-muted-foreground group-hover:text-primary" />
        </div>
        <h3 class="font-semibold text-foreground mb-1">
          {{ t('system.journal_dashboard.cards.security.title') }}
        </h3>
        <p class="text-sm text-muted-foreground mb-3">
          {{ t('system.journal_dashboard.cards.security.description') }}
        </p>
        <div class="flex items-center gap-4 text-sm">
          <span class="text-foreground font-medium">{{ stats.security?.total || 0 }}</span>
          <span class="text-muted-foreground">{{ t('system.journal_dashboard.cards.security.total') }}</span>
        </div>
      </router-link>

      <!-- Login History Card -->
      <router-link 
        :to="consolePath('/access-journal')" 
        class="bg-card border border-border rounded-lg p-6 hover:border-primary/50 hover:shadow-md group"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-full bg-green-500/20 dark:bg-green-500/10 flex items-center justify-center">
            <Users class="w-6 h-6 text-green-500 dark:text-green-400" />
          </div>
          <ChevronRight class="w-5 h-5 text-muted-foreground group-hover:text-primary" />
        </div>
        <h3 class="font-semibold text-foreground mb-1">
          {{ t('system.journal_dashboard.cards.login.title') }}
        </h3>
        <p class="text-sm text-muted-foreground mb-3">
          {{ t('system.journal_dashboard.cards.login.description') }}
        </p>
        <div class="flex items-center gap-4 text-sm">
          <span class="text-foreground font-medium">{{ stats.login?.total || 0 }}</span>
          <span class="text-muted-foreground">{{ t('system.journal_dashboard.cards.login.total') }}</span>
        </div>
      </router-link>

      <!-- System Logs Card -->
      <router-link 
        :to="consolePath('/system-journal')" 
        class="bg-card border border-border rounded-lg p-6 hover:border-primary/50 hover:shadow-md group"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-full bg-yellow-500/20 dark:bg-yellow-500/10 flex items-center justify-center">
            <Cpu class="w-6 h-6 text-yellow-500 dark:text-yellow-400" />
          </div>
          <ChevronRight class="w-5 h-5 text-muted-foreground group-hover:text-primary" />
        </div>
        <h3 class="font-semibold text-foreground mb-1">
          {{ t('system.journal_dashboard.cards.system.title') }}
        </h3>
        <p class="text-sm text-muted-foreground mb-3">
          {{ t('system.journal_dashboard.cards.system.description') }}
        </p>
        <div class="flex items-center gap-4 text-sm">
          <span class="text-foreground font-medium">{{ stats.system?.files || 0 }}</span>
          <span class="text-muted-foreground">{{ t('system.journal_dashboard.cards.system.files') }}</span>
        </div>
      </router-link>
    </div>

    <!-- Recent Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Activity Logs -->
      <div class="bg-card border border-border rounded-lg">
        <div class="px-6 py-4 border-b border-border flex items-center justify-between">
          <h2 class="font-semibold text-foreground">
            {{ t('system.journal_dashboard.recent.activity') }}
          </h2>
          <router-link
            :to="consolePath('/activity-journal')"
            class="text-sm text-primary hover:underline"
          >
            {{ t('system.journal_dashboard.viewAll') }}
          </router-link>
        </div>
        <div
          v-if="loading"
          class="p-6 text-center text-muted-foreground"
        >
          {{ t('system.journal_dashboard.loading') }}
        </div>
        <div
          v-else-if="recentActivity.length === 0"
          class="p-6 text-center text-muted-foreground"
        >
          {{ t('system.journal_dashboard.empty') }}
        </div>
        <div
          v-else
          class="divide-y divide-border"
        >
          <div
            v-for="log in recentActivity"
            :key="log.id"
            class="px-6 py-3 flex items-center justify-between"
          >
            <div class="flex items-center gap-3">
              <span
                :class="getActionBadgeClass(log.action)"
                class="text-xs px-2 py-1 rounded-full"
              >
                {{ getActionLabel(log.action) }}
              </span>
              <span class="text-sm text-foreground">{{ log.description || log.model_type }}</span>
            </div>
            <span class="text-xs text-muted-foreground">{{ formatDate(log.created_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Recent Security Events -->
      <div class="bg-card border border-border rounded-lg">
        <div class="px-6 py-4 border-b border-border flex items-center justify-between">
          <h2 class="font-semibold text-foreground">
            {{ t('system.journal_dashboard.recent.security') }}
          </h2>
          <router-link
            :to="consolePath('/security-journal')"
            class="text-sm text-primary hover:underline"
          >
            {{ t('system.journal_dashboard.viewAll') }}
          </router-link>
        </div>
        <div
          v-if="loading"
          class="p-6 text-center text-muted-foreground"
        >
          {{ t('system.journal_dashboard.loading') }}
        </div>
        <div
          v-else-if="recentSecurity.length === 0"
          class="p-6 text-center text-muted-foreground"
        >
          {{ t('system.journal_dashboard.empty') }}
        </div>
        <div
          v-else
          class="divide-y divide-border"
        >
          <div
            v-for="log in recentSecurity"
            :key="log.id"
            class="px-6 py-3 flex items-center justify-between"
          >
            <div class="flex items-center gap-3">
              <span
                :class="getSecurityBadgeClass(log.event_type)"
                class="text-xs px-2 py-1 rounded-full"
              >
                {{ getSecurityEventLabel(log.event_type) }}
              </span>
              <span class="text-sm text-foreground">{{ log.ip_address }}</span>
            </div>
            <span class="text-xs text-muted-foreground">{{ formatDate(log.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>
      </div>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { consolePath } from '@/shared/utils/consoleRoute';
import {PageHeader, ConsoleListCard} from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { formatSecurityEventLabel } from '@/modules/Core/Security/utils/securityEventLabel';
import api from '@/engine/api/client';
import {
  ChevronRight,
  ClipboardList,
  Cpu,
  ShieldAlert,
  Users,
} from 'lucide-vue-next';

interface ActivityStats {
    total?: number;
}

interface SecurityStats {
    total?: number;
}

interface LoginStats {
    total?: number;
}

interface SystemStats {
    files?: number;
}

interface Stats {
    activity?: ActivityStats;
    security?: SecurityStats;
    login?: LoginStats;
    system?: SystemStats;
}

interface ActivityLog {
    id: string;
    action: string;
    description: string;
    model_type: string;
    created_at: string;
}

interface SecurityLog {
    id: string;
    event_type: string;
    ip_address: string;
    created_at: string;
}

const { t } = useI18n();

const loading = ref(true);
const stats = ref<Stats>({});
const recentActivity = ref<ActivityLog[]>([]);
const recentSecurity = ref<SecurityLog[]>([]);

const fetchStats = async () => {
    try {
        const [activityRes, securityRes, loginRes, systemRes] = await Promise.allSettled([
            api.get('/manage/activity-journal/statistics'),
            api.get('/manage/security/stats'),
            api.get('/manage/access-journal/statistics'),
            api.get('/manage/system-journal'),
        ]);

        if (activityRes.status === 'fulfilled') {
            const data = activityRes.value.data;
            stats.value.activity = data?.data || data || {};
        }

        if (securityRes.status === 'fulfilled') {
            const secData = securityRes.value.data?.data || securityRes.value.data || {};
            stats.value.security = {
                ...secData,
                total: secData.total_events || 0
            };
        }

        if (loginRes.status === 'fulfilled') {
            const logData = loginRes.value.data?.data || loginRes.value.data || {};
            stats.value.login = {
                ...logData,
                total: (logData.total_logins || 0) + (logData.failed_logins || 0)
            };
        }

        if (systemRes.status === 'fulfilled') {
            const sysData = systemRes.value.data?.data || systemRes.value.data || [];
            stats.value.system = { files: Array.isArray(sysData) ? sysData.length : 0 };
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch stats:', error);
    }
};

const fetchRecentLogs = async () => {
    try {
        const [activityRes, securityRes] = await Promise.allSettled([
            api.get('/manage/activity-journal/recent?limit=10'),
            api.get('/manage/security/journal?per_page=10'),
        ]);

        if (activityRes.status === 'fulfilled') {
            const activityData = activityRes.value.data;
            recentActivity.value = Array.isArray(activityData)
                ? activityData
                : (activityData?.data || []);
        }

        if (securityRes.status === 'fulfilled') {
            const secData = securityRes.value.data?.data?.data || securityRes.value.data?.data || [];
            recentSecurity.value = Array.isArray(secData) ? secData : [];
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch recent logs:', error);
    }
};

const getActionBadgeClass = (action?: string) => {
    if (!action) return 'bg-gray-500/20 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400';
    
    const classes: Record<string, string> = {
        created: 'bg-green-500/20 dark:bg-green-500/10 text-green-500 dark:text-green-400',
        updated: 'bg-blue-500/20 dark:bg-blue-500/10 text-blue-500 dark:text-blue-400',
        deleted: 'bg-red-500/20 dark:bg-red-500/10 text-red-500 dark:text-red-400',
        login: 'bg-indigo-500/20 dark:bg-indigo-500/10 text-indigo-500 dark:text-indigo-400',
        logout: 'bg-gray-500/20 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400',
    };
    return classes[action] || 'bg-gray-500/20 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400';
};

const getSecurityBadgeClass = (eventType?: string) => {
    if (!eventType) return 'bg-yellow-500/20 dark:bg-yellow-500/10 text-yellow-500 dark:text-yellow-400';
    
    if (eventType.includes('failed') || eventType.includes('blocked')) {
        return 'bg-red-500/20 dark:bg-red-500/10 text-red-500 dark:text-red-400';
    }
    if (eventType.includes('success')) {
        return 'bg-green-500/20 dark:bg-green-500/10 text-green-500 dark:text-green-400';
    }
    return 'bg-yellow-500/20 dark:bg-yellow-500/10 text-yellow-500 dark:text-yellow-400';
};

const getActionLabel = (action?: string) => {
    if (!action) return '-';
    const key = `system.activity_journal.filters.types.${action}`;
    const translated = t(key);
    return translated !== key ? translated : action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getSecurityEventLabel = (eventType?: string) => formatSecurityEventLabel(t, eventType);

const formatDate = (dateString?: string) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    
    if (diffMins < 1) return t('common.time.justNow');
    if (diffMins < 60) return t('common.time.minsAgo', { count: diffMins }) || `${diffMins} menit lalu`;
    if (diffHours < 24) return t('common.time.hoursAgo', { count: diffHours }) || `${diffHours} jam lalu`;
    return date.toLocaleDateString();
};

onMounted(async () => {
    loading.value = true;
    await Promise.all([fetchStats(), fetchRecentLogs()]);
    loading.value = false;
});
</script>
