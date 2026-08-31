import { onMounted, watch, nextTick } from 'vue';
import { useRoute } from 'vue-router';

/** Smooth-scroll to `#id` on mount and when route hash changes (sticky header offset). */
export function useThemeHashScroll(headerOffset = 112) {
  const route = useRoute();

  const scrollToHash = async () => {
    const raw = route.hash?.replace(/^#/, '').trim();
    if (!raw) return;

    await nextTick();
    requestAnimationFrame(() => {
      const el = document.getElementById(raw);
      if (!el) return;
      const top = el.getBoundingClientRect().top + window.scrollY - headerOffset;
      window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
    });
  };

  onMounted(scrollToHash);
  watch(() => route.hash, scrollToHash);
}
