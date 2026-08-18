<template>
  <ErrorLayout>
    <template #icon>
      <div class="h-20 w-20 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center">
        <FileQuestion class="h-10 w-10 text-blue-600 dark:text-blue-500" />
      </div>
    </template>

    <template #title>
      404
    </template>

    <template #message>
      {{ t('common.errors.404.title') }}
    </template>

    <template #description>
      {{ t('common.errors.404.message') }}
    </template>

    <template #actions>
      <button
        type="button"
        class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-2xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 shadow-sm transition-[background-color,transform] active:scale-95"
        @click="goHome"
      >
        <Home class="w-4 h-4 mr-2" />
        {{ t('common.errors.404.home') }}
      </button>

      <button
        type="button"
        class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-border text-sm font-medium rounded-2xl text-foreground bg-muted hover:bg-muted/80 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-[background-color,transform] active:scale-95"
        @click="goBack"
      >
        <ArrowLeft class="w-4 h-4 mr-2 text-muted-foreground" />
        {{ t('common.errors.404.back') }}
      </button>
    </template>

    <template #footer>
      <div class="flex items-center justify-center gap-3">
        <span>Error Code: 404</span>
        <span class="w-1 h-1 rounded-full bg-border" />
        <span class="font-mono opacity-60">{{ traceId }}</span>
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
  FileQuestion,
  Home,
} from 'lucide-vue-next';

const { t } = useI18n();
const traceId = ref(`TRC-${Date.now().toString().slice(-6)}-${Math.random().toString(36).substring(7).toUpperCase()}`);

const { goHome, goBack, prepareErrorPage } = useErrorPageNavigation({ errorPath: '/404' });
prepareErrorPage();
</script>
