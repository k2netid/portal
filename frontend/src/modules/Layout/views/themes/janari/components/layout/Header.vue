<template>
  <header
    ref="headerRef"
    data-ja-customizer-target="header"
    :class="[
      headerSticky ? 'fixed top-0 left-0 w-full z-[100] bg-background/80 backdrop-blur-xl border-b border-border/20 shadow-sm' : 'relative z-40',
      headerStyleClasses
    ]"
  >
    <!-- Main Header Container -->
    <div class="container mx-auto px-4 md:px-8 relative z-20">
      <div class="flex items-center justify-between h-14 md:h-16 transition-all duration-500">
        <!-- Branding: Logo + Name + Official (always horizontal) -->
        <router-link
          to="/"
          class="branding-link flex items-center gap-3 group shrink-0"
        >
          <img 
            v-if="siteLogo && brandingDisplay !== 'text_only'" 
            :src="siteLogo" 
            class="h-8 w-auto object-contain brightness-100 group-hover:brightness-110 transition-all duration-300" 
            :alt="brandingDisplay === 'logo_only' ? siteName : ''"
            :aria-hidden="brandingDisplay === 'logo_only' ? undefined : 'true'"
            width="160"
            height="32"
            loading="eager"
            fetchpriority="high"
            decoding="async"
            sizes="160px"
          >
          <div 
            v-else-if="brandingDisplay !== 'text_only'"
            class="w-7 h-7 bg-foreground flex items-center justify-center text-background font-black text-sm shrink-0"
          >
            {{ siteName.substring(0, 1).toUpperCase() }}
          </div>
          <template v-if="brandingDisplay !== 'logo_only'">
            <span class="text-lg md:text-xl font-heading font-black tracking-tight uppercase text-foreground leading-none whitespace-nowrap">
              {{ siteName }}
            </span>
            <div class="h-5 w-px bg-foreground/20 hidden sm:block" />
            <span class="text-[7px] font-black tracking-[0.35em] uppercase text-foreground/40 leading-tight whitespace-nowrap hidden sm:block">
              {{ officialLine1 }}<br>{{ officialLine2 }}
            </span>
          </template>
        </router-link>

        <!-- Desktop Nav -->
        <nav
          v-if="isDesktop"
          data-ja-customizer-target="nav"
          class="flex items-center ml-auto pr-8"
        >
          <template
            v-for="(item, index) in navItems"
            :key="String(item.id || item.title)"
          >
            <span
              v-if="index > 0"
              class="text-foreground mx-1"
            >|</span>
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
                <span class="relative z-10">{{ item.title }}</span>
              </a>
              <router-link
                v-else-if="item.url"
                :to="getInternalUrl(item.url)"
                :class="[getNavItemClasses(isParentActive(item)), 'group/btn']"
              >
                <span class="relative z-10">{{ item.title }}</span>
              </router-link>
              <button
                v-else
                type="button"
                :class="[getNavItemClasses(isParentActive(item)), 'group/btn']"
              >
                <span class="relative z-10">{{ item.title }}</span>
              </button>
              <div class="absolute top-full pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible group-focus-within:opacity-100 group-focus-within:visible transition-all duration-300 z-50">
                <div class="bg-background border border-border p-6 min-w-[240px]">
                  <div class="flex flex-col gap-2 text-foreground/70">
                    <template
                      v-for="child in item.children"
                      :key="String(child.id || child.title)"
                    >
                      <a
                        v-if="isExternalLink(child.url)"
                        :href="child.url || '#'"
                        target="_blank"
                        class="text-[10px] uppercase tracking-widest hover:text-primary transition-colors py-1"
                      >
                        {{ child.title }}
                      </a>
                      <router-link
                        v-else
                        :to="getInternalUrl(child.url)"
                        :class="[
                          'text-[10px] uppercase tracking-widest hover:text-primary transition-colors py-1',
                          isMenuItemActive(child) ? '!text-primary' : ''
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
                :class="[getNavItemClasses(false), 'group/link']"
                :data-title="item.title"
              >
                <span class="relative z-10">{{ item.title }}</span>
              </a>
              <router-link
                v-else
                :to="getInternalUrl(item.url)"
                :class="[getNavItemClasses(isMenuItemActive(item)), 'group/link']"
                :data-title="item.title"
              >
                <span class="relative z-10">{{ item.title }}</span>
              </router-link>
            </template>
          </template>
        </nav>

        <!-- Desktop utilities (console-style toolbar) -->
        <div
          v-if="isDesktop"
          class="janari-header-toolbar flex items-center gap-1.5 pl-4 ml-2 shrink-0 border-l border-border/40 relative z-30"
        >
          <ThemeToggle />
          <DropdownMenu>
            <DropdownMenuTrigger
              :class="toolbarPillClass"
              :aria-label="t('theme.janari.header.languageAria')"
              :title="currentLanguage?.native_name || t('theme.janari.header.languageAria')"
            >
              <Globe class="w-4 h-4 shrink-0 opacity-60" />
              <span class="text-[10px] font-bold uppercase tracking-wider">{{ currentLanguageCode }}</span>
              <ChevronDown class="w-3 h-3 opacity-50 shrink-0" />
            </DropdownMenuTrigger>
            <DropdownMenuContent
              align="end"
              :side-offset="10"
              class="w-52 p-1.5 rounded-xl shadow-lg ring-1 ring-border/50"
            >
              <DropdownMenuItem
                v-for="lang in languages"
                :key="lang.code"
                class="flex items-center gap-3 cursor-pointer rounded-lg px-3 py-2"
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
          <!-- Reader account / Login (mem_members — not console auth) -->
          <DropdownMenu v-if="memberEnabled && memberStore.isAuthenticated">
            <DropdownMenuTrigger as-child>
              <button
                type="button"
                class="inline-flex items-center gap-2 h-9 px-3 text-xs font-semibold text-foreground bg-primary/10 hover:bg-primary/20 rounded-lg transition-all focus:outline-none cursor-pointer"
              >
                <UserIcon class="w-3.5 h-3.5 text-primary" />
                <span class="max-w-[100px] truncate">{{ memberDisplayName }}</span>
                <ChevronDown class="w-3 h-3 text-muted-foreground" />
              </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
              align="end"
              class="w-48 bg-popover/95 backdrop-blur-md border-border/40 shadow-xl"
            >
              <DropdownMenuItem as-child>
                <router-link
                  to="/member/profile"
                  class="flex items-center gap-2 cursor-pointer w-full"
                >
                  <UserIcon class="w-4 h-4 text-primary" />
                  <span>{{ profileLabel }}</span>
                </router-link>
              </DropdownMenuItem>
              <DropdownMenuItem
                class="text-destructive focus:text-destructive cursor-pointer"
                @click="handleLogout"
              >
                <LogOut class="w-4 h-4 mr-2" />
                <span>{{ logoutLabel }}</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
          <a
            v-else-if="memberEnabled"
            :href="loginUrl"
            :class="toolbarLoginClass"
          >
            {{ loginLabel }}
          </a>
        </div>

        <!-- Mobile Burger -->
        <button
          v-if="!isDesktop"
          class="relative flex items-center justify-center w-10 h-10 rounded-full text-muted-foreground hover:text-foreground hover:bg-primary/5 hover:ring-4 hover:ring-primary/10 active:scale-95 transition-all duration-300 z-[200]"
          :aria-label="t('theme.janari.header.openNavAria')"
          @click="toggleMenu"
        >
          <MenuIcon class="w-6 h-6" />
        </button>
      </div>
    </div>

    <!-- 2nd Header: Artist / ticker bar (always solid so light/dark stay consistent) -->
    <div
      v-if="isDesktop"
      class="artist-bar relative z-0 overflow-hidden bg-muted/70 dark:bg-muted/40 border-b border-border"
    >
      <div class="absolute inset-0 bg-gradient-to-r from-primary/8 via-transparent to-primary/8 pointer-events-none -z-10" />
      <div class="container mx-auto px-8 flex justify-between items-center py-2 relative z-10">
        <div class="flex-1 min-w-0 overflow-hidden flex items-center h-8">
          <div
            v-if="isHomePage"
            class="whitespace-nowrap flex items-center gap-12 marquee-track animate-marquee"
          >
            <span
              v-for="i in 5"
              :key="'m'+i"
              class="text-[10px] font-bold tracking-normal text-foreground/80 flex items-center gap-6"
            >
              <span class="bg-primary/10 px-2 py-0.5 text-primary text-[8px] font-black uppercase tracking-widest">{{ newsBadge }}</span>
              {{ latestNewsText }}
              <span class="opacity-30 mx-2">|</span>
            </span>
          </div>
          <JanariBreadcrumbs v-else />
        </div>
        <!-- Social dock: expand/collapse via SOSIAL; no magnetic hover on icons -->
        <div class="flex items-center gap-1 bg-background/80 dark:bg-background/50 backdrop-blur-sm border border-border/50 rounded-lg px-2 py-1 shadow-xs shrink-0 ml-4">
          <div
            ref="socialLinksWrap"
            class="flex items-center gap-0.5 overflow-hidden"
            :style="socialExpanded ? undefined : { display: 'none' }"
          >
            <a
              v-for="(link, idx) in socialLinks"
              :key="idx"
              :href="resolveSocialHref(link)"
              :target="getSocialTarget(link)"
              :rel="getSocialRel(link)"
              :aria-label="getSocialAriaLabel(link)"
              class="inline-flex items-center justify-center w-8 h-8 rounded-full text-foreground/70 hover:text-foreground hover:bg-primary/10"
            >
              <component
                :is="getSocialIcon(link.icon)"
                class="w-4 h-4"
              />
            </a>
            <div
              v-if="socialLinks.length > 0"
              class="w-px h-4 bg-border/60 mx-0.5"
            />
          </div>
          <button
            class="janari-social-toggle inline-flex items-center gap-1.5 h-7 px-2 rounded-md text-foreground/70 bg-transparent border-0 hover:bg-muted hover:text-foreground focus:outline-none focus-visible:bg-muted focus-visible:ring-1 focus-visible:ring-border/60 focus-visible:ring-inset active:bg-muted cursor-pointer transition-colors duration-150"
            type="button"
            data-motion-interactive="off"
            :aria-expanded="socialExpanded"
            :aria-label="socialLabel"
            @click="toggleSocialLinks"
          >
            <span class="text-[10px] font-bold uppercase tracking-wider">{{ socialLabel }}</span>
            <ChevronDown
              class="w-3 h-3 opacity-60 transition-transform duration-300"
              :class="{ 'rotate-180': socialExpanded }"
            />
          </button>
        </div>
      </div>
    </div>

    <!-- Bottom accent -->
    <div class="absolute bottom-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-primary/40 to-transparent z-50 dark:block hidden" />
  </header>

  <!-- ========== MOBILE MENU OVERLAY (teleported outside header when live, scoped inside canvas when in builder) ========== -->
  <teleport to="body" :disabled="isBuilder">
    <transition name="mobile-menu">
      <div
        v-if="isOpen && !isDesktop"
        class="fixed inset-0 z-[9999] flex flex-col overflow-y-auto"
      >
        <!-- Mobile Header -->
        <div class="mobile-menu-header px-6 py-5 flex items-center justify-between shrink-0">
          <router-link
            to="/"
            class="flex items-center gap-3"
            @click="isOpen = false"
          >
            <img
              v-if="siteLogo"
              :src="siteLogo"
              class="h-8 w-auto object-contain"
              :alt="brandingDisplay === 'logo_only' ? siteName : ''"
              :aria-hidden="brandingDisplay === 'logo_only' ? undefined : 'true'"
              width="160"
              height="32"
              loading="eager"
              fetchpriority="high"
              decoding="async"
              sizes="160px"
            >
            <span class="text-lg font-heading font-black tracking-tight uppercase text-white leading-none">{{ siteName }}</span>
            <div class="h-4 w-px bg-white/20" />
            <span class="text-[7px] font-black tracking-[0.35em] uppercase text-white/40 leading-tight">{{ officialLine1 }}<br>{{ officialLine2 }}</span>
          </router-link>
          <button
            class="text-white/70 hover:text-white p-1 min-w-10 min-h-10"
            :aria-label="t('theme.janari.header.closeNavAria')"
            @click="isOpen = false"
          >
            <X class="w-8 h-8" />
          </button>
        </div>

        <!-- Language/Theme (console-style) -->
        <div class="px-6 py-3 bg-black/40 backdrop-blur-md flex items-center gap-2 shrink-0 border-b border-white/10">
          <ThemeToggle class="!text-white/70 hover:!text-white hover:!bg-white/10 hover:!ring-white/10" />
          <DropdownMenu>
            <DropdownMenuTrigger
              class="inline-flex items-center gap-1.5 h-9 px-2.5 text-xs font-medium text-white/70 border border-white/20 rounded-lg hover:bg-white/10 hover:text-white transition-all duration-300"
              :aria-label="t('theme.janari.header.languageAria')"
            >
              <Globe class="w-4 h-4 opacity-60" />
              <span class="font-bold uppercase tracking-wider">{{ currentLanguageCode }}</span>
              <ChevronDown class="w-3 h-3 opacity-50" />
            </DropdownMenuTrigger>
            <DropdownMenuContent
              align="end"
              :side-offset="8"
              class="w-52 p-1.5 rounded-xl"
            >
              <DropdownMenuItem
                v-for="lang in languages"
                :key="lang.code"
                class="flex items-center gap-3 cursor-pointer rounded-lg px-3 py-2"
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
          <router-link
            v-if="memberEnabled && memberStore.isAuthenticated"
            to="/member/profile"
            class="ml-auto inline-flex items-center gap-1.5 justify-center h-9 px-3 text-xs font-semibold uppercase tracking-wide text-primary bg-primary-foreground rounded-lg hover:bg-primary-foreground/90 transition-all duration-300"
            @click="isOpen = false"
          >
            <UserIcon class="w-3.5 h-3.5" />
            <span class="max-w-[100px] truncate">{{ memberDisplayName }}</span>
          </router-link>
          <a
            v-else-if="memberEnabled"
            :href="loginUrl"
            class="ml-auto inline-flex items-center justify-center h-9 px-3 text-xs font-semibold uppercase tracking-wide text-primary bg-primary-foreground rounded-lg hover:bg-primary-foreground/90 transition-all duration-300"
            @click="isOpen = false"
          >
            {{ loginLabel }}
          </a>
        </div>

        <!-- Primary Links (Dark Section) -->
        <div class="flex-1 bg-black/95 backdrop-blur-xl px-8 py-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
            <template
              v-for="item in navItems"
              :key="'mob-'+String(item.id || item.title)"
            >
              <div class="space-y-2">
                <div class="flex items-center justify-between gap-2">
                  <a 
                    v-if="isExternalLink(resolveMobileItemUrl(item))" 
                    :href="resolveMobileItemUrl(item) || '#'" 
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-3 text-white/80 hover:text-white transition-colors group/link min-w-0"
                    @click="isOpen = false"
                  >
                    <ChevronRight class="w-3 h-3 text-primary group-hover/link:translate-x-1 transition-transform shrink-0" />
                    <span class="text-sm font-medium truncate">{{ item.title }}</span>
                  </a>
                  <router-link 
                    v-else
                    :to="getInternalUrl(resolveMobileItemUrl(item))" 
                    :class="[
                      'flex items-center gap-3 transition-colors group/link min-w-0',
                      isMenuItemActive(item) ? 'text-white' : 'text-white/80 hover:text-white'
                    ]"
                    @click="isOpen = false"
                  >
                    <ChevronRight class="w-3 h-3 text-primary group-hover/link:translate-x-1 transition-transform shrink-0" />
                    <span class="text-sm font-medium truncate">{{ item.title }}</span>
                  </router-link>

                  <button
                    v-if="item.children && item.children.length > 0"
                    type="button"
                    class="p-1 text-white/55 hover:text-white transition-colors"
                    :aria-label="t('theme.janari.header.toggleSubmenuAria', { title: item.title })"
                    @click="toggleMobileSubmenu(item)"
                  >
                    <ChevronDown
                      class="w-4 h-4 transition-transform duration-200"
                      :class="{ 'rotate-180': isMobileSubmenuOpen(item) }"
                    />
                  </button>
                </div>

                <div
                  v-if="item.children && item.children.length > 0 && isMobileSubmenuOpen(item)"
                  class="pl-6 space-y-1.5"
                >
                  <template
                    v-for="child in item.children"
                    :key="'mob-child-'+String(child.id || child.title)"
                  >
                    <a
                      v-if="isExternalLink(child.url)"
                      :href="child.url || '#'"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="flex items-center gap-2 text-xs text-white/55 hover:text-white/85 transition-colors"
                      @click="isOpen = false"
                    >
                      <span class="w-1 h-1 rounded-full bg-primary/70" />
                      {{ child.title }}
                    </a>
                    <router-link
                      v-else-if="child.url"
                      :to="getInternalUrl(child.url)"
                      :class="[
                        'flex items-center gap-2 text-xs transition-colors',
                        isMenuItemActive(child) ? 'text-white/90' : 'text-white/55 hover:text-white/85'
                      ]"
                      @click="isOpen = false"
                    >
                      <span class="w-1 h-1 rounded-full bg-primary/70" />
                      {{ child.title }}
                    </router-link>
                  </template>
                </div>
              </div>
            </template>
          </div>
        </div>

        <!-- Accent band (Jejakawan) — follows theme primary -->
        <div class="mobile-accent-section px-8 py-8 shrink-0">
          <p class="text-[10px] uppercase tracking-[0.3em] text-primary-foreground/55 mb-3">
            Jejakawan
          </p>
          <div class="flex items-center gap-4 mb-6">
            <span class="text-2xl font-black text-primary-foreground tracking-tight uppercase">{{ siteName }}</span>
            <div class="h-5 w-px bg-primary-foreground/25" />
            <template v-if="memberEnabled && memberStore.isAuthenticated">
              <router-link
                to="/member/profile"
                class="bg-primary-foreground text-primary text-xs font-bold px-4 py-2 rounded-lg hover:bg-primary-foreground/90 transition-colors inline-flex items-center gap-1.5"
                @click="isOpen = false"
              >
                <UserIcon class="w-3.5 h-3.5" />
                <span class="max-w-[120px] truncate">{{ memberDisplayName }}</span>
              </router-link>
            </template>
            <a
              v-else-if="memberEnabled"
              :href="loginUrl"
              class="bg-primary-foreground text-primary text-xs font-bold px-4 py-2 rounded-lg hover:bg-primary-foreground/90 transition-colors"
              @click="isOpen = false"
            >
              {{ loginLabel }}
            </a>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
            <router-link
              to="/"
              class="flex items-center gap-3 text-primary-foreground/70 hover:text-primary-foreground text-sm"
              @click="isOpen = false"
            >
              <ChevronRight class="w-3 h-3" /> Top
            </router-link>
            <router-link
              to="/blog"
              class="flex items-center gap-3 text-primary-foreground/70 hover:text-primary-foreground text-sm"
              @click="isOpen = false"
            >
              <ChevronRight class="w-3 h-3" /> News
            </router-link>
            <router-link
              to="/profil"
              class="flex items-center gap-3 text-primary-foreground/70 hover:text-primary-foreground text-sm"
              @click="isOpen = false"
            >
              <ChevronRight class="w-3 h-3" /> Profile
            </router-link>
            <router-link
              to="/contact"
              class="flex items-center gap-3 text-primary-foreground/70 hover:text-primary-foreground text-sm"
              @click="isOpen = false"
            >
              <ChevronRight class="w-3 h-3" /> Contact
            </router-link>
          </div>
        </div>

        <!-- Social Footer -->
        <div class="bg-black py-6 px-8 flex items-center justify-center gap-6 shrink-0">
          <a 
            v-for="(link, idx) in socialLinks" 
            :key="idx"
            :href="resolveSocialHref(link)"
            :target="getSocialTarget(link)"
            :rel="getSocialRel(link)"
            :aria-label="getSocialAriaLabel(link)"
            class="text-white/40 hover:text-white transition-colors"
          >
            <component
              :is="getSocialIcon(link.icon)"
              class="w-5 h-5"
            />
          </a>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch, inject } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting';
import { useMenu } from '@/modules/Layout/composables/useMenu';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useMemberStore } from '@/modules/Member/stores/member';
import { useI18n } from 'vue-i18n';
import { useResponsiveDevice } from '@/shared/composables/useResponsiveDevice';
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion';
import { useRoute, useRouter } from 'vue-router';
import { useJanariIdentity, trimStr, toWhatsAppDialDigits } from '@/modules/Layout/views/themes/janari/composables/useJanariIdentity';
import JanariBreadcrumbs from './JanariBreadcrumbs.vue';
import { useLanguage } from '@/shared/composables/useLanguage';
import {
    ThemeToggle,
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
    DropdownMenuItem,
} from '@/modules/Layout/views/themes/janari/ui';
import {
    Check,
    ChevronRight,
    ChevronDown,
    Twitter,
    Instagram,
    Facebook,
    Youtube,
    Linkedin,
    Github,
    Music2,
    Mail,
    MessageCircle,
    Globe,
    Menu as MenuIcon,
    X,
    User as UserIcon,
    LogOut,
} from 'lucide-vue-next';
import type { MenuItem } from '@/modules/Layout/types/menu';

const builder = inject('builder', null);
const isBuilder = computed(() => !!builder);

const { getSetting } = useTheme();
const { localizedString } = useLocalizedThemeSetting();
const { menus, fetchMenuByIdentifier } = useMenu();
const { t: tt } = useThemeI18n('janari');
const device = useResponsiveDevice();
const { motion } = useThemeMotion();
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const memberStore = useMemberStore();

const toolbarPillClass =
  'inline-flex items-center gap-1.5 h-9 px-2.5 text-xs font-medium text-muted-foreground bg-transparent border border-border/40 rounded-lg hover:bg-primary/5 hover:text-foreground hover:ring-4 hover:ring-primary/5 active:scale-95 transition-all duration-300 focus:outline-none cursor-pointer';
const toolbarLoginClass =
  'inline-flex items-center justify-center h-9 px-3 text-xs font-semibold uppercase tracking-wide text-primary-foreground bg-primary rounded-lg hover:bg-primary/90 hover:scale-[1.02] active:scale-95 shadow-sm transition-all duration-300 focus:outline-none';
const { locale, t } = useI18n({ useScope: 'global' });
const { setLanguage, initializeLanguage, currentLanguageCode, currentLanguage, languages, getLanguageFlag } = useLanguage();

const handleSelectLanguage = async (code: string) => {
    await setLanguage(code);
    if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent('language-changed', { detail: { code: locale.value } }));
    }
};

const isOpen = ref(false);
const mobileOpenSubmenus = ref<Set<string>>(new Set());
const loginUrl = computed(() => {
  const raw = getSetting('header_login_url', '/member/login')
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/member/login'
})
const memberEnabled = computed(() => Boolean(getSetting('enable_members', true)));
const memberDisplayName = computed(() =>
  memberStore.member?.name
    || t('theme.janari.header.memberFallback'),
);
const officialLine1 = computed(() => localizedString('header_official_line1') || t('theme.janari.header.officialLine1'))
const officialLine2 = computed(() => localizedString('header_official_line2') || t('theme.janari.header.officialLine2'))
const loginLabel = computed(() => localizedString('header_login_label') || t('theme.janari.header.login'))
const profileLabel = computed(() => localizedString('header_profile_label') || t('theme.janari.header.profile'))
const logoutLabel = computed(() => localizedString('header_logout_label') || t('theme.janari.header.logout'))
const newsBadge = computed(() => localizedString('header_news_badge') || t('theme.janari.header.newsBadge'))
const latestNewsText = computed(() =>
  localizedString('header_marquee_text')
    || t('theme.janari.header.marqueeDefault'),
)
const handleLogout = async () => {
    await memberStore.logout();
    isOpen.value = false;
    if (route.path.startsWith('/member')) {
        await router.replace('/');
        return;
    }
};
const headerRef = ref<HTMLElement>();
const isDesktop = computed(() => device.value === 'desktop');
const isHomePage = computed(() => route.path === '/');

const headerSticky = computed(() => getSetting('header_sticky', true));
const headerStyle = computed(() => getSetting('header_style', 'glass'));
const brandingDisplay = computed(() => getSetting('branding_display', 'logo_only'));

const systemStore = useSystemStore();
const siteSettings = computed(() => systemStore.settings);
const { displaySiteName } = useJanariIdentity();
const siteName = computed(() => displaySiteName.value);
const siteLogo = computed((): string => {
    const fromSetting = getSetting('brand_logo');
    const fromSite = siteSettings.value?.site_logo;
    const logo = (typeof fromSetting === 'string' ? fromSetting : '')
        || (typeof fromSite === 'string' ? fromSite : '');

    return logo;
});

const socialLinks = computed(() => (getSetting('social_links') as any[]) || []);
const socialLabel = computed(() => localizedString('header_social_label') || t('theme.janari.header.socialLabel'));
const socialExpanded = ref(true);
const socialLinksWrap = ref<HTMLElement>();

const getSocialIcon = (key: string) => {
    switch (key) {
        case 'Twitter': return Twitter;
        case 'Instagram': return Instagram;
        case 'Facebook': return Facebook;
        case 'Youtube': return Youtube;
        case 'Linkedin': return Linkedin;
        case 'Github': return Github;
        case 'Music2': return Music2;
        case 'MessageCircle':
        case 'WhatsApp':
            return MessageCircle;
        case 'Mail': return Mail;
        case 'Email': return Mail;
        default: return Globe;
    }
};

const resolveSocialHref = (link: { icon?: string; url?: string }) => {
    const icon = trimStr(link?.icon);
    const raw = trimStr(link?.url);
    if (!raw) return '#';

    if (icon === 'Mail' || icon === 'Email') {
        if (raw.startsWith('mailto:')) return raw;
        if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(raw)) return `mailto:${raw}`;
        return raw;
    }

    if (icon === 'MessageCircle' || icon === 'WhatsApp') {
        if (raw.includes('wa.me/') || raw.includes('api.whatsapp.com/') || raw.includes('whatsapp.com/')) {
            return raw.startsWith('http') ? raw : `https://${raw.replace(/^\/+/, '')}`;
        }
        const digits = toWhatsAppDialDigits(raw);
        return digits ? `https://wa.me/${digits}` : '#';
    }

    return raw;
};

const getSocialTarget = (link: { icon?: string; url?: string }) => {
    const href = resolveSocialHref(link);
    if (href.startsWith('mailto:') || href.startsWith('tel:') || href === '#') return undefined;
    return '_blank';
};

const getSocialRel = (link: { icon?: string; url?: string }) => {
    return getSocialTarget(link) ? 'noopener noreferrer' : undefined;
};

const getSocialAriaLabel = (link: { icon?: string; url?: string }) => {
    const icon = trimStr(link?.icon) || 'social';
    const href = resolveSocialHref(link);
    if (href.startsWith('mailto:')) return `Kirim email via ${icon}`;
    if (href.startsWith('tel:')) return `Hubungi via ${icon}`;
    if (href === '#') return `Tautan ${icon}`;
    try {
        const parsed = new URL(href, window.location.origin);
        const path = parsed.pathname.replace(/^\/+/, '');
        const suffix = path ? `${parsed.hostname}/${path}` : parsed.hostname;
        return `Kunjungi ${icon} (${suffix})`;
    } catch {
        return `Kunjungi ${icon}`;
    }
};

const headerStyleClasses = computed(() => {
    switch (headerStyle.value) {
        case 'solid': return 'bg-background border-b border-border';
        case 'transparent': return 'bg-transparent';
        default: return 'bg-background/90 backdrop-blur-3xl border-b border-border shadow-xs';
    }
});

const getNavItemClasses = (isActive: boolean) => {
    const base = 'px-4 py-2 text-[10.5px] font-black uppercase tracking-[0.35em] transition-all duration-300 relative inline-block';
    if (isActive) return `${base} text-primary`;
    return `${base} text-foreground hover:text-primary`;
};

const normalizePath = (raw?: string | null): string => {
    if (!raw) return '/';
    if (isExternalLink(raw)) return '';
    const path = raw.startsWith('/') ? raw : `/${raw}`;
    return path !== '/' && path.endsWith('/') ? path.slice(0, -1) : path;
};

const isRouteMatch = (targetPath: string, currentPath: string): boolean => {
    if (!targetPath) return false;
    if (targetPath === '/') return currentPath === '/';
    return currentPath === targetPath || currentPath.startsWith(`${targetPath}/`);
};

const isMenuItemActive = (item: MenuItem): boolean => {
    const currentPath = normalizePath(route.path);
    const parentPath = normalizePath(item.url);
    if (isRouteMatch(parentPath, currentPath)) return true;

    const children = Array.isArray(item.children) ? item.children : [];
    return children.some((child): boolean => {
        return isMenuItemActive(child);
    });
};

const isParentActive = (item: MenuItem) => isMenuItemActive(item);
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
        .filter(item => {
            const meta = item.metadata as Record<string, any> | undefined;
            if (meta) {
                if (meta.guest_only && authStore.isAuthenticated) {
                    return false;
                }
                if (meta.requires_auth && !authStore.isAuthenticated) {
                    return false;
                }
                if (meta.required_permission && (!authStore.isAuthenticated || !authStore.hasPermission(meta.required_permission))) {
                    return false;
                }
            }
            return true;
        })
        .map(item => {
            let title = item.title;
            const meta = item.metadata as Record<string, any> | undefined;
            if (meta) {
                const currentLang = locale.value;
                if (currentLang === 'en' && meta.title_en) {
                    title = String(meta.title_en);
                } else if (currentLang === 'id' && meta.title_id) {
                    title = String(meta.title_id);
                }
            }

            const mappedItem = {
                ...item,
                title
            };

            if (item.children && item.children.length > 0) {
                mappedItem.children = filterMenuItems(item.children);
            }

            return mappedItem;
        });
};

/** Shown when Menu Builder has no active header menu yet (same idea as Footer defaults). */
const defaultNavItems = computed((): MenuItem[] => [
    { id: 'janari-nav-home', title: tt('header.navHome'), url: '/', type: 'custom', sort_order: 0 },
    { id: 'janari-nav-about', title: tt('header.navAbout'), url: '/about', type: 'custom', sort_order: 1 },
    { id: 'janari-nav-solusi', title: tt('header.navSolutions'), url: '/solusi', type: 'custom', sort_order: 2 },
    { id: 'janari-nav-tim', title: tt('header.navTeam'), url: '/tim', type: 'custom', sort_order: 3 },
    { id: 'janari-nav-pricing', title: tt('header.navPricing'), url: '/pricing', type: 'custom', sort_order: 4 },
    { id: 'janari-nav-news', title: tt('header.navNews'), url: '/blog', type: 'custom', sort_order: 5 },
    { id: 'janari-nav-contact', title: tt('header.navContact'), url: '/contact', type: 'custom', sort_order: 6 },
]);

const navItems = computed<MenuItem[]>(() => {
    const menu = menus.value.header || menus.value[currentMenuLocation.value];
    const rawItems = (menu?.items || []) as MenuItem[];
    const filtered = filterMenuItems(rawItems);
    return filtered.length > 0 ? filtered : filterMenuItems(defaultNavItems.value);
});

const toggleMenu = () => {
    isOpen.value = !isOpen.value;
    if (!isOpen.value) {
        mobileOpenSubmenus.value = new Set();
    }
};

const toggleSocialLinks = () => {
    const wrap = socialLinksWrap.value;
    if (!wrap) return;

    if (socialExpanded.value) {
        motion.to(wrap, {
            scaleX: 0,
            opacity: 0,
            x: 10,
            transformOrigin: 'right center',
            duration: 0.3,
            ease: 'power2.out',
            onComplete: () => {
                wrap.style.display = 'none';
            },
        });
        socialExpanded.value = false;
        return;
    }

    wrap.style.display = 'flex';
    motion.set(wrap, { scaleX: 0, opacity: 0, x: 10, transformOrigin: 'right center' });
    motion.to(wrap, {
        scaleX: 1,
        opacity: 1,
        x: 0,
        duration: 0.35,
        ease: 'power2.out',
    });
    socialExpanded.value = true;
};

// URL Helpers
const isExternalLink = (url?: string | null) => {
    if (!url) return false;
    return url.startsWith('http://') || url.startsWith('https://') || url.startsWith('mailto:') || url.startsWith('tel:');
};

const getInternalUrl = (url?: string | null) => {
    if (!url) return '/';
    if (url.startsWith('http')) return url; // Let standard anchors handle it if missed
    if (!url.startsWith('/')) return `/${url}`;
    return url;
};

const resolveMobileItemUrl = (item: MenuItem): string => {
    const direct = trimStr(item.url);
    if (direct) return direct;
    const children = Array.isArray(item.children) ? item.children : [];
    const firstChildUrl = trimStr(children[0]?.url);
    return firstChildUrl || '/';
};

const getMobileMenuKey = (item: MenuItem): string => {
    return String(item.id || item.title || item.url || '');
};

const isMobileSubmenuOpen = (item: MenuItem): boolean => {
    return mobileOpenSubmenus.value.has(getMobileMenuKey(item));
};

const toggleMobileSubmenu = (item: MenuItem) => {
    const key = getMobileMenuKey(item);
    const next = new Set(mobileOpenSubmenus.value);
    if (next.has(key)) {
        next.delete(key);
    } else {
        next.add(key);
    }
    mobileOpenSubmenus.value = next;
};

// Use a watch for more robust body scroll locking
watch(isOpen, (newValue) => {
    if (!isDesktop.value && !isBuilder.value) {
        document.body.style.overflow = newValue ? 'hidden' : '';
    } else {
        document.body.style.overflow = '';
    }
});

// Close menu on route change
watch(() => route.path, () => {
    isOpen.value = false;
    mobileOpenSubmenus.value = new Set();
    document.body.style.overflow = '';
});

// Use a local ref to prevent redundant triggers if something else changes the location
const menuFetched = ref<Set<string>>(new Set());
watch(currentMenuLocation, async (newLoc) => { 
    if (!newLoc || menuFetched.value.has(newLoc)) return;
    menuFetched.value.add(newLoc);
    await fetchMenuByIdentifier(newLoc, 'header');
}, { immediate: true });

onMounted(() => {
    const topMenuIdentifier = normalizeMenuSetting(getSetting('menu_location_header_top', 'header_top'), 'header_top');
    fetchMenuByIdentifier(topMenuIdentifier, 'header_top');
    if (headerRef.value) {
        // Use set to ensure visibility before animation
        motion.set(headerRef.value, { y: -100, opacity: 0 });
        motion.to(headerRef.value, { y: 0, opacity: 1, duration: 1, ease: 'expo.out', clearProps: 'all' });
    }
    initializeLanguage();
});

onUnmounted(() => {
    document.body.style.overflow = '';
});
</script>

<style scoped>
@keyframes marquee {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
.animate-marquee {
  animation: marquee 40s linear infinite;
  display: inline-flex;
}
.marquee-track:hover {
  animation-play-state: paused;
}
.mobile-menu-header {
    background: hsl(var(--card));
    border-bottom: 2px solid hsl(var(--border));
}
.mobile-accent-section {
    background: linear-gradient(180deg, hsl(var(--primary) / 0.88), hsl(var(--primary)));
}
.mobile-menu-enter-active { transition: opacity 0.3s ease; }
.mobile-menu-leave-active { transition: opacity 0.2s ease; }
.mobile-menu-enter-from, .mobile-menu-leave-to { opacity: 0; }
</style>
