<template>
  <div class="min-h-screen bg-background text-foreground flex selection:bg-primary/20 selection:text-primary">
    <!-- Mobile Sidebar Backdrop -->
    <transition
      enter-active-class="transition-opacity duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isMobileMenuOpen"
        class="fixed inset-0 z-40 bg-background/80 backdrop-blur-sm lg:hidden"
        @click="isMobileMenuOpen = false"
      />
    </transition>

    <!-- Sidebar (Desktop Fixed + Mobile Drawer) -->
    <aside
      class="fixed top-0 bottom-0 left-0 z-50 w-64 border-r border-border/50 bg-card/95 backdrop-blur-xl flex flex-col justify-between transition-transform duration-300 ease-in-out lg:translate-x-0 shadow-lg lg:shadow-none"
      :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <!-- Sidebar Header & Branding -->
      <div class="h-16 px-4 border-b border-border/40 flex items-center justify-between gap-3 bg-card/50">
        <router-link
          to="/"
          class="block hover:opacity-90 transition-opacity focus:outline-none focus:ring-2 focus:ring-primary/20 rounded-lg"
          @click="isMobileMenuOpen = false"
        >
          <TheLogo
            :minimized="false"
            subtitle="PORTAL"
          />
        </router-link>

        <!-- Mobile Close Button -->
        <button
          type="button"
          class="lg:hidden p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted/60 transition-colors"
          :aria-label="t('common.actions.close', 'Tutup')"
          @click="isMobileMenuOpen = false"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Navigation Links -->
      <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
        <!-- Main Member Section -->
        <div>
          <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-muted-foreground/70 mb-2">
            {{ t('system.member.nav.sectionMember', 'Menu Anggota') }}
          </p>
          <div class="space-y-1">
            <router-link
              v-for="item in memberNavItems"
              :key="item.to"
              :to="item.to"
              class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group cursor-pointer"
              :class="[
                isActive(item.to)
                  ? 'bg-primary/10 text-primary font-bold shadow-sm ring-1 ring-primary/20'
                  : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'
              ]"
              @click="isMobileMenuOpen = false"
            >
              <component
                :is="item.icon"
                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-110"
                :class="isActive(item.to) ? 'text-primary' : 'text-muted-foreground group-hover:text-foreground'"
              />
              <span class="truncate">{{ item.label }}</span>
            </router-link>
          </div>
        </div>

        <!-- Site Navigation Section -->
        <div>
          <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-muted-foreground/70 mb-2">
            {{ t('system.member.nav.sectionSite', 'Navigasi Situs') }}
          </p>
          <div class="space-y-1">
            <router-link
              to="/"
              class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-medium text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors group"
              @click="isMobileMenuOpen = false"
            >
              <div class="flex items-center gap-3">
                <Globe class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                <span>{{ t('system.member.backToWebsite', 'Kembali ke Website') }}</span>
              </div>
              <ArrowUpRight class="w-3.5 h-3.5 text-muted-foreground/50 group-hover:text-primary transition-colors" />
            </router-link>

            <router-link
              to="/blog"
              class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-medium text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors group"
              @click="isMobileMenuOpen = false"
            >
              <div class="flex items-center gap-3">
                <Compass class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                <span>{{ t('system.member.exploreArticles', 'Eksplorasi Artikel') }}</span>
              </div>
              <ArrowUpRight class="w-3.5 h-3.5 text-muted-foreground/50 group-hover:text-primary transition-colors" />
            </router-link>
          </div>
        </div>
      </div>

      <!-- Sidebar Footer (User Card & Quick Actions) -->
      <div class="p-3 border-t border-border/40 bg-card/60 space-y-2">
        <div class="flex items-center justify-between p-2.5 rounded-xl bg-background/80 border border-border/40 hover:border-border/80 transition-colors">
          <div class="flex items-center gap-2.5 overflow-hidden">
            <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xs font-black border border-primary/20 shrink-0 shadow-inner">
              {{ userInitial }}
            </div>
            <div class="overflow-hidden">
              <p class="text-xs font-bold text-foreground truncate leading-tight">{{ authStore.user?.name || 'Member' }}</p>
              <p class="text-[10px] text-muted-foreground truncate leading-tight">{{ authStore.user?.email }}</p>
            </div>
          </div>

          <Button
            variant="ghost"
            size="icon"
            class="h-7 w-7 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg shrink-0 transition-colors"
            :title="t('common.actions.logout', 'Keluar')"
            @click="handleLogout"
          >
            <LogOut class="w-3.5 h-3.5" />
          </Button>
        </div>
      </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-h-screen lg:pl-64">
      <!-- Top Navbar Header -->
      <header class="sticky top-0 z-30 w-full h-16 border-b border-border/40 bg-background/80 backdrop-blur-md px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
        <!-- Left: Mobile Burger & Active Page Title -->
        <div class="flex items-center gap-3">
          <button
            type="button"
            class="lg:hidden p-2 rounded-xl border border-border/40 hover:bg-muted/60 text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
            :aria-label="t('common.navigation.sidebar.expand', 'Buka Menu')"
            @click="isMobileMenuOpen = true"
          >
            <MenuIcon class="w-4 h-4" />
          </button>

          <div>
            <h2 class="text-sm sm:text-base font-bold text-foreground tracking-tight flex items-center gap-2">
              <span>{{ currentRouteTitle }}</span>
            </h2>
          </div>
        </div>

        <!-- Right: Actions & User Controls -->
        <div class="flex items-center gap-2 sm:gap-3">
          <!-- Back to Web Quick Link -->
          <Button
            as-child
            variant="ghost"
            size="sm"
            class="text-xs text-muted-foreground hover:text-foreground h-9 px-3 gap-1.5 rounded-xl border border-border/30 hover:border-border/60 hover:bg-muted/50 hidden sm:inline-flex"
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
            class="h-9 w-9 rounded-xl border border-border/30 hover:border-border/60 hover:bg-muted/50 text-muted-foreground hover:text-foreground"
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
                class="flex items-center gap-2 h-9 pl-2 pr-2.5 rounded-xl border border-border/40 hover:border-border bg-card/60 hover:bg-card transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/20"
              >
                <div class="w-6 h-6 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs font-black shrink-0">
                  {{ userInitial }}
                </div>
                <span class="text-xs font-semibold max-w-[110px] truncate text-foreground hidden sm:inline">
                  {{ authStore.user?.name || 'Member' }}
                </span>
                <ChevronDown class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
              </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
              align="end"
              class="w-52 bg-popover/95 backdrop-blur-md border-border/50 shadow-xl"
            >
              <div class="px-3 py-2 border-b border-border/40 mb-1">
                <p class="text-xs font-bold text-foreground truncate">{{ authStore.user?.name }}</p>
                <p class="text-[11px] text-muted-foreground truncate">{{ authStore.user?.email }}</p>
              </div>
              <DropdownMenuItem as-child>
                <router-link
                  to="/member/profile"
                  class="flex items-center gap-2 cursor-pointer"
                >
                  <UserIcon class="w-4 h-4 text-primary" />
                  <span>{{ t('system.member.nav.profile', 'Akun & Profil') }}</span>
                </router-link>
              </DropdownMenuItem>
              <DropdownMenuItem as-child>
                <router-link
                  to="/member/bookmarks"
                  class="flex items-center gap-2 cursor-pointer"
                >
                  <Bookmark class="w-4 h-4 text-amber-500" />
                  <span>{{ t('system.member.nav.bookmarks', 'Artikel Tersimpan') }}</span>
                </router-link>
              </DropdownMenuItem>
              <DropdownMenuItem as-child>
                <router-link
                  to="/member/comments"
                  class="flex items-center gap-2 cursor-pointer"
                >
                  <MessageSquare class="w-4 h-4 text-blue-500" />
                  <span>{{ t('system.member.nav.comments', 'Riwayat Komentar') }}</span>
                </router-link>
              </DropdownMenuItem>
              <DropdownMenuItem as-child>
                <router-link
                  to="/member/newsletter"
                  class="flex items-center gap-2 cursor-pointer"
                >
                  <Mail class="w-4 h-4 text-emerald-500" />
                  <span>{{ t('system.member.nav.newsletter', 'Preferensi Buletin') }}</span>
                </router-link>
              </DropdownMenuItem>
              <div class="h-px bg-border/40 my-1" />
              <DropdownMenuItem
                class="text-destructive focus:text-destructive cursor-pointer flex items-center gap-2"
                @click="handleLogout"
              >
                <LogOut class="w-4 h-4" />
                <span>{{ t('common.actions.logout', 'Keluar') }}</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </header>

      <!-- Main Page Content Area -->
      <main class="flex-1 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <router-view v-slot="{ Component }">
          <transition
            name="fade"
            mode="out-in"
          >
            <component :is="Component" />
          </transition>
        </router-view>
      </main>

      <!-- Footer -->
      <footer class="border-t border-border/40 py-5 text-center text-xs text-muted-foreground bg-muted/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
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
            <span class="text-border/60">&bull;</span>
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
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useDarkMode } from '@/shared/composables/useDarkMode';
import TheLogo from '@/shared/layouts/partials/TheLogo.vue';
import {
  Button,
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/shared/components/ui';
import {
  X,
  Sun,
  Moon,
  ChevronDown,
  User as UserIcon,
  Bookmark,
  MessageSquare,
  Mail,
  LogOut,
  Globe,
  Compass,
  ArrowLeft,
  ArrowUpRight,
  Menu as MenuIcon,
} from 'lucide-vue-next';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const systemStore = useSystemStore();
const { isDark, toggleMode } = useDarkMode('console');

const isMobileMenuOpen = ref(false);
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
    icon: UserIcon,
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
  transition: opacity 0.2s ease, transform 0.2s ease;
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
