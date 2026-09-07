<template>
  <nav
    aria-label="Navigasi Seksi Beranda"
    class="sarangenge-nav-dots fixed right-3 xl:right-6 top-1/2 -translate-y-1/2 z-50 hidden md:flex flex-col items-end gap-2.5 py-3 px-1.5 rounded-full bg-background/50 hover:bg-background/90 backdrop-blur-md border border-border/50 shadow-xl transition-all duration-300"
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
</template>

<script setup lang="ts">
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue';

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
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    activeSectionId.value = id;
  }
};

const attachObserver = () => {
  if (typeof window === 'undefined' || !('IntersectionObserver' in window)) return;

  if (observer) {
    observer.disconnect();
  }

  observer = new IntersectionObserver(
    (entries) => {
      // Find the entry that has the highest intersection ratio or is currently intersecting
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
};

onMounted(() => {
  nextTick(() => {
    attachObserver();
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
});
</script>

<style scoped>
.sarangenge-nav-dots {
  opacity: 0.75;
}
.sarangenge-nav-dots:hover {
  opacity: 1;
}
</style>
