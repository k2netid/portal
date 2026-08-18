import { defineAsyncComponent, type Component } from 'vue';

/** Lazy-load tab panels to keep route chunks small and defer mount cost. */
export function lazyTab(loader: () => Promise<{ default: Component }>): Component {
    return defineAsyncComponent({
        loader,
        delay: 80,
        timeout: 60000,
    });
}
