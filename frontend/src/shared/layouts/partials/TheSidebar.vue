<template>
  <aside
    :class="[ 'fixed inset-y-0 left-0 z-50 bg-sidebar text-sidebar-foreground border-r border-border', sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0', sidebarMinimized ? 'w-[68px]' : 'w-64' ]"
  >
    <!-- Floating Toggle Button (Desktop) -->
    <button
      class="hidden lg:flex absolute -right-3 top-5 items-center justify-center h-6 w-6 rounded-full border border-border bg-sidebar text-muted-foreground hover:text-foreground shadow-sm z-[51]"
      :title="sidebarMinimized ? t('common.navigation.sidebar.expand') : t('common.navigation.sidebar.minimize')"
      :aria-label="sidebarMinimized ? t('common.navigation.sidebar.expand') : t('common.navigation.sidebar.minimize')"
      @click="$emit('toggle-minimize')"
    >
      <component
        :is="getIcon('chevron-left')"
        v-if="!sidebarMinimized"
      />
      <component
        :is="getIcon('chevron-right')"
        v-else
      />
    </button>

    <div class="flex flex-col h-full">
      <!-- Logo -->
      <div class="flex items-center justify-between h-16 px-4 border-b border-border">
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger as-child>
              <a
                href="/"
                target="_blank"
                rel="noopener noreferrer"
                class="block hover:opacity-80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring rounded-md"
              >
                <TheLogo :minimized="sidebarMinimized" />
              </a>
            </TooltipTrigger>
            <TooltipContent
              side="bottom"
              :side-offset="10"
            >
              {{ getVisitTooltip }}
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
        <div class="flex items-center gap-2">
          <!-- Mobile Close Button -->
          <button
            class="lg:hidden text-muted-foreground hover:text-accent-foreground"
            :aria-label="t('common.actions.close')"
            @click="$emit('close')"
          >
            <component :is="getIcon('x')" />
          </button>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
        <!-- Dashboard (standalone) -->
        <router-link
          :to="dashboardLink"
          class="flex items-center px-3 py-2 text-sm font-medium rounded-xl group transition-colors"
          :class="[ isDashboardActive ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' ]"
          :title="sidebarMinimized ? dashboardLabel : ''"
          @mouseenter="prefetchRoute(router, dashboardLink)"
          @click="$emit('close')"
        >
          <component
            :is="getIcon('dashboard')"
            class="w-5 h-5 flex-shrink-0"
          />
          <span
            v-if="!sidebarMinimized"
            class="ml-3 truncate font-semibold"
          >
            {{ dashboardLabel }}
          </span>
        </router-link>

        <!-- EXPANDED MODE: Flat & Parallel Accordion Groups -->
        <template v-if="!sidebarMinimized">
          <template
            v-for="item in filteredNavigation"
            :key="navItemKey(item)"
          >
            <!-- Divider -->
            <div
              v-if="item.type === 'divider'"
              class="py-2 px-3 flex items-center gap-2"
            >
              <div class="h-px bg-border flex-1" />
              <span v-if="getNavigationLabel(item)" class="text-[10px] font-bold text-muted-foreground/40 tracking-wider whitespace-nowrap">{{ getNavigationLabel(item) }}</span>
              <div class="h-px bg-border flex-1" />
            </div>

            <!-- Accordion Group with Sub-Items -->
            <div
              v-else-if="item.children && item.children.length > 0"
              class="space-y-0.5"
            >
              <button 
                class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-xl group transition-colors"
                :class="[ isMenuGroupActive(item) ? 'text-foreground hover:bg-accent' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' ]"
                @click="toggleGroup(navItemKey(item))"
              >
                <div class="flex items-center gap-2.5">
                  <component
                    :is="getIcon(item.icon || '')"
                    class="w-4 h-4 flex-shrink-0"
                  />
                  <span class="truncate font-medium">{{ getNavigationLabel(item) }}</span>
                </div>
                <component 
                  :is="getIcon('chevron-down')" 
                  :class="{ 'rotate-180': expandedGroups[navItemKey(item)] }"
                  class="w-3.5 h-3.5 transition-transform duration-200"
                />
              </button>
                                  
              <div 
                v-show="expandedGroups[navItemKey(item)]"
                class="mt-0.5 space-y-0.5"
              >
                <template
                  v-for="subItem in item.children"
                  :key="subItem.name || subItem.label"
                >
                  <div
                    v-if="subItem.type === 'divider'"
                    class="py-1.5 px-3 pl-9 flex items-center gap-2"
                  >
                    <div class="h-px bg-border flex-1" />
                    <span v-if="getNavigationLabel(subItem)" class="text-[9px] uppercase font-bold text-muted-foreground/40 tracking-widest whitespace-nowrap">{{ getNavigationLabel(subItem) }}</span>
                    <div class="h-px bg-border flex-1" />
                  </div>
                  <router-link
                    v-else-if="subItem.resolvedTo"
                    :to="subItem.resolvedTo"
                    class="flex items-center px-3 py-1.5 text-xs font-medium rounded-xl group pl-9 transition-colors"
                    :class="[ isChildActive(subItem) ? 'bg-primary/10 text-primary font-semibold' : 'text-muted-foreground/80 hover:bg-accent hover:text-accent-foreground' ]"
                    @mouseenter="prefetchNavTarget(subItem)"
                    @click="$emit('close')"
                  >
                    <component
                      :is="getIcon(subItem.icon || subItem.name || '')"
                      class="w-3.5 h-3.5 flex-shrink-0 mr-2"
                    />
                    <span class="truncate">{{ getNavigationLabel(subItem) }}</span>
                  </router-link>
                </template>
              </div>
            </div>

            <!-- Standalone Single Link -->
            <router-link
              v-else-if="item.resolvedTo"
              :to="item.resolvedTo"
              class="flex items-center px-3 py-2 text-sm font-medium rounded-xl group transition-colors"
              :class="[ isChildActive(item) ? 'bg-primary/10 text-primary font-semibold' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' ]"
              @mouseenter="prefetchNavTarget(item)"
              @click="$emit('close')"
            >
              <component
                :is="getIcon(item.icon || item.name || '')"
                class="w-4 h-4 flex-shrink-0 mr-2.5"
              />
              <span class="truncate font-medium">{{ getNavigationLabel(item) }}</span>
            </router-link>
          </template>
        </template>

        <!-- MINIMIZED MODE: Icon List with Dropdown Popover -->
        <template v-else>
          <template
            v-for="item in filteredNavigation"
            :key="navItemKey(item)"
          >
            <!-- Dropdown Menu for Group with Children -->
            <div
              v-if="item.children && item.children.length > 0"
              class="flex justify-center p-0.5"
            >
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <button
                    class="w-full flex items-center justify-center p-2.5 rounded-xl cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring transition-colors"
                    :class="[ isMenuGroupActive(item) ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' ]"
                    :title="getNavigationLabel(item)"
                  >
                    <component
                      :is="getIcon(item.icon || '')"
                      class="w-5 h-5"
                    />
                  </button>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                  side="right"
                  :side-offset="12"
                  class="w-56 p-0 overflow-hidden"
                >
                  <!-- Group Header -->
                  <div class="px-3 py-2 text-xs font-semibold text-muted-foreground tracking-wide border-b border-border bg-muted/30">
                    {{ getNavigationLabel(item) }}
                  </div>
                                      
                  <div class="max-h-[80vh] overflow-y-auto py-1">
                    <template
                      v-for="subItem in item.children"
                      :key="subItem.name || subItem.label"
                    >
                      <div
                        v-if="subItem.type === 'divider'"
                        class="py-1 px-3 flex items-center gap-2 pointer-events-none"
                      >
                        <div class="h-px bg-border flex-1" />
                        <span v-if="getNavigationLabel(subItem)" class="text-[9px] uppercase font-bold text-muted-foreground/30 tracking-widest whitespace-nowrap">{{ getNavigationLabel(subItem) }}</span>
                        <div class="h-px bg-border flex-1" />
                      </div>
                      <DropdownMenuItem
                        v-else-if="subItem.resolvedTo"
                        as-child
                      >
                        <router-link
                          :to="subItem.resolvedTo"
                          class="flex items-center px-3 py-2 text-xs font-medium cursor-pointer"
                          :class="[ isChildActive(subItem) ? 'text-primary bg-primary/10 font-semibold' : 'text-muted-foreground' ]"
                          @click="$emit('close')"
                        >
                          <component
                            :is="getIcon(subItem.icon || subItem.name || '')"
                            class="w-3.5 h-3.5 flex-shrink-0 mr-2 opacity-70"
                          />
                          <span class="truncate">{{ getNavigationLabel(subItem) }}</span>
                        </router-link>
                      </DropdownMenuItem>
                    </template>
                  </div>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>

            <!-- Single Standalone Item Tooltip -->
            <div
              v-else-if="item.resolvedTo"
              class="flex justify-center p-0.5"
            >
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <router-link
                      :to="item.resolvedTo"
                      class="w-full flex items-center justify-center p-2.5 rounded-xl cursor-pointer transition-colors"
                      :class="[ isChildActive(item) ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' ]"
                      @click="$emit('close')"
                    >
                      <component
                        :is="getIcon(item.icon || item.name || '')"
                        class="w-5 h-5"
                      />
                    </router-link>
                  </TooltipTrigger>
                  <TooltipContent side="right">
                    {{ getNavigationLabel(item) }}
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </div>
          </template>
        </template>
      </nav>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useNavigationStore } from '@/shared/stores/navigation';
import type { NavItem } from '@/shared/utils/navigation';
import type { RouteLocationRaw } from 'vue-router';

type ResolvedNavChild = NavItem & { resolvedTo: RouteLocationRaw | null };
type ResolvedNavItem = NavItem & {
    resolvedTo: RouteLocationRaw | null;
    children?: ResolvedNavChild[];
};
import { getIcon } from '@/shared/utils/icons';
import { resolveNavTo } from '@/shared/utils/consoleRoute';
import { prefetchRoute } from '@/shared/utils/routePrefetch';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useConsoleContextStore } from '@/engine/stores/consoleContext';
import { useSubscriptionFeatures } from '@/shared/composables/useSubscriptionFeatures';
import { isConsoleShell } from '@/config/shell';
import TheLogo from '@/shared/layouts/partials/TheLogo.vue';
import { 
    Tooltip, 
    TooltipContent, 
    TooltipProvider, 
    TooltipTrigger,
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
    DropdownMenuItem,
} from '@/shared/components/ui';
import type { User } from '@/engine/types/auth';

defineProps<{
    sidebarMinimized?: boolean;
    sidebarOpen?: boolean;
    user?: User | null;
}>();

defineEmits<{
    (e: 'toggle-minimize'): void;
    (e: 'close'): void;
    (e: 'logout'): void;
}>();

const { t, te } = useI18n();
const $route = useRoute();
const router = useRouter();

function buildRouteNameSet(): Set<string> {
    const names = new Set<string>();
    for (const record of router.getRoutes()) {
        if (record.name != null && record.name !== '') {
            names.add(String(record.name));
        }
    }
    return names;
}

function resolveNavToForItem(
    target: Pick<NavItem, 'to' | 'name'>,
    routeNames: Set<string>,
): RouteLocationRaw | null {
    const to = resolveNavTo(target);
    if (!to) return null;

    if (typeof to === 'object' && to !== null && 'name' in to) {
        const name = (to as { name?: string }).name;
        if (typeof name === 'string' && !routeNames.has(name)) {
            return null;
        }
    }

    return to;
}

const authStore = useAuthStore();
const systemStore = useSystemStore();
const consoleStore = useConsoleContextStore();
const navigationStore = useNavigationStore();
const { fetchFeatures, can: subscriptionCan } = useSubscriptionFeatures();

// Dynamic Dashboard logic
const dashboardLink = computed(() => {
    if (consoleStore.isSystem) {
        return { name: 'system.dashboard' };
    }
    return { name: 'dashboard' };
});

const dashboardLabel = computed(() => t('common.navigation.menu.dashboard'));

const isDashboardActive = computed(() => {
    const names = ['dashboard', 'system.dashboard', 'foundation.dashboard'];
    return names.includes(String($route.name));
});

const expandedGroups = ref<Record<string, boolean>>({});

const toggleGroup = (key: string) => {
    expandedGroups.value[key] = !expandedGroups.value[key];
};

const isChildActive = (item: Pick<NavItem, 'name' | 'to'>) => {
    if (!$route.name) return false;
    const currentName = String($route.name);
    if (item.name === currentName) return true;
    if (item.to && typeof item.to === 'object' && 'name' in item.to && item.to.name === currentName) return true;
    if (item.name === 'model-index' && (currentName.startsWith('model-') || currentName.startsWith('dynamic-records-'))) {
        return true;
    }
    return false;
};

const isMenuGroupActive = (item: NavItem): boolean => {
    if (!$route.name) return false;
    if (isChildActive(item)) return true;
    if (item.children && item.children.length > 0) {
        return item.children.some(child => isChildActive(child));
    }
    return false;
};

const autoExpandActiveGroup = () => {
    if (!$route.name) return;
    for (const item of filteredNavigation.value) {
        if (item.children && item.children.length > 0) {
            if (item.children.some(c => isChildActive(c))) {
                expandedGroups.value[navItemKey(item)] = true;
            }
        }
    }
};

const filteredNavigation = computed<ResolvedNavItem[]>(() => {
    const routeNames = buildRouteNameSet();
    const permissionSet = authStore.permissionNameSet;
    const isSuperAdmin = authStore.getRoleRank() >= 100;

    const canPermission = (permission?: string): boolean => {
        if (!permission) return true;
        if (isSuperAdmin) return true;
        return permissionSet.has(permission);
    };

    return navigationStore.navigationItems
        .filter((item: NavItem) => {
            const role = Array.isArray(item.role) ? item.role[0] : item.role;
            if (role && !authStore.isAtLeastRole(role)) return false;
            if (!canPermission(item.permission)) return false;
            
            const requiredFeature = (item as any).feature || (item as any).requiredFeature;
            if (requiredFeature && !subscriptionCan(requiredFeature)) return false;

            return true;
        })
        .map((item: NavItem) => {
            const filteredChildren = item.children?.filter(child => {
                if (!canPermission(child.permission)) return false;
                
                const childFeature = (child as any).feature || (child as any).requiredFeature;
                if (childFeature && !subscriptionCan(childFeature)) return false;
                
                return true;
            });
            
            const resolvedChildren = filteredChildren?.map((child) => ({
                ...child,
                label: resolveNavLabel(child),
                resolvedTo: resolveNavToForItem(child, routeNames),
            }));

            return {
                ...item,
                label: resolveNavLabel(item),
                resolvedTo: resolveNavToForItem(item, routeNames),
                children: resolvedChildren,
            };
        })
        .filter((item) => {
            if (item.children && item.children.length === 0 && !item.resolvedTo) {
                return false;
            }
            return true;
        });
});

const prefetchNavTarget = (item: ResolvedNavItem | ResolvedNavChild) => {
    const to = item.resolvedTo;
    if (to) {
        prefetchRoute(router, to);
    }
};

const navItemKey = (item: NavItem) => item.group || item.labelKey || item.name || item.label || '';

const resolveNavLabel = (item: NavItem) => {
    const lk = item.labelKey || '';
    return lk && te(lk) ? t(lk) : (item.label || item.name || '');
};

const getNavigationLabel = (item: NavItem) => item.label || resolveNavLabel(item);

const getVisitTooltip = computed(() => {
    const siteUrl = systemStore.siteSettings?.site_url || 'domain.com';
    let domain = siteUrl;
    try {
        const url = new URL(siteUrl.startsWith('http') ? siteUrl : `https://${siteUrl}`);
        domain = url.hostname;
    } catch { /* keep default */ }
    return t('common.navigation.visit_site', { url: domain });
});

watch(expandedGroups, (newVal) => {
    localStorage.setItem('sidebarExpandedGroups', JSON.stringify(newVal));
}, { deep: true });

onMounted(() => {
    if (isConsoleShell()) {
        fetchFeatures();
    }
    const saved = localStorage.getItem('sidebarExpandedGroups');
    if (saved) {
        try { expandedGroups.value = JSON.parse(saved); } 
        catch { /* ignore */ }
    }
    autoExpandActiveGroup();
});

watch(() => $route.name, () => autoExpandActiveGroup());
</script>

<style scoped>
.context-fade-enter-active,
.context-fade-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.context-fade-enter-from {
  opacity: 0;
  transform: translateY(4px) scale(0.98);
}
.context-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px) scale(0.98);
}
</style>
