import { onUnmounted, ref } from 'vue';

/**
 * Unused by public themes. Real GSAP runtime lives in
 * `@/modules/Layout/composables/useThemeMotion`.
 */

export interface AnimationOptions {
  delay?: number;
  duration?: number;
  ease?: string;
  start?: string;
  end?: string;
  toggleActions?: string;
  distance?: number;
  stagger?: number;
  scrub?: boolean | number;
}

type Cleanup = () => void;

interface ScrollTriggerInstance {
  kill: () => void;
}

const noop = () => {};

const toArray = (el: unknown): Element[] => {
  if (!el) return [];
  if (Array.isArray(el)) return el as Element[];
  if (typeof NodeList !== 'undefined' && el instanceof NodeList) return Array.from(el as NodeListOf<Element>);
  return [el as Element];
};

const resolveTarget = (el: Element | string): Element | null => {
  if (typeof el === 'string') return document.querySelector(el);
  return el;
};

const applyStyles = (el: Element, styles: Record<string, unknown>) => {
  const node = el as HTMLElement;
  if (!node.style) return;
  for (const [key, val] of Object.entries(styles)) {
    if (key === 'x') node.style.transform = `translateX(${Number(val) || 0}px)`;
    else if (key === 'y') node.style.transform = `translateY(${Number(val) || 0}px)`;
    else if (key === 'opacity') node.style.opacity = String(val);
  }
};

const createTimelineStub = () => ({
  to: (..._args: unknown[]) => createTimelineStub(),
  from: (..._args: unknown[]) => createTimelineStub(),
  fromTo: (..._args: unknown[]) => createTimelineStub(),
  add: (..._args: unknown[]) => createTimelineStub(),
  play: noop,
  pause: noop,
  kill: noop,
});

export function useThemeMotion() {
  const prefersReducedMotion = ref(false);
  const cleanups: Cleanup[] = [];

  if (typeof window !== 'undefined') {
    const media = window.matchMedia('(prefers-reduced-motion: reduce)');
    prefersReducedMotion.value = media.matches;
    const onChange = (e: MediaQueryListEvent) => {
      prefersReducedMotion.value = e.matches;
    };
    media.addEventListener('change', onChange);
    cleanups.push(() => media.removeEventListener('change', onChange));
  }

  const isAnimationEnabled = (): boolean => !prefersReducedMotion.value;

  const reveal = (target: Element | null) => {
    if (!target) return;
    applyStyles(target, { opacity: 1, x: 0, y: 0 });
  };

  const revealMany = (targets: Element[]) => {
    targets.forEach((target) => reveal(target));
  };

  const fadeInUp = (el: Element | string, _opts: AnimationOptions = {}) => reveal(resolveTarget(el));
  const fadeInLeft = (el: Element | string, _opts: AnimationOptions = {}) => reveal(resolveTarget(el));
  const fadeInRight = (el: Element | string, _opts: AnimationOptions = {}) => reveal(resolveTarget(el));
  const scaleReveal = (el: Element | string, _opts: AnimationOptions = {}) => reveal(resolveTarget(el));
  const parallaxBg = (_el: Element | string, _opts: Record<string, unknown> = {}) => {};
  const splitTextReveal = (el: Element | string, _opts: AnimationOptions = {}) => reveal(resolveTarget(el));
  const splitTextRevealSafe = (el: HTMLElement | null, _opts: AnimationOptions = {}) => reveal(el);
  const floatingAnimation = (_el: Element | string, _opts: Record<string, unknown> = {}) => {};
  const marquee = (_container: Element | string, _opts: Record<string, unknown> = {}) => {};
  const smartHeader = (_el: Element | string) => {};
  const batchFadeInUp = (selector: string, _opts: AnimationOptions = {}) => revealMany(Array.from(document.querySelectorAll(selector)));

  const staggerChildren = (parent: Element | string, childSelector: string, _opts: AnimationOptions = {}) => {
    const container = resolveTarget(parent);
    if (!container) return;
    revealMany(Array.from(container.querySelectorAll(childSelector)));
  };

  const counterUp = (el: Element | string, endValue: number, _opts: AnimationOptions = {}) => {
    const target = resolveTarget(el);
    if (!(target instanceof HTMLElement)) return;
    target.textContent = Number.isFinite(endValue) ? Math.round(endValue).toLocaleString('id-ID') : target.textContent;
  };

  const magneticHover = (el: Element | string, _strength = 0.3) => {
    if (!isAnimationEnabled()) return;
    const target = resolveTarget(el);
    if (!(target instanceof HTMLElement)) return;
    const onLeave = () => {
      target.style.transform = '';
    };
    target.addEventListener('mouseleave', onLeave);
    cleanups.push(() => target.removeEventListener('mouseleave', onLeave));
  };

  const cleanup = () => {
    cleanups.forEach((fn) => fn());
    cleanups.length = 0;
  };
  const createTimeline = (_vars?: Record<string, unknown>) => ({
    to: (_target: unknown, _vars: Record<string, unknown>, _pos?: string | number) => createTimelineStub(),
    from: (_target: unknown, _vars: Record<string, unknown>, _pos?: string | number) => createTimelineStub(),
    fromTo: (_target: unknown, _fromVars: Record<string, unknown>, _toVars: Record<string, unknown>, _pos?: string | number) => createTimelineStub(),
    add: (_anim: unknown, _pos?: string | number) => createTimelineStub(),
    play: noop,
    pause: noop,
    kill: noop,
  });

  onUnmounted(cleanup);

  const ScrollTrigger = {
    create: (_opts?: Record<string, unknown>): ScrollTriggerInstance => ({ kill: noop }),
    refresh: noop,
  };

  const motion = {
    to: (target: unknown, vars: Record<string, unknown>, _pos?: string | number) => {
      toArray(target).forEach((el) => applyStyles(el, vars));
      return { kill: noop, scrollTrigger: null };
    },
    fromTo: (
      target: unknown,
      _fromVars: Record<string, unknown>,
      toVars: Record<string, unknown>,
      _pos?: string | number
    ) => {
      toArray(target).forEach((el) => applyStyles(el, toVars));
      return { kill: noop, scrollTrigger: null };
    },
    set: (target: unknown, vars: Record<string, unknown>) => {
      toArray(target).forEach((el) => applyStyles(el, vars));
    },
    timeline: (_vars?: Record<string, unknown>) => createTimeline(),
  };

  return {
    prefersReducedMotion,
    fadeInUp,
    fadeInLeft,
    fadeInRight,
    scaleReveal,
    parallaxBg,
    staggerChildren,
    splitTextReveal,
    splitTextRevealSafe,
    counterUp,
    magneticHover,
    floatingAnimation,
    marquee,
    smartHeader,
    createTimeline,
    batchFadeInUp,
    cleanup,
    motion,
    ScrollTrigger,
  };
}
