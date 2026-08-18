<template>
  <div 
    v-if="state.isVisible" 
    class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-950/20 backdrop-blur-md animate-in fade-in duration-500"
  >
    <!-- Overlay backdrop clicking should not close critical errors -->
    <div
      class="fixed inset-0 -z-10"
      @click="state.code !== 401 && state.code !== 419 && hideError()"
    />

    <div class="max-w-md w-full bg-card/80 backdrop-blur-xl border border-white/20 rounded-[2.5rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.3)] overflow-hidden animate-in zoom-in-95 slide-in-from-bottom-8 duration-500 ease-out">
      <div class="p-10 pb-8 flex flex-col items-center text-center">
        <!-- Icon Box with glow -->
        <div 
          class="h-24 w-24 rounded-[2rem] flex items-center justify-center mb-8 border relative ring-4 ring-white/10"
          :class="config.iconBgClass"
        >
          <div class="absolute inset-0 blur-2xl opacity-20 bg-current rounded-full" />
          <component 
            :is="config.icon" 
            class="h-12 w-12 relative z-10"
            :class="config.iconClass"
          />
        </div>

        <!-- Error Code -->
        <h1 class="text-6xl font-bold tracking-tighter mb-4 text-foreground/90 font-sans">
          {{ state.code }}
        </h1>

        <!-- Title -->
        <h2 class="text-xl font-bold mb-4 text-foreground">
          {{ title }}
        </h2>

        <!-- Message -->
        <p class="text-sm text-muted-foreground leading-relaxed px-4 opacity-80">
          {{ message }}
        </p>

        <!-- Actions -->
        <div class="w-full flex flex-col gap-3 mt-10">
          <button
            v-if="state.code === 401 || state.code === 419"
            class="w-full inline-flex items-center justify-center px-6 py-4 border border-transparent text-sm font-semibold rounded-2xl text-warning-foreground bg-warning hover:bg-warning/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning transition-colors active:scale-[0.98]"
            @click="handleLogin"
          >
            <LogIn class="w-5 h-5 mr-2" />
            {{ t('common.errors.419.login') }}
          </button>
                    
          <button
            class="w-full inline-flex items-center justify-center px-6 py-4 border border-border text-sm font-semibold rounded-2xl text-foreground bg-muted/50 hover:bg-muted focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-[colors,transform] active:scale-95"
            @click="handleRefresh"
          >
            <RefreshCw class="w-5 h-5 mr-2 text-muted-foreground" />
            {{ t('common.errors.419.refresh') }}
          </button>
        </div>
      </div>

      <!-- Footer Details -->
      <div class="px-10 py-6 border-t border-white/10 bg-black/5">
        <div class="flex items-center justify-between text-[10px] text-muted-foreground/60 font-bold tracking-wider">
          <span>{{ t('common.errors.modal.footerCode') }} {{ state.code }}</span>
          <span class="font-mono">{{ state.traceId }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, type Component } from 'vue';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useI18n } from 'vue-i18n';
import { SECURITY_ROUTES } from '@/config/security';
import { buildSessionExpiredHref } from '@/shared/utils/errorReturn';
import {
  AlertTriangle,
  FileQuestion,
  Fingerprint,
  Lock,
  LogIn,
  RefreshCw,
  ServerCrash,
} from 'lucide-vue-next';
import { reactive } from 'vue';
import { useSystemError } from '@/shared/composables/useSystemError';

// Use a local reactive state that mirrors the system error store
// This breaks the static import cycle while maintaining UI responsiveness
const state = reactive({
    isVisible: false,
    code: null,
    title: '',
    message: '',
    description: '',
    reason: null,
    redirect: null,
    traceId: '',
});

const { state: systemState, hideError: hideErrorFn } = useSystemError();
Object.assign(state, systemState);

const hideError = () => hideErrorFn();
const authStore = useAuthStore();
const { t, te } = useI18n();

const title = computed(() => {
    if (state.title) return state.title;
    const key = `common.errors.${state.code}.title`;
    return te(key) ? t(key) : t('common.errors.unknown.title');
});

const message = computed(() => {
    if (state.message) return state.message;
    
    if (state.code === 419 || state.code === 401) {
        if (state.reason === 'concurrent') return t('common.errors.419.concurrent');
        if (state.reason === 'timeout') return t('common.errors.419.timeout');
    }
    
    const key = `common.errors.${state.code}.message`;
    return te(key) ? t(key) : t('common.errors.unknown.message');
});

interface ErrorConfig {
    icon: Component;
    iconBgClass: string;
    iconClass: string;
}

const config = computed<ErrorConfig>(() => {
    const code = Number(state.code);
    switch (code) {
        case 401:
        case 419:
            return {
                icon: Fingerprint,
                iconBgClass: 'bg-orange-500/10 border-orange-500/20',
                iconClass: 'text-orange-600 dark:text-orange-500'
            };
        case 403:
            return {
                icon: Lock,
                iconBgClass: 'bg-red-500/10 border-red-500/20',
                iconClass: 'text-red-600 dark:text-red-500'
            };
        case 404:
            return {
                icon: FileQuestion,
                iconBgClass: 'bg-blue-500/10 border-blue-500/20',
                iconClass: 'text-blue-600 dark:text-blue-500'
            };
        case 429:
            return {
                icon: AlertTriangle,
                iconBgClass: 'bg-yellow-500/10 border-yellow-500/20',
                iconClass: 'text-yellow-600 dark:text-yellow-500'
            };
        case 500:
        default:
            return {
                icon: ServerCrash,
                iconBgClass: 'bg-destructive/10 border-destructive/20',
                iconClass: 'text-destructive'
            };
    }
});

const handleLogin = () => {
    hideError();
    authStore.clearAuth();

    if (state.code === 401 || state.code === 419) {
        const reason = state.reason === 'concurrent' ? 'concurrent' : 'timeout';
        window.location.assign(buildSessionExpiredHref({
            reason,
            redirect: state.redirect,
            currentPath: window.location.pathname + window.location.search,
        }));
        return;
    }

    window.location.assign(SECURITY_ROUTES.login);
};

const handleRefresh = () => {
    window.location.reload();
};
</script>
