import { logger } from '@/shared/utils/logger';

/**
 * Stub for Theme Motion (GSAP wrapper)
 * This satisfy TypeScript requirements while providing a safe fallback if GSAP is missing.
 */
export function useThemeMotion() {
    const dummyMotion = {
        to: (el: any, vars: any) => {
            logger.debug('Motion.to', { el, vars });
            return { kill: () => {} };
        },
        from: (el: any, vars: any) => {
            logger.debug('Motion.from', { el, vars });
            return { kill: () => {} };
        },
        fromTo: (el: any, fromVars: any, toVars: any) => {
            logger.debug('Motion.fromTo', { el, fromVars, toVars });
            return { kill: () => {} };
        },
        set: (el: any, vars: any) => {
            logger.debug('Motion.set', { el, vars });
        },
        killTweensOf: (el: any) => {
            logger.debug('Motion.killTweensOf', el);
        }
    };

    const dummyScrollTrigger = {
        refresh: () => logger.debug('ScrollTrigger.refresh'),
        create: (config: any) => {
            logger.debug('ScrollTrigger.create', config);
            return { kill: () => {} };
        }
    };

    return {
        motion: dummyMotion,
        ScrollTrigger: dummyScrollTrigger,
        splitTextRevealSafe: (el: HTMLElement | null, vars: any = {}) => {
            if (!el) return;
            logger.debug('splitTextRevealSafe', { el, vars });
        },
        scaleReveal: (el: HTMLElement | null, vars: any = {}) => {
            if (!el) return;
            logger.debug('scaleReveal', { el, vars });
        },
        fadeInUp: (el: HTMLElement | null, vars: any = {}) => {
            if (!el) return;
            logger.debug('fadeInUp', { el, vars });
        },
        staggerChildren: (container: HTMLElement | null, selector: string, vars: any = {}) => {
            if (!container) return;
            logger.debug('staggerChildren', { container, selector, vars });
        },
        splitTextReveal: (el: any, vars: any = {}) => {
            logger.debug('splitTextReveal', { el, vars });
        },
        fadeInLeft: (el: any, vars: any = {}) => {
            logger.debug('fadeInLeft', { el, vars });
        },
        fadeInRight: (el: any, vars: any = {}) => {
            logger.debug('fadeInRight', { el, vars });
        },
        parallaxBg: (el: any, vars: any = {}) => {
            logger.debug('parallaxBg', { el, vars });
        },
        magneticHover: (el: any, strength: number = 0.3) => {
            logger.debug('magneticHover', { el, strength });
        },
        floatingAnimation: (el: any, vars: any = {}) => {
            logger.debug('floatingAnimation', { el, vars });
        },
        marquee: (el: any, vars: any = {}) => {
            logger.debug('marquee', { el, vars });
        },
        smartHeader: (el: any) => {
            logger.debug('smartHeader', el);
        },
        createTimeline: (vars: any = {}) => {
            logger.debug('createTimeline', { vars });
            return {
                to: (el: any, v: any, pos?: any) => { logger.debug('Timeline.to', { el, v, pos }); return { kill: () => {} }; },
                from: (el: any, v: any, pos?: any) => { logger.debug('Timeline.from', { el, v, pos }); return { kill: () => {} }; },
                fromTo: (el: any, f: any, t: any, pos?: any) => { logger.debug('Timeline.fromTo', { el, f, t, pos }); return { kill: () => {} }; },
                add: (anim: any, pos?: any) => { logger.debug('Timeline.add', { anim, pos }); return { kill: () => {} }; },
                kill: () => {},
                restart: () => {},
                play: () => {},
                pause: () => {},
            };
        },
        counterUp: (el: HTMLElement | null, raw?: any, vars: any = {}) => {
            if (!el) return;
            logger.debug('counterUp', { el, raw, vars });
        }
    };
}
