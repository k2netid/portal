<template>
  <div class="min-h-screen bg-background text-foreground flex flex-col selection:bg-primary/20 selection:text-primary">
    <!-- Top Navbar -->
    <header class="sticky top-0 z-40 w-full border-b border-border/40 bg-background/80 backdrop-blur-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <!-- Brand & Title -->
        <div class="flex items-center gap-3">
          <router-link
            to="/"
            class="flex items-center gap-2.5 group transition-opacity hover:opacity-90"
          >
            <img
              v-if="siteLogo"
              :src="siteLogo"
              :alt="siteName"
              class="h-8 w-auto object-contain max-w-[120px]"
            />
            <span
              v-else
              class="font-bold text-lg tracking-tight bg-gradient-to-r from-primary to-primary/70 bg-clip-text text-transparent"
            >
              {{ siteName }}
            </span>
          </router-link>

          <div class="h-4 w-px bg-border/60 hidden sm:block" />

          <Badge
            variant="secondary"
            class="text-[11px] font-medium tracking-wide bg-primary/10 text-primary border-primary/20 hidden sm:inline-flex items-center gap-1"
          >
            <Sparkles class="w-3 h-3 text-primary" />
            {{ t('system.member.badge', 'Member Portal') }}
          </Badge>
        </div>

        <!-- Actions & User Controls -->
        <div class="flex items-center gap-2 sm:gap-3">
          <!-- Back to Website Button -->
          <Button
            as-child
            variant="ghost"
            size="sm"
            class="text-xs text-muted-foreground hover:text-foreground h-9 px-3 gap-1.5 rounded-lg border border-border/30 hover:border-border/60 hover:bg-muted/50"
          >
            <router-link to="/">
              <ArrowLeft class="w-3.5 h-3.5" />
              <span class="hidden sm:inline">{{ t('system.member.backToWebsite', 'Kembali ke Website') }}</span>
              <span class="sm:hidden">{{ t('system.member.backShort', 'Website') }}</span>
            </router-link>
          </Button>

          <!-- Theme Toggle -->
          <Button
            variant="ghost"
            size="icon"
            class="h-9 w-9 rounded-lg border border-border/30 hover:border-border/60 hover:bg-muted/50 text-muted-foreground hover:text-foreground"
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
                class="flex items-center gap-2 h-9 pl-2 pr-3 rounded-lg border border-border/40 hover:border-border bg-card/60 hover:bg-card transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/20"
              >
                <div class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold shrink-0">
                  {{ userInitial }}
                </div>
                <span class="text-xs font-semibold max-w-[110px] truncate text-foreground">
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
      </div>
    </header>

    <!-- Sub-Navbar / Member Navigation Tabs -->
    <nav class="border-b border-border/30 bg-muted/20 backdrop-blur-sm sticky top-16 z-30">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-1 overflow-x-auto no-scrollbar py-2">
          <router-link
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all shrink-0 cursor-pointer"
            :class="[
              isActive(item.to)
                ? 'bg-primary text-primary-foreground shadow-sm shadow-primary/20'
                : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'
            ]"
          >
            <component
              :is="item.icon"
              class="w-3.5 h-3.5"
            />
            <span>{{ item.label }}</span>
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Main Member Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
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
    <footer class="border-t border-border/40 py-6 text-center text-xs text-muted-foreground bg-muted/10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p>&copy; {{ currentYear }} {{ siteName }}. {{ t('system.member.allRightsReserved', 'All rights reserved.') }}</p>
        <div class="flex items-center gap-4 text-xs">
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
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useDarkMode } from '@/shared/composables/useDarkMode';
import {
  Badge,
  Button,
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/shared/components/ui';
import {
  Sparkles,
  ArrowLeft,
  Sun,
  Moon,
  ChevronDown,
  User as UserIcon,
  Bookmark,
  MessageSquare,
  Mail,
  LogOut,
} from 'lucide-vue-next';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const systemStore = useSystemStore();
const { isDark, toggleMode } = useDarkMode('console');

const currentYear = new Date().getFullYear();
const siteSettings = computed(() => systemStore.settings);
const siteName = computed(() => siteSettings.value?.site_name || 'Jejakawan CMS');
const siteLogo = computed(() => siteSettings.value?.site_logo || '');

const userInitial = computed(() => {
  const name = authStore.user?.name || '';
  return name.charAt(0).toUpperCase() || 'M';
});

const navItems = computed(() => [
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

const isActive = (path: string): boolean => {
  return route.path === path || route.path.startsWith(`${path}/`);
};

const handleLogout = async () => {
  await authStore.logout();
  void router.replace('/');
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

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
