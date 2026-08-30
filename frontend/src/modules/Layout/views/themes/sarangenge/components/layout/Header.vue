<template>
  <header class="sticky top-0 z-50 w-full border-b border-border/50 bg-[hsl(var(--background)/0.88)] backdrop-blur-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 sm:h-[4.25rem]">
        <router-link
          to="/"
          class="flex items-center gap-3 group focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--sarangenge-teal)] rounded-[var(--sarangenge-radius-sm)]"
        >
          <img
            v-if="siteLogo"
            :src="siteLogo"
            :alt="siteName"
            class="h-9 w-auto object-contain"
            width="120"
            height="36"
          >
          <div
            v-else
            class="w-9 h-9 rounded-[var(--sarangenge-radius-sm)] bg-gradient-to-br from-[var(--sarangenge-teal)] to-[var(--sarangenge-sun)] text-white flex items-center justify-center font-black text-base shadow-md shadow-[var(--sarangenge-teal)]/25"
            aria-hidden="true"
          >
            {{ siteName.charAt(0).toUpperCase() }}
          </div>
          <span class="text-lg sm:text-xl font-bold tracking-tight text-foreground font-heading group-hover:text-[var(--sarangenge-teal)] transition-colors">
            {{ siteName }}
          </span>
        </router-link>

        <nav
          class="hidden lg:flex items-center gap-0.5"
          aria-label="Main"
        >
          <router-link
            v-for="item in navItems"
            :key="item.path"
            :to="item.path"
            class="px-3.5 py-2 rounded-[var(--sarangenge-radius-sm)] text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-muted/60 transition-colors"
            active-class="!text-[var(--sarangenge-teal-deep)] !bg-[var(--sarangenge-teal)]/10 !font-semibold"
          >
            {{ item.name }}
          </router-link>
        </nav>

        <div class="flex items-center gap-2 sm:gap-3">
          <ThemeToggle />

          <Button
            v-if="memberEnabled && memberStore.isAuthenticated"
            as="router-link"
            to="/member/account"
            variant="outline"
            size="sm"
            class="hidden sm:inline-flex"
          >
            {{ t('theme.sarangenge.header.account', 'Account') }}
          </Button>
          <Button
            v-else-if="memberEnabled"
            as="router-link"
            to="/member/login"
            variant="outline"
            size="sm"
            class="hidden sm:inline-flex"
          >
            {{ t('theme.sarangenge.header.signIn', 'Sign in') }}
          </Button>

          <Button
            as="router-link"
            to="/contact"
            variant="primary"
            size="sm"
            class="hidden sm:inline-flex"
          >
            {{ t('theme.sarangenge.header.getStarted', 'Admissions') }}
          </Button>

          <button
            type="button"
            class="lg:hidden w-9 h-9 rounded-[var(--sarangenge-radius-sm)] border border-border/70 flex items-center justify-center text-foreground hover:bg-muted"
            :aria-expanded="mobileMenuOpen"
            aria-controls="sarangenge-mobile-nav"
            :aria-label="mobileMenuOpen ? 'Close menu' : 'Open menu'"
            @click="mobileMenuOpen = !mobileMenuOpen"
          >
            <X
              v-if="mobileMenuOpen"
              class="w-5 h-5"
            />
            <Menu
              v-else
              class="w-5 h-5"
            />
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="mobileMenuOpen"
      id="sarangenge-mobile-nav"
      class="lg:hidden border-b border-border/60 bg-background/95 backdrop-blur-2xl px-4 pt-2 pb-6 space-y-1"
    >
      <router-link
        v-for="item in navItems"
        :key="item.path"
        :to="item.path"
        class="block px-4 py-2.5 rounded-[var(--sarangenge-radius-sm)] text-base font-medium text-foreground hover:bg-muted"
        active-class="bg-[var(--sarangenge-teal)]/10 text-[var(--sarangenge-teal-deep)] font-bold"
        @click="mobileMenuOpen = false"
      >
        {{ item.name }}
      </router-link>
      <div class="pt-3 space-y-2">
        <Button
          v-if="memberEnabled"
          as="router-link"
          :to="memberStore.isAuthenticated ? '/member/account' : '/member/login'"
          variant="outline"
          class="w-full justify-center"
          @click="mobileMenuOpen = false"
        >
          {{ memberStore.isAuthenticated
            ? t('theme.sarangenge.header.account', 'Account')
            : t('theme.sarangenge.header.signIn', 'Sign in') }}
        </Button>
        <Button
          as="router-link"
          to="/contact"
          variant="primary"
          class="w-full justify-center"
          @click="mobileMenuOpen = false"
        >
          {{ t('theme.sarangenge.header.getStarted', 'Admissions') }}
        </Button>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { ThemeToggle, Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useMemberStore } from '@/modules/Member/stores/member';
import { Menu, X } from 'lucide-vue-next';

const { t } = useI18n();
const { getSetting } = useTheme();
const systemStore = useSystemStore();
const memberStore = useMemberStore();
const memberEnabled = computed(() => systemStore.activeExtensions.includes('member'));

const mobileMenuOpen = ref(false);

const siteName = computed(() => {
  return String(getSetting('site_title') || systemStore.siteSettings?.site_name || systemStore.appIdentity?.app_name || 'Sarangenge');
});

const siteLogo = computed(() => {
  return String(getSetting('brand_logo') || systemStore.siteSettings?.site_logo || systemStore.appIdentity?.app_logo || '');
});

const navItems = computed(() => [
  { name: t('theme.sarangenge.header.home', 'Home'), path: '/' },
  { name: t('theme.sarangenge.header.about', 'Profile'), path: '/about' },
  { name: t('theme.sarangenge.header.solusi', 'Programs'), path: '/solusi' },
  { name: t('theme.sarangenge.header.achievement', 'Achievements'), path: '/achievement' },
  { name: t('theme.sarangenge.header.blog', 'News'), path: '/blog' },
  { name: t('theme.sarangenge.header.contact', 'Contact'), path: '/contact' },
]);
</script>
