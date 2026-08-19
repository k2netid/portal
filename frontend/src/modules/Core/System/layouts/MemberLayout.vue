<template>
  <div class="min-h-screen bg-background text-foreground admin-instant admin-layout flex flex-col">
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 bg-sidebar text-sidebar-foreground border-r border-border transition-all duration-200 ease-in-out flex flex-col justify-between',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarMinimized ? 'w-[68px]' : 'w-64'
      ]"
    >
      <!-- Floating Toggle Button (Desktop) -->
      <button
        class="hidden lg:flex absolute -right-3 top-5 items-center justify-center h-6 w-6 rounded-full border border-border bg-sidebar text-muted-foreground hover:text-foreground shadow-sm z-[51] transition-transform hover:scale-110"
        :title="sidebarMinimized ? t('common.navigation.sidebar.expand', 'Perluas') : t('common.navigation.sidebar.minimize', 'Kecilkan')"
        :aria-label="sidebarMinimized ? t('common.navigation.sidebar.expand', 'Perluas') : t('common.navigation.sidebar.minimize', 'Kecilkan')"
        @click="toggleSidebarMinimize"
      >
        <ChevronRight
          v-if="sidebarMinimized"
          class="w-3.5 h-3.5"
        />
        <ChevronLeft
          v-else
          class="w-3.5 h-3.5"
        />
      </button>

      <div class="flex flex-col h-full overflow-hidden">
        <!-- Logo Area -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-border shrink-0">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger as-child>
                <router-link
                  to="/"
                  class="block hover:opacity-80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring rounded-md"
                  @click="closeSidebar"
                >
                  <TheLogo
                    :minimized="sidebarMinimized"
                    subtitle="PORTAL"
                  />
                </router-link>
              </TooltipTrigger>
              <TooltipContent
                v-if="sidebarMinimized"
                side="right"
                :side-offset="10"
              >
                {{ siteName }} — {{ t('system.member.badge', 'Portal Member') }}
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          <!-- Mobile Close Button -->
          <button
            class="lg:hidden text-muted-foreground hover:text-foreground p-1 rounded-lg hover:bg-accent"
            :aria-label="t('common.actions.close', 'Tutup')"
            @click="closeSidebar"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Navigation Items -->
        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
          <!-- SECTION: Menu Anggota -->
          <div
            v-if="!sidebarMinimized"
            class="pt-1 pb-1.5 px-3 flex items-center gap-2 select-none"
          >
            <span class="text-[10px] uppercase font-bold text-muted-foreground/60 tracking-wider whitespace-nowrap">
              {{ t('system.member.nav.sectionMember', 'Menu Anggota') }}
            </span>
            <div class="h-px bg-border/60 flex-1" />
          </div>

          <TooltipProvider
            v-for="item in memberNavItems"
            :key="item.to"
          >
            <Tooltip>
              <TooltipTrigger as-child>
                <router-link
                  :to="item.to"
                  class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl group transition-colors"
                  :class="[
                    isActive(item.to)
                      ? 'bg-primary/10 text-primary font-semibold'
                      : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                  ]"
                  @click="closeSidebar"
                >
                  <component
                    :is="item.icon"
                    class="w-5 h-5 flex-shrink-0"
                    :class="isActive(item.to) ? 'text-primary' : 'text-muted-foreground group-hover:text-foreground'"
                  />
                  <span
                    v-if="!sidebarMinimized"
                    class="ml-3 truncate font-semibold"
                  >
                    {{ item.label }}
                  </span>
                </router-link>
              </TooltipTrigger>
              <TooltipContent
                v-if="sidebarMinimized"
                side="right"
                :side-offset="10"
              >
                {{ item.label }}
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          <!-- SECTION: Navigasi Situs -->
          <div
            v-if="!sidebarMinimized"
            class="pt-4 pb-1.5 px-3 flex items-center gap-2 select-none"
          >
            <span class="text-[10px] uppercase font-bold text-muted-foreground/60 tracking-wider whitespace-nowrap">
              {{ t('system.member.nav.sectionSite', 'Navigasi Situs') }}
            </span>
            <div class="h-px bg-border/60 flex-1" />
          </div>
          <div
            v-else
            class="pt-3 pb-1 flex justify-center"
          >
            <div class="h-px w-8 bg-border/60" />
          </div>

          <!-- Site link: Home -->
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger as-child>
                <router-link
                  to="/"
                  class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl text-muted-foreground hover:bg-accent hover:text-accent-foreground group transition-colors"
                  @click="closeSidebar"
                >
                  <Globe class="w-5 h-5 flex-shrink-0 text-muted-foreground group-hover:text-foreground" />
                  <span
                    v-if="!sidebarMinimized"
                    class="ml-3 truncate"
                  >
                    {{ t('system.member.backToWebsite', 'Kembali ke Website') }}
                  </span>
                  <ArrowUpRight
                    v-if="!sidebarMinimized"
                    class="ml-auto w-4 h-4 opacity-50 group-hover:opacity-100 transition-opacity"
                  />
                </router-link>
              </TooltipTrigger>
              <TooltipContent
                v-if="sidebarMinimized"
                side="right"
                :side-offset="10"
              >
                {{ t('system.member.backToWebsite', 'Kembali ke Website') }}
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          <!-- Site link: Blog -->
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger as-child>
                <router-link
                  to="/blog"
                  class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl text-muted-foreground hover:bg-accent hover:text-accent-foreground group transition-colors"
                  @click="closeSidebar"
                >
                  <Compass class="w-5 h-5 flex-shrink-0 text-muted-foreground group-hover:text-foreground" />
                  <span
                    v-if="!sidebarMinimized"
                    class="ml-3 truncate"
                  >
                    {{ t('system.member.exploreArticles', 'Eksplorasi Artikel') }}
                  </span>
                  <ArrowUpRight
                    v-if="!sidebarMinimized"
                    class="ml-auto w-4 h-4 opacity-50 group-hover:opacity-100 transition-opacity"
                  />
                </router-link>
              </TooltipTrigger>
              <TooltipContent
                v-if="sidebarMinimized"
                side="right"
                :side-offset="10"
              >
                {{ t('system.member.exploreArticles', 'Eksplorasi Artikel') }}
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </nav>

        <!-- Sidebar Footer / Bottom User Card -->
        <div class="p-2 border-t border-border shrink-0 bg-sidebar">
          <div
            v-if="!sidebarMinimized"
            class="flex items-center justify-between p-2 rounded-xl bg-accent/40 border border-border/50"
          >
            <div class="flex items-center gap-2.5 overflow-hidden">
              <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs font-bold shrink-0 border border-primary/20">
                {{ userInitial }}
              </div>
              <div class="overflow-hidden">
                <p class="text-xs font-semibold text-foreground truncate leading-tight">
                  {{ authStore.user?.name || 'Member' }}
                </p>
                <p class="text-[10px] text-muted-foreground truncate leading-tight">
                  {{ authStore.user?.email }}
                </p>
              </div>
            </div>

            <Button
              variant="ghost"
              size="icon"
              class="h-7 w-7 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg shrink-0"
              :title="t('common.actions.logout', 'Keluar')"
              @click="handleLogout"
            >
              <LogOut class="w-3.5 h-3.5" />
            </Button>
          </div>

          <div
            v-else
            class="flex flex-col items-center gap-2 py-1"
          >
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger as-child>
                  <button
                    type="button"
                    class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs font-bold border border-primary/20 hover:bg-primary/20 transition-colors"
                  >
                    {{ userInitial }}
                  </button>
                </TooltipTrigger>
                <TooltipContent
                  side="right"
                  :side-offset="10"
                >
                  {{ authStore.user?.name }} ({{ authStore.user?.email }})
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>

            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger as-child>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg"
                    @click="handleLogout"
                  >
                    <LogOut class="w-4 h-4" />
                  </Button>
                </TooltipTrigger>
                <TooltipContent
                  side="right"
                  :side-offset="10"
                >
                  {{ t('common.actions.logout', 'Keluar') }}
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </div>
        </div>
      </div>
    </aside>

    <!-- Mobile Backdrop -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-background/60 backdrop-blur-sm lg:hidden"
      @click="closeSidebar"
    />

    <!-- Main Wrapper (Shifted right on Desktop) -->
    <div
      :class="[
        'flex-1 flex flex-col min-h-screen transition-all duration-200 ease-in-out',
        sidebarMinimized ? 'lg:pl-[68px]' : 'lg:pl-64'
      ]"
    >
      <!-- Top Navbar -->
      <header class="console-chrome sticky top-0 z-40 bg-card border-b border-border h-16 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
        <!-- Left: Mobile Menu Toggle & Title -->
        <div class="flex items-center space-x-4">
          <button
            class="lg:hidden text-muted-foreground hover:text-foreground p-1.5 rounded-lg hover:bg-accent transition-colors"
            :aria-label="t('common.navigation.menu.toggleSidebar', 'Toggle Sidebar')"
            @click="toggleSidebarOpen"
          >
            <Menu class="w-6 h-6" />
          </button>

          <!-- Route Title Badge -->
          <div class="flex items-center gap-2.5">
            <span class="text-sm font-semibold text-foreground tracking-tight">
              {{ currentRouteTitle }}
            </span>
            <Badge
              variant="secondary"
              class="text-[10px] font-medium bg-primary/10 text-primary border-primary/20 hidden sm:inline-flex"
            >
              {{ t('system.member.badge', 'Portal') }}
            </Badge>
          </div>
        </div>

        <!-- Right: Actions, Theme, and User Dropdown -->
        <div class="flex items-center space-x-3 ml-auto">
          <!-- Back to Website Button -->
          <Button
            as-child
            variant="outline"
            size="sm"
            class="text-xs h-9 px-3 gap-1.5 rounded-xl border-border hover:bg-accent hidden sm:inline-flex"
          >
            <router-link to="/">
              <ArrowLeft class="w-3.5 h-3.5" />
              <span>{{ t('system.member.backToWebsite', 'Kembali ke Website') }}</span>
            </router-link>
          </Button>

          <!-- Theme Toggle -->
          <Button
            variant="ghost"
            size="icon"
            class="h-9 w-9 rounded-full text-muted-foreground hover:text-foreground hover:bg-accent transition-all"
            :title="isDark ? t('common.theme.light', 'Light Mode') : t('common.theme.dark', 'Dark Mode')"
            @click="toggleMode"
          >
            <Sun
              v-if="isDark"
              class="w-4 h-4 text-amber-400 animate-in spin-in-90 duration-300"
            />
            <Moon
              v-else
              class="w-4 h-4 text-slate-700 dark:text-slate-200 animate-in spin-in-90 duration-300"
            />
          </Button>

          <!-- User Menu Dropdown -->
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <button
                type="button"
                class="flex items-center gap-2 h-9 pl-1.5 pr-2.5 rounded-xl border border-border bg-card hover:bg-accent transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-ring"
              >
                <div class="w-6 h-6 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs font-bold shrink-0 border border-primary/20">
                  {{ userInitial }}
                </div>
                <span class="text-xs font-semibold max-w-[100px] truncate text-foreground hidden sm:inline">
                  {{ authStore.user?.name || 'Member' }}
                </span>
                <ChevronDown class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
              </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
              align="end"
              class="w-52 bg-popover/95 backdrop-blur-md border-border shadow-xl rounded-xl"
            >
              <div class="px-3 py-2 border-b border-border mb-1">
                <p class="text-xs font-bold text-foreground truncate">{{ authStore.user?.name }}</p>
                <p class="text-[11px] text-muted-foreground truncate">{{ authStore.user?.email }}</p>
              </div>
              <DropdownMenuItem as-child>
                <router-link
                  to="/member/profile"
                  class="flex items-center gap-2 cursor-pointer text-xs"
                >
                  <User class="w-4 h-4 text-primary" />
                  <span>{{ t('system.member.nav.profile', 'Akun & Profil') }}</span>
                </router-link>
              </DropdownMenuItem>
              <DropdownMenuItem as-child>
                <router-link
                  to="/member/bookmarks"
                  class="flex items-center gap-2 cursor-pointer text-xs"
                >
                  <Bookmark class="w-4 h-4 text-amber-500" />
                  <span>{{ t('system.member.nav.bookmarks', 'Artikel Tersimpan') }}</span>
                </router-link>
              </DropdownMenuItem>
              <DropdownMenuItem as-child>
                <router-link
                  to="/member/comments"
                  class="flex items-center gap-2 cursor-pointer text-xs"
                >
                  <MessageSquare class="w-4 h-4 text-blue-500" />
                  <span>{{ t('system.member.nav.comments', 'Riwayat Komentar') }}</span>
                </router-link>
              </DropdownMenuItem>
              <DropdownMenuItem as-child>
                <router-link
                  to="/member/newsletter"
                  class="flex items-center gap-2 cursor-pointer text-xs"
                >
                  <Mail class="w-4 h-4 text-emerald-500" />
                  <span>{{ t('system.member.nav.newsletter', 'Preferensi Buletin') }}</span>
                </router-link>
              </DropdownMenuItem>
              <div class="h-px bg-border my-1" />
              <DropdownMenuItem
                class="text-destructive focus:text-destructive cursor-pointer flex items-center gap-2 text-xs"
                @click="handleLogout"
              >
                <LogOut class="w-4 h-4" />
                <span>{{ t('common.actions.logout', 'Keluar') }}</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </header>

      <!-- Main Content Page -->
      <main class="flex-1 p-6 lg:p-8 relative overflow-hidden">
        <div class="max-w-7xl mx-auto space-y-6">
          <router-view v-slot="{ Component }">
            <transition
              name="fade"
              mode="out-in"
            >
              <component :is="Component" />
            </transition>
          </router-view>
        </div>
      </main>

      <!-- Footer -->
      <footer class="border-t border-border py-4 px-6 text-center text-xs text-muted-foreground bg-card/40">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
            <p>&copy; {{ currentYear }} {{ siteName }}. {{ t('system.member.allRightsReserved', 'All rights reserved.') }}</p>
          </div>
          <div class="flex items-center gap-4 text-xs font-medium">
            <router-link
              to="/blog"
              class="hover:text-foreground transition-colors"
            >
              {{ t('system.member.exploreArticles', 'Eksplorasi Artikel') }}
            </router-link>
            <span class="text-border">&bull;</span>
            <router-link
              to="/kontak"
              class="hover:text-foreground transition-colors"
            >
              {{ t('theme.janari.footer.contact', 'Bantuan') }}
            </router-link>
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useSidebar } from '@/shared/composables/useSidebar';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useDarkMode } from '@/shared/composables/useDarkMode';
import TheLogo from '@/shared/layouts/partials/TheLogo.vue';
import {
  Badge,
  Button,
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  Tooltip,
  TooltipTrigger,
  TooltipContent,
  TooltipProvider,
} from '@/shared/components/ui';
import {
  X,
  Sun,
  Moon,
  Menu,
  ChevronLeft,
  ChevronRight,
  ChevronDown,
  User,
  Bookmark,
  MessageSquare,
  Mail,
  LogOut,
  Globe,
  Compass,
  ArrowLeft,
  ArrowUpRight,
} from 'lucide-vue-next';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const systemStore = useSystemStore();
const { isDark, toggleMode } = useDarkMode('console');
const { sidebarMinimized, sidebarOpen, toggleSidebarMinimize, toggleSidebarOpen, closeSidebar } = useSidebar();

const currentYear = new Date().getFullYear();
const siteSettings = computed(() => systemStore.settings);
const siteName = computed(() => siteSettings.value?.site_name || 'Jejakawan CMS');

const userInitial = computed(() => {
  const name = authStore.user?.name || '';
  return name.charAt(0).toUpperCase() || 'M';
});

const memberNavItems = computed(() => [
  {
    to: '/member/profile',
    label: t('system.member.nav.profile', 'Akun & Profil'),
    icon: User,
  },
  {
    to: '/member/bookmarks',
    label: t('system.member.nav.bookmarks', 'Artikel Tersimpan'),
    icon: Bookmark,
  },
  {
    to: '/member/comments',
    label: t('system.member.nav.comments', 'Riwayat Komentar'),
    icon: MessageSquare,
  },
  {
    to: '/member/newsletter',
    label: t('system.member.nav.newsletter', 'Preferensi Buletin'),
    icon: Mail,
  },
]);

const currentRouteTitle = computed(() => {
  const current = memberNavItems.value.find((item) => isActive(item.to));
  return current ? current.label : t('system.member.badge', 'Portal Member');
});

const isActive = (path: string): boolean => {
  return route.path === path || route.path.startsWith(`${path}/`);
};

const handleLogout = async () => {
  await authStore.logout();
  void router.replace('/');
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: translateY(4px);
}

.fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
