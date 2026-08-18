<template>
  <Card class="flex flex-col h-full overflow-hidden border-border/40">
    <CardHeader class="flex flex-row items-center justify-between pb-4 space-y-0">
      <div class="space-y-1">
        <h2 class="text-xl font-bold flex items-center gap-2">
          <History class="w-5 h-5 text-primary" />
          {{ $t('system.dashboard.widgets.recentActivity.title') }}
        </h2>
        <CardDescription v-if="activities.length > 0">
          {{ $t('system.dashboard.widgets.recentActivity.description') }}
        </CardDescription>
      </div>
      <div class="flex items-center gap-1.5 px-2 py-1 rounded-full bg-success/10 text-foreground">
        <div class="w-1.5 h-1.5 rounded-full bg-success" />
        <span class="text-xs font-medium">{{ $t('system.dashboard.widgets.recentActivity.live') }}</span>
      </div>
    </CardHeader>

    <CardContent class="flex-1 overflow-y-auto p-0 border-t border-border/40">
      <div
        v-if="loading && activities.length === 0"
        class="flex flex-col items-center justify-center p-12 text-muted-foreground space-y-3"
      >
        <Loader2 class="w-8 h-8 opacity-50" />
        <p>{{ $t('system.dashboard.widgets.recentActivity.loading') }}</p>
      </div>
            
      <div
        v-else-if="activities.length === 0"
        class="flex flex-col items-center justify-center p-12 text-muted-foreground space-y-3"
      >
        <div class="p-4 rounded-full bg-muted/30">
          <ZapOff class="w-8 h-8 opacity-20" />
        </div>
        <p>{{ $t('system.dashboard.widgets.recentActivity.empty') }}</p>
      </div>

      <div
        v-else
        class="divide-y divide-border/40"
      >
        <div
          v-for="activity in activities.slice(0, 5)"
          :key="activity.id"
          class="p-4 hover:bg-muted/30 group"
        >
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
              <Avatar class="h-10 w-10 ring-2 ring-background group-hover:ring-muted">
                <AvatarFallback
                  :class="getUserAvatarClass(activity)"
                  class="font-bold text-xs"
                >
                  {{ getDisplayInitials(activity.user?.name) }}
                </AvatarFallback>
              </Avatar>
            </div>
            <div class="flex-1 min-w-0 space-y-1">
              <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-foreground truncate">
                  {{ activity.user?.name || $t('common.labels.system') }}
                </p>
                <span class="text-[10px] text-muted-foreground flex items-center gap-1">
                  <Clock class="w-3 h-3" />
                  {{ formatTime(activity.created_at) }}
                </span>
              </div>
              <p class="text-sm text-muted-foreground flex items-center flex-wrap gap-2">
                <Badge 
                  variant="outline"
                  class="h-5 px-1.5 text-[10px] font-bold border-none"
                  :class="getActionBadgeClass(activity.action || activity.type)"
                >
                  {{ getActionLabel(activity.action || activity.type) }}
                </Badge>
                <span class="line-clamp-1">{{ activity.description }}</span>
              </p>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
        
    <div class="p-3 bg-muted/10 border-t border-border/40">
      <router-link
        :to="consolePath('/activity-journal')"
        class="inline-flex h-10 w-full items-center justify-center rounded-md px-4 text-sm font-medium text-primary hover:bg-primary/5"
      >
        {{ $t('system.dashboard.widgets.recentActivity.viewAll') }}
        <ArrowRight class="w-4 h-4 ml-2" />
      </router-link>
    </div>
  </Card>
</template>

<script setup lang="ts">
import { consolePath } from '@/shared/utils/consoleRoute';
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { formatSecurityEventLabel } from '@/modules/Core/Security/utils/securityEventLabel';
import api from '@/engine/api/client';
import { getDisplayInitials } from '@/shared/utils/displayInitials';
import { parseResponse } from '@/shared/utils/responseParser';
import { 
    Card, 
    CardHeader, 
    CardDescription, 
    CardContent, 
    Badge, 
    Avatar, 
    AvatarFallback 
} from '@/shared/components/ui';
import {
  ArrowRight,
  Clock,
  History,
  Loader2,
  ZapOff,
} from 'lucide-vue-next';

interface Activity {
    id: string;
    user_id?: string | null;
    user?: {
        name: string;
        [key: string]: unknown;
    } | null;
    action?: string;
    type?: string;
    description: string;
    created_at: string;
    [key: string]: unknown;
}

const { t } = useI18n();

const activities = ref<Activity[]>([]);
const loading = ref(false);
const refreshInterval = ref<ReturnType<typeof setInterval> | null>(null);

const stopRefresh = () => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
        refreshInterval.value = null;
    }
};

const fetchActivities = async () => {
    if ((window as unknown as { __isSessionTerminated?: boolean }).__isSessionTerminated) {
        stopRefresh();
        return;
    }

    if (activities.value.length === 0) {
        loading.value = true;
    }
    
    try {
        const response = await api.get('/manage/system/activity-journal', { params: { per_page: 6 } });
        const { data } = parseResponse(response);
        activities.value = (data as Activity[]) || [];
    } catch (error: unknown) {
        if (error && typeof error === 'object' && 'code' in error && 'response' in error) {
            const err = error as { code: string; response?: { status: number } };
            if (err.response?.status === 401 || err.response?.status === 403) {
                stopRefresh();
                return;
            }
            if (err.code !== 'ERR_CANCELED') {
                logger.error('Failed to fetch recent activities:', error);
            }
        }
    } finally {
        loading.value = false;
    }
};

const formatTime = (date: string) => {
    if (!date) return '';
    const d = new Date(date);
    const now = new Date();
    const diff = Math.floor((now.getTime() - d.getTime()) / 1000);

    if (diff < 60) return t('system.dashboard.widgets.recentActivity.time.justNow');
    if (diff < 3600) return t('system.dashboard.widgets.recentActivity.time.ago', { time: `${Math.floor(diff / 60)}m` });
    if (diff < 86400) return t('system.dashboard.widgets.recentActivity.time.ago', { time: `${Math.floor(diff / 3600)}h` });
    
    return d.toLocaleDateString();
};

const getUserAvatarClass = (activity: Activity) => {
    const id = activity.user_id || '0';
    const colors = [
        'bg-primary/10 text-primary',
        'bg-success/10 text-success',
        'bg-info/10 text-info',
        'bg-warning/10 text-warning',
        'bg-destructive/10 text-destructive',
    ];
    // Simple hash for string id to pick a color
    let hash = 0;
    const strId = String(id);
    for (let i = 0; i < strId.length; i++) {
        hash = ((hash << 5) - hash) + strId.charCodeAt(i);
        hash |= 0;
    }
    return colors[Math.abs(hash % colors.length)];
};

const getActionBadgeClass = (action?: string) => {
    const a = (action || '').toLowerCase();
    if (a.includes('create')) return 'bg-success/10 text-foreground';
    if (a.includes('update')) return 'bg-info/10 text-foreground';
    if (a.includes('delete')) return 'bg-destructive/10 text-foreground';
    if (a.includes('login') || a.includes('logout')) return 'bg-primary/10 text-foreground';
    return 'bg-muted text-muted-foreground';
};

const getActionLabel = (action?: string) => {
    if (!action) return '-';
    const secKey = `system.security.logs.eventTypes.${action}`;
    const secLabel = t(secKey);
    if (secLabel !== secKey) return secLabel;
    const actKey = `system.activity_journal.filters.types.${action}`;
    const actLabel = t(actKey);
    if (actLabel !== actKey) return actLabel;
    return formatSecurityEventLabel(t, action);
};

onMounted(() => {
    fetchActivities();
    // Refresh every 3 minutes, but only if the tab is visible
    refreshInterval.value = setInterval(() => {
        if (document.visibilityState === 'visible') {
            fetchActivities();
        }
    }, 180000);
});

onUnmounted(() => {
    stopRefresh();
});

defineExpose({ fetchActivities });
</script>
