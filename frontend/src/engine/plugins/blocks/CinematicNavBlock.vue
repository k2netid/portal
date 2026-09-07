<template>
  <Teleport to="body">
    <nav
      v-if="isVisible"
      data-plugin="cinematic-nav"
      :data-theme="currentThemeSlug"
      :aria-label="te('plugin.cinematicNav.ariaLabel') ? t('plugin.cinematicNav.ariaLabel') : t('nav_sections.aria_label', 'Navigasi Seksi Halaman')"
      class="ja-cinematic-nav hidden md:block select-none"
      :class="[
        `ja-cinematic-nav--${dockPosition}`,
        `ja-cinematic-nav--${stylePreset}`,
        `ja-cinematic-nav--theme-${currentThemeSlug}`
      ]"
      :style="navPositionStyle"
    >
      <!-- Inner Dock Container with GSAP motion -->
      <div
        ref="innerDockRef"
        class="ja-cinematic-nav__dock flex flex-col items-center gap-2.5 transition-all duration-300 relative"
        :class="dockPresetClasses"
      >
        <!-- Telemetry Progress Rail track line (for 'bars' preset) -->
        <div
          v-if="stylePreset === 'bars'"
          class="ja-cinematic-nav__rail-track absolute top-3.5 bottom-3.5 left-1/2 -translate-x-1/2 w-[2px] bg-border/45 -z-0 pointer-events-none"
        />

        <button
          v-for="(item, index) in discoveredSections"
          :key="item.id"
          :data-section-id="item.id"
          type="button"
          :aria-label="item.label"
          :aria-current="activeSectionId === item.id ? 'true' : undefined"
          class="ja-cinematic-nav__btn group relative flex items-center justify-center p-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-full z-10"
          @click="handleDotClick(item.id, $event)"
        >
          <!-- Tooltip Label (Floating on Hover) -->
          <div
            v-if="showTooltips"
            class="ja-cinematic-nav__tooltip absolute px-2.5 py-1.5 text-xs font-medium whitespace-nowrap shadow-xl pointer-events-none opacity-0 transition-all duration-200 backdrop-blur-xl flex items-center gap-2 z-30 group-hover:opacity-100"
            :class="[tooltipPresetClasses, tooltipPositionClasses]"
          >
            <!-- Monospace Index Badge -->
            <span class="ja-cinematic-nav__tooltip-idx text-[10px] font-mono text-primary font-bold tracking-wider px-1 py-0.5 rounded bg-primary/10 border border-primary/20">
              {{ String(index + 1).padStart(2, '0') }}
            </span>
            <span class="ja-cinematic-nav__tooltip-text font-medium tracking-tight">{{ item.label }}</span>
            <!-- Micro pointing caret -->
            <span
              class="ja-cinematic-nav__tooltip-caret absolute top-1/2 -translate-y-1/2 w-1.5 h-1.5 rotate-45 bg-inherit"
              :class="[tooltipCaretClasses, tooltipPresetCaretClasses]"
            />
          </div>

          <!-- Indicator based on distinct physical preset shapes -->

          <!-- 1. BARS PRESET: Architectural Horizontal Slabs on Guide Rail -->
          <template v-if="stylePreset === 'bars'">
            <div class="relative flex items-center justify-center py-1">
              <span
                class="ja-cinematic-nav__indicator ja-cinematic-nav__shape-bar transition-all duration-300 block origin-center"
                :class="[
                  activeSectionId === item.id
                    ? 'ja-cinematic-nav__bar-active w-7 h-2 bg-primary shadow-[0_0_14px_hsl(var(--primary)/0.6)] ring-1 ring-primary/50'
                    : 'w-3.5 h-1 bg-muted-foreground/40 group-hover:w-5.5 group-hover:bg-foreground/80'
                ]"
              />
            </div>
          </template>

          <!-- 2. MINIMAL PRESET: Magnetic Orbital Target Rings -->
          <template v-else-if="stylePreset === 'minimal'">
            <div class="relative flex items-center justify-center w-5 h-5">
              <!-- Inactive: Hollow Ring | Active: Concentric Breathing Target Core -->
              <template v-if="activeSectionId === item.id">
                <!-- Outer breathing orbital ring -->
                <span
                  class="absolute inset-0 rounded-full border border-primary/40 animate-ping opacity-60 pointer-events-none"
                />
                <span
                  class="absolute inset-0.5 rounded-full border-2 border-primary/30 pointer-events-none"
                />
                <!-- Inner solid luminous bead -->
                <span
                  class="ja-cinematic-nav__indicator ja-cinematic-nav__shape-orbital w-2.5 h-2.5 rounded-full bg-primary shadow-[0_0_10px_hsl(var(--primary))] block transition-transform duration-300"
                />
              </template>
              <template v-else>
                <!-- Inactive: Hollow precision ring -->
                <span
                  class="ja-cinematic-nav__indicator ja-cinematic-nav__shape-orbital w-2.5 h-2.5 rounded-full border-2 border-foreground/35 bg-transparent group-hover:border-primary group-hover:bg-primary/20 group-hover:scale-125 transition-all duration-200 block"
                />
              </template>
            </div>
          </template>

          <!-- 3. GLOW PRESET: Faceted Cyber Shard / Rotated Diamond Beacon -->
          <template v-else-if="stylePreset === 'glow'">
            <div class="relative flex items-center justify-center w-5 h-6">
              <!-- Active: Elongated vertical tech shard with neon pulse -->
              <template v-if="activeSectionId === item.id">
                <span
                  class="ja-cinematic-nav__indicator ja-cinematic-nav__shape-shard ja-cinematic-nav__glow-active w-3 h-7 bg-primary shadow-[0_0_16px_hsl(var(--primary)),0_0_32px_hsl(var(--primary)/0.5)] ring-2 ring-primary/70 block transition-all duration-300"
                />
              </template>
              <!-- Inactive: 45-degree diamond shard with hover spin -->
              <template v-else>
                <span
                  class="ja-cinematic-nav__indicator ja-cinematic-nav__shape-shard w-2.5 h-2.5 rotate-45 border border-primary/60 bg-primary/25 group-hover:scale-125 group-hover:rotate-90 group-hover:bg-primary group-hover:shadow-[0_0_10px_hsl(var(--primary)/0.8)] transition-all duration-300 block"
                />
              </template>
            </div>
          </template>

          <!-- 4. GLASS PRESET: Liquid Morphic Capsule (Smooth Round Pill) -->
          <template v-else>
            <div class="relative flex items-center justify-center">
              <span
                class="ja-cinematic-nav__indicator ja-cinematic-nav__shape-pill transition-all duration-300 block origin-center rounded-full"
                :class="[
                  activeSectionId === item.id
                    ? 'ja-cinematic-nav__pill-active w-2.5 h-7 bg-gradient-to-b from-primary via-primary to-primary/85 shadow-[0_0_14px_hsl(var(--primary)/0.5)] ring-2 ring-primary/30'
                    : 'w-2.5 h-2.5 bg-foreground/25 group-hover:bg-foreground/80 group-hover:scale-125'
                ]"
              />
            </div>
          </template>
        </button>
      </div>
    </nav>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import gsap from 'gsap';
import { throttle } from '@/shared/utils/performance';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion';

export interface SectionNavItem {
  id: string;
  label: string;
}

const route = useRoute();
const { t, te } = useI18n();
const { getSetting, activeTheme } = useTheme();
const { isAnimationEnabled } = useThemeMotion();

const innerDockRef = ref<HTMLElement | null>(null);
const activeSectionId = ref<string>('');
const discoveredSections = ref<SectionNavItem[]>([]);
let observer: IntersectionObserver | null = null;
let mutationObserver: MutationObserver | null = null;

const currentThemeSlug = computed<string>(() => {
  return activeTheme.value?.slug || 'generic';
});

// Settings Resolution
const isEnabledBySetting = computed(() => {
  const s1 = getSetting('home_side_nav_dots', null);
  if (s1 !== null) return Boolean(s1);
  const s2 = getSetting('enable_side_nav', null);
  if (s2 !== null) return Boolean(s2);
  return true;
});

const isVisible = computed(() => {
  if (!isEnabledBySetting.value || discoveredSections.value.length < 2) return false;
  // If the active page already renders a theme-level section nav (e.g. Sareupna or Sarangenge), prevent duplicate dock
  if (typeof document !== 'undefined') {
    const existing = document.querySelector('.sareupna-nav-dots, .sarangenge-nav-dots, .janari-nav-dots');
    if (existing && !existing.hasAttribute('data-plugin')) {
      return false;
    }
  }
  return true;
});

const stylePreset = computed<string>(() => {
  const p1 = getSetting('home_side_nav_style', null);
  if (p1) return String(p1);
  const p2 = getSetting('side_nav_preset', null);
  if (p2) return String(p2);
  return 'glass';
});

const dockPosition = computed<string>(() => {
  return String(getSetting('side_nav_position', 'right') || 'right');
});

const isRightDock = computed(() => dockPosition.value !== 'left');

const showTooltips = computed<boolean>(() => {
  return getSetting('side_nav_show_tooltips', true) !== false;
});

const enableScrollSnap = computed<boolean>(() => {
  return (
    getSetting('home_side_nav_scroll_snap', false) === true ||
    getSetting('enable_scroll_snap', false) === true
  );
});

const navPositionStyle = computed(() => {
  return {
    position: 'fixed' as const,
    top: '50%',
    transform: 'translateY(-50%)',
    right: isRightDock.value ? '0.85rem' : 'auto',
    left: isRightDock.value ? 'auto' : '0.85rem',
    zIndex: 9990,
    pointerEvents: 'auto' as const,
  };
});

const dockPresetClasses = computed(() => {
  switch (stylePreset.value) {
    case 'minimal':
      return 'bg-transparent border-none shadow-none py-1 px-1 gap-3';
    case 'glow':
      return 'bg-card/85 hover:bg-card/95 backdrop-blur-2xl border border-primary/45 shadow-[0_0_24px_hsl(var(--primary)/0.25)] hover:shadow-[0_0_35px_hsl(var(--primary)/0.4)] py-3 px-1.5 rounded-xl ring-1 ring-primary/25';
    case 'bars':
      return 'bg-card/80 hover:bg-card/95 backdrop-blur-xl border border-border/70 shadow-2xl py-3.5 px-2.5 rounded-2xl';
    case 'glass':
    default:
      return 'bg-background/70 hover:bg-background/90 backdrop-blur-2xl border border-border/60 dark:border-white/10 shadow-[0_8px_32px_rgba(0,0,0,0.12)] hover:shadow-[0_12px_40px_rgba(0,0,0,0.18)] py-3 px-1.5 rounded-full ring-1 ring-black/5 dark:ring-white/5';
  }
});

const tooltipPresetClasses = computed(() => {
  switch (stylePreset.value) {
    case 'glow':
      return 'bg-card/95 text-card-foreground border border-primary/40 shadow-[0_4px_20px_hsl(var(--primary)/0.25)]';
    case 'minimal':
      return 'bg-popover/95 text-popover-foreground border border-border/60 shadow-xl';
    case 'bars':
      return 'bg-card/95 text-card-foreground border border-border/60 shadow-xl';
    case 'glass':
    default:
      return 'bg-background/90 text-foreground border border-border/60 shadow-xl backdrop-blur-xl';
  }
});

const tooltipPositionClasses = computed(() => {
  if (isRightDock.value) {
    return 'right-full mr-3.5 origin-right translate-x-2 group-hover:translate-x-0';
  }
  return 'left-full ml-3.5 origin-left -translate-x-2 group-hover:translate-x-0';
});

const tooltipCaretClasses = computed(() => {
  if (isRightDock.value) {
    return '-right-1 border-r border-t';
  }
  return '-left-1 border-l border-b';
});

const tooltipPresetCaretClasses = computed(() => {
  switch (stylePreset.value) {
    case 'glow':
      return 'border-primary/40';
    case 'minimal':
    case 'bars':
      return 'border-border/60';
    case 'glass':
    default:
      return 'border-border/60';
  }
});

// Helper to humanize ID into a clean label
const humanizeSectionId = (id: string): string => {
  const clean = id.replace(/^section[-_]/i, '').replace(/[-_]/g, '_');
  const kebab = id.replace(/^section[-_]/i, '').replace(/[-_]/g, '-');

  // 1. Check centralized plugin translations
  const pluginKey = `plugin.cinematicNav.sections.${clean}`;
  if (te(pluginKey)) return t(pluginKey);

  // 2. Check theme-scoped translations
  const theme = activeTheme.value?.slug || '';
  const themeNavSections = `theme.${theme}.nav_sections.${clean}`;
  if (te(themeNavSections)) return t(themeNavSections);

  const themeNavDots = `theme.${theme}.navDots.${clean}`;
  if (te(themeNavDots)) return t(themeNavDots);

  // 3. Check legacy theme keys
  const legacySections = `nav_sections.${clean}`;
  if (te(legacySections)) return t(legacySections);

  const legacyDots = `navDots.${clean}`;
  if (te(legacyDots)) return t(legacyDots);

  // 4. Also check kebab variant in plugin
  const pluginKeyKebab = `plugin.cinematicNav.sections.${kebab}`;
  if (te(pluginKeyKebab)) return t(pluginKeyKebab);

  // Fallback to clean title case
  return clean
    .split(/[-_]/)
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

// Scan DOM for sections dynamically
const discoverSections = () => {
  if (typeof document === 'undefined') return;

  const selectorCandidates = [
    '[data-nav-section]',
    '[data-section-id]',
    'main section[id]',
    'main div[id^="section-"]',
    '#section-hero',
    '#section-bento',
    '#section-terminal',
    '#section-products',
    '#section-services',
    '#section-calculator',
    '#section-sla',
    '#section-managed-services',
    '#section-testimonials',
    '#section-cta',
    '#section-faq',
    '#section-announcements',
    '#section-principal',
    '#section-majors',
    '#section-facilities',
    '#section-achievements',
    '#section-partners',
    '#section-contact',
  ];

  const elements = Array.from(
    document.querySelectorAll<HTMLElement>(selectorCandidates.join(','))
  );

  const seenIds = new Set<string>();
  const list: SectionNavItem[] = [];

  for (const el of elements) {
    const id = el.getAttribute('data-section-id') || el.id;
    if (!id || seenIds.has(id)) continue;

    // Must be visible in layout
    if (el.offsetParent === null && el.offsetHeight === 0) continue;

    seenIds.add(id);

    const explicitLabel =
      el.getAttribute('data-nav-label') ||
      el.getAttribute('aria-label') ||
      el.querySelector('h1, h2')?.textContent?.trim();

    list.push({
      id,
      label: explicitLabel || humanizeSectionId(id),
    });
  }

  discoveredSections.value = list;
  const first = list[0];
  if (first && !activeSectionId.value) {
    activeSectionId.value = first.id;
  }
};

const playEntranceAnimation = () => {
  if (!innerDockRef.value || !isAnimationEnabled()) return;

  gsap.fromTo(
    innerDockRef.value,
    { x: isRightDock.value ? 25 : -25, opacity: 0 },
    { x: 0, opacity: 1, duration: 0.5, ease: 'power3.out' }
  );

  const dots = innerDockRef.value.querySelectorAll('.ja-cinematic-nav__btn');
  if (dots.length > 0) {
    gsap.fromTo(
      dots,
      { scale: 0, opacity: 0 },
      { scale: 1, opacity: 1, duration: 0.35, stagger: 0.03, ease: 'back.out(2)', delay: 0.1 }
    );
  }
};

const animateActiveChange = (sectionId: string) => {
  if (!innerDockRef.value || !isAnimationEnabled()) return;
  const target = innerDockRef.value.querySelector(
    `[data-section-id="${sectionId}"] .ja-cinematic-nav__indicator`
  );
  if (target) {
    gsap.fromTo(
      target,
      { scaleY: 0.45, scaleX: 1.25 },
      { scaleY: 1, scaleX: 1, duration: 0.45, ease: 'elastic.out(1.2, 0.45)' }
    );
  }
};

const handleDotClick = (id: string, event?: MouseEvent) => {
  if (event && isAnimationEnabled()) {
    const currentBtn = event.currentTarget as HTMLElement;
    const indicator = currentBtn?.querySelector('.ja-cinematic-nav__indicator');
    if (indicator) {
      gsap.fromTo(
        indicator,
        { scale: 0.75 },
        { scale: 1, duration: 0.3, ease: 'back.out(2)' }
      );
    }
  }
  scrollToSection(id);
};

const scrollToSection = (id: string) => {
  const el = document.getElementById(id);
  if (el) {
    const headerEl = document.querySelector('header');
    const navOffset = headerEl ? headerEl.offsetHeight + 16 : 72;
    const elementPosition = el.getBoundingClientRect().top + window.scrollY;
    const offsetPosition = Math.max(0, elementPosition - navOffset);

    window.scrollTo({
      top: offsetPosition,
      behavior: 'smooth',
    });
    if (activeSectionId.value !== id) {
      activeSectionId.value = id;
      animateActiveChange(id);
    }
  }
};

const handleScroll = () => {
  if (typeof window === 'undefined') return;
  const scrollY = window.scrollY || window.pageYOffset || document.documentElement.scrollTop;
  const viewportHeight = window.innerHeight;
  const docHeight = document.documentElement.scrollHeight;

  // At the bottom of the page, activate the last section
  if (scrollY + viewportHeight >= docHeight - 80 && discoveredSections.value.length > 0) {
    const lastSection = discoveredSections.value[discoveredSections.value.length - 1];
    if (lastSection && activeSectionId.value !== lastSection.id) {
      activeSectionId.value = lastSection.id;
      animateActiveChange(lastSection.id);
    }
    return;
  }

  const triggerLine = scrollY + viewportHeight * 0.35;

  let current = '';
  for (const item of discoveredSections.value) {
    const el = document.getElementById(item.id);
    if (el) {
      const top = el.getBoundingClientRect().top + scrollY;
      if (top <= triggerLine) {
        current = item.id;
      }
    }
  }

  if (current && current !== activeSectionId.value) {
    activeSectionId.value = current;
    animateActiveChange(current);
  }
};

const throttledScroll = throttle(handleScroll, 100);

const attachObserver = () => {
  if (typeof window === 'undefined') return;

  if ('IntersectionObserver' in window) {
    if (observer) {
      observer.disconnect();
    }

    observer = new IntersectionObserver(
      (entries) => {
        const visible = entries
          .filter((entry) => entry.isIntersecting)
          .sort((a, b) => b.intersectionRatio - a.intersectionRatio);

        const primary = visible[0];
        if (primary && primary.target.id !== activeSectionId.value) {
          activeSectionId.value = primary.target.id;
          animateActiveChange(primary.target.id);
        }
      },
      {
        root: null,
        rootMargin: '-20% 0px -35% 0px',
        threshold: [0.1, 0.3, 0.6],
      }
    );

    discoveredSections.value.forEach((item) => {
      const el = document.getElementById(item.id);
      if (el && observer) {
        observer.observe(el);
      }
    });
  }

  handleScroll();
};

const applyScrollSnap = (enable: boolean) => {
  if (typeof document === 'undefined') return;
  const html = document.documentElement;
  if (enable) {
    html.style.scrollSnapType = 'y proximity';
  } else {
    html.style.scrollSnapType = '';
  }
};

onMounted(() => {
  nextTick(() => {
    discoverSections();
    attachObserver();
    window.addEventListener('scroll', throttledScroll, { passive: true });
    window.addEventListener('resize', throttledScroll, { passive: true });
    playEntranceAnimation();
    applyScrollSnap(enableScrollSnap.value);

    // Mutation observer to handle dynamically injected sections / slots
    if (typeof MutationObserver !== 'undefined') {
      mutationObserver = new MutationObserver(() => {
        const prevCount = discoveredSections.value.length;
        discoverSections();
        if (discoveredSections.value.length !== prevCount) {
          attachObserver();
        }
      });
      mutationObserver.observe(document.body, { childList: true, subtree: true });
    }
  });
});

watch(
  () => route.path,
  () => {
    nextTick(() => {
      discoverSections();
      attachObserver();
    });
  }
);

watch(enableScrollSnap, (val) => {
  applyScrollSnap(val);
});

onUnmounted(() => {
  if (observer) {
    observer.disconnect();
    observer = null;
  }
  if (mutationObserver) {
    mutationObserver.disconnect();
    mutationObserver = null;
  }
  if (typeof window !== 'undefined') {
    window.removeEventListener('scroll', throttledScroll);
    window.removeEventListener('resize', throttledScroll);
  }
  applyScrollSnap(false);
});
</script>

<style scoped>
.ja-cinematic-nav {
  pointer-events: auto !important;
}

@media (min-width: 1280px) {
  .ja-cinematic-nav--right {
    right: 1.5rem !important;
  }
  .ja-cinematic-nav--left {
    left: 1.5rem !important;
  }
}

/* ==========================================================================
   Indicator Shape Animations
   ========================================================================== */

/* 1. Bar Glow */
@keyframes ja-bar-pulse {
  0%, 100% {
    box-shadow: 0 0 10px hsl(var(--primary) / 0.5), 0 0 20px hsl(var(--primary) / 0.25);
  }
  50% {
    box-shadow: 0 0 16px hsl(var(--primary) / 0.8), 0 0 28px hsl(var(--primary) / 0.4);
  }
}
.ja-cinematic-nav__bar-active {
  animation: ja-bar-pulse 2.8s ease-in-out infinite;
}

/* 2. Glass Pill Pulse */
@keyframes ja-pill-pulse {
  0%, 100% {
    box-shadow: 0 0 10px hsl(var(--primary) / 0.4), 0 0 20px hsl(var(--primary) / 0.2);
  }
  50% {
    box-shadow: 0 0 16px hsl(var(--primary) / 0.75), 0 0 32px hsl(var(--primary) / 0.35);
  }
}
.ja-cinematic-nav__pill-active {
  animation: ja-pill-pulse 2.8s ease-in-out infinite;
}

/* 3. Cyber Shard / Neon Aurora */
@keyframes ja-neon-aurora {
  0%, 100% {
    box-shadow: 0 0 12px hsl(var(--primary)), 0 0 24px hsl(var(--primary) / 0.5);
    filter: drop-shadow(0 0 4px hsl(var(--primary) / 0.6));
  }
  50% {
    box-shadow: 0 0 20px hsl(var(--primary)), 0 0 40px hsl(var(--primary) / 0.8);
    filter: drop-shadow(0 0 8px hsl(var(--primary) / 0.9));
  }
}
.ja-cinematic-nav__glow-active {
  animation: ja-neon-aurora 2.2s ease-in-out infinite;
}

/* ==========================================================================
   Theme-Specific Physical Shapes & Typography
   ========================================================================== */

/* ── 1. Sareupna: Cyberpunk / Developer Terminal / Obsidian & Shards ── */
[data-theme="sareupna"] .ja-cinematic-nav__dock {
  background-color: rgba(7, 9, 14, 0.88) !important;
  border-color: rgba(139, 92, 246, 0.3) !important;
  border-radius: 0.5rem !important; /* Tech chamfered dock */
}
[data-theme="sareupna"] .ja-cinematic-nav__rail-track {
  background-color: rgba(139, 92, 246, 0.25) !important;
}
[data-theme="sareupna"] .ja-cinematic-nav__tooltip {
  font-family: var(--sareupna-font-mono, 'Geist Mono', ui-monospace, monospace);
  border-radius: 0.25rem;
  background-color: rgba(12, 16, 24, 0.96) !important;
  border-color: rgba(139, 92, 246, 0.35) !important;
}
[data-theme="sareupna"] .ja-cinematic-nav__shape-bar {
  border-radius: 1px !important; /* Crisp terminal cursor slabs */
}
[data-theme="sareupna"] .ja-cinematic-nav__shape-shard {
  border-radius: 0px !important; /* Sharp razor cyber-diamond */
}

/* ── 2. Layung: Telecom ISP / Optical Fiber Transceiver & Laser Grid ── */
[data-theme="layung"] .ja-cinematic-nav__dock {
  background-color: rgba(7, 10, 15, 0.85) !important;
  border-color: rgba(0, 174, 239, 0.35) !important;
  border-radius: 0.75rem !important;
}
[data-theme="layung"] .ja-cinematic-nav__rail-track {
  background: linear-gradient(180deg, rgba(0, 174, 239, 0.1), rgba(0, 174, 239, 0.5), rgba(0, 174, 239, 0.1)) !important;
  box-shadow: 0 0 6px rgba(0, 174, 239, 0.3);
}
[data-theme="layung"] .ja-cinematic-nav__tooltip {
  font-family: var(--layung-font-mono, 'Geist Mono', ui-monospace, monospace);
  border-radius: 0.375rem;
  background-color: rgba(11, 17, 26, 0.95) !important;
  border-color: rgba(0, 174, 239, 0.4) !important;
}
[data-theme="layung"] .ja-cinematic-nav__glow-active {
  box-shadow: 0 0 16px #00aeef, 0 0 32px rgba(0, 174, 239, 0.6) !important;
}

/* ── 3. Janari: Editorial Modern Newspaper / Letterpress Ticks ── */
[data-theme="janari"] .ja-cinematic-nav__dock {
  border-radius: 0.25rem !important; /* Clean rectangular editorial cartridge */
  border-width: 1px !important;
}
[data-theme="janari"] .ja-cinematic-nav__tooltip {
  font-family: var(--janari-font-heading, ui-sans-serif, system-ui);
  border-radius: 0.125rem !important;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
[data-theme="janari"] .ja-cinematic-nav__shape-bar {
  border-radius: 0px !important; /* Editorial horizontal rule */
}
[data-theme="janari"] .ja-cinematic-nav__shape-pill {
  border-radius: 2px !important; /* Clean editorial block */
}
[data-theme="janari"] .ja-cinematic-nav__shape-orbital {
  border-radius: 0px !important; /* Typographic square dots */
}

/* ── 4. Sarangenge: Scholastic Dawn / Organic Rounded Pebble ── */
[data-theme="sarangenge"] .ja-cinematic-nav__dock {
  border-radius: 9999px !important; /* Ultra-friendly organic capsule */
  box-shadow: 0 10px 30px -5px rgba(245, 158, 11, 0.15), 0 8px 16px -6px rgba(15, 23, 42, 0.1) !important;
}
[data-theme="sarangenge"] .ja-cinematic-nav__tooltip {
  font-family: var(--sarangenge-font-body, ui-sans-serif, system-ui);
  border-radius: 0.75rem !important;
}
[data-theme="sarangenge"] .ja-cinematic-nav__shape-bar {
  border-radius: 9999px !important; /* Soft pebble pills */
}
[data-theme="sarangenge"] .ja-cinematic-nav__shape-pill {
  border-radius: 9999px !important;
}
</style>
