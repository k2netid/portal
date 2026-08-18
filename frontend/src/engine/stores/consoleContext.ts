import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

/** Hub operator console — single site (no CMS org partition on this deploy). */
export const useConsoleContextStore = defineStore('consoleContext', () => {
    const context = ref<'system'>('system');

    const isSystem = computed(() => true);

    function initConsoleContext(): void {
        context.value = 'system';
        sessionStorage.removeItem('active_organization_id');
        sessionStorage.removeItem('active_organization_context');
        sessionStorage.removeItem('active_organization_name');
    }

    async function setSystemContext(_silent = false): Promise<void> {
        initConsoleContext();
    }

    return {
        context,
        isSystem,
        initConsoleContext,
        setSystemContext,
    };
});
