import { ref, watch, onMounted, onUnmounted, type MaybeRefOrGetter, toValue } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import { normalizeLocaleCode } from '@/engine/i18n';
import { parseResponse } from '@/shared/utils/responseParser';
import { logger } from '@/shared/utils/logger';

/**
 * Load public Jejakawan page by slug; refetches when global locale changes.
 */
export function usePublicPageContent(slug: MaybeRefOrGetter<string>) {
    const { locale } = useI18n({ useScope: 'global' });
    const pageData = ref<Record<string, unknown> | null>(null);
    const loading = ref(true);
    const error = ref<unknown>(null);
    let active = true;

    const fetchPage = async () => {
        const resolvedSlug = toValue(slug);
        if (!resolvedSlug) return;

        loading.value = true;
        error.value = null;
        try {
            const response = await api.get(publishingPaths.publicContent(resolvedSlug), {
                params: { locale: normalizeLocaleCode(locale.value) },
            });
            const { data } = parseResponse(response);
            if (active) {
                pageData.value =
                    data && typeof data === 'object' && !Array.isArray(data)
                        ? (data as Record<string, unknown>)
                        : null;
            }
        } catch (err: unknown) {
            const e = err as { name?: string; code?: string; message?: string };
            if (e.name === 'CanceledError' || e.code === 'ERR_CANCELED' || e.message?.includes('aborted')) {
                return;
            }
            if (active) {
                error.value = err;
                pageData.value = null;
            }
            logger.error(`[PublicPage] Failed to load "${resolvedSlug}":`, err);
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

    onMounted(() => {
        active = true;
        void fetchPage();
        if (typeof window !== 'undefined') {
            window.addEventListener('language-changed', onLanguageChanged);
        }
    });

    onUnmounted(() => {
        active = false;
        if (typeof window !== 'undefined') {
            window.removeEventListener('language-changed', onLanguageChanged);
        }
    });

    return {
        pageData,
        loading,
        error,
        reload: fetchPage,
    };
}
