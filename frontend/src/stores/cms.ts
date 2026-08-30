import { defineStore } from 'pinia';
import { useDarkMode } from '@/shared/composables/useDarkMode';

export const useCmsStore = defineStore('cms', () => {
    const { isDark, toggleMode } = useDarkMode();

    return {
        isDarkMode: isDark,
        toggleDarkMode: toggleMode,
    };
});

export default useCmsStore;
