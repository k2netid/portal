<template>
  <header
    ref="headerRef"
    data-ja-customizer-target="header"
    :class="[
      'sareupna-header fixed top-0 left-0 w-full z-[100]',
      'bg-background/70 backdrop-blur-xl border-b border-border/40 transition-all duration-300',
      {
        'sareupna-header--scrolled': isHeaderScrolled,
        'sareupna-header--hidden': isHeaderHidden && !isOpen && !isBuilder
      }
    ]"
  >
    <!-- Main Header Container -->
    <div class="container mx-auto px-4 md:px-8 relative z-20">
      <div class="flex items-center justify-between h-16 md:h-20 transition-all duration-300">
        <!-- Branding: Logo + Name + Enterprise Tagline -->
        <router-link
          to="/"
          class="flex items-center gap-3.5 group shrink-0"
        >
          <img 
            v-if="siteLogo && brandingDisplay !== 'text_only'" 
            :src="siteLogo" 
            class="h-8 md:h-9 w-auto object-contain brightness-100 group-hover:brightness-110 transition-all duration-300" 
            :alt="brandingDisplay === 'logo_only' ? siteName : ''"
            width="160"
            height="36"
            loading="eager"
            fetchpriority="high"
          >
          <div 
            v-else-if="brandingDisplay !== 'text_only'"
            class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-primary-foreground font-black text-base shadow-md shadow-primary/25 shrink-0"
          >
            {{ siteName.substring(0, 1).toUpperCase() }}
          </div>
          <template v-if="brandingDisplay !== 'logo_only'">
            <div class="flex flex-col">
              <span class="text-base md:text-lg font-heading font-black tracking-tight uppercase text-foreground leading-none">
                {{ siteName }}
              </span>
              <span
                v-if="siteTagline"
                class="text-[8px] font-mono tracking-[0.28em] uppercase text-foreground/50 mt-1 hidden sm:block truncate max-w-[280px]"
                :title="siteTagline"
              >
                {{ siteTagline }}
              </span>
            </div>
          </template>
        </router-link>

        <!-- Desktop Navigation: Floating Pill Capsule Style -->
        <nav
          v-if="isDesktop"
          data-ja-customizer-target="nav"
          class="flex items-center gap-1.5 ml-auto pr-6"
        >
          <template
            v-for="item in navItems"
            :key="String(item.id || item.title)"
          >
            <div
              v-if="item.children && item.children.length > 0"
              class="group relative"
            >
              <a
                v-if="isExternalLink(item.url)"
                :href="item.url || '#'"
                target="_blank"
                rel="noopener noreferrer"
                :class="[getNavItemClasses(isParentActive(item)), 'group/btn']"
              >
                <span>{{ item.title }}</span>
                <ChevronDown class="w-3.5 h-3.5 opacity-60 group-hover:rotate-180 transition-transform duration-200" />
              </a>
              <router-link
                v-else-if="item.url"
                :to="getInternalUrl(item.url)"
                :class="[getNavItemClasses(isParentActive(item)), 'group/btn']"
              >
                <span>{{ item.title }}</span>
                <ChevronDown class="w-3.5 h-3.5 opacity-60 group-hover:rotate-180 transition-transform duration-200" />
              </router-link>
              <button
                v-else
                type="button"
                :class="[getNavItemClasses(isParentActive(item)), 'group/btn']"
              >
                <span>{{ item.title }}</span>
                <ChevronDown class="w-3.5 h-3.5 opacity-60 group-hover:rotate-180 transition-transform duration-200" />
              </button>

              <!-- Dropdown Menu -->
              <div class="absolute top-full left-0 pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible group-focus-within:opacity-100 group-focus-within:visible transition-all duration-200 z-50">
                <div class="bg-card/95 backdrop-blur-xl border border-border/70 p-3 rounded-2xl shadow-xl min-w-[220px]">
                  <div class="flex flex-col gap-1">
                    <template
                      v-for="child in item.children"
                      :key="String(child.id || child.title)"
                    >
                      <a
                        v-if="isExternalLink(child.url)"
                        :href="child.url || '#'"
                        target="_blank"
                        class="text-xs font-medium text-foreground/75 hover:text-primary hover:bg-primary/5 rounded-lg px-3 py-2 transition-colors"
                      >
                        {{ child.title }}
                      </a>
                      <router-link
                        v-else
                        :to="getInternalUrl(child.url)"
                        :class="[
                          'text-xs font-medium text-foreground/75 hover:text-primary hover:bg-primary/5 rounded-lg px-3 py-2 transition-colors',
                          isMenuItemActive(child) ? '!text-primary !bg-primary/10 font-semibold' : ''
                        ]"
                      >
                        {{ child.title }}
                      </router-link>
                    </template>
                  </div>
                </div>
              </div>
            </div>
            <template v-else>
              <a
                v-if="isExternalLink(item.url)"
                :href="item.url || '#'"
                target="_blank"
                :class="getNavItemClasses(false)"
              >
                {{ item.title }}
              </a>
              <router-link
                v-else
                :to="getInternalUrl(item.url)"
                :class="getNavItemClasses(isMenuItemActive(item))"
              >
                {{ item.title }}
              </router-link>
            </template>
          </template>
        </nav>

        <!-- Desktop Toolbar (Theme, Lang, CTA/Auth) -->
        <div
          v-if="isDesktop"
          class="flex items-center gap-2 pl-4 border-l border-border/40 shrink-0 relative z-30"
        >
          <ThemeToggle />

          <!-- Language Selector -->
          <DropdownMenu>
            <DropdownMenuTrigger
              class="inline-flex items-center gap-1.5 h-9 px-2.5 text-xs font-mono uppercase text-muted-foreground bg-transparent border border-border/40 rounded-xl hover:bg-primary/5 hover:text-foreground transition-all cursor-pointer"
              :aria-label="t('header.languageAria')"
            >
              <Globe class="w-3.5 h-3.5" />
              <span>{{ currentLanguageCode }}</span>
            </DropdownMenuTrigger>
            <DropdownMenuContent
              align="end"
              class="w-40 bg-card/95 backdrop-blur-xl border-border/60 rounded-xl shadow-xl p-1.5"
            >
              <DropdownMenuItem
                v-for="lang in languages"
                :key="lang.code"
                class="flex items-center justify-between px-2.5 py-1.5 text-xs rounded-lg cursor-pointer hover:bg-primary/10 hover:text-primary"
                @click="handleSelectLanguage(lang.code)"
              >
                <span>{{ lang.name }}</span>
                <Check
                  v-if="lang.code === currentLanguageCode"
                  class="w-3.5 h-3.5 text-primary"
                />
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>

          <!-- Primary Console / CTA Button -->
          <router-link
            to="/solutions"
            class="inline-flex items-center justify-center h-9 px-4 text-xs font-semibold text-primary-foreground bg-primary hover:bg-primary/90 rounded-xl shadow-sm shadow-primary/25 hover:shadow-primary/40 hover:scale-[1.02] active:scale-95 transition-all duration-200"
          >
            {{ t('header.getStarted') }}
          </router-link>
        </div>

        <!-- Mobile Menu Toggle -->
        <button
          v-if="!isDesktop"
          type="button"
          class="flex items-center justify-center w-10 h-10 rounded-xl text-foreground bg-card/50 border border-border/60 hover:bg-primary/10 transition-colors"
          :aria-label="t('header.openMenuAria')"
          @click="isOpen = !isOpen"
        >
          <MenuIcon v-if="!isOpen" class="w-5 h-5" />
          <X v-else class="w-5 h-5" />
        </button>
      </div>
    </div>
  </header>

  <!-- Mobile Drawer Menu -->
  <teleport to="body" :disabled="isBuilder">
    <transition name="sareupna-fade">
      <div
        v-if="isOpen && !isDesktop"
        class="fixed inset-0 z-[999] bg-background/95 backdrop-blur-2xl flex flex-col p-6 overflow-y-auto"
      >
        <div class="flex items-center justify-between pb-6 border-b border-border/40">
          <div class="flex flex-col">
            <span class="text-lg font-heading font-black tracking-tight uppercase text-foreground leading-none">
              {{ siteName }}
            </span>
            <span
              v-if="siteTagline"
              class="text-[8px] font-mono tracking-[0.22em] uppercase text-foreground/50 mt-1.5 truncate max-w-[240px]"
            >
              {{ siteTagline }}
            </span>
          </div>
          <button
            type="button"
            class="p-2 text-foreground/70 hover:text-foreground"
            @click="isOpen = false"
          >
            <X class="w-6 h-6" />
          </button>
        </div>

        <nav class="flex flex-col gap-2 py-8 flex-1">
          <template
            v-for="item in navItems"
            :key="'mob-' + String(item.id || item.title)"
          >
            <a
              v-if="isExternalLink(item.url)"
              :href="item.url || '#'"
              target="_blank"
              class="text-base font-semibold text-foreground/90 py-2.5 px-3 rounded-xl hover:bg-primary/10 transition-colors"
              @click="isOpen = false"
            >
              {{ item.title }}
            </a>
            <router-link
              v-else-if="item.url"
              :to="getInternalUrl(item.url)"
              class="text-base font-semibold text-foreground/90 py-2.5 px-3 rounded-xl hover:bg-primary/10 transition-colors"
              @click="isOpen = false"
            >
              {{ item.title }}
            </router-link>
          </template>
        </nav>

        <div class="pt-6 border-t border-border/40 flex items-center justify-between">
          <ThemeToggle />
          <router-link
            to="/solutions"
            class="px-5 py-2.5 text-xs font-semibold text-primary-foreground bg-primary rounded-xl"
            @click="isOpen = false"
          >
            {{ t('header.getStarted') }}
          </router-link>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, inject, watch } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting';
import { useMenu } from '@/modules/Layout/composables/useMenu';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useResponsiveDevice } from '@/shared/composables/useResponsiveDevice';
import { useRoute } from 'vue-router';
import { useLanguage } from '@/shared/composables/useLanguage';
import {
  ThemeToggle,
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/modules/Layout/views/themes/sareupna/ui';
import {
  Check,
  ChevronDown,
  Globe,
  Menu as MenuIcon,
  X,
} from 'lucide-vue-next';
import type { MenuItem } from '@/modules/Layout/types/menu';

const builder = inject('builder', null);
const isBuilder = computed(() => !!builder);

const { getSetting } = useTheme();
const { localizedString } = useLocalizedThemeSetting();
const { menus, fetchMenuByIdentifier } = useMenu();
const { t } = useThemeI18n('sareupna');
const device = useResponsiveDevice();
const route = useRoute();

const { currentLanguageCode, languages, setLanguage, initializeLanguage } = useLanguage();

const isOpen = ref(false);
const isHeaderScrolled = ref(false);
const isHeaderHidden = ref(false);
let lastScrollY = 0;
let scrollTicking = false;

const handleScroll = () => {
  if (scrollTicking || typeof window === 'undefined') return;
  scrollTicking = true;
  window.requestAnimationFrame(() => {
    const currentScrollY = window.scrollY || window.pageYOffset || 0;
    isHeaderScrolled.value = currentScrollY > 20;

    if (currentScrollY > 80) {
      if (currentScrollY > lastScrollY + 5) {
        isHeaderHidden.value = true;
      } else if (currentScrollY < lastScrollY - 5) {
        isHeaderHidden.value = false;
      }
    } else {
      isHeaderHidden.value = false;
    }

    lastScrollY = Math.max(0, currentScrollY);
    scrollTicking = false;
  });
};

const handleSelectLanguage = async (code: string) => {
  await setLanguage(code);
};

const siteName = computed(() => (getSetting('site_title') as string) || (getSetting('site_name') as string) || 'Jejakawan');
const siteLogo = computed(() => (getSetting('brand_logo') as string) || '');
const brandingDisplay = computed(() => (getSetting('branding_display', 'logo_and_text') as string));
const siteTagline = computed(() => {
  return (
    localizedString('header_official_line2') ||
    localizedString('site_tagline') ||
    (getSetting('site_tagline') as string) ||
    t('header.officialLine2') ||
    ''
  );
});

const isDesktop = computed(() => device.value === 'desktop');

const currentMenuLocation = computed(() => {
  return String(getSetting('menu_location_header', 'header') || 'header');
});

const navItems = computed((): MenuItem[] => {
  const menu = menus.value?.header || menus.value?.[currentMenuLocation.value];
  const rawItems = (menu?.items || []) as MenuItem[];
  if (Array.isArray(rawItems) && rawItems.length > 0) {
    return rawItems;
  }
  // Fallback items if menu not configured
  return [
    { id: '1', title: t('header.home'), url: '/' },
    { id: '2', title: t('header.solutions'), url: '/solutions' },
    { id: '3', title: t('header.pricing'), url: '/pricing' },
    { id: '4', title: t('header.blog'), url: '/blog' },
    { id: '5', title: t('header.contact'), url: '/contact' },
  ];
});

const getNavItemClasses = (isActive: boolean) => {
  const base = 'px-3.5 py-1.5 rounded-full text-xs font-medium tracking-wide transition-all duration-200 inline-flex items-center gap-1.5';
  if (isActive) {
    return `${base} bg-primary/12 text-primary font-semibold border border-primary/30 shadow-xs`;
  }
  return `${base} text-foreground/80 hover:text-foreground hover:bg-primary/5 hover:border hover:border-border/60`;
};

const isExternalLink = (url?: string | null): boolean => {
  if (!url) return false;
  return url.startsWith('http://') || url.startsWith('https://');
};

const getInternalUrl = (url?: string | null): string => {
  if (!url) return '/';
  return url.startsWith('/') ? url : `/${url}`;
};

const isMenuItemActive = (item: MenuItem): boolean => {
  if (!item.url) return false;
  const target = getInternalUrl(item.url);
  if (target === '/') return route.path === '/';
  return route.path === target || route.path.startsWith(`${target}/`);
};

const isParentActive = (item: MenuItem): boolean => {
  if (isMenuItemActive(item)) return true;
  if (item.children && Array.isArray(item.children)) {
    return item.children.some((c) => isMenuItemActive(c));
  }
  return false;
};

watch(currentMenuLocation, async (newLoc) => {
  if (newLoc) {
    await fetchMenuByIdentifier(newLoc, 'header');
  }
}, { immediate: true });

onMounted(() => {
  initializeLanguage();
  if (typeof window !== 'undefined') {
    window.addEventListener('scroll', handleScroll, { passive: true });
  }
});

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('scroll', handleScroll);
  }
});
</script>

<style scoped>
.sareupna-fade-enter-active,
.sareupna-fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.sareupna-fade-enter-from,
.sareupna-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
