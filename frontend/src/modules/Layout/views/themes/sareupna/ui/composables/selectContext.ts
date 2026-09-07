import type { ComputedRef, InjectionKey, Ref } from 'vue';

export interface JanariSelectContext {
    open: Ref<boolean>;
    modelValue: ComputedRef<string>;
    placeholder: Ref<string>;
    labels: Ref<Record<string, string>>;
    triggerRef: Ref<HTMLElement | null>;
    close: () => void;
    select: (value: string) => void;
}

export const JANARI_SELECT_KEY: InjectionKey<JanariSelectContext> = Symbol('janari-select');
