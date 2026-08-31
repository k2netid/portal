import type { InjectionKey, Ref } from 'vue';

export interface SarangengeDropdownContext {
    open: Ref<boolean>;
    align: Ref<'start' | 'end' | 'center'>;
    sideOffset: Ref<number>;
    triggerRef: Ref<HTMLElement | null>;
    close: () => void;
    toggle: () => void;
}

export const SARANGENGE_DROPDOWN_KEY: InjectionKey<SarangengeDropdownContext> = Symbol('sarangenge-dropdown');
