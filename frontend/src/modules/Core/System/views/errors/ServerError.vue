<template>
  <ErrorLayout>
    <template #icon>
      <div class="h-20 w-20 rounded-2xl bg-destructive/10 border border-destructive/20 flex items-center justify-center">
        <ServerCrash class="h-10 w-10 text-destructive" />
      </div>
    </template>

    <template #title>
      500
    </template>

    <template #message>
      {{ t('common.errors.500.title') }}
    </template>

    <template #description>
      {{ t('common.errors.500.message') }}
    </template>

    <template #actions>
      <button
        type="button"
        :disabled="retrying"
        class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-2xl text-white bg-destructive hover:bg-destructive/90 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-destructive shadow-lg shadow-destructive/20 transition-colors disabled:opacity-70 disabled:cursor-not-allowed active:scale-95"
        @click="retry"
      >
        <RefreshCw
          class="w-4 h-4 mr-2"
          :class="{ 'animate-spin': retrying }"
        />
        {{ retrying ? t('common.errors.500.retrying') : t('common.errors.500.retry') }}
      </button>

      <button
        v-if="hasSafeReturn"
        type="button"
        class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-border text-sm font-medium rounded-2xl text-foreground bg-muted hover:bg-muted/80 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-destructive transition-[background-color,transform] active:scale-95"
        @click="goBack"
      >
        <ArrowLeft class="w-4 h-4 mr-2 text-muted-foreground" />
        {{ t('common.errors.404.back') }}
      </button>

      <button
        type="button"
        class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-border text-sm font-medium rounded-2xl text-foreground bg-muted hover:bg-muted/80 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-destructive transition-[background-color,transform] active:scale-95"
        @click="goHome"
      >
        <Home class="w-4 h-4 mr-2 text-muted-foreground" />
        {{ t('common.errors.404.home') }}
      </button>
    </template>

    <template #footer>
      <div class="flex items-center justify-center gap-3">
        <span>Error Code: 500</span>
        <span class="w-1 h-1 rounded-full bg-border" />
        <span class="font-mono opacity-60">{{ errorId }}</span>
      </div>
    </template>
  </ErrorLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useErrorPageNavigation } from '@/shared/composables/useErrorPageNavigation';
import ErrorLayout from '@/modules/Core/System/layouts/ErrorLayout.vue';
import {
  ArrowLeft,
  Home,
  RefreshCw,
  ServerCrash,
} from 'lucide-vue-next';

const { t } = useI18n();
const retrying = ref(false);
const errorId = ref(`ERR-${Date.now().toString().slice(-6)}-${Math.random().toString(36).substring(7).toUpperCase()}`);

const { goHome, goBack, hasSafeReturn, prepareErrorPage } = useErrorPageNavigation({ errorPath: '/500' });
prepareErrorPage();

const retry = async () => {
    retrying.value = true;
    await new Promise((resolve) => setTimeout(resolve, 1000));
    window.location.reload();
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 4px;
}
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-5px);
  opacity: 0;
}
</style>
