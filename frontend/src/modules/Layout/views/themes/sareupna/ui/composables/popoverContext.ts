import type { ComputedRef, InjectionKey, Ref } from 'vue';

export type SareupnaPanelSide = 'top' | 'bottom' | 'left' | 'right';
export type SareupnaPanelAlign = 'start' | 'end' | 'center';

export interface SareupnaPopoverContext {
    open: ComputedRef<boolean> | Ref<boolean>;
    side: Ref<SareupnaPanelSide>;
    align: Ref<SareupnaPanelAlign>;
    sideOffset: Ref<number>;
    triggerRef: Ref<HTMLElement | null>;
    close: () => void;
    toggle: () => void;
}

export const SAREUPNA_POPOVER_KEY: InjectionKey<SareupnaPopoverContext> = Symbol('sareupna-popover');
