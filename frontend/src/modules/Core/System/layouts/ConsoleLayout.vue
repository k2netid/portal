<template>
  <div 
    class="min-h-screen bg-background text-foreground admin-instant admin-layout"
    :class="{ 'no-transitions': resizing }"
    v-bind="consoleLayoutAttrs"
    :style="consoleStyles"
  >
    <!-- Sidebar -->
    <TheSidebar
      :sidebar-minimized="sidebarMinimized"
      :sidebar-open="sidebarOpen"
      :user="authStore.user || undefined"
      @toggle-minimize="toggleSidebarMinimize"
      @close="closeSidebar"
      @logout="handleLogout"
    />

    <!-- Mobile Backdrop -->
    <div 
      v-if="sidebarOpen" 
      class="fixed inset-0 z-40 bg-background/60 lg:hidden"
      @click="closeSidebar"
    />

    <!-- Main Content -->
    <div
      :class="[
        'min-h-screen',
        sidebarMinimized ? 'lg:pl-[68px]' : 'lg:pl-64'
      ]"
    >
      <!-- Top Navbar -->
      <TheNavbar
        :is-authenticated="authStore.isAuthenticated"
        :user="authStore.user || undefined"
        @toggle-sidebar="toggleSidebarOpen"
        @logout="handleLogout"
      />

      <!-- Page Content -->
      <main
        class="relative overflow-hidden transition-all duration-200"
        :class="route.meta?.fullWidth ? 'p-3.5 sm:p-4 lg:p-5' : 'p-6 lg:p-8'"
      >
        <div
          class="w-full transition-all duration-200"
          :class="route.meta?.fullWidth ? 'max-w-none' : 'console-content-wrap'"
        >
        <router-view v-slot="{ Component, route: slotRoute }">
          <KeepAlive
            v-if="!slotRoute.meta?.noCache"
            :max="10"
          >
            <component
              :is="Component"
              :key="`cached:${String(slotRoute.name || slotRoute.path)}`"
            />
          </KeepAlive>
          <component
            v-else
            :is="Component"
            :key="`live:${String(slotRoute.fullPath || slotRoute.path)}`"
          />
        </router-view>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useSidebar } from '@/shared/composables/useSidebar';
import { useHead } from '@unhead/vue';
import { useI18n } from 'vue-i18n';
import TheSidebar from '@/shared/layouts/partials/TheSidebar.vue';
import TheNavbar from '@/shared/layouts/partials/TheNavbar.vue';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useConsoleTheme } from '@/modules/Core/System/composables/useConsoleTheme';
import { useConsoleThemeDocumentSync } from '@/modules/Core/System/composables/useConsoleThemeDocumentSync';
import { useAuthStore } from '@/modules/Core/System/stores/auth';

const router = useRouter();
const route = useRoute();
const systemStore = useSystemStore();
const authStore = useAuthStore();
const { t, te } = useI18n();
const { sidebarMinimized, sidebarOpen, toggleSidebarMinimize, toggleSidebarOpen, closeSidebar } = useSidebar();

const { cssVars: consoleStyles, layoutAttrs: consoleLayoutAttrs, load: loadConsoleTheme } = useConsoleTheme();
useConsoleThemeDocumentSync();

// Resize Throttling
const resizing = ref(false);
let resizeTimer: ReturnType<typeof setTimeout> | null = null;

const handleResize = () => {
    resizing.value = true;
    document.body.classList.add('no-transitions');
    if (resizeTimer) clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        resizing.value = false;
        document.body.classList.remove('no-transitions');
    }, 200);
};

// Reactive Global Title Management
const pageTitle = ref('Jejakawan');

watch([() => route?.name, () => systemStore.appIdentity?.app_name, () => route?.meta], () => {
    if (!route) return;
    const appName = systemStore.appIdentity?.app_name || 'Jejakawan';
    
    if (route.meta?.title) {
        const titleKey = route.meta.title as string;
        const title = te(titleKey) ? t(titleKey) : titleKey;
        pageTitle.value = `${appName} | ${title}`;
        return;
    }
    
    if (route.name) {
        const name = String(route.name);
        const segments = name.replace(/-([a-z])/g, (_, g1) => (g1 || '').toUpperCase()).split('.');
        const camelName = segments[0] || ''; 
        const key = `common.navigation.menu.${camelName}`;
        
        let label: string;
        if (te(key)) {
            label = t(key);
        } else {
            const baseLabel = name.split('.').pop() || name;
            label = baseLabel.charAt(0).toUpperCase() + baseLabel.slice(1);
        }
        
        pageTitle.value = `${appName} | ${label}`;
        return;
    }

    pageTitle.value = appName;
}, { immediate: true });

onMounted(() => {
    window.addEventListener('resize', handleResize);
    const warmShell = () => {
        void Promise.all([systemStore.fetchAppIdentity(), loadConsoleTheme()]);
    };
    if (typeof window !== 'undefined' && 'requestIdleCallback' in window) {
        window.requestIdleCallback(warmShell, { timeout: 2000 });
    } else {
        setTimeout(warmShell, 0);
    }
});

onUnmounted(() => {
    if (resizeTimer) clearTimeout(resizeTimer);
    document.body.classList.remove('no-transitions');
    window.removeEventListener('resize', handleResize);
});

useHead({
    title: pageTitle
});

const handleLogout = async () => {
    await authStore.logout();
    router.push({ name: 'login' });
};
</script>
