import type { InjectionKey, Ref } from 'vue';

export interface JanariDropdownContext {
    open: Ref<boolean>;
    align: Ref<'start' | 'end' | 'center'>;
    sideOffset: Ref<number>;
    triggerRef: Ref<HTMLElement | null>;
    close: () => void;
    toggle: () => void;
}

export const JANARI_DROPDOWN_KEY: InjectionKey<JanariDropdownContext> = Symbol('janari-dropdown');
