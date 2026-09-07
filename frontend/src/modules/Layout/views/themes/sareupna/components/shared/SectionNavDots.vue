<template>
  <Teleport to="body">
    <nav
      :aria-label="t('nav_sections.aria_label', 'Navigasi Seksi Beranda')"
      class="sareupna-nav-dots"
      :class="[showOnMobile ? 'block' : 'hidden md:block']"
      :style="rootStyles"
    >
      <!-- Inner Dock Container (animated with GSAP, isolated from vertical center anchor) -->
      <div
        ref="innerDockRef"
        class="sareupna-nav-dock flex flex-col gap-2 transition-colors duration-300"
        :class="[dockPresetClasses, position === 'left' ? 'items-start' : 'items-end']"
      >
        <button
          v-for="(item, index) in sections"
          :key="item.id"
          :data-section-id="item.id"
          type="button"
          :aria-label="item.label"
          :aria-current="activeSectionId === item.id ? 'true' : undefined"
          class="sareupna-nav-btn group relative flex items-center p-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-full"
          :class="[position === 'left' ? 'justify-start' : 'justify-end']"
          @click="handleClick(item.id, $event)"
        >
          <!-- Tooltip Label (Floating on Hover or Always) -->
          <div
            v-if="labelMode !== 'none'"
            class="sareupna-nav-tooltip absolute px-2.5 py-1 rounded-lg text-xs font-semibold whitespace-nowrap shadow-lg pointer-events-none transition-all duration-200 backdrop-blur-md flex items-center gap-1.5"
            :class="[tooltipPresetClasses, tooltipPositionClasses]"
          >
            <span v-if="stylePreset === 'bars'" class="text-[10px] font-mono text-primary font-bold">
              {{ String(index + 1).padStart(2, '0') }}
            </span>
            <span>{{ item.label }}</span>
            <!-- Micro pointing triangle caret -->
            <span
              class="absolute top-1/2 -translate-y-1/2 w-1.5 h-1.5 rotate-45"
              :class="[tooltipCaretClasses, caretPositionClasses]"
            />
          </div>

          <!-- Indicator based on preset -->
          <!-- 1. BARS PRESET -->
          <template v-if="stylePreset === 'bars'">
            <span
              class="sareupna-nav-indicator transition-all duration-300 block origin-center rounded-full"
              :class="[
                activeSectionId === item.id
                  ? 'sareupna-nav-bar-active w-6 h-1.5 bg-primary shadow-sm shadow-primary/50 ring-1 ring-primary/40'
                  : 'w-3.5 h-1 bg-muted-foreground/35 group-hover:w-5 group-hover:bg-foreground/80'
              ]"
            />
          </template>

          <!-- 2. MINIMAL PRESET -->
          <template v-else-if="stylePreset === 'minimal'">
            <span
              class="sareupna-nav-indicator transition-all duration-300 block origin-center rounded-full"
              :class="[
                activeSectionId === item.id
                  ? 'sareupna-nav-minimal-active w-3 h-3 bg-primary shadow-md shadow-primary/40 ring-4 ring-primary/20'
                  : 'w-2 h-2 bg-foreground/30 group-hover:bg-foreground/80 group-hover:scale-125'
              ]"
            />
          </template>

          <!-- 3. GLOW PRESET -->
          <template v-else-if="stylePreset === 'glow'">
            <span
              class="sareupna-nav-indicator transition-all duration-300 block origin-center rounded-full"
              :class="[
                activeSectionId === item.id
                  ? 'sareupna-nav-glow-active w-2.5 h-6 bg-primary ring-2 ring-primary/60'
                  : 'w-2 h-2 bg-primary/40 group-hover:bg-primary group-hover:scale-125 group-hover:shadow-[0_0_8px_hsl(var(--primary))]'
              ]"
            />
          </template>

          <!-- 4. GLASS PRESET (DEFAULT) -->
          <template v-else>
            <span
              class="sareupna-nav-indicator transition-all duration-300 block origin-center rounded-full"
              :class="[
                activeSectionId === item.id
                  ? 'sareupna-nav-pill-active w-2.5 h-6 bg-primary shadow-sm shadow-primary/50 ring-2 ring-primary/30'
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
import { ref, computed, watch, nextTick, onMounted, onUnmounted, type CSSProperties } from 'vue';
import gsap from 'gsap';
import { throttle } from '@/shared/utils/performance';
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';

export interface SectionNavItem {
  id: string;
  label: string;
}

interface Props {
  sections: SectionNavItem[];
  position?: 'right' | 'left';
  stylePreset?: 'glass' | 'minimal' | 'glow' | 'bars';
  labelMode?: 'hover' | 'always' | 'none';
  showOnMobile?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  position: 'right',
  stylePreset: 'glass',
  labelMode: 'hover',
  showOnMobile: false,
});

const { t } = useThemeI18n('sareupna');
const { isAnimationEnabled } = useThemeMotion();

const activeSectionId = ref<string>('');
const innerDockRef = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

const rootStyles = computed<CSSProperties>(() => {
  const isLeft = props.position === 'left';
  return {
    position: 'fixed',
    top: '50%',
    transform: 'translateY(-50%)',
    left: isLeft ? '0.75rem' : 'auto',
    right: isLeft ? 'auto' : '0.75rem',
    zIndex: 99999,
    pointerEvents: 'auto',
  };
});

const dockPresetClasses = computed(() => {
  switch (props.stylePreset) {
    case 'minimal':
      return 'bg-transparent border-none shadow-none py-1 px-1';
    case 'glow':
      return 'bg-zinc-950/90 dark:bg-black/95 hover:bg-zinc-900/95 backdrop-blur-xl border border-primary/40 shadow-2xl shadow-primary/15 py-2.5 px-1.5 rounded-full';
    case 'bars':
      return 'bg-background/70 hover:bg-background/95 backdrop-blur-md border border-border/60 shadow-xl py-3 px-2 rounded-2xl';
    case 'glass':
    default:
      return 'bg-background/70 hover:bg-background/95 backdrop-blur-md border border-border/60 shadow-xl py-2.5 px-1.5 rounded-full';
  }
});

const tooltipPositionClasses = computed(() => {
  const isLeft = props.position === 'left';
  if (isLeft) {
    return [
      'left-7',
      props.labelMode === 'always'
        ? 'opacity-100 translate-x-0'
        : 'opacity-0 -translate-x-1.5 group-hover:opacity-100 group-hover:translate-x-0',
    ];
  }
  return [
    'right-7',
    props.labelMode === 'always'
      ? 'opacity-100 translate-x-0'
      : 'opacity-0 translate-x-1.5 group-hover:opacity-100 group-hover:translate-x-0',
  ];
});

const caretPositionClasses = computed(() => {
  return props.position === 'left' ? '-left-1' : '-right-1';
});

const tooltipPresetClasses = computed(() => {
  switch (props.stylePreset) {
    case 'glow':
      return 'bg-zinc-950/95 text-zinc-100 border border-primary/40 shadow-primary/20';
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
  const isLeft = props.position === 'left';
  const borderSides = isLeft ? 'border-l border-b' : 'border-r border-t';
  switch (props.stylePreset) {
    case 'glow':
      return `bg-zinc-950 ${borderSides} border-primary/40`;
    case 'minimal':
      return `bg-card ${borderSides} border-border`;
    case 'bars':
      return `bg-card ${borderSides} border-primary/30`;
    case 'glass':
    default:
      return `bg-card ${borderSides} border-primary/25`;
  }
});

const playEntranceAnimation = () => {
  if (!innerDockRef.value || !isAnimationEnabled()) return;

  const enterX = props.position === 'left' ? -25 : 25;
  gsap.fromTo(
    innerDockRef.value,
    { x: enterX, opacity: 0 },
    { x: 0, opacity: 1, duration: 0.5, ease: 'power3.out' }
  );

  const dots = innerDockRef.value.querySelectorAll('.sareupna-nav-btn');
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
  const target = innerDockRef.value.querySelector(`[data-section-id="${sectionId}"] .sareupna-nav-indicator`);
  if (target) {
    gsap.fromTo(
      target,
      { scaleY: 0.4, scaleX: 1.3 },
      { scaleY: 1, scaleX: 1, duration: 0.4, ease: 'elastic.out(1.2, 0.45)' }
    );
  }
};

const handleClick = (id: string, event?: MouseEvent) => {
  if (event && isAnimationEnabled()) {
    const currentBtn = event.currentTarget as HTMLElement;
    const indicator = currentBtn?.querySelector('.sareupna-nav-indicator');
    if (indicator) {
      gsap.fromTo(
        indicator,
        { scale: 0.75 },
        { scale: 1, duration: 0.3, ease: 'back.out(2)' }
      );
    }
  }
  scrollTo(id);
};

const scrollTo = (id: string) => {
  const el = document.getElementById(id);
  if (el) {
    const navOffset = 72; // header height 4.5rem
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
  if (scrollY + viewportHeight >= docHeight - 80 && props.sections.length > 0) {
    const lastSection = props.sections[props.sections.length - 1];
    if (lastSection && activeSectionId.value !== lastSection.id) {
      activeSectionId.value = lastSection.id;
      animateActiveChange(lastSection.id);
    }
    return;
  }

  const triggerLine = scrollY + viewportHeight * 0.35;

  let current = '';
  for (const item of props.sections) {
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

    props.sections.forEach((item) => {
      const el = document.getElementById(item.id);
      if (el && observer) {
        observer.observe(el);
      }
    });
  }

  handleScroll();
};

onMounted(() => {
  nextTick(() => {
    attachObserver();
    window.addEventListener('scroll', throttledScroll, { passive: true });
    window.addEventListener('resize', throttledScroll, { passive: true });
    playEntranceAnimation();
  });
});

watch(
  () => props.sections,
  () => {
    nextTick(() => {
      attachObserver();
    });
  },
  { deep: true }
);

onUnmounted(() => {
  if (observer) {
    observer.disconnect();
    observer = null;
  }
  if (typeof window !== 'undefined') {
    window.removeEventListener('scroll', throttledScroll);
    window.removeEventListener('resize', throttledScroll);
  }
});
</script>

<style scoped>
.sareupna-nav-dots {
  position: fixed !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
  right: 0.75rem !important;
  z-index: 99999 !important;
  pointer-events: auto !important;
}

@media (min-width: 1280px) {
  .sareupna-nav-dots {
    right: 1.5rem !important;
  }
}

.sareupna-nav-dock {
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.15);
}

@keyframes sareupna-pill-glow {
  0%, 100% {
    box-shadow: 0 0 10px hsl(var(--primary) / 0.4), 0 0 20px hsl(var(--primary) / 0.2);
  }
  50% {
    box-shadow: 0 0 18px hsl(var(--primary) / 0.8), 0 0 35px hsl(var(--primary) / 0.35);
  }
}

.sareupna-nav-pill-active {
  animation: sareupna-pill-glow 2.5s ease-in-out infinite;
}

@keyframes sareupna-cyber-glow {
  0%, 100% {
    box-shadow: 0 0 12px hsl(var(--primary)), 0 0 25px hsl(var(--primary) / 0.4);
  }
  50% {
    box-shadow: 0 0 22px hsl(var(--primary)), 0 0 45px hsl(var(--primary) / 0.75);
  }
}

.sareupna-nav-glow-active {
  animation: sareupna-cyber-glow 2s ease-in-out infinite;
}

.sareupna-nav-bar-active {
  animation: sareupna-pill-glow 2.5s ease-in-out infinite;
}

.sareupna-nav-minimal-active {
  animation: sareupna-pill-glow 2.5s ease-in-out infinite;
}
</style>
