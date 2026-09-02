import { onMounted, watch, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import { PUBLIC_HEADER_SCROLL_OFFSET } from '@/engine/router/publicScrollBehavior';

/** Scroll to `#id` after async theme pages mount (sticky header offset). Page-top is handled by the router. */
export function useThemeHashScroll(headerOffset = PUBLIC_HEADER_SCROLL_OFFSET) {
  const route = useRoute();

  const scrollToHash = async () => {
    const raw = route.hash?.replace(/^#/, '').trim();
    if (!raw) return;

    await nextTick();

    const tryScroll = (attempt = 0) => {
      const el = document.getElementById(raw);
      if (el) {
        const top = el.getBoundingClientRect().top + window.scrollY - headerOffset;
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
        return;
      }
      if (attempt < 12) {
        window.setTimeout(() => tryScroll(attempt + 1), 50);
      }
    };

    requestAnimationFrame(() => tryScroll());
  };

  onMounted(() => {
    if (route.hash) void scrollToHash();
  });

  watch(
    () => route.hash,
    (hash) => {
      if (hash) void scrollToHash();
    },
  );
}
