import type { InjectionKey, Ref } from 'vue';

export interface SareupnaDropdownContext {
    open: Ref<boolean>;
    align: Ref<'start' | 'end' | 'center'>;
    sideOffset: Ref<number>;
    triggerRef: Ref<HTMLElement | null>;
    close: () => void;
    toggle: () => void;
}

export const SAREUPNA_DROPDOWN_KEY: InjectionKey<SareupnaDropdownContext> = Symbol('sareupna-dropdown');
