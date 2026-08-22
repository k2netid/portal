<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="t('system.system.notifications.title')"
      :subtitle="t('system.settings.groups.notifications.description')"
    />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Send Notification Form -->
      <div
        v-show="!sidebarCollapsed"
        class="lg:col-span-4"
      >
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle>{{ $t('system.system.notifications.create.title') }}</CardTitle>
            <Button
              variant="ghost"
              size="icon"
              class="h-8 w-8"
              :aria-label="$t('actions.close')"
              @click="sidebarCollapsed = true"
            >
              <ChevronLeft class="h-4 w-4" />
            </Button>
          </CardHeader>
          <div
            v-if="queueHealth"
            class="px-6 pb-2"
          >
            <div class="flex items-center gap-2">
              <div
                :class="[ 'h-2 w-2 rounded-full', queueHealth.is_active ? 'bg-green-500' : 'bg-yellow-500' ]"
              />
              <span class="text-[10px] font-medium uppercase tracking-wider text-muted-foreground">
                {{ queueHealth.message }} ({{ queueHealth.driver }})
              </span>
            </div>
          </div>
          <CardContent class="space-y-4 pt-2">
            <div class="space-y-2">
              <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                {{ $t('system.system.notifications.form.target_type') }}
              </label>
              <Select v-model="form.target_type">
                <SelectTrigger :aria-label="$t('system.system.notifications.form.target_type')">
                  <SelectValue :placeholder="$t('system.system.notifications.form.target_type')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">
                    {{ $t('system.system.notifications.form.targets.all') }}
                  </SelectItem>
                  <SelectItem value="role">
                    {{ $t('system.system.notifications.form.targets.role') }}
                  </SelectItem>
                  <SelectItem value="user">
                    {{ $t('system.system.notifications.form.targets.user') }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div
              v-if="form.target_type === 'role'"
              class="space-y-2"
            >
              <label class="text-sm font-medium leading-none">{{ $t('system.system.notifications.form.targets.role') }}</label>
              <Select v-model="form.target_id">
                <SelectTrigger :aria-label="$t('system.system.notifications.form.targets.role')">
                  <SelectValue :placeholder="$t('system.system.notifications.form.targets.role')" />
                </SelectTrigger>
                <SelectContent>
                  <template v-if="roles && roles.length">
                    <SelectItem
                      v-for="role in roles"
                      :key="role.id"
                      :value="String(role.id)"
                    >
                      {{ role.name }}
                    </SelectItem>
                  </template>
                </SelectContent>
              </Select>
            </div>

            <div
              v-if="form.target_type === 'user'"
              class="space-y-2"
            >
              <label class="text-sm font-medium leading-none">{{ $t('system.system.notifications.form.targets.user') }}</label>
              <!-- Simple select for users, might need autocomplete for large user bases -->
              <Select v-model="form.target_id">
                <SelectTrigger :aria-label="$t('system.system.notifications.form.targets.user')">
                  <SelectValue :placeholder="$t('system.system.notifications.form.targets.user')" />
                </SelectTrigger>
                <SelectContent>
                  <template v-if="users && users.length">
                    <SelectItem
                      v-for="user in users"
                      :key="user.id"
                      :value="String(user.id)"
                    >
                      {{ user.name }} ({{ user.email }})
                    </SelectItem>
                  </template>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium leading-none">{{ $t('system.system.notifications.form.type') }}</label>
              <Select v-model="form.type">
                <SelectTrigger :aria-label="$t('system.system.notifications.form.type')">
                  <SelectValue :placeholder="$t('system.system.notifications.form.type')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="info">
                    Info
                  </SelectItem>
                  <SelectItem value="success">
                    Success
                  </SelectItem>
                  <SelectItem value="warning">
                    Warning
                  </SelectItem>
                  <SelectItem value="error">
                    Error
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Async Toggle -->
            <div class="flex items-center gap-2 py-2">
              <Checkbox 
                id="is_async"
                :checked="form.is_async"
                :aria-label="$t('system.system.notifications.form.process_async')"
                @update:checked="form.is_async = $event"
              />
              <label
                for="is_async"
                class="text-sm font-medium leading-none cursor-pointer"
              >
                {{ $t('system.system.notifications.form.process_async') }}
              </label>
            </div>

            <!-- Queue Status Warning -->
            <div
              v-if="form.is_async && queueHealth && !queueHealth.is_active"
              class="flex items-start gap-2 p-3 rounded-md bg-amber-500/10 text-amber-500 text-xs border border-amber-500/20"
            >
              <AlertTriangle class="h-4 w-4 shrink-0" />
              <div>
                <p class="font-semibold">
                  {{ $t('system.system.notifications.queue.inactive_title') }}
                </p>
                <p>{{ $t('system.system.notifications.queue.inactive_message') }}</p>
              </div>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium leading-none">{{ $t('system.system.notifications.form.title') }}</label>
              <Input
                v-model="form.title"
                :placeholder="$t('system.system.notifications.form.title')"
              />
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium leading-none">{{ $t('system.system.notifications.form.message') }}</label>
              <textarea
                v-model="form.message"
                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                :placeholder="$t('system.system.notifications.form.message')"
              />
            </div>

            <Button
              class="w-full"
              :disabled="sending || !isFormValid"
              @click="handleSend"
            >
              <Send
                v-if="!sending"
                class="mr-2 h-4 w-4"
              />
              <Loader2
                v-else
                class="mr-2 h-4 w-4"
              />
              {{ sending ? $t('system.system.notifications.form.sending') : $t('system.system.notifications.form.send') }}
            </Button>
          </CardContent>
        </Card>
      </div>

      <!-- History Table -->
      <div :class="[sidebarCollapsed ? 'lg:col-span-12' : 'lg:col-span-8', ' ']">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0">
            <div class="flex items-center gap-4">
              <Button
                v-if="sidebarCollapsed"
                variant="outline"
                size="sm"
                @click="sidebarCollapsed = false"
              >
                <Plus class="mr-2 h-4 w-4" />
                {{ $t('system.system.notifications.create.title') }}
              </Button>
              <CardTitle>{{ $t('system.system.notifications.history.title') }}</CardTitle>
            </div>
            <div
              v-if="selectedItems.length > 0"
              class="flex items-center gap-2"
            >
              <span class="text-sm text-muted-foreground mr-2">{{ selectedItems.length }} selected</span>
              <Button 
                variant="destructive" 
                size="sm" 
                :disabled="bulkRevoking"
                @click="handleBulkRevoke"
              >
                <Trash2
                  v-if="!bulkRevoking"
                  class="h-4 w-4 mr-2"
                />
                <Loader2
                  v-else
                  class="h-4 w-4 mr-2"
                />
                {{ $t('system.system.notifications.form.revoke') }}
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            <div class="rounded-md border">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead class="w-[40px]">
                      <Checkbox 
                        :checked="isAllSelected"
                        :aria-label="$t('actions.selectAll')"
                        @update:checked="toggleSelectAll"
                      />
                    </TableHead>
                    <TableHead>{{ $t('system.system.notifications.table.title') }}</TableHead>
                    <TableHead>{{ $t('system.system.notifications.table.type') }}</TableHead>
                    <TableHead>{{ $t('system.system.notifications.table.recipients') }}</TableHead>
                    <TableHead>{{ $t('system.system.notifications.table.sent_at') }}</TableHead>
                    <TableHead class="text-right">
                      {{ $t('common.actions.title') }}
                    </TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <template v-if="history && history.length">
                    <TableRow
                      v-for="notification in history"
                      :key="notification.id"
                      :class="{ 'bg-muted/50': isSelected(notification) }"
                    >
                      <TableCell>
                        <Checkbox 
                          :checked="isSelected(notification)"
                          :aria-label="$t('actions.select') + ': ' + notification.title"
                          @update:checked="toggleSelect(notification)"
                        />
                      </TableCell>
                      <TableCell>
                        <div class="font-medium">
                          {{ notification.title }}
                        </div>
                        <div class="text-xs text-muted-foreground truncate max-w-[200px]">
                          {{ notification.message }}
                        </div>
                      </TableCell>
                      <TableCell>
                        <Badge :variant="getBadgeVariant(notification.type)">
                          {{ notification.type }}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        {{ notification.recipient_count }}
                      </TableCell>
                      <TableCell>
                        {{ formatDate(notification.created_at) }}
                      </TableCell>
                      <TableCell class="text-right">
                        <Button
                          variant="ghost"
                          size="sm"
                          class="h-8 w-8 p-0 text-destructive hover:text-destructive hover:bg-destructive/10"
                          :disabled="revoking === notification.id"
                          :aria-label="$t('system.system.notifications.form.revoke')"
                          @click="handleRevoke(notification)"
                        >
                          <Trash2
                            v-if="revoking !== notification.id"
                            class="h-4 w-4"
                          />
                          <Loader2
                            v-else
                            class="h-4 w-4"
                          />
                        </Button>
                      </TableCell>
                    </TableRow>
                  </template>
                  <TableRow v-if="history.length === 0">
                    <TableCell
                      colspan="6"
                      class="h-24 text-center text-muted-foreground"
                    >
                      No history found
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>

            <!-- Pagination -->
            <div
              v-if="pagination && pagination.last_page > 1"
              class="flex items-center justify-between px-2 py-4"
            >
              <div class="text-sm text-muted-foreground">
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
              </div>
              <div class="flex items-center gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  :disabled="pagination.current_page === 1"
                  @click="fetchHistory(pagination.current_page - 1)"
                >
                  Previous
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  :disabled="pagination.current_page === pagination.last_page"
                  @click="fetchHistory(pagination.current_page + 1)"
                >
                  Next
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { PageHeader } from '@/shared/components/shell';
import { useI18n } from 'vue-i18n';
import { logger } from '@/shared/utils/logger';
import { ref, reactive, computed, onMounted, watch, onUnmounted } from 'vue';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import {
  AlertTriangle,
  ChevronLeft,
  Loader2,
  Plus,
  Send,
  Trash2,
} from 'lucide-vue-next';
import { parseResponse, parseSingleResponse } from '@/shared/utils/responseParser';

// UI Components
// UI Components
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    Button,
    Input,
    Badge,
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    Checkbox
} from '@/shared/components/ui';

interface Role {
    id: string;
    name: string;
}

interface User {
    id: string;
    name: string;
    email: string;
}

interface Notification {
    id: string;
    title: string;
    message: string;
    type: string;
    recipient_count: number;
    created_at: string;
    broadcast_id?: string;
}

interface NotificationForm {
    target_type: 'all' | 'role' | 'user';
    target_id: string | undefined;
    type: string;
    title: string;
    message: string;
    is_async: boolean;
}

interface QueueHealth {
    is_active: boolean;
    message: string;
    driver: string;
}

interface PaginationInfo {
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
}

interface SelectedItem {
    id?: string;
    broadcast_id?: string;
    title: string;
    message: string;
    created_at: string;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const roles = ref<Role[]>([]);
const users = ref<User[]>([]);
const history = ref<Notification[]>([]);
const pagination = ref<PaginationInfo | null>(null);
const sending = ref(false);
const revoking = ref<string | null>(null);
const selectedItems = ref<SelectedItem[]>([]);
const bulkRevoking = ref(false);
const queueHealth = ref<QueueHealth | null>(null);
const sidebarCollapsed = ref(false);
let healthCheckInterval: ReturnType<typeof setInterval> | null = null;

const form = reactive<NotificationForm>({
    target_type: 'all',
    target_id: undefined,
    type: 'info',
    title: '',
    message: '',
    is_async: true
});

// Reset target_id when target_type changes to prevent invalid selections
watch(() => form.target_type, () => {
    form.target_id = undefined;
});

// Form validation
const isFormValid = computed(() => {
    // Title and message are always required
    if (!form.title?.trim() || !form.message?.trim()) {
        return false;
    }
    // target_id is required when target_type is 'role' or 'user'
    if ((form.target_type === 'role' || form.target_type === 'user') && !form.target_id) {
        return false;
    }
    return true;
});

const getBadgeVariant = (type: string) => {
    switch (type) {
        case 'error': return 'destructive';
        case 'warning': return 'secondary';
        case 'success': return 'default';
        default: return 'secondary';
    }
};

const formatDate = (date: string) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
};

const fetchQueueHealth = async (): Promise<void> => {
    try {
        const res = await api.get('/manage/system/info');
        const data = parseSingleResponse<{ queue_health?: QueueHealth }>(res);
        queueHealth.value = data?.queue_health || null;
    } catch (error: unknown) {
        // Silent fail for polling
        logger.debug('Failed to fetch queue health:', error);
    }
};

const fetchData = async (): Promise<void> => {
    try {
        const [rolesRes, usersRes] = await Promise.all([
            api.get('/manage/system/roles'),
            api.get('/manage/system/users')
        ]);
        roles.value = parseResponse<Role>(rolesRes).data;
        users.value = parseResponse<User>(usersRes).data;
        
        // Initial health check
        await fetchQueueHealth();
    } catch (error: unknown) {
        logger.error('Failed to fetch data:', error);
    }
};

const fetchHistory = async (page = 1) : Promise<void> => {
    try {
        const res = await api.get('/manage/notifications/system', {
            params: { page, limit: 10 }
        });
        const parsed = parseResponse<Notification>(res);
        history.value = parsed.data;
        pagination.value = parsed.pagination || null;
        
        // Clear selection on page change or refresh
        selectedItems.value = [];
    } catch (error: unknown) {
        logger.error('Failed to fetch history:', error);
    }
};

const isSelected = (item: Notification) => {
    return selectedItems.value.some((i: SelectedItem) => 
        (i.id && item.id && i.id === item.id) ||
        (i.broadcast_id && item.broadcast_id && i.broadcast_id === item.broadcast_id) ||
        (i.title === item.title && i.message === item.message)
    );
};

const toggleSelect = (item: Notification) => {
    const index = selectedItems.value.findIndex((i: SelectedItem) => 
        (i.id && item.id && i.id === item.id) ||
        (i.broadcast_id && item.broadcast_id && i.broadcast_id === item.broadcast_id) ||
        (i.title === item.title && i.message === item.message)
    );
    
    if (index > -1) {
        selectedItems.value.splice(index, 1);
    } else {
        selectedItems.value.push({
            id: item.id,
            broadcast_id: item.broadcast_id,
            title: item.title,
            message: item.message,
            created_at: item.created_at
        });
    }
};

const isAllSelected = computed(() => {
    return history.value.length > 0 && history.value.every(item => isSelected(item));
});

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedItems.value = [];
    } else {
        history.value.forEach((item: Notification) => {
            if (!isSelected(item)) {
                selectedItems.value.push({
                    id: item.id,
                    broadcast_id: item.broadcast_id,
                    title: item.title,
                    message: item.message,
                    created_at: item.created_at
                });
            }
        });
    }
};

const handleBulkRevoke = async () => {
    const confirmed = await confirm({
        title: t('system.system.notifications.form.revoke'),
        message: t('system.system.notifications.confirm.revoke'),
        variant: 'danger',
        confirmText: t('system.system.notifications.form.revoke'),
    });

    if (!confirmed) return;

    bulkRevoking.value = true;
    try {
        await api.post('/manage/notifications/system/bulk-revoke', {
            broadcasts: selectedItems.value
        });
        toast.success.action(t('system.system.notifications.messages.revoked'));
        selectedItems.value = [];
        await fetchHistory(pagination.value?.current_page || 1);
        window.dispatchEvent(new CustomEvent('notification:updated'));
    } catch (error: unknown) {
        logger.error('Failed to bulk revoke:', error);
        toast.error.validation(t('system.system.notifications.messages.failed'));
    } finally {
        bulkRevoking.value = false;
    }
};

const handleRevoke = async (notification: Notification) => {
    const confirmed = await confirm({
        title: t('system.system.notifications.form.revoke'),
        message: t('system.system.notifications.confirm.revoke'),
        variant: 'danger',
        confirmText: t('system.system.notifications.form.revoke'),
    });

    if (!confirmed) return;

    revoking.value = notification.id;
    try {
        await api.post('/manage/notifications/system/revoke', {
            id: notification.id,
            broadcast_id: notification.broadcast_id,
            title: notification.title,
            message: notification.message,
            created_at: notification.created_at
        });
        toast.success.action(t('system.system.notifications.messages.revoked'));
        await fetchHistory(pagination.value?.current_page || 1);
        window.dispatchEvent(new CustomEvent('notification:updated'));
    } catch (error: unknown) {
        logger.error('Failed to revoke:', error);
        toast.error.validation(t('system.system.notifications.messages.failed'));
    } finally {
        revoking.value = null;
    }
};

const handleSend = async () => {
    if (!form.title || !form.message) {
        toast.error.validation('Please fill in all fields');
        return;
    }

    if (form.target_type !== 'all' && !form.target_id) {
        toast.error.validation('Please select a target');
        return;
    }

    const confirmed = await confirm({
        title: t('system.system.notifications.create.title'),
        message: t('system.system.notifications.confirm.send'),
        variant: 'info',
        confirmText: t('system.system.notifications.form.send'),
    });

    if (!confirmed) return;

    sending.value = true;
    try {
        await api.post('/manage/notifications/broadcast', form);
        toast.success.action(form.is_async 
            ? t('system.system.notifications.messages.sent') 
            : 'Notifikasi berhasil dikirim secara langsung'
        );
        
        // Reset form slightly but keep some defaults
        form.title = '';
        form.message = '';
        
        // Refresh history immediately
        fetchHistory();

        // Trigger global event for Navbar to update
        window.dispatchEvent(new CustomEvent('notification:sent'));
        window.dispatchEvent(new CustomEvent('notification:updated'));

    } catch (error: unknown) {
        logger.error('Failed to send:', error);
        toast.error.validation(t('system.system.notifications.messages.failed'));
    } finally {
        sending.value = false;
    }
};

onMounted(() => {
    fetchData();
    fetchHistory();

    // Poll queue health every 15 seconds
    healthCheckInterval = setInterval(() => {
        fetchQueueHealth();
        // Also poll history to catch up with async jobs
        fetchHistory(pagination.value?.current_page || 1);
    }, 10000);
});

onUnmounted(() => {
    if (healthCheckInterval) {
        clearInterval(healthCheckInterval);
    }
});
</script>
