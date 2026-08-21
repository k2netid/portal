<template>
  <header class="console-chrome sticky top-0 z-40 bg-card border-b border-border">
    <div class="flex items-center justify-between h-16 px-6">
      <!-- Left Group: Mobile Toggle + Breadcrumb -->
      <div class="flex items-center space-x-4">
        <!-- Mobile Menu Toggle -->
        <button
          class="lg:hidden text-muted-foreground hover:text-foreground"
          :aria-label="t('common.navigation.menu.toggleSidebar')"
          @click="$emit('toggle-sidebar')"
        >
          <Menu class="w-6 h-6" />
        </button>

        <!-- Breadcrumb (Desktop Only) -->
        <BreadcrumbTrail
          compact
          class="hidden lg:flex"
        />
      </div>

      <!-- Right: Search, Notifications, User Menu -->
      <div class="flex items-center space-x-4 ml-auto">
        <!-- Search -->
        <div class="relative">
          <!-- Global Search Trigger -->
          <button
            data-slot="search-trigger"
            class="hidden md:flex items-center w-64 px-3 py-2 text-sm text-muted-foreground bg-transparent border border-border/40 rounded-lg hover:bg-primary/5 hover:ring-4 hover:ring-primary/5 hover:text-foreground hover:scale-[1.02] active:scale-95 transition-all duration-300"
            @click="showGlobalSearch = true"
          >
            <Search class="mr-2 w-4 h-4 opacity-50" />
            <span>{{ t('common.actions.search') }}...</span>
            <kbd class="ml-auto pointer-events-none inline-flex h-5 select-none items-center gap-1 rounded-md border-border/40 bg-background px-1.5 font-mono text-[10px] font-medium text-muted-foreground opacity-100 uppercase">
              <span class="text-[10px]">Ctrl</span>
              <span class="text-[10px]">K</span>
            </kbd>
          </button>

          <!-- Mobile Search Trigger -->
          <button
            class="md:hidden p-2 text-muted-foreground hover:text-foreground rounded-xl hover:bg-accent hover:scale-110 active:scale-90 transition-all duration-300"
            :aria-label="t('common.actions.search')"
            @click="showGlobalSearch = true"
          >
            <Search class="w-5 h-5" />
          </button>

          <!-- Global Search Component -->
          <GlobalSearch 
            v-model:is-open="showGlobalSearch" 
          />
        </div>
                
        <div class="hidden lg:flex items-center gap-1.5 mr-2">
          <!-- Notification & Search icons are managed below -->
        </div>

        <!-- Notifications -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <button
              class="relative flex items-center justify-center w-9 h-9 rounded-full text-muted-foreground hover:text-foreground hover:bg-primary/5 hover:ring-4 hover:ring-primary/10 hover:scale-110 active:scale-90 transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background"
              :aria-label="t('common.labels.notifications')"
            >
              <Bell class="w-6 h-6" />
              <span
                v-if="unreadNotificationsCount > 0"
                class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-destructive animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.8)]"
              >
              </span>
            </button>
          </DropdownMenuTrigger>
                    
          <DropdownMenuContent
            class="w-80 p-0"
            align="end"
            :side-offset="8"
          >
            <div class="p-4 border-b border-border flex items-center justify-between">
              <h3 class="text-sm font-semibold text-foreground">
                {{ t('common.labels.notifications') }}
              </h3>
              <router-link
                :to="{ name: 'notifications' }"
                class="text-xs text-primary hover:text-primary/80"
              >
                {{ t('common.actions.viewAll') }}
              </router-link>
            </div>
            <div class="max-h-96 overflow-y-auto py-1">
              <div
                v-if="loadingNotifications"
                class="p-4 text-center text-sm text-muted-foreground"
              >
                {{ t('common.messages.loading.default') }}
              </div>
              <div
                v-else-if="recentNotifications.length === 0"
                class="p-4 text-center text-sm text-muted-foreground"
              >
                {{ t('common.messages.empty.default') }}
              </div>
              <div v-else>
                <DropdownMenuItem
                  v-for="notification in recentNotifications"
                  :key="notification.id"
                  class="p-4 cursor-pointer focus:bg-muted"
                  :class="{ 'bg-primary/5': !notification.read_at }"
                  @click="handleNotificationClick(notification)"
                >
                  <div class="flex items-start w-full">
                    <span
                      v-if="!notification.read_at"
                      class="h-2 w-2 bg-primary rounded-full mt-2 mr-2 flex-shrink-0"
                    />
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-foreground line-clamp-1">
                        {{ notification.title }}
                      </p>
                      <p class="text-xs text-muted-foreground mt-1 line-clamp-2 leading-relaxed">
                        {{ notification.message }}
                      </p>
                      <p class="text-[10px] text-muted-foreground/60 mt-1.5">
                        {{ formatNotificationDate(notification.created_at) }}
                      </p>
                    </div>
                  </div>
                </DropdownMenuItem>
              </div>
            </div>
          </DropdownMenuContent>
        </DropdownMenu>

        <!-- Dark Mode Toggle -->
        <DarkModeToggle />


        <!-- User Menu -->
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger as-child>
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <button
                    class="group relative flex items-center p-0.5 rounded-full hover:ring-4 hover:ring-primary/10 hover:scale-105 active:scale-95 transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background"
                    :aria-label="t('common.labels.myProfile')"
                  >
                    <div class="relative w-9 h-9 rounded-full overflow-hidden border-2 border-border/50 group-hover:border-primary/50 transition-all duration-300">
                      <img
                        v-if="userAvatar"
                        :src="userAvatar"
                        :alt="user?.name"
                        class="w-full h-full object-cover"
                        @error="avatarError = true"
                      >
                      <div
                        v-else
                        class="w-full h-full bg-primary flex items-center justify-center text-primary-foreground font-bold text-xs"
                      >
                        {{ userInitial }}
                      </div>
                    </div>
                  </button>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                  class="w-64 mt-2 p-0 overflow-hidden border-none shadow-2xl rounded-[1.5rem] bg-background/95 backdrop-blur-xl ring-1 ring-border/50"
                  align="end"
                  :side-offset="8"
                >
                  <!-- Premium User Header -->
                  <div class="relative p-5 bg-gradient-to-br from-primary/10 via-transparent to-muted/20 border-b border-border/30">
                    <div class="flex items-center gap-4">
                      <div class="w-12 h-12 rounded-full border-2 border-primary/20 p-0.5">
                        <img
                          v-if="userAvatar"
                          :src="userAvatar"
                          :alt="user?.name"
                          class="w-full h-full rounded-full object-cover"
                        >
                        <div
                          v-else
                          class="w-full h-full rounded-full bg-primary flex items-center justify-center text-primary-foreground font-bold"
                        >
                          {{ userInitial }}
                        </div>
                      </div>
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-black text-foreground truncate leading-tight mb-1">
                          {{ user?.name || t('common.labels.user') }}
                        </p>
                        <Badge 
                          variant="secondary" 
                          class="h-4 px-1.5 py-0 text-[9px] font-bold bg-primary text-primary-foreground border-none rounded-[3px] tracking-tight"
                        >
                          {{ currentRoleName }}
                        </Badge>
                      </div>
                    </div>
                    <p class="text-[11px] text-muted-foreground/80 mt-3 truncate px-1">
                      {{ user?.email || '' }}
                    </p>
                  </div>

                  <div class="p-1.5">
                    <DropdownMenuItem as-child>
                      <router-link
                        :to="{ name: 'profile' }"
                        class="flex items-center w-full cursor-pointer py-2"
                      >
                        <UserIcon class="w-4 h-4 mr-3 opacity-60" />
                        <span class="text-sm">{{ t('common.labels.myProfile') }}</span>
                      </router-link>
                    </DropdownMenuItem>
                                
                    <DropdownMenuItem
                      v-if="authStore.hasPermission('manage settings')"
                      as-child
                    >
                      <router-link
                        :to="{ name: 'settings' }"
                        class="flex items-center w-full cursor-pointer py-2"
                      >
                        <Settings class="w-4 h-4 mr-3 opacity-60" />
                        <span class="text-sm">{{ t('common.labels.settings') }}</span>
                      </router-link>
                    </DropdownMenuItem>
                  </div>
                              
                  <DropdownMenuSeparator />
                              
                  <!-- Language Selection -->
                  <div class="p-1.5">
                    <div class="px-2 py-1.5 text-[10px] font-bold text-muted-foreground/60 tracking-wider">
                      {{ t('common.labels.language') }}
                    </div>
                                
                    <DropdownMenuItem
                      v-for="lang in languages"
                      :key="lang.id"
                      class="flex items-center cursor-pointer rounded-lg py-2"
                      :class="{ 'bg-primary/5 text-primary font-bold': currentLanguage?.code === lang.code }"
                      @click="selectLanguage(lang)"
                    >
                      <span class="mr-3 text-base leading-none">{{ getLanguageFlag(lang) }}</span>
                      <span class="flex-1 text-sm">{{ lang.native_name }}</span>
                      <Check
                        v-if="currentLanguage?.code === lang.code"
                        class="ml-auto w-4 h-4"
                      />
                    </DropdownMenuItem>
                  </div>

                  <DropdownMenuSeparator />

                  <div class="p-1.5">
                    <DropdownMenuItem
                      class="text-destructive focus:bg-destructive/10 focus:text-destructive cursor-pointer rounded-lg py-2"
                      @click="handleLogout"
                    >
                      <LogOut class="w-4 h-4 mr-3" />
                      <span class="text-sm font-semibold">{{ t('common.navigation.menu.logout') }}</span>
                    </DropdownMenuItem>
                  </div>
                </DropdownMenuContent>
              </DropdownMenu>
            </TooltipTrigger>
            <TooltipContent
              side="bottom"
              align="end"
              class="bg-foreground text-background border-none px-3 py-1.5 rounded-lg shadow-xl"
            >
              <div class="text-xs font-bold">{{ user?.name }}</div>
              <div class="text-[10px] opacity-70">{{ currentRoleName }}</div>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
      </div>
    </div>

    <!-- Mobile Breadcrumb (below navbar, full width) -->
    <div class="lg:hidden border-t border-border px-6 py-2">
      <BreadcrumbTrail compact />
    </div>
  </header>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useLanguage, type Language } from '@/shared/composables/useLanguage';
import api from '@/engine/api/client';
import { getResponseList } from '@/shared/utils/responseParser';
import BreadcrumbTrail from '@/shared/components/BreadcrumbTrail.vue';
import DarkModeToggle from '@/shared/components/DarkModeToggle.vue';
import GlobalSearch from '@/shared/components/shell/GlobalSearch.vue';


import {
  Bell,
  Check,
  LogOut,
  Menu,
  Search,
  Settings,
  UserIcon,
} from 'lucide-vue-next';

import { 
    DropdownMenu, 
    DropdownMenuTrigger, 
    DropdownMenuContent, 
    DropdownMenuItem,
    DropdownMenuSeparator,
    Badge,
    Tooltip,
    TooltipTrigger,
    TooltipContent,
    TooltipProvider
} from '@/shared/components/ui';

import type { User } from '@/engine/types/auth';

interface Notification {
    id: string;
    title: string;
    message: string;
    read_at: string | null;
    created_at: string;
    [key: string]: unknown;
}

// Navigation for notification clicks
import { useRouter } from 'vue-router';
const router = useRouter();
const authStore = useAuthStore();
const { t } = useI18n();

import { ROLE_RANKS } from '@/modules/Core/System/stores/auth';

const currentRoleName = computed(() => {
    const roles = authStore.user?.roles;
    if (!roles || roles.length === 0) return t('common.auth.roles.guest');
    
    const sortedRoles = [...roles].sort((a, b) => {
        const rankA = ROLE_RANKS[a.name] || 0;
        const rankB = ROLE_RANKS[b.name] || 0;
        return rankB - rankA;
    });
    
    const topRole = sortedRoles[0];
    const label = (topRole as any)?.label || topRole?.name || '';
    return label.replace(/-/g, ' ');
});

// Language Composable
const {
    currentLanguage,
    languages,
    setLanguage,
    getLanguageFlag,
    initializeLanguage,
} = useLanguage();

const props = withDefaults(defineProps<{
    isAuthenticated?: boolean;
    user?: User | null;
}>(), {
    isAuthenticated: false,
    user: null,
});

const emit = defineEmits<{
    (e: 'toggle-sidebar'): void;
    (e: 'logout'): void;
}>();

const notifications = ref<Notification[]>([]);
const loadingNotifications = ref(false);
const notificationInterval = ref<ReturnType<typeof setInterval> | null>(null);
const isFetchingNotifications = ref(false);
const hasLoadedNotificationsOnce = ref(false);
const avatarError = ref(false);
const showGlobalSearch = ref(false);

const selectLanguage = async (lang: Language) => {
    await setLanguage(lang.code);
};

const unreadNotificationsCount = computed(() => {
    return notifications.value.filter(n => !n.read_at).length;
});

const recentNotifications = computed(() => {
    return notifications.value.slice(0, 5);
});

const userAvatar = computed(() => {
    if (!props.user?.avatar || avatarError.value) return null;

    const formatUrl = (path: unknown) => {
        if (!path || typeof path !== 'string') return null;
        if (path.startsWith('http') || path.startsWith('/storage/')) return path;
        return `/storage/${path.replace(/^\//, '')}`;
    };

    const avatar = props.user.avatar;
    if (typeof avatar === 'string') return formatUrl(avatar);
    if (typeof avatar === 'object') {
        return formatUrl(avatar.url || avatar.path);
    }
    return null;
});

const userInitial = computed(() => {
    if (!props.user?.name) return 'U';
    return props.user.name.charAt(0).toUpperCase();
});

const stopNotificationPolling = () => {
    if (!notificationInterval.value) return;
    clearInterval(notificationInterval.value);
    notificationInterval.value = null;
};

const startNotificationPolling = () => {
    stopNotificationPolling();
    notificationInterval.value = setInterval(() => {
        void fetchNotifications();
    }, 15000);
};

const fetchNotifications = async () => {
    if (!props.isAuthenticated || (window as unknown as { __isSessionTerminated?: boolean }).__isSessionTerminated) return;
    if (isFetchingNotifications.value) return;
    
    isFetchingNotifications.value = true;
    if (!hasLoadedNotificationsOnce.value) {
        loadingNotifications.value = true;
    }
    try {
        const response = await api.get('/manage/notifications?limit=5');
        notifications.value = getResponseList<Notification>(response.data);
        hasLoadedNotificationsOnce.value = true;
    } catch (error) {
        logger.error('Failed to fetch notifications:', error);
        notifications.value = [];
    } finally {
        isFetchingNotifications.value = false;
        loadingNotifications.value = false;
    }
};

const handleNotificationClick = async (notification: Notification) => {
    if (!notification.read_at) {
        try {
            await api.put(`/manage/notifications/${notification.id}/read`);
            notification.read_at = new Date().toISOString();
        } catch (error) {
            logger.error('Failed to mark notification as read:', error);
        }
    }
    // Navigate to the notification target
    const url = notification.action_url;
    if (typeof url === 'string' && url !== '') {
        if (url.startsWith('/')) {
            void router.push(url);
        } else {
            // Treat as a named route
            void router.push({ name: url });
        }
    }
};

const formatNotificationDate = (date: string) => {
    if (!date) return '';
    const now = new Date();
    const notifDate = new Date(date);
    const diffMs = now.getTime() - notifDate.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return t('common.messages.time.justNow');
    if (diffMins < 60) return t('common.messages.time.ago', { time: `${diffMins}m` });
    if (diffHours < 24) return t('common.messages.time.ago', { time: `${diffHours}h` });
    if (diffDays < 7) return t('common.messages.time.ago', { time: `${diffDays}d` });
    return notifDate.toLocaleDateString();
};

const handleLogout = () => {
    emit('logout');
};

watch(() => props.isAuthenticated, (isAuth) => {
    if (isAuth) {
        void fetchNotifications();
        startNotificationPolling();
    } else {
        stopNotificationPolling();
        hasLoadedNotificationsOnce.value = false;
        isFetchingNotifications.value = false;
        loadingNotifications.value = false;
        notifications.value = [];
    }
});

onMounted(() => {
    const warmChrome = () => {
        if (props.isAuthenticated) {
            void fetchNotifications();
            startNotificationPolling();
        }
        void initializeLanguage();
    };
    if (typeof window !== 'undefined' && 'requestIdleCallback' in window) {
        window.requestIdleCallback(warmChrome, { timeout: 3000 });
    } else {
        setTimeout(warmChrome, 100);
    }
    window.addEventListener('notification:sent', fetchNotifications);
    window.addEventListener('notification:updated', fetchNotifications);
});

onUnmounted(() => {
    stopNotificationPolling();
    window.removeEventListener('notification:sent', fetchNotifications);
    window.removeEventListener('notification:updated', fetchNotifications);
});
</script>

