/**
 * Admin Theme Customizer (route `themes.customizer` → ThemeCustomizer.vue).
 * Bindings persist as `settings.theme_data_bindings`.
 */
import { computed, ref, watch } from 'vue';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { logger } from '@/shared/utils/logger';
import { resolveThemeCustomizerExtension } from '@/modules/Layout/customizer/loaders/resolveThemeCustomizerExtension';
import { applyMergedSettingsSchema } from '@/modules/Layout/customizer/loaders/mergeThemeSettingsSchema';
import type { ComponentBindings } from '@/modules/Layout/composables/useThemeDataBindings';
import { THEME_DATA_BINDINGS_KEY, isPlainSettingsObject } from '@/modules/Layout/constants/themeBindings';

import { useSystemStore } from '@/modules/Core/System/stores/system';

function omitThemeBindingKeys(settings: Record<string, unknown>): Record<string, unknown> {
    const next = { ...settings };
    delete next[THEME_DATA_BINDINGS_KEY];
    return next;
}

type TranslateFn = (key: string, ...args: unknown[]) => string;

export function useThemeCustomizer(slug: string, t: TranslateFn) {
    const toast = useToast();
    const systemStore = useSystemStore();
    const theme = ref<any>(null);
    const loading = ref(true);
    const saving = ref(false);
    const formValues = ref<Record<string, unknown>>({});
    const customCss = ref('');
    const bindings = ref<Record<string, ComponentBindings>>({});

    const initialDataSnapshot = ref('');
    const history = ref<string[]>([]);
    const historyIndex = ref(-1);
    const isInternalChange = ref(false);

    const isDirty = computed(() => {
        const current = JSON.stringify({ f: formValues.value, c: customCss.value, b: bindings.value });
        return current !== initialDataSnapshot.value;
    });

    const canUndo = computed(() => historyIndex.value > 0);
    const canRedo = computed(() => historyIndex.value < history.value.length - 1);

    function pushCurrentStateToHistory() {
        const state = JSON.stringify({ f: formValues.value, c: customCss.value, b: bindings.value });
        if (history.value[historyIndex.value] === state) return;
        if (historyIndex.value < history.value.length - 1) {
            history.value = history.value.slice(0, historyIndex.value + 1);
        }
        history.value.push(state);
        if (history.value.length > 50) history.value.shift();
        else historyIndex.value++;
    }

    function saveHistory() {
        if (isInternalChange.value) return;
        pushCurrentStateToHistory();
    }

    function restoreState(stateStr: string) {
        isInternalChange.value = true;
        const state = JSON.parse(stateStr);
        formValues.value = JSON.parse(JSON.stringify(state.f));
        customCss.value = state.c;
        bindings.value = JSON.parse(JSON.stringify(state.b));
        setTimeout(() => {
            isInternalChange.value = false;
        }, 0);
    }

    function undo() {
        if (!canUndo.value) return;
        historyIndex.value--;
        const state = history.value[historyIndex.value];
        if (state) restoreState(state);
    }

    function redo() {
        if (!canRedo.value) return;
        historyIndex.value++;
        const state = history.value[historyIndex.value];
        if (state) restoreState(state);
    }

    function recordSettingChange(key: string, val: unknown) {
        formValues.value[key] = val;

        const extension = resolveThemeCustomizerExtension(slug);
        extension?.onSettingChange?.(key, val, formValues.value);

        saveHistory();
    }

    watch(customCss, (next, prev) => {
        if (!isInternalChange.value && next !== prev) saveHistory();
    });

    async function fetchThemeData() {
        loading.value = true;
        try {
            if (!systemStore.publicSettingsLoaded) {
                await systemStore.fetchPublicSettings();
            }

            const response = await api.get(`/manage/layout/themes/${slug}`);
            theme.value = response.data;
            applyMergedSettingsSchema(theme.value, slug);

            const defaults: Record<string, unknown> = {};
            const schema = theme.value?.manifest?.settings_schema || {};
            Object.keys(schema).forEach((k) => {
                if (schema[k]) defaults[k] = schema[k].default ?? '';
            });

            const siteName = systemStore.siteSettings?.site_name || '';
            const siteLogo = systemStore.siteSettings?.site_logo || '';
            const siteFavicon = systemStore.siteSettings?.site_favicon || '';
            const siteDesc = systemStore.siteSettings?.site_description || '';

            const fallbackGlobalMap: Record<string, string> = {
                site_title: siteName,
                site_name: siteName,
                school_name: siteName,
                brand_logo: siteLogo,
                site_logo: siteLogo,
                brand_favicon: siteFavicon,
                site_favicon: siteFavicon,
                site_tagline: siteDesc,
                site_description: siteDesc,
                school_tagline: siteDesc,
            };

            const rawSettings = (theme.value?.settings || {}) as Record<string, unknown>;
            const mergedValues: Record<string, unknown> = { ...defaults, ...omitThemeBindingKeys(rawSettings) };

            // Populate fallback from global Settings > Identity when not explicitly customized in theme
            Object.entries(fallbackGlobalMap).forEach(([k, fallbackVal]) => {
                if (fallbackVal && (!mergedValues[k] || mergedValues[k] === '')) {
                    mergedValues[k] = fallbackVal;
                }
            });

            formValues.value = mergedValues;
            customCss.value = theme.value?.custom_css || '';

            const rawBindings = rawSettings[THEME_DATA_BINDINGS_KEY];
            if (isPlainSettingsObject(rawBindings)) {
                bindings.value = JSON.parse(JSON.stringify(rawBindings)) as Record<string, ComponentBindings>;
            } else {
                bindings.value = {};
            }

            const state = JSON.stringify({ f: formValues.value, c: customCss.value, b: bindings.value });
            initialDataSnapshot.value = state;
            history.value = [state];
            historyIndex.value = 0;
        } catch (err: unknown) {
            logger.error('Theme customizer init failed:', err);
            toast.error.fromResponse(err);
        } finally {
            loading.value = false;
        }
    }

    async function saveAll() {
        saving.value = true;
        try {
            const payload = {
                ...formValues.value,
                [THEME_DATA_BINDINGS_KEY]: bindings.value,
            };
            await api.put(`/manage/layout/themes/${slug}/customization`, {
                settings: payload,
                custom_css: customCss.value,
            });

            const state = JSON.stringify({ f: formValues.value, c: customCss.value, b: bindings.value });
            initialDataSnapshot.value = state;
            toast.success.action(t('publishing.theme_customizer.messages.published'));
        } catch (err: unknown) {
            logger.error('Theme customizer publish failed:', err);
            toast.error.fromResponse(err);
        } finally {
            saving.value = false;
        }
    }

    function resetToInitial() {
        restoreState(initialDataSnapshot.value);
        pushCurrentStateToHistory();
    }

    function resetToDefaults() {
        if (!theme.value?.manifest?.settings_schema) return;
        const defaults: Record<string, unknown> = {};
        const schema = theme.value.manifest.settings_schema;
        Object.keys(schema).forEach((k) => {
            if (schema[k]) defaults[k] = schema[k].default ?? '';
        });

        const siteName = systemStore.siteSettings?.site_name || '';
        const siteLogo = systemStore.siteSettings?.site_logo || '';
        const siteFavicon = systemStore.siteSettings?.site_favicon || '';
        const siteDesc = systemStore.siteSettings?.site_description || '';

        const fallbackGlobalMap: Record<string, string> = {
            site_title: siteName,
            site_name: siteName,
            brand_logo: siteLogo,
            site_logo: siteLogo,
            brand_favicon: siteFavicon,
            site_favicon: siteFavicon,
            site_tagline: siteDesc,
            site_description: siteDesc,
        };

        Object.entries(fallbackGlobalMap).forEach(([k, fallbackVal]) => {
            if (fallbackVal && (!defaults[k] || defaults[k] === '')) {
                defaults[k] = fallbackVal;
            }
        });

        isInternalChange.value = true;
        formValues.value = defaults;
        customCss.value = '';
        bindings.value = {};
        pushCurrentStateToHistory();
        setTimeout(() => {
            isInternalChange.value = false;
        }, 0);
        toast.info(t('publishing.theme_customizer.messages.info'), t('publishing.theme_customizer.messages.reset_done'));
    }

    return {
        theme,
        loading,
        saving,
        formValues,
        customCss,
        bindings,
        isDirty,
        canUndo,
        canRedo,
        fetchThemeData,
        saveAll,
        resetToInitial,
        resetToDefaults,
        undo,
        redo,
        saveHistory,
        recordSettingChange,
    };
}
