import { computed, ref } from 'vue';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { readConsoleDarkModeFromStorage } from '@/config/theme';

import { collectConsoleDashboardSlugCandidates, pathUsesConsoleDashboardSlug } from '@/config/console';

type ThemeMode = 'light' | 'dark' | 'system';
/** `console` = Jejakawan Console (dashboard); `frontend` = public site theme. */
export type DarkModeScope = 'console' | 'frontend';

export const FRONTEND_THEME_KEY = 'frontend-dark-mode';
export const FRONTEND_DEFAULT_THEME_MODE_KEY = 'ja_theme_default_mode';
const frontendThemeMode = ref<ThemeMode>('dark');
const frontendIsDarkMode = ref(true);
let frontendInitialized = false;

export const getThemeDefaultMode = (): ThemeMode => {
    if (typeof window === 'undefined') return 'dark';
    try {
        const raw = localStorage.getItem(FRONTEND_DEFAULT_THEME_MODE_KEY);
        if (raw === 'light' || raw === 'dark' || raw === 'system') return raw;
    } catch {
        // ignore
    }
    return 'dark';
};

export const applyFrontendThemeDefault = (defaultMode?: unknown): void => {
    if (typeof window === 'undefined') return;
    if (defaultMode !== 'light' && defaultMode !== 'dark' && defaultMode !== 'system') return;

    try {
        localStorage.setItem(FRONTEND_DEFAULT_THEME_MODE_KEY, defaultMode);
    } catch {
        // ignore
    }

    const savedMode = localStorage.getItem(FRONTEND_THEME_KEY);
    if (!savedMode) {
        frontendThemeMode.value = defaultMode;
        frontendIsDarkMode.value = resolveIsDark(defaultMode);
        if (frontendIsDarkMode.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
};

const resolveIsDark = (mode: ThemeMode) => {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    return mode === 'dark' || (mode === 'system' && prefersDark);
};

const initFrontendTheme = () => {
    if (frontendInitialized) return;
    const savedMode = localStorage.getItem(FRONTEND_THEME_KEY);
    const defaultMode = getThemeDefaultMode();
    const parsedMode: ThemeMode = savedMode === 'light' || savedMode === 'dark' || savedMode === 'system'
        ? savedMode
        : defaultMode;
    frontendThemeMode.value = parsedMode;
    frontendIsDarkMode.value = resolveIsDark(parsedMode);
    frontendInitialized = true;
};

/**
 * Keep `document.documentElement` dark class in sync with the *current* route:
 * console (`/dash` or custom slug) uses system store + `console-dark-mode`; everything else uses frontend prefs + `frontend-dark-mode`.
 */
const isConsoleOrMemberRoute = (path: string): boolean => {
    if (path.startsWith('/member') || path.startsWith('/auth')) {
        return true;
    }
    const candidates = collectConsoleDashboardSlugCandidates();
    return candidates.some(slug => pathUsesConsoleDashboardSlug(path, slug));
};

export function syncDocumentDarkClassForRoute(path: string) {
    const isConsole = isConsoleOrMemberRoute(path);
    if (isConsole) {
        const systemStore = useSystemStore();
        const raw = readConsoleDarkModeFromStorage();
        const mode: ThemeMode = raw === 'light' || raw === 'dark' || raw === 'system' ? raw : 'system';
        if (systemStore.themeMode !== mode) {
            systemStore.setThemeMode(mode, false);
        } else {
            systemStore.applyThemeToDocument();
        }
        return;
    }

    initFrontendTheme();
    frontendIsDarkMode.value = resolveIsDark(frontendThemeMode.value);
    if (frontendIsDarkMode.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

export function useDarkMode(scope: DarkModeScope = 'console') {
    const systemStore = useSystemStore();
    if (scope === 'frontend') {
        initFrontendTheme();
    }

    const isConsole = scope === 'console';
    const isDark = computed(() => isConsole ? systemStore.isDarkMode : frontendIsDarkMode.value);
    const actualMode = computed(() => isDark.value ? 'dark' : 'light');
    const currentMode = computed(() => isConsole ? systemStore.themeMode : frontendThemeMode.value);

    const setMode = (mode: ThemeMode) => {
        if (isConsole) {
            systemStore.setThemeMode(mode);
            return;
        }

        frontendThemeMode.value = mode;
        frontendIsDarkMode.value = resolveIsDark(mode);
        localStorage.setItem(FRONTEND_THEME_KEY, mode);
        if (frontendIsDarkMode.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };

    const toggleMode = () => {
        if (isConsole) {
            systemStore.toggleDarkMode();
            return;
        }

        const nextMode: ThemeMode = frontendIsDarkMode.value ? 'light' : 'dark';
        setMode(nextMode);
    };

    return {
        currentMode,
        actualMode,
        isDark,
        setMode,
        toggleMode,
        modes: {
            LIGHT: 'light',
            DARK: 'dark',
            SYSTEM: 'system',
        },
        loadFromBackend: () => isConsole ? systemStore.loadThemePreferences() : Promise.resolve(),
    };
}
