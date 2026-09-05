<template>
  <Card class="quick-actions h-full border-border/40 bg-card">
    <CardHeader class="pb-3">
      <CardTitle class="text-xl font-bold flex items-center gap-2">
        <Zap class="w-5 h-5 text-warning fill-warning" />
        {{ $t('system.dashboard.widgets.quickActions.title') }}
      </CardTitle>
    </CardHeader>
    
    <CardContent>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
        <!-- Create Post -->
        <Button
          v-if="authStore.hasPermission('create content')"
          variant="ghost"
          class="h-auto flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group relative overflow-hidden whitespace-normal"
          :disabled="loading"
          @click="handleAction('create-post')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-primary/10 text-primary group-hover:scale-110">
            <FileEdit class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('system.dashboard.widgets.quickActions.createPost') }}
          </span>
        </Button>
 
        <!-- Create Page -->
        <Button
          v-if="authStore.hasPermission('create content')"
          variant="ghost"
          class="h-auto flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group relative overflow-hidden whitespace-normal"
          :disabled="loading"
          @click="handleAction('create-page')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-info/10 text-info group-hover:scale-110">
            <PlusSquare class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('system.dashboard.widgets.quickActions.createPage') }}
          </span>
        </Button>
 
        <!-- Upload Media -->
        <Button
          v-if="authStore.hasPermission('upload media') || authStore.hasPermission('create media')"
          variant="ghost"
          class="h-auto flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group relative overflow-hidden whitespace-normal"
          :disabled="loading"
          @click="handleAction('upload-media')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-success/10 text-success group-hover:scale-110">
            <Upload class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('system.dashboard.widgets.quickActions.uploadMedia') }}
          </span>
        </Button>
 
        <!-- Create Category -->
        <Button
          v-if="authStore.hasPermission('create categories')"
          variant="ghost"
          class="h-auto flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group relative overflow-hidden whitespace-normal"
          :disabled="loading"
          @click="handleAction('create-category')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-warning/10 text-warning group-hover:scale-110">
            <Hash class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('system.dashboard.widgets.quickActions.createCategory') }}
          </span>
        </Button>
 
        <!-- Create Tag -->
        <Button
          v-if="authStore.hasPermission('create tags')"
          variant="ghost"
          class="h-auto flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group relative overflow-hidden whitespace-normal"
          :disabled="loading"
          @click="handleAction('create-tag')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-primary/10 text-primary group-hover:scale-110">
            <Tag class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('system.dashboard.widgets.quickActions.createTag') }}
          </span>
        </Button>
 
        <!-- Manage Users -->
        <Button
          v-if="authStore.hasPermission('view users')"
          variant="ghost"
          class="h-auto flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group relative overflow-hidden whitespace-normal"
          :disabled="loading"
          @click="handleAction('manage-users')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-primary/10 text-primary group-hover:scale-110">
            <UserCog class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('system.dashboard.widgets.quickActions.manageUsers') }}
          </span>
        </Button>
 
        <!-- View Comments -->
        <Button
          v-if="authStore.hasPermission('view comments')"
          variant="ghost"
          class="h-auto flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group relative overflow-hidden whitespace-normal"
          :disabled="loading"
          @click="handleAction('view-comments')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-warning/10 text-warning group-hover:scale-110">
            <MessageSquare class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('system.dashboard.widgets.quickActions.viewComments') }}
          </span>
        </Button>
 
        <!-- Settings -->
        <Button
          v-if="authStore.hasPermission('view settings')"
          variant="ghost"
          class="h-auto flex flex-col items-center justify-center p-3 rounded-lg hover:bg-slate-500/5 group relative overflow-hidden whitespace-normal"
          :disabled="loading"
          @click="handleAction('settings')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-muted text-muted-foreground group-hover:scale-110">
            <Settings class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('system.dashboard.widgets.quickActions.settings') }}
          </span>
        </Button>

        <!-- Command Runner -->
        <Button
          v-if="authStore.hasPermission('manage system')"
          variant="ghost"
          class="h-auto flex flex-col items-center justify-center p-3 rounded-lg hover:bg-primary/5 group relative overflow-hidden whitespace-normal"
          :disabled="loading"
          @click="handleAction('command-runner')"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-warning/10 text-warning group-hover:scale-110">
            <Terminal class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('system.dashboard.widgets.quickActions.commandRunner') }}
          </span>
        </Button>
      </div>
      
      <!-- Recent Actions -->
      <div
        v-if="showRecent && recentActions.length > 0"
        class="mt-6 pt-4 border-t border-border/40"
      >
        <h4 class="text-xs font-bold text-muted-foreground mb-3">
          {{ $t('system.dashboard.widgets.quickActions.recentActions') }}
        </h4>
        <div class="space-y-1">
          <div
            v-for="action in recentActions.slice(0, 3)"
            :key="action.id"
            class="flex items-center p-2 rounded-lg text-sm text-muted-foreground hover:text-foreground hover:bg-muted/50 cursor-pointer group"
            @click="repeatAction(action)"
          >
            <Clock class="w-4 h-4 mr-2 opacity-50 group-hover:opacity-100" />
            <span class="flex-1 truncate font-medium">{{ getActionLabel(action.action) }}</span>
            <span class="text-[10px] tabular-nums opacity-50">{{ formatTime(action.timestamp) }}</span>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, type RouteLocationRaw } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { 
    Button,
    Card, 
    CardHeader, 
    CardTitle, 
    CardContent 
} from '@/shared/components/ui';
import {
  Clock,
  FileEdit,
  Hash,
  MessageSquare,
  PlusSquare,
  Settings,
  Tag,
  Terminal,
  Upload,
  UserCog,
  Zap,
} from 'lucide-vue-next';

interface RecentAction {
    id: string;
    action: string;
    timestamp: string;
}

const { t } = useI18n();
const router = useRouter();
const authStore = useAuthStore();

const props = withDefaults(defineProps<{
  showRecent?: boolean;
}>(), {
  showRecent: true,
});

const loading = ref(false);
const recentActions = ref<RecentAction[]>([]);

const actionRoutes: Record<string, RouteLocationRaw> = {
  'create-post': { name: 'contents.create', query: { type: 'post' } },
  'create-page': { name: 'contents.create', query: { type: 'page' } },
  'upload-media': { name: 'media' },
  'create-category': { name: 'contents.index', query: { tab: 'categories' } },
  'create-tag': { name: 'tags' },
  'manage-users': { name: 'users.index' },
  'view-comments': { name: 'comments.index' },
  'settings': { name: 'settings' },
  'command-runner': { name: 'scheduled-tasks', query: { action: 'run_command' } },
};

const actionLabels: Record<string, string> = {
  'create-post': 'system.dashboard.widgets.quickActions.createPost',
  'create-page': 'system.dashboard.widgets.quickActions.createPage',
  'upload-media': 'system.dashboard.widgets.quickActions.uploadMedia',
  'create-category': 'system.dashboard.widgets.quickActions.createCategory',
  'create-tag': 'system.dashboard.widgets.quickActions.createTag',
  'manage-users': 'system.dashboard.widgets.quickActions.manageUsers',
  'view-comments': 'system.dashboard.widgets.quickActions.viewComments',
  'settings': 'system.dashboard.widgets.quickActions.settings',
  'command-runner': 'system.dashboard.widgets.quickActions.commandRunner',
};

const getActionLabel = (action: string) => {
    const key = actionLabels[action];
    return key ? t(key) : action;
};

const saveRecentAction = (action: string) => {
  const newAction: RecentAction = {
    id: String(Date.now()),
    action,
    timestamp: new Date().toISOString(),
  };
  
  const stored = localStorage.getItem('quickActions_recent');
  const actions: RecentAction[] = stored ? JSON.parse(stored) : [];
  const filtered = actions.filter(a => a.action !== action);
  filtered.unshift(newAction);
  const limited = filtered.slice(0, 10);
  localStorage.setItem('quickActions_recent', JSON.stringify(limited));
  recentActions.value = limited;
};

const handleAction = (action: string) => {
  if (loading.value) return;
  
  loading.value = true;
  saveRecentAction(action);
  const route = actionRoutes[action];
  if (route) {
    router.push(route);
  }
  
  setTimeout(() => {
    loading.value = false;
  }, 500);
};

const repeatAction = (action: RecentAction) => {
  handleAction(action.action);
};

const formatTime = (timestamp: string) => {
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now.getTime() - date.getTime();
  
  const minutes = Math.floor(diff / 60000);
  const hours = Math.floor(diff / 3600000);
  const days = Math.floor(diff / 86400000);
  
  if (minutes < 1) return t('system.dashboard.widgets.recentActivity.time.justNow');
  if (minutes < 60) return t('system.dashboard.widgets.recentActivity.time.ago', { time: `${minutes}m` });
  if (hours < 24) return t('system.dashboard.widgets.recentActivity.time.ago', { time: `${hours}h` });
  return t('system.dashboard.widgets.recentActivity.time.ago', { time: `${days}d` });
};

const loadRecentActions = () => {
  const stored = localStorage.getItem('quickActions_recent');
  if (stored) {
    try {
        recentActions.value = JSON.parse(stored);
    } catch {
        recentActions.value = [];
    }
  }
};

onMounted(() => {
  if (props.showRecent) {
    loadRecentActions();
  }
});
</script>

