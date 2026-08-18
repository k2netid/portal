<template>
  <Dialog
    :open="open"
    @update:open="setOpen"
  >
    <DialogContent class="console-dialog-lg p-0 gap-0 overflow-hidden bg-background text-foreground rounded-xl border border-border/50 shadow-2xl">
      <!-- Search Input Header -->
      <div
        class="flex items-center border-b border-border/40 pl-4 pr-12 py-2"
        cmdk-input-wrapper
      >
        <Search class="ml-1 h-5 w-5 shrink-0 opacity-50" />
        <input
          ref="inputRef"
          v-model="searchQuery"
          data-slot="search-input"
          class="flex h-12 w-full bg-transparent px-3 text-base outline-none border-none focus:ring-0 placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
          :placeholder="t('common.actions.search') + '...'"
          autocomplete="off"
          autocorrect="off"
          spellcheck="false" 
          @input="handleSearch" 
          @keydown="handleKeydown"
        >
        <div class="flex items-center gap-1">
          <span
            v-if="loading"
            class="h-4 w-4 animate-spin rounded-full border-2 border-primary border-t-transparent"
          />
          <kbd class="pointer-events-none inline-flex h-5 select-none items-center gap-1 rounded-md border border-border/40 bg-muted px-1.5 font-mono text-[10px] font-medium text-muted-foreground opacity-100">
            <span class="text-xs uppercase">Esc</span>
          </kbd>
        </div>
      </div>

      <!-- Results List -->
      <div 
        id="cmdk-list" 
        ref="listRef" 
        class="max-h-[60vh] overflow-y-auto overflow-x-hidden py-2" 
        role="listbox"
      >
        <!-- Loading State (Initial) -->
        <div
          v-if="loading && !results.length && searchQuery"
          class="py-6 text-center text-sm text-muted-foreground"
        >
          {{ t('common.messages.loading.searching') }}...
        </div>

        <!-- Empty State -->
        <div
          v-if="!loading && searchQuery && results.length === 0 && matchingStaticActions.length === 0"
          class="py-6 text-center text-sm text-muted-foreground"
        >
          {{ t('common.messages.empty.search', { query: searchQuery }) }}
        </div>

        <!-- Default/Static Actions (When Empty) -->
        <template v-if="!searchQuery && !results.length">
          <div class="px-2">
            <div class="px-2 py-1.5 text-xs font-semibold text-muted-foreground">
              {{ t('common.labels.quickActions') }}
            </div>
            <!-- Static Navigation Items -->
            <div 
              v-for="(item, index) in staticActions" 
              :key="'static-' + index"
              :class="[
                'relative flex cursor-default select-none items-center rounded-md px-3 py-2 text-sm outline-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
                selectedIndex === index ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-foreground' : 'text-foreground/70 hover:bg-muted/50 hover:text-foreground'
              ]"
              @click="handleSelect(item)"
              @mousemove="selectedIndex = index"
            >
              <component
                :is="item.icon"
                class="mr-2 h-4 w-4"
              />
              <span>{{ item.title }}</span>
              <span
                v-if="item.shortcut"
                class="ml-auto text-xs text-muted-foreground"
              >
                {{ item.shortcut }}
              </span>
            </div>
          </div>
        </template>

        <!-- Loose Match Warning -->
        <div
          v-if="isLoose && results.length > 0"
          class="px-4 py-2 text-xs text-amber-500 bg-amber-500/10 border-y border-amber-500/20 mb-2"
        >
          No exact match. Showing similar results.
        </div>

        <!-- Suggestions & Search History -->
        <div
          v-if="suggestions.length > 0 && results.length === 0"
          class="px-2"
        >
          <div class="flex items-center justify-between px-2 py-2">
            <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
              {{ hasHistorySuggestions ? 'Recent Searches' : 'Suggestions' }}
            </span>
            <button
              v-if="hasHistorySuggestions"
              class="text-xs text-muted-foreground hover:text-destructive transition-colors font-medium"
              @click="clearSearchHistory"
            >
              Clear All
            </button>
          </div>
          <div class="space-y-0.5">
            <div 
              v-for="(suggestion, idx) in suggestions" 
              :key="'sug-' + idx"
              class="group flex items-center justify-between px-3 py-2 text-sm cursor-pointer hover:bg-accent hover:text-accent-foreground rounded-md text-foreground transition-all duration-150"
              @click="applySuggestion(suggestion.text)"
            >
              <div class="flex items-center min-w-0">
                <Clock
                  v-if="suggestion.type === 'history'"
                  class="mr-2.5 h-4 w-4 text-muted-foreground shrink-0"
                />
                <Sparkles
                  v-else
                  class="mr-2.5 h-4 w-4 text-amber-500 shrink-0"
                />
                <span class="font-medium truncate" :class="{ 'text-muted-foreground': suggestion.type === 'history' }">
                  {{ safeTranslate(suggestion.text) }}
                </span>
              </div>
              
              <button
                v-if="suggestion.type === 'history'"
                class="opacity-0 group-hover:opacity-100 p-1 hover:bg-muted-foreground/10 rounded text-muted-foreground hover:text-destructive transition-all shrink-0"
                @click.stop="deleteHistoryItem(suggestion)"
              >
                <X class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
        </div>

        <!-- Dynamic Results -->
        <template
          v-for="(group, groupName) in groupedResults"
          :key="groupName"
        >
          <div class="px-2 pt-2 pb-1 text-xs font-semibold text-muted-foreground">
            {{ groupLabel(groupName as string) }}
          </div>
          <div class="px-2">
            <div
              v-for="(item, index) in group"
              :key="item.type + '-' + (item.id || index)"
              :class="[
                'relative flex cursor-default select-none items-center rounded-md px-3 py-2 text-sm outline-none',
                selectedIndex === (groupOffset(groupName as string) + index) ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-foreground' : 'text-foreground/70 hover:bg-muted/50 hover:text-foreground'
              ]"
              @click="handleSelect(item)"
              @mousemove="updateSelectedIndex(item)"
            >
              <FileText
                v-if="item.type === 'post' || item.type === 'page'"
                class="mr-2 h-4 w-4"
              />
              <Folder
                v-if="item.type === 'category'"
                class="mr-2 h-4 w-4"
              />
              <Tag
                v-if="item.type === 'tag'"
                class="mr-2 h-4 w-4"
              />
              <User
                v-if="item.type === 'user'"
                class="mr-2 h-4 w-4"
              />
                    
              <div class="flex flex-col flex-1 min-w-0">
                <span class="truncate">{{ safeTranslate(item.title) }}</span>
                <span
                  v-if="item.description"
                  class="text-xs text-muted-foreground truncate"
                >{{ item.description }}</span>
              </div>
            </div>
          </div>
        </template>
      </div>
      
      <!-- Footer/Status Bar -->
      <div class="flex items-center border-t p-2 text-xs text-muted-foreground bg-muted/20">
        <span class="mr-2">ProTip:</span>
        <span class="flex items-center mr-2"><kbd class="mr-1 rounded bg-muted-foreground/10 px-1 font-sans">↑</kbd><kbd class="rounded bg-muted-foreground/10 px-1 font-sans">↓</kbd> to navigate</span>
        <span class="flex items-center mr-2"><kbd class="mr-1 rounded bg-muted-foreground/10 px-1 font-sans">↵</kbd> to select</span>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, watch, onMounted, onUnmounted, type Component } from 'vue';
import { isNavigationFailure, useRouter, type RouteLocationRaw } from 'vue-router';
import { consoleNamedRoute } from '@/shared/utils/consoleRoute';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useConsoleContextStore } from '@/engine/stores/consoleContext';
import { SearchService } from '@/modules/Intelligence/Search/services/searchService';
import { Dialog, DialogContent } from '@/shared/components/ui';
import {
  Activity,
  Calendar,
  Clock,
  Cpu,
  Database,
  FileText,
  Folder,
  Globe,
  HardDrive,
  Home,
  Layers,
  ListTodo,
  Mail,
  MessageSquare,
  Monitor,
  PlusCircle,
  Search,
  Settings,
  Sparkles,
  Tag,
  User,
  UserCircle,
  Webhook,
  X,
} from 'lucide-vue-next';
interface SearchItem {
    id?: string;
    type: string;
    title: string;
    description?: string;
    url?: string;
    route?: RouteLocationRaw;
    searchable_id?: string;
    searchable_type?: string;
    score?: number;
    keywords?: string;
    icon?: Component;
    shortcut?: string;
    group?: string;
}

interface SearchSuggestion {
    text: string;
    type?: string;
    url?: string | null;
    id?: string;
}

// Props & Emitters
const props = defineProps<{
    isOpen?: boolean;
}>();
const emit = defineEmits<{
    (e: 'update:isOpen', value: boolean): void;
    (e: 'close'): void;
}>();

// Utils
const router = useRouter();
const { t, te } = useI18n();
const authStore = useAuthStore();
const consoleStore = useConsoleContextStore();

const safeTranslate = (key: string): string => {
    if (!key) return '';
    // Translation keys usually contain only alphanumeric characters, dots, underscores, and dashes
    // and no spaces. This prevents Vue I18n's path compiler from throwing a syntax error on arbitrary strings.
    if (typeof key === 'string' && /^[a-zA-Z0-9._-]+$/.test(key)) {
        try {
            return te(key) ? t(key) : key;
        } catch {
            return key;
        }
    }
    return key;
};

// State
const open = ref(false);
const searchQuery = ref('');
const results = ref<SearchItem[]>([]);
const suggestions = ref<SearchSuggestion[]>([]);
const isLoose = ref(false);
const loading = ref(false);
const selectedIndex = ref(0);
const searchTimeout = ref<ReturnType<typeof setTimeout> | null>(null);
const inputRef = ref<HTMLInputElement | null>(null);
const listRef = ref<HTMLDivElement | null>(null);

// Auto-focus input when search opens
watch(open, (isOpen) => {
    if (isOpen) {
        setTimeout(() => {
            inputRef.value?.focus();
        }, 100);
    }
});

const hasHistorySuggestions = computed(() => {
    return suggestions.value.some(sug => sug.type === 'history');
});

const deleteHistoryItem = async (suggestion: SearchSuggestion) => {
    try {
        await SearchService.deleteQuery(suggestion.text);
        suggestions.value = suggestions.value.filter(sug => sug.text !== suggestion.text);
    } catch (err) {
        logger.error('Failed to delete search history item:', err);
    }
};

const clearSearchHistory = async () => {
    try {
        await SearchService.clearQueries();
        suggestions.value = suggestions.value.filter(sug => sug.type !== 'history');
    } catch (err) {
        logger.error('Failed to clear search history:', err);
    }
};

const applySuggestion = (text: string) => {
    searchQuery.value = text;
    handleSearch();
};

// Static Actions (Nav Items) - Comprehensive list of admin shortcuts
const staticActions = computed<SearchItem[]>(() => [
    // Core Navigation
    { title: t('common.navigation.menu.dashboard'), icon: Home, route: { name: 'dashboard' }, type: 'action', keywords: 'home overview dashboard' },
    { title: t('publishing.content.list.createNew'), icon: PlusCircle, route: { name: 'contents.create' }, type: 'action', keywords: 'new post article write', permission: 'manage content' },
    { title: t('common.labels.myProfile'), icon: UserCircle, route: { name: 'profile' }, type: 'action', keywords: 'account me user profile' },
    { title: t('system.navigation.menu.settings'), icon: Settings, route: { name: 'settings' }, type: 'action', keywords: 'system settings general config preferences options', permission: 'manage settings' },
    
    // Content & Media Management
    { title: t('sharedConsole.navigation.menu.studio'), icon: FileText, route: { name: 'contents.index' }, type: 'action', keywords: 'posts articles pages blogs content', permission: 'view content' },
    { title: t('sharedConsole.navigation.menu.mediaLibrary'), icon: HardDrive, route: { name: 'media' }, type: 'action', keywords: 'media library images files upload gallery filemanager', permission: 'manage media' },
    { title: t('sharedConsole.navigation.menu.comments'), icon: MessageSquare, route: { name: 'comments.index' }, type: 'action', keywords: 'comments discussion feedback moderation', permission: 'view comments' },
    { title: t('sharedConsole.navigation.menu.forms'), icon: ListTodo, route: { name: 'forms' }, type: 'action', keywords: 'forms submissions contact questionnaires fields', permission: 'manage content' },
    { title: t('sharedConsole.navigation.menu.newsletter'), icon: Mail, route: { name: 'newsletter' }, type: 'action', keywords: 'newsletter subscribers emails campaigns broadcast', permission: 'manage content' },
    { title: t('sharedConsole.navigation.menu.emailTemplates'), icon: Mail, route: { name: 'email-templates' }, type: 'action', keywords: 'email templates notification mail', permission: 'manage settings' },
    { title: t('library.navigation.menu.customFields'), icon: Layers, route: { name: 'custom-fields' }, type: 'action', keywords: 'custom fields metadata models fieldgroups', permission: 'manage content' },
    { title: t('library.navigation.menu.tags'), icon: Tag, route: { name: 'tags' }, type: 'action', keywords: 'labels keywords tags', permission: 'manage content' },
    
    // Design & Config
    { title: t('sharedConsole.navigation.menu.themes'), icon: Settings, route: { name: 'themes' }, type: 'action', keywords: 'theme customizer design config appearance styling', permission: 'manage themes' },
    { title: t('sharedConsole.navigation.menu.menus'), icon: Folder, route: { name: 'menus' }, type: 'action', keywords: 'menus navigation links headers footer', permission: 'manage settings' },
    { title: t('sharedConsole.navigation.menu.widgets'), icon: Folder, route: { name: 'widgets' }, type: 'action', keywords: 'widgets blocks dashboard layout', permission: 'manage settings' },
    
    // Users & Access
    { title: t('system.navigation.menu.users'), icon: User, route: { name: 'users.index' }, type: 'action', keywords: 'members accounts people users', permission: 'view users' },
    { title: t('system.navigation.menu.roles'), icon: Settings, route: { name: 'roles' }, type: 'action', keywords: 'permissions access rbac roles', permission: 'view roles' },
    
    // SEO & Analytics
    { title: t('sharedConsole.navigation.menu.seoTools'), icon: Search, route: { name: 'publishing.seo' }, type: 'action', keywords: 'seo search engine optimize meta robots sitemap', permission: 'manage settings' },
    { title: t('sharedConsole.navigation.menu.analytics'), icon: FileText, route: { name: 'analytics' }, type: 'action', keywords: 'stats visitors traffic analytics', permission: 'manage settings' },
    { title: t('sharedConsole.navigation.menu.redirects'), icon: FileText, route: { name: 'redirects' }, type: 'action', keywords: '301 302 url forward redirects', permission: 'manage settings' },
    
    // Infrastructure
    { title: t('system.navigation.menu.systemInfo'), icon: Monitor, route: { name: 'system' }, type: 'action', keywords: 'system info server environment specs specs check health', permission: 'view system', role: 'super', context: 'foundation' },
    { title: t('system.navigation.menu.systemNotifications'), icon: Settings, route: { name: 'system-notifications' }, type: 'action', keywords: 'notifications settings alerts system', permission: 'manage system' },
    { title: t('system.navigation.menu.backups'), icon: Database, route: { name: 'backups' }, type: 'action', keywords: 'backups database files restore export backup', permission: 'view backups', role: 'super', context: 'foundation' },
    { title: t('system.navigation.menu.redis'), icon: Cpu, route: { name: 'redis' }, type: 'action', keywords: 'redis cache system performance memory infrastructure', permission: 'manage settings', role: 'super', context: 'foundation' },
    { title: t('system.navigation.menu.scheduledTasks'), icon: Calendar, route: { name: 'scheduled-tasks' }, type: 'action', keywords: 'scheduled tasks cron background jobs schedules', permission: 'view scheduled tasks', role: 'super', context: 'foundation' },
    { title: t('system.navigation.menu.languages'), icon: Globe, route: { name: 'languages' }, type: 'action', keywords: 'languages translate locale localization', permission: 'view settings' },
    { title: t('infra.webhooks.title'), icon: Webhook, route: { name: 'webhooks' }, type: 'action', keywords: 'webhooks api integrations events webhook infrastructure', permission: 'manage webhooks', context: 'foundation' },
    
    // Monitoring & Journals
    { title: t('system.navigation.menu.journalDashboard'), icon: Activity, route: { name: 'journal-dashboard' }, type: 'action', keywords: 'monitoring dashboard logs summary journals', permission: 'view logs', role: 'super', context: 'foundation' },
    { title: t('system.navigation.menu.activityJournal'), icon: Activity, route: { name: 'activity-journal' }, type: 'action', keywords: 'activity journal logs audit history monitoring security events', permission: 'view activity logs', role: 'super', context: 'foundation' },
    { title: t('system.navigation.menu.securityJournal'), icon: Activity, route: { name: 'security-journal' }, type: 'action', keywords: 'security journal logs login history lockouts monitoring', permission: 'view security logs', role: 'super', context: 'foundation' },
    { title: t('system.navigation.menu.systemJournal'), icon: Activity, route: { name: 'system-journal' }, type: 'action', keywords: 'system journal backend logs errors warnings', permission: 'view system', role: 'super', context: 'foundation' },
    { title: t('system.navigation.menu.accessJournal'), icon: Activity, route: { name: 'access-journal' }, type: 'action', keywords: 'access journal session history logins visits', permission: 'view users', role: 'super', context: 'foundation' }
]);

// Computed
const filterActions = computed<SearchItem[]>(() => {
    if (!searchQuery.value || searchQuery.value.length < 2) return [];
    
    return [
        {
            id: 'filter-posts',
            type: 'action',
            title: t('common.actions.searchIn') + ' ' + t('common.labels.posts'),
            icon: FileText,
            route: { name: 'contents.index', query: { q: searchQuery.value } },
            group: 'filters'
        },
        {
            id: 'filter-users',
            type: 'action',
            title: t('common.actions.searchIn') + ' ' + t('common.labels.users'),
            icon: User,
            route: { name: 'users.index', query: { q: searchQuery.value } },
            group: 'filters'
        },
        {
            id: 'filter-categories',
            type: 'action',
            title: t('common.actions.searchIn') + ' ' + t('common.labels.categories'),
            icon: Folder,
            route: { name: 'categories.index', query: { q: searchQuery.value } },
            group: 'filters'
        },
        {
            id: 'filter-tags',
            type: 'action',
            title: t('common.actions.searchIn') + ' ' + t('common.labels.tags'),
            icon: Tag,
            route: { name: 'tags', query: { q: searchQuery.value } },
            group: 'filters'
        }
    ];
});

/**
 * Comprehensive Fuzzy Match Utility
 * Returns a score from 0 to 1 based on multiple matching strategies
 */
const fuzzyMatch = (query: string, target: string): number => {
    if (!query || !target) return 0;
    
    const q = query.toLowerCase().trim();
    const targetLower = target.toLowerCase().trim();
    
    // 1. Exact match (Score: 1.0)
    if (q === targetLower) return 1.0;
    
    // 2. Target starts with query (Score: 0.95)
    if (targetLower.startsWith(q)) return 0.95;
    
    // 3. Target contains query as substring (Score: 0.85)
    if (targetLower.includes(q)) return 0.85;
    
    // 4. Query matches start of any word in target (Score: 0.75)
    // e.g., "prof" matches "My Profile" because "Profile" starts with "prof"
    const words = targetLower.split(/\s+/);
    for (const word of words) {
        if (word.startsWith(q)) return 0.75;
    }
    
    // 5. All query characters appear in order in target (Score: 0.6)
    // e.g., "mprof" matches "My Profile" (m...p-r-o-f)
    let qIdx = 0;
    for (let i = 0; i < targetLower.length && qIdx < q.length; i++) {
        if (targetLower[i] === q[qIdx]) qIdx++;
    }
    if (qIdx === q.length) return 0.6;
    
    // 6. Bigram similarity (Dice Coefficient style)
    // Calculate how many character pairs match
    const getBigrams = (str: string) => {
        const bigrams = new Set<string>();
        for (let i = 0; i < str.length - 1; i++) {
            bigrams.add(str.substring(i, i + 2));
        }
        return bigrams;
    };
    
    const qBigrams = getBigrams(q);
    const tBigrams = getBigrams(targetLower);
    
    if (qBigrams.size === 0 || tBigrams.size === 0) return 0;
    
    let intersection = 0;
    for (const pair of qBigrams) {
        if (tBigrams.has(pair)) intersection++;
    }
    
    // Dice coefficient: 2 * |A ∩ B| / (|A| + |B|)
    const bigramScore = (2 * intersection) / (qBigrams.size + tBigrams.size);
    
    // Scale it down to max 0.5 since it's a weaker match
    return bigramScore * 0.5;
};

// Minimum score threshold for a match to be considered
const MATCH_THRESHOLD = 0.3;

const matchingStaticActions = computed<SearchItem[]>(() => {
    if (!searchQuery.value || searchQuery.value.length < 2) return [];
    
    const isGlobal = consoleStore.isSystem;
    
    // Score and filter all static actions against title AND keywords, checking role, permissions, and context
    return staticActions.value
        .filter(action => {
            // 1. Role check
            const role = (action as any).role;
            if (role && !authStore.isAtLeastRole(role)) return false;
            
            // 2. Permission check
            const permission = (action as any).permission;
            if (permission && !authStore.hasPermission(permission)) return false;
            
            // 3. organization context check
            const context = (action as any).context;
            if (context) {
                if (context === 'foundation' && !isGlobal) return false;
                if (context === 'unit' && isGlobal) return false;
            }
            return true;
        })
        .map(action => {
            const titleScore = fuzzyMatch(searchQuery.value, action.title);
            const keywordScore = action.keywords ? fuzzyMatch(searchQuery.value, action.keywords) : 0;
            return {
                ...action,
                score: Math.max(titleScore, keywordScore)
            };
        })
        .filter(action => action.score >= MATCH_THRESHOLD)
        .sort((a, b) => (b.score || 0) - (a.score || 0)); // Sort by score descending
});

const groupedResults = computed<Record<string, SearchItem[]>>(() => {
    const allItems = [...results.value];
    
    // Add filter actions if searching
    if (searchQuery.value && !loading.value) {
        // Prepend filter actions
        const grouped: Record<string, SearchItem[]> = {
            filters: filterActions.value,
            ...allItems.reduce((acc, item) => {
                const type = item.type || 'other';
                if (!acc[type]) acc[type] = [];
                acc[type].push(item);
                return acc;
            }, {} as Record<string, SearchItem[]>)
        };
        
        // Add matching static actions if any
        if (matchingStaticActions.value.length) {
            grouped.action = matchingStaticActions.value;
        }

        return grouped;
    }
    
    if (!results.value.length) return {};
    
    // Group by type
    return results.value.reduce((acc, item) => {
        const type = item.type || 'other';
        if (!acc[type]) acc[type] = [];
        acc[type].push(item);
        return acc;
    }, {} as Record<string, SearchItem[]>);
});

const flatResults = computed<SearchItem[]>(() => {
    // If searching, return results + filter actions + matching static actions
    if (searchQuery.value) {
        return [...filterActions.value, ...matchingStaticActions.value, ...results.value];
    }
    
    // If empty, return static actions
    return staticActions.value;
});

// Calculate offset for each group for linear indexing in flatResults
const groupOffset = (targetGroup: string): number => {
    let offset = 0;
    for (const [groupName, items] of Object.entries(groupedResults.value)) {
        if (groupName === targetGroup) return offset;
        offset += items.length;
    }
    return 0;
};

// Methods
const setOpen = (value: boolean) => {
    open.value = value;
    emit('update:isOpen', value);
    if (!value) {
        searchQuery.value = '';
        results.value = [];
        selectedIndex.value = 0;
    }
};

const handleSearch = () => {
    selectedIndex.value = 0;
    
    if (searchTimeout.value) clearTimeout(searchTimeout.value);
    
    if (!searchQuery.value || searchQuery.value.length < 2) {
        results.value = [];
        loading.value = false;
        return;
    }
    
    loading.value = true;
    searchTimeout.value = setTimeout(async () => {
        try {
            const response = await SearchService.search({
                q: searchQuery.value,
                limit: 10,
            });
            // API returns { data: { results: [...], total: ... } }
            const responseData = response.data;
            results.value = Array.isArray(responseData) ? responseData : (responseData.results || []);
            suggestions.value = responseData.suggestions || [];
            isLoose.value = responseData.is_loose || false;
        } catch (error) {
            logger.error('Search error:', error);
            results.value = [];
            suggestions.value = [];
        } finally {
            loading.value = false;
        }
    }, 300);
};

const navigateTo = async (to: RouteLocationRaw): Promise<void> => {
    try {
        await router.push(to);
    } catch (err) {
        if (isNavigationFailure(err)) {
            return;
        }
        logger.error('Navigation failed', err);
    }
};

const handleSelect = (item: SearchItem) => {
    if (!item) return;
    
    setOpen(false);
    
    // 1. Static Actions (Navigation)
    if (item.route) {
        const target = item.route;
        if (typeof target === 'object' && target !== null && 'name' in target && typeof target.name === 'string') {
            const params = 'params' in target && target.params && typeof target.params === 'object'
                ? Object.fromEntries(
                    Object.entries(target.params).map(([k, v]) => [k, String(v)]),
                )
                : {};
            void navigateTo(consoleNamedRoute(target.name, params));
        } else {
            void navigateTo(target);
        }
        return;
    } 

    // 1.5 System Administrative Crawled Pages (Direct routing bypass)
    if (item.searchable_type === 'SystemPage' && item.url) {
        if (item.url.startsWith('http')) {
             window.open(item.url, '_blank');
        } else {
             router.push(item.url);
        }
        return;
    }
    
    // 2. Internal Admin Resources (Prioritize over generic URL)
    // Use searchable_id if available, otherwise fallback to id (just in case)
    const resourceId = item.searchable_id || item.id;
    
    if ((item.type === 'post' || item.type === 'page' || item.type === 'content') && resourceId) {
         void navigateTo(consoleNamedRoute('contents.edit', { id: String(resourceId) }));
         return;
    }
    
    if (item.type === 'category' && resourceId) {
         void navigateTo(consoleNamedRoute('categories.index'));
         return;
    }
    
    if (item.type === 'tag' && resourceId) {
         void navigateTo(consoleNamedRoute('tags'));
         return;
    }

    if (item.type === 'user' && resourceId) {
         void navigateTo(consoleNamedRoute('users.edit', { id: String(resourceId) }));
         return;
    }
    
    // 3. Generic URL Fallback (Public links etc)
    if (item.url) {
        if (item.url.startsWith('http')) {
             window.open(item.url, '_blank');
        } else {
             void navigateTo(item.url);
        }
        return;
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    // Navigation Down
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        const max = flatResults.value.length - 1;
        selectedIndex.value = selectedIndex.value >= max ? 0 : selectedIndex.value + 1;
        scrollToSelected();
    }
    // Navigation Up
    else if (e.key === 'ArrowUp') {
        e.preventDefault();
        const max = flatResults.value.length - 1;
        selectedIndex.value = selectedIndex.value <= 0 ? max : selectedIndex.value - 1;
        scrollToSelected();
    }
    // Selection
    else if (e.key === 'Enter') {
        e.preventDefault();
        const item = flatResults.value[selectedIndex.value];
        if (item) handleSelect(item);
    }
    // Close
    else if (e.key === 'Escape') {
        setOpen(false);
    }
};

const scrollToSelected = () => {
    if (!listRef.value) return;
    const selectedElement = listRef.value.querySelector('.bg-primary\\/10');
    if (selectedElement) {
        selectedElement.scrollIntoView({ block: 'nearest' });
    }
};

const updateSelectedIndex = (item: SearchItem) => {
    const index = flatResults.value.indexOf(item);
    if (index !== -1) selectedIndex.value = index;
};

const groupLabel = (type: string): string => {
    const labels: Record<string, string> = {
        filters: t('common.actions.filter'),
        action: t('common.labels.quickActions'),
        post: t('common.labels.posts'),
        page: t('common.labels.pages'),
        category: t('common.labels.categories'),
        tag: t('common.labels.tags'),
        user: t('common.labels.users'),
        other: t('common.labels.other'),
    };
    return labels[type] || type;
};

// Keyboard Shortcuts (Cmd+K)
const onKeydownShortcut = (e: KeyboardEvent) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        setOpen(!open.value);
    }
};

onMounted(() => {
    window.addEventListener('keydown', onKeydownShortcut);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeydownShortcut);
});

// Watch external prop
watch(() => props.isOpen, (val) => {
    if (val !== undefined) {
        open.value = val;
    }
});
</script>

<style scoped>
/* Optional: Custom scrollbar styling if needed */
</style>
