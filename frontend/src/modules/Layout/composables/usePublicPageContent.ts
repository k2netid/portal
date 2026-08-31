import { ref, watch, onMounted, onUnmounted, computed, type MaybeRefOrGetter, toValue } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import { normalizeLocaleCode } from '@/engine/i18n';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import { logger } from '@/shared/utils/logger';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { FRONTEND_THEME_ACTIVATION_REV_KEY } from '@/modules/Layout/utils/themeActivationSync';

function cmsPageMatchesActiveTheme(
    raw: Record<string, unknown> | null,
    activeSlug: string | null | undefined,
): Record<string, unknown> | null {
    if (!raw) return null;
    const meta = raw.meta as Record<string, unknown> | undefined;
    const sampleTheme = typeof meta?.sample_theme === 'string'
        ? meta.sample_theme.trim().toLowerCase()
        : '';
    if (!sampleTheme) return raw;
    const active = typeof activeSlug === 'string' ? activeSlug.trim().toLowerCase() : '';
    // Theme still booting — keep CMS payload so builder_blocks can apply once slug is known.
    if (!active) return raw;
    if (sampleTheme !== active) return null;
    return raw;
}

/**
 * Load public Jejakawan page by slug; refetches when global locale changes.
 * CMS rows tagged with meta.sample_theme only apply to that theme package.
 */
export function usePublicPageContent(slug: MaybeRefOrGetter<string>) {
    const { locale } = useI18n({ useScope: 'global' });
    const { activeTheme } = useTheme();
    const pageData = ref<Record<string, unknown> | null>(null);
    const loading = ref(true);
    const error = ref<unknown>(null);
    let active = true;

    const scopedPageData = computed(() =>
        cmsPageMatchesActiveTheme(pageData.value, activeTheme.value?.slug ?? null),
    );

    const fetchPage = async () => {
        const resolvedSlug = toValue(slug);
        if (!resolvedSlug) return;

        loading.value = true;
        error.value = null;
        try {
            const response = await api.get(publishingPaths.publicContent(resolvedSlug), {
                params: { locale: normalizeLocaleCode(locale.value) },
            });
            // Single-resource endpoint — must NOT use parseResponse (list helper).
            const data = parseSingleResponse<Record<string, unknown>>(response);
            if (active) {
                pageData.value =
                    data && typeof data === 'object' && !Array.isArray(data)
                        ? data
                        : null;
            }
        } catch (err: unknown) {
            const e = err as { name?: string; code?: string; message?: string; response?: { status?: number } };
            if (e.name === 'CanceledError' || e.code === 'ERR_CANCELED' || e.message?.includes('aborted')) {
                return;
            }
            if (active) {
                error.value = err;
                pageData.value = null;
            }
            // Theme pages often have no CMS row yet — theme Vue is the fallback UI.
            const status = e.response?.status;
            if (status === 404) {
                logger.debug(`[PublicPage] No CMS content for "${resolvedSlug}" (theme fallback)`);
            } else {
                logger.error(`[PublicPage] Failed to load "${resolvedSlug}":`, err);
            }
        } finally {
            if (active) {
                loading.value = false;
            }
        }
    };

    const onLanguageChanged = () => {
        void fetchPage();
    };

    watch(locale, () => {
        void fetchPage();
    });

    watch(
        () => activeTheme.value?.slug,
        () => {
            void fetchPage();
        },
    );

    onMounted(() => {
        active = true;
        void fetchPage();
        if (typeof window !== 'undefined') {
            window.addEventListener('language-changed', onLanguageChanged);
            window.addEventListener('ja-frontend-theme-activated', onLanguageChanged);
            window.addEventListener('storage', (event: StorageEvent) => {
                if (event.key === FRONTEND_THEME_ACTIVATION_REV_KEY) {
                    onLanguageChanged();
                }
            });
        }
    });

    onUnmounted(() => {
        active = false;
        if (typeof window !== 'undefined') {
            window.removeEventListener('language-changed', onLanguageChanged);
            window.removeEventListener('ja-frontend-theme-activated', onLanguageChanged);
        }
    });

    return {
        pageData: scopedPageData,
        loading,
        error,
        reload: fetchPage,
    };
}
