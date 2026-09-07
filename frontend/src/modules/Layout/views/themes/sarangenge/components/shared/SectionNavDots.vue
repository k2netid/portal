<template>
  <Teleport to="body">
    <nav
      aria-label="Navigasi Seksi Beranda"
      class="sarangenge-nav-dots hidden md:flex flex-col items-end gap-2 py-2.5 px-1.5 rounded-full bg-background/50 hover:bg-background/90 backdrop-blur-md border border-border/50 shadow-xl"
      style="position: fixed !important; top: 50% !important; transform: translateY(-50%) !important; right: 0.75rem !important; z-index: 99999 !important; pointer-events: auto !important;"
    >
      <button
        v-for="item in sections"
        :key="item.id"
        type="button"
        :aria-label="item.label"
        :aria-current="activeSectionId === item.id ? 'true' : undefined"
        class="group relative flex items-center justify-end p-1 focus:outline-none"
        @click="scrollTo(item.id)"
      >
        <!-- Tooltip Label (Floating on Hover) -->
        <span
          class="absolute right-7 px-2.5 py-1 rounded-md text-xs font-medium whitespace-nowrap bg-card text-foreground shadow-md border border-border pointer-events-none opacity-0 translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-200"
        >
          {{ item.label }}
        </span>

        <!-- Indicator Dot / Pill -->
        <span
          class="transition-all duration-300 block"
          :class="[
            activeSectionId === item.id
              ? 'w-2.5 h-6 rounded-full bg-gradient-to-b from-amber-400 to-amber-500 shadow-sm shadow-amber-500/50 ring-2 ring-amber-400/30'
              : 'w-2 h-2 rounded-full bg-muted-foreground/30 group-hover:bg-foreground/60 group-hover:scale-125'
          ]"
        />
      </button>
    </nav>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { throttle } from '@/shared/utils/performance';

export interface SectionNavItem {
  id: string;
  label: string;
}

const props = defineProps<{
  sections: SectionNavItem[];
}>();

const activeSectionId = ref<string>(props.sections[0]?.id || '');
let observer: IntersectionObserver | null = null;

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
    activeSectionId.value = id;
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
        if (primary) {
          activeSectionId.value = primary.target.id;
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

  // Also run initial scroll check
  handleScroll();
};

onMounted(() => {
  nextTick(() => {
    attachObserver();
    window.addEventListener('scroll', throttledScroll, { passive: true });
    window.addEventListener('resize', throttledScroll, { passive: true });
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
.sarangenge-nav-dots {
  position: fixed !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
  right: 0.75rem !important;
  z-index: 99999 !important;
  pointer-events: auto !important;
  opacity: 0.85;
  transition: opacity 0.2s ease, background-color 0.2s ease;
}

@media (min-width: 1280px) {
  .sarangenge-nav-dots {
    right: 1.5rem !important;
  }
}

.sarangenge-nav-dots:hover {
  opacity: 1;
}
</style>
