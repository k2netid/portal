import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useSystemStore } from '@/modules/Core/System/stores/system';

/** Branding for auth screens (console / public Jejakawan). */
export function useAuthBranding() {
    const route = useRoute();
    const systemStore = useSystemStore();

    const branding = computed(() => ({
        name: systemStore.appIdentity.app_name,
        logo: systemStore.appIdentity.app_logo,
        description: systemStore.siteSettings?.site_description ?? '',
        type: (route.meta.authContext as string | undefined) ?? 'system',
    }));

    return {
        branding,
        context: computed(() => route.meta.authContext ?? 'system'),
    };
}
