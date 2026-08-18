<template>
  <div class="space-y-6">
    <PageHeader
borderless
      :title="$t('system.notifications.title')"
    :subtitle="$t('system.notifications.subtitle')"
    >
      <template #actions>
        <div class="flex items-center gap-2">
<Button
                v-if="unreadCount > 0"
                variant="outline"
                @click="markAllAsRead"
              >
                <CheckCheck class="mr-2 h-4 w-4" />
                {{ $t('system.notifications.actions.markAllRead') }}
              </Button>
</div>
      </template>
    </PageHeader>

<ConsoleListCard>
      <div class="p-6 space-y-6">
<Card>
      <CardHeader class="pb-3">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="relative flex-1">
            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="search"
              type="text"
              :placeholder="$t('system.notifications.filters.search')"
              :aria-label="$t('system.notifications.filters.search')"
              class="pl-9"
            />
          </div>
          <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
            <Select v-model="typeFilter">
              <SelectTrigger class="w-full sm:w-[150px]" :aria-label="$t('system.notifications.filters.allTypes')">
                <SelectValue :placeholder="$t('system.notifications.filters.allTypes')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('system.notifications.filters.allTypes') }}
                </SelectItem>
                <SelectItem value="info">
                  {{ $t('system.notifications.filters.type.info') }}
                </SelectItem>
                <SelectItem value="success">
                  {{ $t('system.notifications.filters.type.success') }}
                </SelectItem>
                <SelectItem value="warning">
                  {{ $t('system.notifications.filters.type.warning') }}
                </SelectItem>
                <SelectItem value="error">
                  {{ $t('system.notifications.filters.type.error') }}
                </SelectItem>
              </SelectContent>
            </Select>

            <Select v-model="readFilter">
              <SelectTrigger class="w-full sm:w-[150px]" :aria-label="$t('system.notifications.filters.readStatus.all')">
                <SelectValue :placeholder="$t('system.notifications.filters.readStatus.all')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('system.notifications.filters.readStatus.all') }}
                </SelectItem>
                <SelectItem value="unread">
                  {{ $t('system.notifications.filters.readStatus.unread') }}
                </SelectItem>
                <SelectItem value="read">
                  {{ $t('system.notifications.filters.readStatus.read') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        <div
          v-if="loading"
          class="flex justify-center py-8"
        >
          <Loader2 class="h-8 w-8 text-muted-foreground" />
        </div>

        <EmptyState
          v-else-if="filteredNotifications.length === 0"
          :title="$t('system.notifications.messages.empty')"
          :icon="BellOff"
        />

        <div
          v-else
          class="space-y-4"
        >
          <div
            v-for="notification in filteredNotifications"
            :key="notification.id"
            class="group flex flex-col sm:flex-row gap-4 p-4 rounded-lg border hover:bg-muted/50"
            :class="notification.read_at ? 'bg-card border-border' : 'bg-primary/5 border-primary/20'"
          >
            <div class="flex-shrink-0 mt-1">
              <span
                v-if="!notification.read_at"
                class="flex h-2.5 w-2.5 rounded-full bg-primary"
              />
            </div>
                        
            <div class="flex-1 space-y-1">
              <div class="flex items-start justify-between gap-2">
                <div class="space-y-1">
                  <div class="flex items-center gap-2 flex-wrap">
                    <Badge :variant="getBadgeVariant(notification.type)">
                      {{ $t(`system.notifications.filters.type.${notification.type}`) }}
                    </Badge>
                    <h4 class="font-semibold leading-none tracking-tight">
                      {{ notification.title }}
                    </h4>
                  </div>
                  <p class="text-sm text-muted-foreground">
                    {{ notification.message }}
                  </p>
                </div>
                <span class="text-xs text-muted-foreground whitespace-nowrap">
                  {{ formatDate(notification.created_at) }}
                </span>
              </div>
            </div>

            <div class="flex sm:flex-col gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100">
              <Button
                v-if="!notification.read_at"
                variant="ghost"
                size="sm"
                class="h-8 w-8 p-0"
                :aria-label="$t('system.notifications.actions.markRead')"
                @click="markAsRead(notification)"
              >
                <Check class="h-4 w-4" />
              </Button>
              <Button
                variant="ghost"
                size="sm"
                class="h-8 w-8 p-0 text-destructive hover:text-destructive"
                :aria-label="$t('system.notifications.actions.delete')"
                @click="deleteNotification(notification)"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
      </div>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { EmptyState } from '@/shared/components/feedback';

import {PageHeader, ConsoleListCard} from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { getResponseList } from '@/shared/utils/responseParser';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import {
  BellOff,
  Check,
  CheckCheck,
  Loader2,
  Search,
  Trash2,
} from 'lucide-vue-next';

interface Notification {
    id: string | string;
    title: string;
    message: string;
    type: string;
    read_at: string | null;
    created_at: string;
}

// UI Components
import { 
    Card, 
    CardHeader, 
    CardContent, 
    Button, 
    Input, 
    Badge, 
    Select, 
    SelectTrigger, 
    SelectValue, 
    SelectContent, 
    SelectItem 
} from '@/shared/components/ui';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const notifications = ref<Notification[]>([]);
const loading = ref(false);
const search = ref('');
const typeFilter = ref('all');
const readFilter = ref('all');
const pollingInterval = ref<ReturnType<typeof setInterval> | null>(null);

const unreadCount = computed(() => {
    if (!Array.isArray(notifications.value)) return 0;
    return notifications.value.filter(n => !n.read_at).length;
});

const filteredNotifications = computed(() => {
    if (!Array.isArray(notifications.value)) return [];
    
    let filtered = notifications.value;
    
    if (typeFilter.value !== 'all') {
        filtered = filtered.filter(n => n?.type === typeFilter.value);
    }
    
    if (readFilter.value === 'read') {
        filtered = filtered.filter(n => n?.read_at);
    } else if (readFilter.value === 'unread') {
        filtered = filtered.filter(n => !n?.read_at);
    }
    
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(n => 
            n?.title?.toLowerCase().includes(searchLower) ||
            n?.message?.toLowerCase().includes(searchLower)
        );
    }
    
    return filtered;
});

const getBadgeVariant = (type: string) => {
    switch (type) {
        case 'error': return 'destructive';
        case 'warning': return 'secondary';
        case 'success': return 'default';
        case 'info': return 'secondary';
        default: return 'outline';
    }
};

const fetchNotifications = async () => {
    // Only show loading on initial fetch
    if (notifications.value.length === 0) {
        loading.value = true;
    }
    
    try {
        const response = await api.get('/manage/notifications');
        notifications.value = getResponseList<Notification>(response.data);
    } catch (error: unknown) {
        logger.error('Failed to fetch notifications:', error);
    } finally {
        loading.value = false;
    }
};

const markAsRead = async (notification: Notification) => {
    try {
        await api.put(`/manage/notifications/${notification.id}/read`);
        toast.success.default(t('system.notifications.messages.markReadSuccess'));
        
        // Optimistic update
        const index = notifications.value.findIndex(n => n.id === notification.id);
        const targetNotification = index !== -1 ? notifications.value[index] : undefined;
        if (targetNotification) {
            targetNotification.read_at = new Date().toISOString();
        }
        
    } catch (error: unknown) {
        logger.error('Failed to mark notification as read:', error);
        toast.error.default(t('system.notifications.messages.markReadFailed'));
    }
};

const markAllAsRead = async () => {
    try {
        await api.put('/manage/notifications/read-all');
        toast.success.default(t('system.notifications.messages.markAllReadSuccess'));
        fetchNotifications();
    } catch (error: unknown) {
        logger.error('Failed to mark all notifications as read:', error);
        toast.error.default(t('system.notifications.messages.markAllReadFailed'));
    }
};

const deleteNotification = async (notification: Notification) => {
    const confirmed = await confirm({
        title: t('system.notifications.actions.delete'),
        message: t('system.notifications.confirm.delete'),
        variant: 'danger',
        confirmText: t('system.notifications.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/manage/notifications/${notification.id}`);
        toast.success.default(t('system.notifications.messages.deleteSuccess'));
        
        // Optimistic update
        notifications.value = notifications.value.filter(n => n.id !== notification.id);
        
    } catch (error: unknown) {
        logger.error('Failed to delete notification:', error);
        toast.error.default(t('system.notifications.messages.deleteFailed'));
    }
};

const formatDate = (date: string) => {
    if (!date) return '-';
    // Use Intl.DateTimeFormat for consistent formatting
    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(new Date(date));
};

onMounted(() => {
    fetchNotifications();
    // Poll for new notifications every 30 seconds
    pollingInterval.value = setInterval(fetchNotifications, 30000);
});

onUnmounted(() => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
    }
});
</script>
