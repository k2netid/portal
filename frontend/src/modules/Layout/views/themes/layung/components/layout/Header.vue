<template>
  <header
    data-ja-customizer-target="header"
    :class="[
      headerSticky ? 'sticky top-0 z-[100]' : 'relative z-40',
      'w-full border-b border-border/80 transition-colors shadow-sm overflow-visible',
      headerStyleClasses,
    ]"
  >
    <!-- Top NOC Status Ticker Bar -->
    <div class="hidden md:block bg-slate-950 text-slate-300 text-[11px] py-1.5 px-4 sm:px-6 lg:px-8 border-b border-slate-800/80 font-mono">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-4">
          <span class="inline-flex items-center gap-1.5 text-emerald-400 font-semibold">
            <span class="layung-status-dot" />
            {{ tt('header.nocOperational', 'Semua Node Normal') }}
          </span>
          <span class="text-slate-600">|</span>
          <span>Latensi Inti: <strong class="text-white">{{ displayNocLatency }}</strong></span>
          <span class="text-slate-600">|</span>
          <span>Backbone: <strong class="text-white">{{ displayBackboneCapacity }}</strong></span>
          <span class="text-slate-600">|</span>
          <span class="text-slate-400">{{ displayAsn }}</span>
        </div>
        <div class="flex items-center gap-4 text-xs font-sans">
          <a
            :href="nocDialHref"
            class="hover:text-orange-400 font-semibold transition-colors flex items-center gap-1"
          >
            <Headset class="w-3.5 h-3.5 text-orange-500" />
            <span>NOC 24/7: {{ displayNocPhone }}</span>
          </a>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 overflow-visible">
      <div class="flex items-center justify-between h-20 overflow-visible">
        <router-link
          to="/"
          class="flex items-center gap-3.5 group focus:outline-none shrink-0"
        >
          <div
            v-if="siteLogo && brandingDisplay !== 'text_only'"
            class="h-10 w-auto flex items-center"
          >
            <img
              :src="siteLogo"
              :alt="brandingDisplay === 'logo_only' ? displayCompanyName : ''"
              class="h-10 w-auto object-contain"
            >
          </div>
          <div
            v-else-if="brandingDisplay !== 'text_only'"
            class="w-11 h-11 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-600 text-white flex items-center justify-center font-black text-xl shadow-md shadow-orange-500/20 group-hover:scale-105 transition-transform"
            aria-hidden="true"
          >
            <Activity class="w-6 h-6" />
          </div>
          <div
            v-if="brandingDisplay !== 'logo_only'"
            class="flex flex-col"
          >
            <span class="text-lg sm:text-xl font-extrabold tracking-tight text-foreground font-heading leading-tight group-hover:text-primary transition-colors">
              {{ displayCompanyName }}
            </span>
            <span class="text-[11px] text-muted-foreground font-semibold flex items-center gap-1.5 mt-0.5 font-mono">
              <span class="inline-block w-2 h-2 rounded-full bg-emerald-500" />
              {{ displaySla }}
            </span>
          </div>
        </router-link>

        <nav
          v-if="isDesktop"
          data-ja-customizer-target="nav"
          class="hidden lg:flex items-center gap-1 overflow-visible relative z-[105]"
          :aria-label="tt('header.navAria', 'Navigasi utama')"
        >
          <template
            v-for="(item, idx) in navItems"
            :key="String(item.id || item.title || item.url)"
          >
            <div
              v-if="item.children && item.children.length > 0"
              class="relative overflow-visible group/nav"
            >
              <a
                v-if="isExternalLink(item.url)"
                :href="item.url || '#'"
                target="_blank"
                rel="noopener noreferrer"
                class="px-3 py-2 rounded-xl text-sm font-semibold transition-colors inline-flex items-center gap-1.5 focus:outline-none whitespace-nowrap shrink-0"
                :class="isNavItemActive(item, route) ? 'text-primary bg-primary/10' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
              >
                <span>{{ item.title }}</span>
                <ChevronDown class="w-3.5 h-3.5 opacity-60 transition-transform duration-200 group-hover/nav:rotate-180 group-hover/nav:text-primary" />
              </a>
              <router-link
                v-else-if="item.url"
                :to="getInternalUrl(item.url)"
                class="px-3 py-2 rounded-xl text-sm font-semibold transition-colors inline-flex items-center gap-1.5 focus:outline-none whitespace-nowrap shrink-0"
                :class="isNavItemActive(item, route) ? 'text-primary bg-primary/10' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
              >
                <span>{{ item.title }}</span>
                <ChevronDown class="w-3.5 h-3.5 opacity-60 transition-transform duration-200 group-hover/nav:rotate-180 group-hover/nav:text-primary" />
              </router-link>
              <button
                v-else
                type="button"
                class="px-3 py-2 rounded-xl text-sm font-semibold transition-colors inline-flex items-center gap-1.5 focus:outline-none whitespace-nowrap shrink-0"
                :class="isNavItemActive(item, route) ? 'text-primary bg-primary/10' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
              >
                <span>{{ item.title }}</span>
                <ChevronDown class="w-3.5 h-3.5 opacity-60 transition-transform duration-200 group-hover/nav:rotate-180 group-hover/nav:text-primary" />
              </button>

              <div
                class="absolute top-full pt-2 z-[120] opacity-0 invisible -translate-y-1 pointer-events-none group-hover/nav:opacity-100 group-hover/nav:visible group-hover/nav:translate-y-0 group-hover/nav:pointer-events-auto group-focus-within/nav:opacity-100 group-focus-within/nav:visible group-focus-within/nav:translate-y-0 group-focus-within/nav:pointer-events-auto transition-all duration-200"
                :class="idx >= 3 ? 'right-0 origin-top-right' : 'left-0 origin-top-left'"
              >
                <div class="absolute -top-3 inset-x-0 h-4 bg-transparent" />
                <div class="layung-panel p-2 min-w-[280px] shadow-2xl border border-border/80 bg-card rounded-2xl space-y-1">
                  <template
                    v-for="child in item.children"
                    :key="String(child.id || child.title || child.url)"
                  >
                    <a
                      v-if="isExternalLink(child.url)"
                      :href="child.url || '#'"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-muted/70 transition-colors group/item"
                    >
                      <div>
                        <div class="text-xs font-bold text-foreground group-hover/item:text-primary transition-colors">
                          {{ child.title }}
                        </div>
                        <div
                          v-if="child.description"
                          class="text-[11px] text-muted-foreground line-clamp-1"
                        >
                          {{ child.description }}
                        </div>
                      </div>
                    </a>
                    <router-link
                      v-else
                      :to="getInternalUrl(child.url)"
                      active-class=""
                      exact-active-class=""
                      class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-muted/70 transition-colors group/item focus:outline-none"
                      :class="isDropdownChildActive(child, item.children || [], route) ? '!bg-primary/10' : ''"
                    >
                      <div>
                        <div class="text-xs font-bold text-foreground group-hover/item:text-primary transition-colors">
                          {{ child.title }}
                        </div>
                        <div
                          v-if="child.description"
                          class="text-[11px] text-muted-foreground line-clamp-1"
                        >
                          {{ child.description }}
                        </div>
                      </div>
                    </router-link>
                  </template>
                </div>
              </div>
            </div>

            <a
              v-else-if="isExternalLink(item.url)"
              :href="item.url || '#'"
              target="_blank"
              rel="noopener noreferrer"
              class="px-3 py-2 rounded-xl text-sm font-semibold text-muted-foreground hover:text-foreground hover:bg-muted/60 transition-colors inline-flex items-center gap-1.5 whitespace-nowrap shrink-0"
            >
              <span>{{ item.title }}</span>
            </a>
            <router-link
              v-else
              :to="getInternalUrl(item.url)"
              class="px-3 py-2 rounded-xl text-sm font-semibold text-muted-foreground hover:text-foreground hover:bg-muted/60 transition-colors inline-flex items-center gap-1.5 whitespace-nowrap shrink-0"
              :class="isNavItemActive(item, route) ? '!text-primary !bg-primary/10 !font-bold' : ''"
            >
              <span>{{ item.title }}</span>
            </router-link>
          </template>
        </nav>

        <div class="flex items-center gap-2 sm:gap-3 overflow-visible relative z-[105]">
          <DropdownMenu v-if="isDesktop">
            <DropdownMenuTrigger
              class="px-2.5 py-1.5 rounded-xl text-xs font-bold border border-border/80 text-muted-foreground hover:text-foreground hover:bg-muted transition-colors inline-flex items-center gap-1.5 focus:outline-none font-mono"
              :aria-label="tt('header.selectLanguage', 'Bahasa')"
            >
              <Globe class="w-3.5 h-3.5 text-primary" />
              <span class="uppercase">{{ currentLanguageCode }}</span>
              <ChevronDown class="w-3 h-3 opacity-50" />
            </DropdownMenuTrigger>
            <DropdownMenuContent
              align="end"
              :side-offset="10"
              class="w-52"
            >
              <DropdownMenuItem
                v-for="lang in languages"
                :key="lang.code"
                class="flex items-center gap-3 cursor-pointer"
                :class="{ 'bg-primary/5 text-primary font-semibold': currentLanguageCode === lang.code }"
                @click.stop="handleSelectLanguage(lang.code)"
              >
                <span class="text-base leading-none">{{ getLanguageFlag(lang) }}</span>
                <span class="flex-1 text-sm">{{ lang.native_name || lang.name }}</span>
                <Check
                  v-if="currentLanguageCode === lang.code"
                  class="w-4 h-4 text-primary shrink-0"
                />
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>

          <ThemeToggle />

          <Button
            v-if="memberEnabled && memberStore.isAuthenticated"
            as="router-link"
            to="/member/profile"
            variant="outline"
            size="sm"
            class="hidden md:inline-flex"
          >
            {{ memberStore.member?.name || tt('header.account', 'Akun Klien') }}
          </Button>
          <Button
            v-else-if="memberEnabled"
            as="a"
            :href="loginUrl"
            variant="outline"
            size="sm"
            class="hidden md:inline-flex font-semibold"
          >
            {{ loginLabel }}
          </Button>

          <Button
            v-if="ctaUrl"
            as="a"
            :href="ctaUrl"
            variant="primary"
            size="sm"
            class="hidden sm:inline-flex font-bold"
          >
            {{ ctaText }}
          </Button>

          <button
            v-if="!isDesktop"
            type="button"
            class="lg:hidden p-2 rounded-xl text-muted-foreground hover:text-foreground hover:bg-muted transition-colors focus:outline-none"
            :aria-expanded="mobileMenuOpen"
            :aria-label="tt('header.openMenuAria', 'Buka menu')"
            @click="mobileMenuOpen = !mobileMenuOpen"
          >
            <Menu
              v-if="!mobileMenuOpen"
              class="w-6 h-6"
            />
            <X
              v-else
              class="w-6 h-6"
            />
          </button>
        </div>
      </div>
    </div>

    <teleport
      to="body"
      :disabled="isBuilder"
    >
      <transition name="layung-mobile-menu">
        <div
          v-if="mobileMenuOpen && !isDesktop"
          class="fixed inset-0 z-[9999] flex flex-col bg-card"
        >
          <div class="flex items-center justify-between h-16 px-4 border-b border-border/80 bg-slate-950 text-white">
            <span class="font-heading font-bold">{{ displayCompanyName }}</span>
            <button
              type="button"
              class="p-2 rounded-xl text-slate-300 hover:bg-slate-800"
              :aria-label="tt('header.closeMenuAria', 'Tutup menu')"
              @click="mobileMenuOpen = false"
            >
              <X class="w-6 h-6" />
            </button>
          </div>

          <div class="flex-1 overflow-y-auto px-4 py-6 space-y-4">
            <nav class="flex flex-col space-y-1">
              <template
                v-for="item in navItems"
                :key="String(item.id || item.title || item.url)"
              >
                <div
                  v-if="item.children && item.children.length > 0"
                  class="space-y-1"
                >
                  <button
                    type="button"
                    class="w-full px-3 py-2.5 text-left text-sm font-bold text-foreground flex items-center justify-between rounded-xl hover:bg-muted"
                    @click="toggleMobileSubmenu(item)"
                  >
                    <span>{{ item.title }}</span>
                    <ChevronDown
                      class="w-4 h-4 transition-transform"
                      :class="{ 'rotate-180': isMobileSubmenuOpen(item) }"
                    />
                  </button>
                  <div
                    v-if="isMobileSubmenuOpen(item)"
                    class="pl-3 space-y-0.5"
                  >
                    <template
                      v-for="child in item.children"
                      :key="String(child.id || child.title || child.url)"
                    >
                      <a
                        v-if="isExternalLink(child.url)"
                        :href="child.url || '#'"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="block pl-3 pr-3 py-2.5 rounded-xl text-sm font-semibold text-foreground hover:bg-muted"
                        @click="mobileMenuOpen = false"
                      >
                        {{ child.title }}
                      </a>
                      <router-link
                        v-else
                        :to="getInternalUrl(child.url)"
                        active-class=""
                        exact-active-class=""
                        class="block pl-3 pr-3 py-2.5 rounded-xl text-sm font-semibold text-foreground hover:bg-muted focus:outline-none"
                        :class="isDropdownChildActive(child, item.children || [], route) ? '!text-primary !bg-primary/10 font-bold' : ''"
                        @click="mobileMenuOpen = false"
                      >
                        {{ child.title }}
                      </router-link>
                    </template>
                  </div>
                </div>

                <a
                  v-else-if="isExternalLink(item.url)"
                  :href="item.url || '#'"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold text-foreground hover:bg-muted"
                  @click="mobileMenuOpen = false"
                >
                  {{ item.title }}
                </a>
                <router-link
                  v-else
                  :to="getInternalUrl(item.url)"
                  class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold text-foreground hover:bg-muted"
                  :class="isNavItemActive(item, route) ? '!text-primary !bg-primary/10 font-bold' : ''"
                  @click="mobileMenuOpen = false"
                >
                  {{ item.title }}
                </router-link>
              </template>
            </nav>

            <div class="pt-4 border-t border-border/60 flex flex-col gap-3">
              <div class="flex items-center justify-between px-1">
                <span class="text-xs font-semibold text-muted-foreground">{{ tt('header.selectLanguage', 'Bahasa:') }}</span>
                <div class="flex flex-wrap gap-1.5 justify-end font-mono">
                  <button
                    v-for="lang in languages"
                    :key="lang.code"
                    type="button"
                    class="px-2.5 py-1 text-xs rounded-md border font-semibold uppercase"
                    :class="currentLanguageCode === lang.code ? 'bg-primary text-white font-bold border-primary' : 'border-border text-muted-foreground'"
                    @click="handleSelectLanguage(lang.code)"
                  >
                    {{ lang.code }}
                  </button>
                </div>
              </div>

              <Button
                v-if="ctaUrl"
                as="a"
                :href="ctaUrl"
                variant="primary"
                size="md"
                class="w-full font-bold"
                @click="mobileMenuOpen = false"
              >
                {{ ctaText }}
              </Button>

              <Button
                v-if="memberEnabled && !memberStore.isAuthenticated"
                as="a"
                :href="loginUrl"
                variant="outline"
                size="md"
                class="w-full"
                @click="mobileMenuOpen = false"
              >
                {{ loginLabel }}
              </Button>
            </div>
          </div>
        </div>
      </transition>
    </teleport>
  </header>
</template>

<script setup lang="ts">
import { ref, computed, watch, inject, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { Globe, Menu, X, ChevronDown, Check, Activity, Headset } from 'lucide-vue-next';
import {
  Button,
  ThemeToggle,
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/modules/Layout/views/themes/layung/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useMenu } from '@/modules/Layout/composables/useMenu';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useLanguage } from '@/shared/composables/useLanguage';
import { useResponsiveDevice } from '@/shared/composables/useResponsiveDevice';
import { useMemberStore } from '@/modules/Member/stores/member';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useLayungIdentity } from '@/modules/Layout/views/themes/layung/composables/useLayungIdentity';
import type { MenuItem } from '@/modules/Layout/types/menu';
import {
  isExternalLink,
  getInternalUrl,
  isDropdownChildActive,
  isMenuItemActive as isNavItemActive,
} from '@/modules/Layout/utils/menuUrl';

const builder = inject('builder', null);
const isBuilder = computed(() => !!builder);

const route = useRoute();
const { locale } = useI18n({ useScope: 'global' });
const { t: tt } = useThemeI18n('layung');
const { getSetting } = useTheme();
const { menus, fetchMenuByIdentifier } = useMenu();
const { setLanguage, initializeLanguage, currentLanguageCode, languages, getLanguageFlag } = useLanguage();
const device = useResponsiveDevice();
const memberStore = useMemberStore();
const authStore = useAuthStore();
const systemStore = useSystemStore();
const {
  displayCompanyName,
  displayAsn,
  displaySla,
  displayNocLatency,
  displayBackboneCapacity,
  displayNocPhone,
  nocDialHref,
} = useLayungIdentity();

const isDesktop = computed(() => device.value === 'desktop');
const mobileMenuOpen = ref(false);
const mobileOpenSubmenus = ref<Set<string>>(new Set());

const headerSticky = computed(() => getSetting('header_sticky', true) !== false);
const headerStyle = computed(() => String(getSetting('header_style', 'glass') || 'glass'));
const brandingDisplay = computed(() => String(getSetting('branding_display', 'both') || 'both'));

const headerStyleClasses = computed(() => {
  switch (headerStyle.value) {
    case 'solid':
      return 'bg-card border-border';
    case 'transparent':
      return 'bg-transparent border-transparent shadow-none';
    default:
      return 'bg-card/95 backdrop-blur-md';
  }
});

const siteLogo = computed(() => {
  const custom = getSetting('brand_logo', '');
  if (custom && typeof custom === 'string') return custom;
  return (systemStore.settings as { site_logo?: string } | undefined)?.site_logo || '';
});

const memberEnabled = computed(() => Boolean(getSetting('enable_members', true)));

const loginUrl = computed(() => {
  const raw = getSetting('header_login_url', '/member/login');
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/member/login';
});

const loginLabel = computed(() => {
  const raw = getSetting('header_login_label', '');
  if (typeof raw === 'string' && raw.trim()) return raw.trim();
  return tt('header.signIn', 'Portal Klien');
});

const ctaText = computed(() => {
  const raw = getSetting('header_cta_text', '');
  if (typeof raw === 'string' && raw.trim()) return raw.trim();
  return tt('header.getStarted', 'Minta Penawaran');
});

const ctaUrl = computed(() => {
  const raw = getSetting('header_cta_url', '/contact');
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/contact';
});

const handleSelectLanguage = async (code: string) => {
  await setLanguage(code);
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent('language-changed', { detail: { code: locale.value } }));
  }
};

const normalizeMenuSetting = (value: unknown, fallback: string): string => {
  if (value === null || value === undefined || value === '' || value === 'none') return fallback;
  return String(value);
};

const currentMenuLocation = computed(() => {
  const routeMenu = route.meta?.menu_location as string | undefined;
  if (routeMenu) return routeMenu;
  return normalizeMenuSetting(getSetting('menu_location_header', 'header'), 'header');
});

const filterMenuItems = (items: MenuItem[]): MenuItem[] => {
  if (!Array.isArray(items)) return [];
  return items
    .filter((item) => {
      const meta = item.metadata as Record<string, unknown> | undefined;
      if (meta) {
        if (meta.guest_only && authStore.isAuthenticated) return false;
        if (meta.requires_auth && !authStore.isAuthenticated) return false;
        if (
          meta.required_permission
          && (!authStore.isAuthenticated || !authStore.hasPermission(String(meta.required_permission)))
        ) {
          return false;
        }
      }
      return true;
    })
    .map((item) => {
      let title = item.title;
      const meta = item.metadata as Record<string, unknown> | undefined;
      if (meta) {
        const currentLang = locale.value;
        if (currentLang === 'en' && meta.title_en) title = String(meta.title_en);
        else if (currentLang === 'id' && meta.title_id) title = String(meta.title_id);
      }
      const mappedItem: MenuItem = { ...item, title };
      if (item.children && item.children.length > 0) {
        mappedItem.children = filterMenuItems(item.children);
      }
      return mappedItem;
    });
};

const defaultNavItems = computed((): MenuItem[] => [
  { id: 'ly-nav-home', title: tt('header.home', 'Beranda'), url: '/', type: 'custom', sort_order: 0 },
  {
    id: 'ly-nav-services',
    title: tt('header.services', 'Konektivitas Fiber'),
    url: '/services',
    type: 'custom',
    sort_order: 1,
    children: [
      { id: 'ly-nav-dia', title: 'Dedicated Internet (DIA 1:1)', url: '/services#dia', type: 'custom', description: 'Bandwidth simetris murni & IP Publik /29' },
      { id: 'ly-nav-dark', title: 'Dark Fiber & Metro Ethernet', url: '/services#dark-fiber', type: 'custom', description: 'Jalur serat optik privat antar gedung' },
      { id: 'ly-nav-dc', title: 'Colocation & Data Center', url: '/about#colocation', type: 'custom', description: 'Tier-3 data center interconnect' },
    ],
  },
  {
    id: 'ly-nav-solusi',
    title: tt('header.solusi', 'Managed IT & SOC'),
    url: '/solusi',
    type: 'custom',
    sort_order: 2,
    children: [
      { id: 'ly-nav-soc', title: '24/7 Cyber Security SOC', url: '/solusi#soc', type: 'custom', description: 'Mitigasi DDoS & Next-Gen Firewall' },
      { id: 'ly-nav-sdwan', title: 'Managed SD-WAN & Multi-Cloud', url: '/solusi#sdwan', type: 'custom', description: 'Direct connect AWS, GCP, Azure' },
      { id: 'ly-nav-tim', title: tt('header.tim', 'Tim Engineer'), url: '/tim', type: 'custom', description: 'Konsultasi arsitektur jaringan' },
    ],
  },
  {
    id: 'ly-nav-pricing',
    title: tt('header.pricing', 'Paket & SLA'),
    url: '/pricing',
    type: 'custom',
    sort_order: 3,
    children: [
      { id: 'ly-nav-bandwidth', title: 'Paket Bandwidth & Harga', url: '/pricing#packages', type: 'custom', description: 'SME hingga Enterprise' },
      { id: 'ly-nav-sla', title: tt('header.achievement', 'Jaminan SLA'), url: '/achievement', type: 'custom', description: '99.999% & ISO 27001' },
      { id: 'ly-nav-career', title: tt('header.career', 'Karir NOC'), url: '/career-center', type: 'custom', description: 'Lowongan engineer & DevOps' },
    ],
  },
  { id: 'ly-nav-blog', title: tt('header.blog', 'Warta'), url: '/blog', type: 'custom', sort_order: 4 },
  { id: 'ly-nav-contact', title: tt('header.contact', 'Kontak NOC'), url: '/contact', type: 'custom', sort_order: 5 },
]);

const navItems = computed<MenuItem[]>(() => {
  const menu = menus.value.header || menus.value[currentMenuLocation.value];
  const filtered = filterMenuItems((menu?.items || []) as MenuItem[]);
  return filtered.length > 0 ? filtered : filterMenuItems(defaultNavItems.value);
});

const getMobileMenuKey = (item: MenuItem): string => String(item.id || item.title || item.url || '');
const isMobileSubmenuOpen = (item: MenuItem) => mobileOpenSubmenus.value.has(getMobileMenuKey(item));
const toggleMobileSubmenu = (item: MenuItem) => {
  const key = getMobileMenuKey(item);
  const next = new Set(mobileOpenSubmenus.value);
  if (next.has(key)) next.delete(key);
  else next.add(key);
  mobileOpenSubmenus.value = next;
};

watch(mobileMenuOpen, (open) => {
  if (!isDesktop.value && !isBuilder.value) {
    document.body.style.overflow = open ? 'hidden' : '';
  } else {
    document.body.style.overflow = '';
  }
});

watch(() => route.fullPath, () => {
  mobileMenuOpen.value = false;
  mobileOpenSubmenus.value = new Set();
  document.body.style.overflow = '';
});

const menuFetched = ref<Set<string>>(new Set());
watch(currentMenuLocation, async (newLoc) => {
  if (!newLoc || menuFetched.value.has(newLoc)) return;
  menuFetched.value.add(newLoc);
  await fetchMenuByIdentifier(newLoc, 'header');
}, { immediate: true });

onMounted(() => initializeLanguage());

onUnmounted(() => {
  document.body.style.overflow = '';
});
</script>

<style scoped>
.layung-mobile-menu-enter-active { transition: opacity 0.25s ease; }
.layung-mobile-menu-leave-active { transition: opacity 0.2s ease; }
.layung-mobile-menu-enter-from,
.layung-mobile-menu-leave-to { opacity: 0; }
</style>
