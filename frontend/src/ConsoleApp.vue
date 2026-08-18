<template>
  <div
    id="app-container"
    class="min-h-screen bg-background text-foreground font-sans antialiased text-sharp"
  >
    <div class="noise-overlay" />
    <template v-if="isThemeReady">
      <router-view />
    </template>
    <div v-else class="min-h-screen flex items-center justify-center">
      <Loader2 class="w-8 h-8 animate-spin text-muted-foreground opacity-20" />
    </div>
    <Toast />
    <ConfirmModal
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
    <GlobalErrorModal />
    <SessionTimeoutModal
      :is-visible="isWarningVisible"
      :time-remaining="timeRemaining"
      @extend="extendSession"
      @logout="manualLogout"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useSessionTimeout } from '@/shared/composables/useSessionTimeout';
import { syncDocumentDarkClassForRoute } from '@/shared/composables/useDarkMode';
import { useHead } from '@unhead/vue';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { applyFavicon, resolveFavicon } from '@/modules/Core/System/utils/favicon';
import { whenConsoleThemeBootstrapped } from '@/modules/Core/System/composables/useConsoleTheme';
import { Loader2 } from 'lucide-vue-next';

const Toast = defineAsyncComponent(() => import('@/shared/components/ui/Toast.vue'));
const ConfirmModal = defineAsyncComponent(() => import('@/shared/components/ui/ConfirmModal.vue'));
const GlobalErrorModal = defineAsyncComponent(() => import('@/shared/components/ui/GlobalErrorModal.vue'));
const SessionTimeoutModal = defineAsyncComponent(() => import('@/shared/components/ui/SessionTimeoutModal.vue'));

const { t } = useI18n();
const { confirmState } = useConfirm();
const { isWarningVisible, timeRemaining, extendSession, manualLogout } = useSessionTimeout();
const route = useRoute();
const systemStore = useSystemStore();

// Initialize theme state immediately so JS CSS vars match the DOM classes
systemStore.initTheme();

const isThemeReady = ref(true);

const consoleTitle = computed(() => {
    const name = systemStore.appIdentity?.app_name;
    return name ? `${name} Console` : t('system.app.consoleTitle');
});

watch(
    () => route.path,
    (path) => {
        syncDocumentDarkClassForRoute(path);
    },
    { immediate: true },
);

useHead({
    title: consoleTitle,
});

onMounted(() => {
    void whenConsoleThemeBootstrapped();

    if (!systemStore.publicSettingsLoaded) {
        void systemStore.fetchPublicSettings();
    }
});

onUnmounted(() => {
});

const faviconHref = computed(() => resolveFavicon([
    systemStore.siteSettings?.site_favicon,
    systemStore.appIdentity?.app_favicon,
]));

watch(
    faviconHref,
    (href) => {
        applyFavicon(href);
    },
    { immediate: true },
);
</script>
