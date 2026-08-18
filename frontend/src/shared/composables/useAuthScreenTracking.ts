import { onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { trackAuthScreen } from '@/shared/utils/errorReturn';

/** Records auth wizard routes for error-page "Kembali" (sessionStorage, per tab). */
export function useAuthScreenTracking(): void {
    const route = useRoute();

    onMounted(() => {
        trackAuthScreen(route.fullPath);
    });
}
