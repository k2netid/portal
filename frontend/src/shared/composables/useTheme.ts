import { ref, computed } from 'vue';
import { useDarkMode } from '@/shared/composables/useDarkMode';

export function useTheme() {
    const { isDark, toggleMode } = useDarkMode();
    const currentTheme = ref('janari');
    return {
        isDarkMode: isDark,
        toggleDarkMode: toggleMode,
        currentTheme,
        theme: computed(() => currentTheme.value),
    };
}

export default useTheme;
