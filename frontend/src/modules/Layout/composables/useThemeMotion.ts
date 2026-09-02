import { onUnmounted, ref } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { coerceThemeFlag, motionIntensityScale } from '@/modules/Layout/composables/useThemeMotionSettings';

export type MotionVars = Record<string, unknown>;

let pluginRegistered = false;

function ensureGsap(): void {
    if (pluginRegistered || typeof window === 'undefined') return;
    gsap.registerPlugin(ScrollTrigger);
    pluginRegistered = true;
}

function resolveEl(el: Element | string | null | undefined): Element | null {
    if (!el) return null;
    if (typeof el === 'string') {
        return typeof document === 'undefined' ? null : document.querySelector(el);
    }
    return el;
}

const noopTween = { kill: () => {} };

/**
 * Real GSAP + ScrollTrigger wrapper for public themes.
 * Honors theme settings `animation_enabled`, `animation_intensity`, `parallax_enabled`
 * and `prefers-reduced-motion`.
 */
export function useThemeMotion() {
    ensureGsap();

    const { getSetting } = useTheme();
    const prefersReducedMotion = ref(false);
    const ctx = typeof window !== 'undefined' ? gsap.context(() => {}) : null;
    const cleanups: Array<() => void> = [];

    if (typeof window !== 'undefined') {
        const media = window.matchMedia('(prefers-reduced-motion: reduce)');
        prefersReducedMotion.value = media.matches;
        const onChange = (event: MediaQueryListEvent) => {
            prefersReducedMotion.value = event.matches;
        };
        media.addEventListener('change', onChange);
        cleanups.push(() => media.removeEventListener('change', onChange));
    }

    const isLive = (): boolean =>
        coerceThemeFlag(getSetting('animation_enabled', true), true) && !prefersReducedMotion.value;

    const isParallaxLive = (): boolean =>
        isLive() && coerceThemeFlag(getSetting('parallax_enabled', true), true);

    const scale = () => motionIntensityScale(getSetting('animation_intensity', 'normal'));

    const run = <T>(fn: () => T): T => {
        if (!ctx) return fn();
        let result!: T;
        ctx.add(() => {
            result = fn();
        });
        return result;
    };

    const snapTo = (target: unknown, vars: MotionVars) => {
        const dest = { ...vars };
        delete dest.scrollTrigger;
        delete dest.onComplete;
        delete dest.onUpdate;
        delete dest.stagger;
        delete dest.delay;
        delete dest.duration;
        delete dest.ease;
        delete dest.clearProps;
        gsap.set(target as gsap.TweenTarget, dest);
    };

    const motion = {
        set: (target: unknown, vars: MotionVars) => {
            run(() => gsap.set(target as gsap.TweenTarget, vars));
        },
        to: (target: unknown, vars: MotionVars, _position?: string | number) => {
            if (!isLive()) {
                snapTo(target, vars);
                const complete = vars.onComplete;
                if (typeof complete === 'function') (complete as () => void)();
                return noopTween;
            }
            return run(() => gsap.to(target as gsap.TweenTarget, vars as gsap.TweenVars));
        },
        from: (target: unknown, vars: MotionVars, _position?: string | number) => {
            if (!isLive()) return noopTween;
            return run(() => gsap.from(target as gsap.TweenTarget, vars as gsap.TweenVars));
        },
        fromTo: (target: unknown, fromVars: MotionVars, toVars: MotionVars, _position?: string | number) => {
            if (!isLive()) {
                snapTo(target, toVars);
                return noopTween;
            }
            return run(() =>
                gsap.fromTo(
                    target as gsap.TweenTarget,
                    fromVars as gsap.TweenVars,
                    toVars as gsap.TweenVars,
                ),
            );
        },
        killTweensOf: (target: unknown) => {
            gsap.killTweensOf(target as gsap.TweenTarget);
        },
        timeline: (vars?: MotionVars) => createTimeline(vars),
    };

    function createTimeline(vars: MotionVars = {}) {
        if (!isLive()) {
            const stub = {
                to: (el: unknown, v: MotionVars) => {
                    snapTo(el, v);
                    return stub;
                },
                from: () => stub,
                fromTo: (el: unknown, _from: MotionVars, to: MotionVars) => {
                    snapTo(el, to);
                    return stub;
                },
                add: () => stub,
                kill: () => {},
                restart: () => {},
                play: () => {},
                pause: () => {},
            };
            return stub;
        }
        return run(() => gsap.timeline(vars as gsap.TimelineVars));
    }

    const fadeInUp = (el: Element | string, opts: MotionVars = {}) => {
        const target = resolveEl(el);
        if (!target) return;
        const { distance, duration } = scale();
        const dist = Number(opts.distance ?? 32) * distance;
        const dur = Number(opts.duration ?? 0.7) * duration;
        if (!isLive()) {
            gsap.set(target, { opacity: 1, y: 0 });
            return;
        }
        run(() =>
            gsap.fromTo(
                target as gsap.TweenTarget,
                { y: dist, opacity: 0 },
                {
                    y: 0,
                    opacity: 1,
                    duration: dur,
                    delay: Number(opts.delay ?? 0),
                    ease: String(opts.ease || 'power3.out'),
                    immediateRender: false,
                    scrollTrigger: {
                        trigger: target,
                        start: String(opts.start || 'top 92%'),
                        toggleActions: String(opts.toggleActions || 'play none none none'),
                        once: true,
                    },
                },
            ),
        );
    };

    const fadeInLeft = (el: Element | string, opts: MotionVars = {}) => {
        const target = resolveEl(el);
        if (!target) return;
        const { distance, duration } = scale();
        if (!isLive()) {
            gsap.set(target, { opacity: 1, x: 0 });
            return;
        }
        run(() =>
            gsap.fromTo(
                target as gsap.TweenTarget,
                { x: -(Number(opts.distance ?? 40) * distance), opacity: 0 },
                {
                    x: 0,
                    opacity: 1,
                    duration: Number(opts.duration ?? 0.8) * duration,
                    delay: Number(opts.delay ?? 0),
                    ease: 'power3.out',
                    immediateRender: false,
                    scrollTrigger: {
                        trigger: target,
                        start: String(opts.start || 'top 92%'),
                        toggleActions: String(opts.toggleActions || 'play none none none'),
                        once: true,
                    },
                },
            ),
        );
    };

    const fadeInRight = (el: Element | string, opts: MotionVars = {}) => {
        const target = resolveEl(el);
        if (!target) return;
        const { distance, duration } = scale();
        if (!isLive()) {
            gsap.set(target, { opacity: 1, x: 0 });
            return;
        }
        run(() =>
            gsap.fromTo(
                target as gsap.TweenTarget,
                { x: Number(opts.distance ?? 40) * distance, opacity: 0 },
                {
                    x: 0,
                    opacity: 1,
                    duration: Number(opts.duration ?? 0.8) * duration,
                    delay: Number(opts.delay ?? 0),
                    ease: 'power3.out',
                    immediateRender: false,
                    scrollTrigger: {
                        trigger: target,
                        start: String(opts.start || 'top 92%'),
                        toggleActions: String(opts.toggleActions || 'play none none none'),
                        once: true,
                    },
                },
            ),
        );
    };

    const scaleReveal = (el: Element | string, opts: MotionVars = {}) => {
        const target = resolveEl(el);
        if (!target) return;
        const { duration } = scale();
        if (!isLive()) {
            gsap.set(target, { opacity: 1, scale: 1 });
            return;
        }
        run(() =>
            gsap.fromTo(
                target as gsap.TweenTarget,
                { scale: 0.94, opacity: 0 },
                {
                    scale: 1,
                    opacity: 1,
                    duration: Number(opts.duration ?? 0.75) * duration,
                    delay: Number(opts.delay ?? 0),
                    ease: 'power3.out',
                    immediateRender: false,
                    scrollTrigger: {
                        trigger: target,
                        start: String(opts.start || 'top 92%'),
                        toggleActions: String(opts.toggleActions || 'play none none none'),
                        once: true,
                    },
                },
            ),
        );
    };

    const staggerChildren = (parent: Element | string | null, childSelector: string, opts: MotionVars = {}) => {
        const container = resolveEl(parent);
        if (!container) return;
        const children = Array.from(container.querySelectorAll(childSelector));
        if (!children.length) return;
        const { distance, duration } = scale();
        if (!isLive()) {
            gsap.set(children, { opacity: 1, y: 0 });
            return;
        }
        run(() =>
            gsap.fromTo(
                children as gsap.TweenTarget,
                { y: Number(opts.distance ?? 28) * distance, opacity: 0 },
                {
                    y: 0,
                    opacity: 1,
                    duration: Number(opts.duration ?? 0.65) * duration,
                    stagger: Number(opts.stagger ?? 0.1),
                    delay: Number(opts.delay ?? 0),
                    ease: 'power3.out',
                    immediateRender: false,
                    scrollTrigger: {
                        trigger: container,
                        start: String(opts.start || 'top 95%'),
                        toggleActions: String(opts.toggleActions || 'play none none none'),
                        once: true,
                    },
                },
            ),
        );
    };

    const splitTextReveal = (el: Element | string | null, opts: MotionVars = {}) => {
        const target = resolveEl(el);
        if (!target) return;
        const units = target.querySelectorAll('.motion-split-inner');
        const nodes = units.length ? Array.from(units) : [target];
        if (!isLive()) {
            gsap.set(nodes, { opacity: 1, y: 0 });
            return;
        }
        const { duration } = scale();
        run(() =>
            gsap.from(nodes, {
                yPercent: units.length ? 110 : 20,
                opacity: 0,
                duration: (Number(opts.duration ?? 0.7) * duration),
                stagger: Number(opts.stagger ?? 0.05),
                delay: Number(opts.delay ?? 0),
                ease: 'power3.out',
            }),
        );
    };

    const splitTextRevealSafe = (el: HTMLElement | null, opts: MotionVars = {}) => splitTextReveal(el, opts);

    const parallaxBg = (el: Element | string, opts: MotionVars = {}) => {
        const target = resolveEl(el);
        if (!target || !isParallaxLive()) return;
        const { distance } = scale();
        run(() =>
            gsap.to(target, {
                y: Number(opts.distance ?? 80) * distance,
                ease: 'none',
                scrollTrigger: {
                    trigger: target,
                    start: String(opts.start || 'top bottom'),
                    end: String(opts.end || 'bottom top'),
                    scrub: typeof opts.scrub === 'number' || typeof opts.scrub === 'boolean' ? opts.scrub : true,
                },
            }),
        );
    };

    const floatingAnimation = (el: Element | string, opts: MotionVars = {}) => {
        const target = resolveEl(el);
        if (!target || !isLive()) return;
        run(() =>
            gsap.to(target, {
                y: Number(opts.distance ?? 12),
                duration: Number(opts.duration ?? 2.4),
                yoyo: true,
                repeat: -1,
                ease: 'sine.inOut',
            }),
        );
    };

    const marquee = (container: Element | string, opts: MotionVars = {}) => {
        const target = resolveEl(container);
        if (!target || !isLive()) return;
        const duration = Math.max(8, Number(opts.speed ?? opts.duration ?? 25));
        run(() =>
            gsap.to(target, {
                xPercent: -50,
                duration,
                ease: 'none',
                repeat: -1,
            }),
        );
    };

    const smartHeader = (el: Element | string) => {
        const target = resolveEl(el);
        if (!(target instanceof HTMLElement) || !isLive()) return;
        let last = 0;
        run(() =>
            ScrollTrigger.create({
                start: 0,
                end: 'max',
                onUpdate: (self) => {
                    const y = self.scroll();
                    if (y > last && y > 80) gsap.to(target, { yPercent: -100, duration: 0.35, ease: 'power2.out' });
                    else gsap.to(target, { yPercent: 0, duration: 0.35, ease: 'power2.out' });
                    last = y;
                },
            }),
        );
    };

    const magneticHover = (el: Element | string | Element, strength = 0.3) => {
        const target = resolveEl(el as Element | string);
        if (!(target instanceof HTMLElement) || !isLive()) return;
        const onMove = (event: MouseEvent) => {
            const rect = target.getBoundingClientRect();
            const x = (event.clientX - rect.left - rect.width / 2) * strength;
            const y = (event.clientY - rect.top - rect.height / 2) * strength;
            gsap.to(target, { x, y, duration: 0.35, ease: 'power2.out' });
        };
        const onLeave = () => {
            gsap.to(target, { x: 0, y: 0, duration: 0.45, ease: 'elastic.out(1, 0.4)' });
        };
        target.addEventListener('mousemove', onMove);
        target.addEventListener('mouseleave', onLeave);
        cleanups.push(() => {
            target.removeEventListener('mousemove', onMove);
            target.removeEventListener('mouseleave', onLeave);
        });
    };

    const counterUp = (el: HTMLElement | Element | string | null, raw?: unknown, opts: MotionVars = {}) => {
        const target = resolveEl(el as Element | string | null);
        if (!(target instanceof HTMLElement)) return;
        const endValue = Number(raw ?? target.textContent?.replace(/[^\d.]/g, '') ?? 0);
        if (!Number.isFinite(endValue)) return;
        if (!isLive()) {
            target.textContent = Math.round(endValue).toLocaleString('id-ID');
            return;
        }
        const proxy = { val: 0 };
        run(() =>
            gsap.to(proxy, {
                val: endValue,
                duration: Number(opts.duration ?? 1.2),
                delay: Number(opts.delay ?? 0),
                ease: 'power2.out',
                onUpdate: () => {
                    target.textContent = Math.round(proxy.val).toLocaleString('id-ID');
                },
                scrollTrigger: { trigger: target, start: 'top 90%' },
            }),
        );
    };

    const batchFadeInUp = (selector: string, opts: MotionVars = {}) => {
        if (typeof document === 'undefined') return;
        const nodes = Array.from(document.querySelectorAll(selector));
        nodes.forEach((node) => fadeInUp(node, opts));
    };

    const cleanup = () => {
        ctx?.revert();
        cleanups.forEach((fn) => fn());
        cleanups.length = 0;
    };

    onUnmounted(cleanup);

    return {
        prefersReducedMotion,
        motion,
        ScrollTrigger,
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
        isAnimationEnabled: isLive,
    };
}
