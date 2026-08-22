import { computed } from 'vue';
import { useSystemStore } from '@/modules/Core/System/stores/system';

function truthySetting(value: unknown): boolean {
    if (value === true || value === 1) return true;
    if (typeof value === 'string') {
        return !['0', 'false', 'off', ''].includes(value.toLowerCase());
    }
    return Boolean(value);
}

/**
 * Global AI availability from Settings → AI (systemStore.settings).
 * Mail features must AND this with mail-local prefs (ai_enabled + scopes).
 */
export function useAiAvailability() {
    const systemStore = useSystemStore();

    const globallyEnabled = computed(() => truthySetting(systemStore.settings['ai_enabled']));

    const defaultProvider = computed(() => {
        const p = systemStore.settings['ai_default_provider'];
        return typeof p === 'string' && p !== '' ? p : 'gemini';
    });

    const hasProviderKey = computed(() => {
        const key = systemStore.settings[`${defaultProvider.value}_api_key`];
        return typeof key === 'string' && key.trim() !== '';
    });

    const isReady = computed(() => globallyEnabled.value && hasProviderKey.value);

    return {
        globallyEnabled,
        defaultProvider,
        hasProviderKey,
        isReady,
    };
}
