<template>
  <div
    id="app-container"
    class="min-h-screen bg-background text-foreground font-sans antialiased text-sharp"
  >
    <div class="noise-overlay" />
    <template v-if="isReady">
      <router-view />
      <Toast v-if="deferUiOverlays" />
      <ConfirmModal
        v-if="deferUiOverlays && confirmState.isOpen"
        :is-open="confirmState.isOpen"
        :title="confirmState.title"
        :message="confirmState.message"
        :description="confirmState.description"
        :variant="confirmState.variant"
        :confirm-text="confirmState.confirmText"
        :cancel-text="confirmState.cancelText"
        :input="confirmState.input"
        :input-placeholder="confirmState.inputPlaceholder"
        :checkbox="confirmState.checkbox"
        :checkbox-label="confirmState.checkboxLabel"
        :checkbox-default="confirmState.checkboxDefault"
        @update:is-open="confirmState.isOpen = $event"
        @confirm="confirmState.onConfirm"
        @cancel="confirmState.onCancel"
      />
      <GlobalErrorModal v-if="deferUiOverlays" />
      <SessionTimeoutModal
        v-if="deferUiOverlays && isWarningVisible"
        :is-visible="isWarningVisible"
        :time-remaining="timeRemaining"
        @extend="extendSession"
        @logout="manualLogout"
      />
    </template>
    <div v-else class="fixed inset-0 flex items-center justify-center bg-background">
      <div class="h-10 w-10 border-4 border-primary border-t-transparent rounded-full animate-spin" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed, ref, watch, defineAsyncComponent } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useSessionTimeout } from '@/shared/composables/useSessionTimeout';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { syncDocumentDarkClassForRoute } from '@/shared/composables/useDarkMode';
import { useHead } from '@unhead/vue';
import { applyFavicon, resolveFavicon } from '@/modules/Core/System/utils/favicon';
import { useLanguage } from '@/shared/composables/useLanguage';

const Toast = defineAsyncComponent(() => import('@/shared/components/ui/Toast.vue'));
const ConfirmModal = defineAsyncComponent(() => import('@/shared/components/ui/ConfirmModal.vue'));
const GlobalErrorModal = defineAsyncComponent(() => import('@/shared/components/ui/GlobalErrorModal.vue'));
const SessionTimeoutModal = defineAsyncComponent(() => import('@/shared/components/ui/SessionTimeoutModal.vue'));

const { t } = useI18n();
const { initializeLanguage } = useLanguage();
const { confirmState } = useConfirm();
const { isWarningVisible, timeRemaining, extendSession, manualLogout } = useSessionTimeout();

const systemStore = useSystemStore();
const route = useRoute();
const isReady = ref(false);
const deferUiOverlays = ref(false);

watch(
    () => route.path,
    (path) => {
        syncDocumentDarkClassForRoute(path);
    },
    { immediate: true },
);

onMounted(() => {
    isReady.value = true;
    void initializeLanguage();
    void systemStore.fetchPublicSettings({ force: true });

    const mountDeferredUi = () => {
        deferUiOverlays.value = true;
    };
    if (typeof window !== 'undefined' && typeof window.requestIdleCallback === 'function') {
        window.requestIdleCallback(mountDeferredUi, { timeout: 1500 });
    } else {
        setTimeout(mountDeferredUi, 600);
    }
});

const faviconHref = computed(() => resolveFavicon([
    systemStore.siteSettings?.site_favicon,
]));

const siteTitle = computed(() => {
    try {
        const title = systemStore.siteSettings?.site_name || systemStore.siteSettings?.site_title;
        return title && typeof title === 'string' ? title : t('common.labels.app.consoleDefaultTitle');
    } catch {
        return t('common.labels.app.consoleDefaultTitle');
    }
});

const siteDescription = computed(() => {
    try {
        const description = systemStore.siteSettings?.site_description;
        if (description && typeof description === 'string' && description.trim().length > 0) {
            return description.trim();
        }
    } catch {
        // use default copy when site settings are unavailable
    }
    return t('common.labels.app.consoleDefaultDescription');
});

useHead({
    title: siteTitle,
    meta: [
        {
            name: 'description',
            content: siteDescription,
        },
    ],
});

watch(
    faviconHref,
    (href) => {
        applyFavicon(href);
    },
    { immediate: true },
);
</script>
