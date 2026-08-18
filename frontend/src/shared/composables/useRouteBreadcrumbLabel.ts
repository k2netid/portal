import { onUnmounted, toValue, watch, type MaybeRefOrGetter } from 'vue';
import { useRoute } from 'vue-router';
import { useBreadcrumbs } from '@/shared/composables/useBreadcrumbs';

/** Override the current route label in the navbar breadcrumb (e.g. record title on edit pages). */
export function useRouteBreadcrumbLabel(label: MaybeRefOrGetter<string | undefined | null>) {
    const route = useRoute();
    const { setBreadcrumb, clearBreadcrumb } = useBreadcrumbs();

    watch(
        () => toValue(label),
        (value) => {
            const trimmed = typeof value === 'string' ? value.trim() : '';
            if (trimmed) {
                setBreadcrumb(route.path, trimmed);
            } else {
                clearBreadcrumb(route.path);
            }
        },
        { immediate: true },
    );

    onUnmounted(() => clearBreadcrumb(route.path));
}
