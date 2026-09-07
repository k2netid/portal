<template>
  <Teleport to="body">
    <nav
      v-if="isVisible"
      data-plugin="cinematic-nav"
      :aria-label="t('nav_sections.aria_label', 'Navigasi Seksi Halaman')"
      class="ja-cinematic-nav hidden md:block"
      :class="[
        `ja-cinematic-nav--${dockPosition}`,
        `ja-cinematic-nav--${stylePreset}`
      ]"
      :style="navPositionStyle"
    >
      <!-- Inner Dock Container with GSAP motion -->
      <div
        ref="innerDockRef"
        class="ja-cinematic-nav__dock flex flex-col items-end gap-2 transition-colors duration-300"
        :class="dockPresetClasses"
      >
        <button
          v-for="(item, index) in discoveredSections"
          :key="item.id"
          :data-section-id="item.id"
          type="button"
          :aria-label="item.label"
          :aria-current="activeSectionId === item.id ? 'true' : undefined"
          class="ja-cinematic-nav__btn group relative flex items-center justify-end p-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-full"
          @click="handleDotClick(item.id, $event)"
        >
          <!-- Tooltip Label (Floating on Hover) -->
          <div
            v-if="showTooltips"
            class="ja-cinematic-nav__tooltip absolute right-7 px-2.5 py-1 rounded-lg text-xs font-semibold whitespace-nowrap shadow-lg pointer-events-none opacity-0 translate-x-1.5 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-200 backdrop-blur-md flex items-center gap-1.5"
            :class="tooltipPresetClasses"
          >
            <span v-if="stylePreset === 'bars'" class="text-[10px] font-mono text-primary font-bold">
              {{ String(index + 1).padStart(2, '0') }}
            </span>
            <span>{{ item.label }}</span>
            <!-- Micro pointing caret -->
            <span
              class="absolute -right-1 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rotate-45"
              :class="tooltipCaretClasses"
            />
          </div>

          <!-- Indicator based on preset -->
          <!-- 1. BARS PRESET -->
          <template v-if="stylePreset === 'bars'">
            <span
              class="ja-cinematic-nav__indicator transition-all duration-300 block origin-center rounded-full"
              :class="[
                activeSectionId === item.id
                  ? 'ja-cinematic-nav__bar-active w-6 h-1.5 bg-primary shadow-sm shadow-primary/50 ring-1 ring-primary/40'
                  : 'w-3.5 h-1 bg-muted-foreground/35 group-hover:w-5 group-hover:bg-foreground/80'
              ]"
            />
          </template>

          <!-- 2. MINIMAL PRESET -->
          <template v-else-if="stylePreset === 'minimal'">
            <span
              class="ja-cinematic-nav__indicator transition-all duration-300 block origin-center rounded-full"
              :class="[
                activeSectionId === item.id
                  ? 'ja-cinematic-nav__minimal-active w-3 h-3 bg-primary shadow-md shadow-primary/40 ring-4 ring-primary/20'
                  : 'w-2 h-2 bg-foreground/30 group-hover:bg-foreground/80 group-hover:scale-125'
              ]"
            />
          </template>

          <!-- 3. GLOW PRESET -->
          <template v-else-if="stylePreset === 'glow'">
            <span
              class="ja-cinematic-nav__indicator transition-all duration-300 block origin-center rounded-full"
              :class="[
                activeSectionId === item.id
                  ? 'ja-cinematic-nav__glow-active w-2.5 h-6 bg-gradient-to-b from-primary via-primary/90 to-primary/70 ring-2 ring-primary/60'
                  : 'w-2 h-2 bg-primary/40 group-hover:bg-primary group-hover:scale-125 group-hover:shadow-[0_0_8px_var(--primary)]'
              ]"
            />
          </template>

          <!-- 4. GLASS PRESET (DEFAULT) -->
          <template v-else>
            <span
              class="ja-cinematic-nav__indicator transition-all duration-300 block origin-center rounded-full"
              :class="[
                activeSectionId === item.id
                  ? 'ja-cinematic-nav__pill-active w-2.5 h-6 bg-primary shadow-sm shadow-primary/50 ring-2 ring-primary/30'
                  : 'w-2 h-2 bg-muted-foreground/35 group-hover:bg-foreground/75 group-hover:scale-125'
              ]"
            />
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
  const isRight = dockPosition.value !== 'left';
  return {
    position: 'fixed' as const,
    top: '50%',
    transform: 'translateY(-50%)',
    right: isRight ? '0.75rem' : 'auto',
    left: isRight ? 'auto' : '0.75rem',
    zIndex: 9990,
    pointerEvents: 'auto' as const,
  };
});

const dockPresetClasses = computed(() => {
  switch (stylePreset.value) {
    case 'minimal':
      return 'bg-transparent border-none shadow-none py-1 px-1';
    case 'glow':
      return 'bg-slate-950/85 hover:bg-slate-900/95 backdrop-blur-xl border border-primary/40 shadow-2xl shadow-primary/20 py-2.5 px-1.5 rounded-full';
    case 'bars':
      return 'bg-background/60 hover:bg-background/95 backdrop-blur-md border border-border/50 shadow-xl py-3 px-2 rounded-2xl';
    case 'glass':
    default:
      return 'bg-background/60 hover:bg-background/95 backdrop-blur-md border border-border/50 shadow-xl py-2.5 px-1.5 rounded-full';
  }
});

const tooltipPresetClasses = computed(() => {
  switch (stylePreset.value) {
    case 'glow':
      return 'bg-slate-950/95 text-foreground border border-primary/40 shadow-primary/20';
    case 'minimal':
      return 'bg-card/95 text-foreground border border-border shadow-md';
    case 'bars':
      return 'bg-card/95 text-foreground border border-primary/30 shadow-lg';
    case 'glass':
    default:
      return 'bg-card/95 text-foreground border border-primary/25 shadow-lg';
  }
});

const tooltipCaretClasses = computed(() => {
  switch (stylePreset.value) {
    case 'glow':
      return 'bg-slate-950 border-r border-t border-primary/40';
    case 'minimal':
      return 'bg-card border-r border-t border-border';
    case 'bars':
      return 'bg-card border-r border-t border-primary/30';
    case 'glass':
    default:
      return 'bg-card border-r border-t border-primary/25';
  }
});

// Helper to humanize ID into a clean label
const humanizeSectionId = (id: string): string => {
  const clean = id.replace(/^section[-_]/i, '');
  // Check theme-specific translation first
  const theme = activeTheme.value?.slug || '';
  const themeKey = `theme.${theme}.nav.${clean}`;
  if (te(themeKey)) return t(themeKey);

  // Common shared keys
  const commonKey = `nav_sections.${clean}`;
  if (te(commonKey)) return t(commonKey);

  // Fallback to title case
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
    '#section-testimonials',
    '#section-cta',
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
    { x: 25, opacity: 0 },
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
      { scaleY: 0.4, scaleX: 1.3 },
      { scaleY: 1, scaleX: 1, duration: 0.4, ease: 'elastic.out(1.2, 0.45)' }
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

.ja-cinematic-nav__dock {
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.15);
}

@keyframes ja-pill-glow {
  0%, 100% {
    box-shadow: 0 0 10px var(--primary, rgba(99, 102, 241, 0.4)), 0 0 20px var(--primary, rgba(99, 102, 241, 0.2));
  }
  50% {
    box-shadow: 0 0 18px var(--primary, rgba(99, 102, 241, 0.8)), 0 0 35px var(--primary, rgba(99, 102, 241, 0.35));
  }
}

.ja-cinematic-nav__pill-active {
  animation: ja-pill-glow 2.5s ease-in-out infinite;
}

@keyframes ja-cyber-glow {
  0%, 100% {
    box-shadow: 0 0 12px var(--primary, #6366f1), 0 0 25px var(--primary, rgba(99, 102, 241, 0.4));
  }
  50% {
    box-shadow: 0 0 22px var(--primary, #6366f1), 0 0 45px var(--primary, rgba(99, 102, 241, 0.75));
  }
}

.ja-cinematic-nav__glow-active {
  animation: ja-cyber-glow 2s ease-in-out infinite;
}

.ja-cinematic-nav__bar-active {
  animation: ja-pill-glow 2.5s ease-in-out infinite;
}

.ja-cinematic-nav__minimal-active {
  animation: ja-pill-glow 2.5s ease-in-out infinite;
}
</style>
