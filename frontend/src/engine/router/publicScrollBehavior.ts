import type { RouterScrollBehavior } from 'vue-router';

/** Sticky public header clearance for hash anchors (px). */
export const PUBLIC_HEADER_SCROLL_OFFSET = 80;

function waitForPaint(): Promise<void> {
  return new Promise((resolve) => {
    requestAnimationFrame(() => {
      window.setTimeout(resolve, 40);
    });
  });
}

/**
 * Smart public scroll:
 * - hash → scroll to that section (below sticky header)
 * - browser back/forward → restore previous position
 * - new page (menu / CTA) → top of the page
 */
export const publicScrollBehavior: RouterScrollBehavior = async (to, _from, savedPosition) => {
  await waitForPaint();

  if (to.hash) {
    return {
      el: to.hash,
      top: PUBLIC_HEADER_SCROLL_OFFSET,
      behavior: 'smooth',
    };
  }

  if (savedPosition) {
    return savedPosition;
  }

  return { top: 0, left: 0, behavior: 'auto' };
};
