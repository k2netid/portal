<template>
  <ErrorLayout>
    <template #icon>
      <div class="h-20 w-20 rounded-2xl bg-red-500/10 border border-red-500/20 flex items-center justify-center">
        <Lock class="h-10 w-10 text-red-600 dark:text-red-500" />
      </div>
    </template>

    <template #title>
      403
    </template>

    <template #message>
      {{ t('common.errors.403.title') }}
    </template>

    <template #description>
      {{ t('common.errors.403.message') }}
    </template>

    <template #actions>
      <button
        v-if="!user"
        type="button"
        class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-2xl text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500 shadow-sm transition-[background-color,transform] active:scale-95"
        @click="handleGuestLogin"
      >
        <LogIn class="w-4 h-4 mr-2" />
        {{ t('common.errors.403.login') }}
      </button>

      <button
        v-else
        type="button"
        class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-2xl text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500 shadow-sm transition-[background-color,transform] active:scale-95"
        @click="logout"
      >
        <LogIn class="w-4 h-4 mr-2" />
        {{ t('common.errors.403.relogin') }}
      </button>

      <button
        type="button"
        class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-border text-sm font-medium rounded-2xl text-foreground bg-muted hover:bg-muted/80 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500 transition-[background-color,transform] active:scale-95"
        @click="goBack"
      >
        <ArrowLeft class="w-4 h-4 mr-2 text-muted-foreground" />
        {{ t('common.errors.404.back') }}
      </button>
    </template>

    <template #footer>
      <div class="flex items-center justify-center gap-3">
        <span>Error Code: 403</span>
        <span class="w-1 h-1 rounded-full bg-border" />
        <span class="font-mono opacity-60">{{ traceId }}</span>
      </div>
    </template>
  </ErrorLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useI18n } from 'vue-i18n';
import { useErrorPageNavigation } from '@/shared/composables/useErrorPageNavigation';
import ErrorLayout from '@/modules/Core/System/layouts/ErrorLayout.vue';
import {
  ArrowLeft,
  Lock,
  LogIn,
} from 'lucide-vue-next';

const route = useRoute();
const authStore = useAuthStore();
const { t } = useI18n();

const user = computed(() => authStore.user);
const traceId = ref(`TRC-${Date.now().toString().slice(-6)}-${Math.random().toString(36).substring(7).toUpperCase()}`);

const { goBack, goToLogin, prepareErrorPage } = useErrorPageNavigation({ errorPath: '/403' });
prepareErrorPage();

const handleGuestLogin = () => {
    authStore.clearAuth();
    void goToLogin({
        redirect: route.fullPath !== '/403' ? route.fullPath : undefined,
    });
};

const logout = async () => {
    await authStore.logout();
    void goToLogin();
};
</script>
