import type { ComputedRef, InjectionKey, Ref } from 'vue';

export type JanariPanelSide = 'top' | 'bottom' | 'left' | 'right';
export type JanariPanelAlign = 'start' | 'end' | 'center';

export interface JanariPopoverContext {
    open: ComputedRef<boolean> | Ref<boolean>;
    side: Ref<JanariPanelSide>;
    align: Ref<JanariPanelAlign>;
    sideOffset: Ref<number>;
    triggerRef: Ref<HTMLElement | null>;
    close: () => void;
    toggle: () => void;
}

export const JANARI_POPOVER_KEY: InjectionKey<JanariPopoverContext> = Symbol('janari-popover');
