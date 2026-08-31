import type { InjectionKey, Ref } from 'vue';

export interface SarangengeDropdownContext {
    open: Ref<boolean>;
    align: Ref<'start' | 'end' | 'center'>;
    sideOffset: Ref<number>;
    triggerRef: Ref<HTMLElement | null>;
    close: () => void;
    toggle: () => void;
}

export const LAYUNG_DROPDOWN_KEY: InjectionKey<SarangengeDropdownContext> = Symbol('layung-dropdown');
