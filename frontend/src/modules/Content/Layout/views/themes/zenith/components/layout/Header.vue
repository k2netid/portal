<template>
  <header class="sticky top-0 z-50 w-full border-b border-border/40 bg-background/80 backdrop-blur-xl transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 sm:h-20">
        <!-- Brand Logo & Title -->
        <router-link
          to="/"
          class="flex items-center gap-3 group focus:outline-none focus:ring-2 focus:ring-primary/50 rounded-xl"
        >
          <img
            v-if="siteLogo"
            :src="siteLogo"
            :alt="siteName"
            class="h-8 w-auto object-contain transition-transform group-hover:scale-105"
            width="120"
            height="32"
          >
          <div
            v-else
            class="w-9 h-9 rounded-xl bg-gradient-to-tr from-primary to-primary/70 text-primary-foreground flex items-center justify-center font-black text-lg shadow-md shadow-primary/20"
          >
            {{ siteName.charAt(0).toUpperCase() }}
          </div>
          <span class="text-xl font-bold tracking-tight text-foreground group-hover:text-primary transition-colors font-heading">
            {{ siteName }}
          </span>
        </router-link>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center gap-1">
          <router-link
            v-for="item in navItems"
            :key="item.path"
            :to="item.path"
            class="px-4 py-2 rounded-full text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-all duration-200"
            active-class="!text-foreground !bg-muted font-semibold"
          >
            {{ item.name }}
          </router-link>
        </nav>

        <!-- Right Action Controls -->
        <div class="flex items-center gap-3">
          <ThemeToggle />

          <Button
            as="router-link"
            to="/contact"
            variant="primary"
            size="sm"
            class="hidden sm:inline-flex"
          >
            {{ t('theme.zenith.header.getStarted', 'Get Started') }}
          </Button>

          <!-- Mobile Menu Button -->
          <button
            type="button"
            aria-label="Open mobile menu"
            class="md:hidden w-9 h-9 rounded-xl border border-border/60 flex items-center justify-center text-foreground hover:bg-muted"
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

    <!-- Mobile Navigation Drawer -->
    <div
      v-if="mobileMenuOpen"
      class="md:hidden border-b border-border/60 bg-background/95 backdrop-blur-2xl px-4 pt-2 pb-6 space-y-2 animate-in slide-in-from-top duration-200"
    >
      <router-link
        v-for="item in navItems"
        :key="item.path"
        :to="item.path"
        class="block px-4 py-2.5 rounded-xl text-base font-medium text-foreground hover:bg-muted"
        active-class="bg-primary/10 text-primary font-bold"
        @click="mobileMenuOpen = false"
      >
        {{ item.name }}
      </router-link>
      <div class="pt-3">
        <Button
          as="router-link"
          to="/contact"
          variant="primary"
          class="w-full justify-center"
          @click="mobileMenuOpen = false"
        >
          {{ t('theme.zenith.header.getStarted', 'Get Started') }}
        </Button>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/modules/Content/Layout/composables/useTheme';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { ThemeToggle, Button } from '@/modules/Content/Layout/views/themes/zenith/ui';
import { Menu, X } from 'lucide-vue-next';

const { t } = useI18n();
const { getSetting } = useTheme();
const systemStore = useSystemStore();

const mobileMenuOpen = ref(false);

const siteName = computed(() => {
  return String(getSetting('site_title') || systemStore.siteSettings?.site_name || systemStore.appIdentity?.app_name || 'Zenith');
});

const siteLogo = computed(() => {
  return String(getSetting('brand_logo') || systemStore.siteSettings?.site_logo || systemStore.appIdentity?.app_logo || '');
});

const navItems = computed(() => [
  { name: t('theme.zenith.header.home', 'Home'), path: '/' },
  { name: t('theme.zenith.header.about', 'About'), path: '/about' },
  { name: t('theme.zenith.header.solusi', 'Solutions'), path: '/solusi' },
  { name: t('theme.zenith.header.services', 'Services'), path: '/services' },
  { name: t('theme.zenith.header.blog', 'Blog'), path: '/blog' },
  { name: t('theme.zenith.header.pricing', 'Pricing'), path: '/pricing' },
  { name: t('theme.zenith.header.contact', 'Contact'), path: '/contact' },
]);
</script>
