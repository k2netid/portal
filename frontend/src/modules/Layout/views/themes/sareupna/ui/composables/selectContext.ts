import type { ComputedRef, InjectionKey, Ref } from 'vue';

export interface SareupnaSelectContext {
    open: Ref<boolean>;
    modelValue: ComputedRef<string>;
    placeholder: Ref<string>;
    labels: Ref<Record<string, string>>;
    triggerRef: Ref<HTMLElement | null>;
    close: () => void;
    select: (value: string) => void;
}

export const SAREUPNA_SELECT_KEY: InjectionKey<SareupnaSelectContext> = Symbol('sareupna-select');
