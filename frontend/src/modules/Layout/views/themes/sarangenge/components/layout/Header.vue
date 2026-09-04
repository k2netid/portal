<template>
  <header
    data-ja-customizer-target="header"
    :class="[
      'relative z-[100]',
      'w-full border-b border-border/80 transition-colors shadow-sm overflow-visible',
      headerStyleClasses,
    ]"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 overflow-visible">
      <div class="flex items-center justify-between h-20 overflow-visible">
        <!-- Branding -->
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
              :alt="brandingDisplay === 'logo_only' ? displaySchoolName : ''"
              class="h-10 w-auto object-contain"
            >
          </div>
          <div
            v-else-if="brandingDisplay !== 'text_only'"
            class="w-11 h-11 rounded-2xl bg-[#0f172a] text-amber-400 flex items-center justify-center font-black text-xl shadow-md border border-slate-700/60 group-hover:scale-105 transition-transform"
            aria-hidden="true"
          >
            {{ displaySchoolName.charAt(0).toUpperCase() }}
          </div>
          <div
            v-if="brandingDisplay !== 'logo_only'"
            class="flex flex-col"
          >
            <span class="text-lg sm:text-xl font-extrabold tracking-tight text-foreground font-heading leading-tight group-hover:text-primary transition-colors">
              {{ displaySchoolName }}
            </span>
            <span class="text-[11px] text-muted-foreground font-semibold flex items-center gap-1.5 mt-0.5">
              <span class="inline-block w-2 h-2 rounded-full bg-emerald-500" />
              {{ displayAccreditation }} · {{ displayNpsn }}
            </span>
          </div>
        </router-link>

        <!-- Desktop Navigation -->
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
                <div class="sarangenge-panel p-2 min-w-[240px] shadow-2xl border border-border/80 bg-card rounded-2xl space-y-1">
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

        <!-- Right utilities -->
        <div class="flex items-center gap-2 sm:gap-3 overflow-visible relative z-[105]">
          <DropdownMenu v-if="isDesktop">
            <DropdownMenuTrigger
              class="px-2.5 py-1.5 rounded-xl text-xs font-bold border border-border/80 text-muted-foreground hover:text-foreground hover:bg-muted transition-colors inline-flex items-center gap-1.5 focus:outline-none"
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
            {{ memberStore.member?.name || tt('header.account', 'Akun Siswa') }}
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

          <a
            v-if="ctaUrl"
            :href="ctaUrl"
            :target="ctaUrl.startsWith('http') ? '_blank' : undefined"
            :rel="ctaUrl.startsWith('http') ? 'noopener noreferrer' : undefined"
            class="hidden sm:inline-flex items-center justify-center font-bold !bg-amber-500 hover:!bg-amber-400 !text-slate-950 shadow-md shadow-amber-500/20 px-3.5 py-1.5 text-xs rounded-[var(--sarangenge-radius-sm,0.85rem)] transition-all"
          >
            {{ ctaText }}
          </a>

          <button
            v-if="!isDesktop"
            type="button"
            class="p-2 rounded-xl text-muted-foreground hover:text-foreground hover:bg-muted transition-colors focus:outline-none"
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

    <!-- Mobile drawer (teleported — escapes sticky overflow/stacking) -->
    <teleport
      to="body"
      :disabled="isBuilder"
    >
      <transition name="sarangenge-mobile-menu">
        <div
          v-if="mobileMenuOpen && !isDesktop"
          class="fixed inset-0 z-[9999] flex flex-col bg-card"
        >
          <div class="flex items-center justify-between h-16 px-4 border-b border-border/80">
            <span class="font-heading font-bold text-foreground">{{ displaySchoolName }}</span>
            <button
              type="button"
              class="p-2 rounded-xl text-muted-foreground hover:bg-muted"
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
                <div class="flex flex-wrap gap-1.5 justify-end">
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
                class="w-full font-bold !bg-amber-500 hover:!bg-amber-400 !text-slate-950 border-none shadow-md shadow-amber-500/20"
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
import { Globe, Menu, X, ChevronDown, Check } from 'lucide-vue-next';
import {
  Button,
  ThemeToggle,
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/modules/Layout/views/themes/sarangenge/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useMenu } from '@/modules/Layout/composables/useMenu';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useLanguage } from '@/shared/composables/useLanguage';
import { useResponsiveDevice } from '@/shared/composables/useResponsiveDevice';
import { useMemberStore } from '@/modules/Member/stores/member';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
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
const { t: tt } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();
const { menus, fetchMenuByIdentifier } = useMenu();
const { setLanguage, initializeLanguage, currentLanguageCode, languages, getLanguageFlag } = useLanguage();
const device = useResponsiveDevice();
const memberStore = useMemberStore();
const authStore = useAuthStore();
const systemStore = useSystemStore();
const { displaySchoolName, displayAccreditation, displayNpsn, ppdbPortalUrl } = useSarangengeIdentity();

const isDesktop = computed(() => device.value === 'desktop');
const mobileMenuOpen = ref(false);
const mobileOpenSubmenus = ref<Set<string>>(new Set());

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
  return tt('header.signIn', 'Masuk Portal');
});

const ctaText = computed(() => {
  const raw = getSetting('header_cta_text', '');
  if (typeof raw === 'string' && raw.trim()) return raw.trim();
  return tt('header.getStarted', 'PPDB Jabar');
});

const ctaUrl = computed(() => {
  const raw = getSetting('header_cta_url', '');
  return typeof raw === 'string' && raw.trim() ? raw.trim() : ppdbPortalUrl.value;
});

const handleSelectLanguage = async (code: string) => {
  await setLanguage(code);
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent('language-changed', { detail: { code: locale.value } }));
  }
};

const normalizeMenuSetting = (value: unknown, fallback: string): string => {
  if (value === null || value === undefined || value === '' || value === 'none') {
    return fallback;
  }
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
  { id: 'sg-nav-home', title: tt('header.home', 'Beranda'), url: '/', type: 'custom', sort_order: 0 },
  {
    id: 'sg-nav-about',
    title: tt('header.about', 'Tentang Kami'),
    url: '/about',
    type: 'custom',
    sort_order: 1,
    children: [
      { id: 'sg-nav-about-history', title: 'Visi & Sejarah', url: '/about#visi', type: 'custom', description: 'Falsafah dan keunggulan sekolah' },
      { id: 'sg-nav-about-facilities', title: 'Fasilitas & Bengkel', url: '/facilities', type: 'custom', description: 'Laboratorium & bengkel kejuruan' },
      { id: 'sg-nav-about-team', title: tt('header.teachers', 'Guru & Staf'), url: '/tim', type: 'custom', description: 'Pendidik bersertifikasi industri' },
      { id: 'sg-nav-about-achieve', title: tt('header.achievements', 'Prestasi Siswa'), url: '/achievement', type: 'custom', description: 'Medali LKS & juara nasional' },
    ],
  },
  {
    id: 'sg-nav-programs',
    title: 'Program Keahlian',
    url: '/programs',
    type: 'custom',
    sort_order: 2,
    children: [
      { id: 'sg-nav-prog-dpib', title: 'DPIB (Desain Bangunan)', url: '/programs#dpib', type: 'custom', description: 'Arsitektur & Pemodelan BIM' },
      { id: 'sg-nav-prog-titl', title: 'TITL (Teknik Listrik)', url: '/programs#titl', type: 'custom', description: 'Ketenagalistrikan & Otomasi PLC' },
      { id: 'sg-nav-prog-tpm', title: 'TPM (Teknik Pemesinan)', url: '/programs#tpm', type: 'custom', description: 'CNC Milling & Bubut Presisi' },
      { id: 'sg-nav-prog-tkro', title: 'TKRO (Teknik Otomotif)', url: '/programs#tkro', type: 'custom', description: 'Perawatan Kendaraan Ringan' },
      { id: 'sg-nav-prog-tav', title: 'TAV (Audio Video)', url: '/programs#tav', type: 'custom', description: 'Elektronika & Smart IoT' },
      { id: 'sg-nav-prog-tflm', title: 'TFLM (Pengelasan)', url: '/programs#tflm', type: 'custom', description: 'Fabrikasi Logam & Las Industri' },
    ],
  },
  {
    id: 'sg-nav-bkk',
    title: 'BKK & Karir',
    url: '/career',
    type: 'custom',
    sort_order: 3,
  },
  { id: 'sg-nav-blog', title: tt('header.blog', 'Warta'), url: '/blog', type: 'custom', sort_order: 4 },
  { id: 'sg-nav-ppdb', title: 'PPDB Jabar', url: ppdbPortalUrl.value, type: 'custom', sort_order: 5 },
  { id: 'sg-nav-contact', title: tt('header.contact', 'Kontak'), url: '/contact', type: 'custom', sort_order: 6 },
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

onMounted(() => {
  initializeLanguage();
});

onUnmounted(() => {
  document.body.style.overflow = '';
});
</script>

<style scoped>
.sarangenge-mobile-menu-enter-active { transition: opacity 0.25s ease; }
.sarangenge-mobile-menu-leave-active { transition: opacity 0.2s ease; }
.sarangenge-mobile-menu-enter-from,
.sarangenge-mobile-menu-leave-to { opacity: 0; }
</style>
