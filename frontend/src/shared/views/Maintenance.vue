<template>
  <div class="min-h-screen flex items-center justify-center p-4 bg-slate-950 text-slate-50 relative overflow-hidden font-sans">
    <!-- Admin bypass login link -->
    <a
      :href="loginUrl"
      class="absolute top-4 right-4 text-xs text-slate-600 hover:text-slate-400 font-mono transition-colors"
    >
      {{ t('common.errors.maintenancePage.adminBypass') }}
    </a>

    <!-- Decorative background elements -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-[10%] -right-[10%] w-[50%] h-[50%] rounded-full bg-blue-900/10 blur-[120px]" />
      <div class="absolute -bottom-[10%] -left-[10%] w-[50%] h-[50%] rounded-full bg-indigo-900/10 blur-[120px]" />
    </div>

    <div class="max-w-2xl w-full text-center space-y-8 z-10">
      <!-- Logo Container -->
      <div class="flex justify-center mb-12">
        <template v-if="publicSettings?.site_logo">
          <img
            :src="publicSettings.site_logo"
            :alt="publicSettings.site_name || 'Logo'"
            class="h-20 w-auto animate-float"
          >
        </template>
        <template v-else>
          <div class="h-20 w-20 bg-blue-600 rounded-2xl flex items-center justify-center text-4xl font-bold text-white shadow-lg shadow-blue-500/20 animate-float">
            {{ (publicSettings?.site_name || 'J').charAt(0) }}
          </div>
        </template>
      </div>

      <!-- Main Content -->
      <div class="space-y-6">
        <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight">
          <span class="gradient-text">{{ title }}</span>
        </h1>
        <p class="text-xl text-slate-400 max-w-lg mx-auto leading-relaxed">
          {{ message }}
        </p>
      </div>

      <!-- Countdown -->
      <div
        v-if="countdownEnabled && targetDate"
        class="grid grid-cols-4 gap-4 max-w-md mx-auto pt-8"
      >
        <div class="glass-card rounded-2xl p-4 flex flex-col items-center justify-center">
          <div class="text-3xl md:text-4xl font-bold font-mono">
            {{ remaining.days.toString().padStart(2, '0') }}
          </div>
          <div class="text-[10px] md:text-xs tracking-wider text-slate-500 mt-1 font-bold">
            {{ t('common.errors.maintenancePage.days') }}
          </div>
        </div>
        <div class="glass-card rounded-2xl p-4 flex flex-col items-center justify-center">
          <div class="text-3xl md:text-4xl font-bold font-mono">
            {{ remaining.hours.toString().padStart(2, '0') }}
          </div>
          <div class="text-[10px] md:text-xs tracking-wider text-slate-500 mt-1 font-bold">
            {{ t('common.errors.maintenancePage.hours') }}
          </div>
        </div>
        <div class="glass-card rounded-2xl p-4 flex flex-col items-center justify-center">
          <div class="text-3xl md:text-4xl font-bold font-mono">
            {{ remaining.minutes.toString().padStart(2, '0') }}
          </div>
          <div class="text-[10px] md:text-xs tracking-wider text-slate-500 mt-1 font-bold">
            {{ t('common.errors.maintenancePage.mins') }}
          </div>
        </div>
        <div class="glass-card rounded-2xl p-4 flex flex-col items-center justify-center border border-blue-500/30 bg-blue-500/5">
          <div class="text-3xl md:text-4xl font-bold text-blue-400 font-mono">
            {{ remaining.seconds.toString().padStart(2, '0') }}
          </div>
          <div class="text-[10px] md:text-xs tracking-wider text-slate-500 mt-1 font-bold">
            {{ t('common.errors.maintenancePage.secs') }}
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <div
        v-if="hasSafeReturn"
        class="flex flex-col sm:flex-row gap-3 justify-center pt-8 max-w-md mx-auto"
      >
        <button
          type="button"
          class="flex-1 inline-flex items-center justify-center px-4 py-3 rounded-2xl text-sm font-medium border border-slate-700 text-slate-200 hover:bg-slate-800/80 transition-colors"
          @click="goBack"
        >
          {{ t('common.errors.404.back') }}
        </button>
        <button
          type="button"
          class="flex-1 inline-flex items-center justify-center px-4 py-3 rounded-2xl text-sm font-medium bg-blue-600 text-white hover:bg-blue-700 transition-colors"
          @click="goHome"
        >
          {{ t('common.errors.404.home') }}
        </button>
      </div>

      <!-- Footer indicator -->
      <div class="pt-16 pb-4">
        <div class="inline-flex items-center space-x-3 text-sm text-slate-500 bg-slate-900/50 rounded-full px-4 py-2 border border-slate-800">
          <div class="relative flex h-2.5 w-2.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75" />
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-500" />
          </div>
          <span class="font-medium tracking-wide">{{ t('common.errors.maintenancePage.footer') }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useHead } from '@unhead/vue';
import { useI18n } from 'vue-i18n';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { SECURITY_ROUTES } from '@/config/security';
import { useErrorPageNavigation } from '@/shared/composables/useErrorPageNavigation';

const { t } = useI18n();
const systemStore = useSystemStore();
const { goHome, goBack, hasSafeReturn, prepareErrorPage } = useErrorPageNavigation({ errorPath: '/maintenance' });
prepareErrorPage();

const publicSettings = computed(() => systemStore.siteSettings);
const loginUrl = SECURITY_ROUTES.login;

const title = computed(() => systemStore.maintenance.title || t('common.errors.maintenancePage.defaultTitle'));
const message = computed(() => systemStore.maintenance.message || t('common.errors.maintenancePage.defaultMessage'));
const countdownEnabled = computed(() => systemStore.maintenance.countdown_enabled);
const targetDateStr = computed(() => systemStore.maintenance.end_time);

const targetDate = computed(() => {
    if (!targetDateStr.value) return null;
    const date = new Date(targetDateStr.value as string);
    return isNaN(date.getTime()) ? null : date;
});

const remaining = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 });
let timer: number | null = null;

const updateCountdown = () => {
    if (!targetDate.value) return;

    const now = new Date().getTime();
    const distance = targetDate.value.getTime() - now;

    if (distance < 0) {
        remaining.value = { days: 0, hours: 0, minutes: 0, seconds: 0 };
        if (timer) clearInterval(timer);
        // If countdown finishes, refresh the page to see if maintenance is lifted
        window.location.reload();
        return;
    }

    remaining.value = {
        days: Math.floor(distance / (1000 * 60 * 60 * 24)),
        hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
        minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
        seconds: Math.floor((distance % (1000 * 60)) / 1000)
    };
};

// Also poll the health check to auto-recover if admin disables it early
let healthTimer: number | null = null;
const pollHealth = async () => {
    try {
        await systemStore.fetchPublicSettings();
        if (!systemStore.maintenance.mode) {
            void goHome();
        }
    } catch {
        // Still down or network error
    }
};

useHead({
  title: computed(() => `${title.value} | ${publicSettings.value?.site_name || t('common.labels.system')}`)
});

onMounted(async () => {
    // Always refresh settings when on maintenance page
    try {
        await systemStore.fetchPublicSettings();
    } catch (error) {
        console.error('Failed to fetch settings for maintenance mode:', error);
    }

    // Small delay to ensure computed properties update
    setTimeout(() => {
        if (countdownEnabled.value && targetDate.value) {
            console.log('Starting countdown to:', targetDate.value);
            updateCountdown();
            timer = window.setInterval(updateCountdown, 1000);
        } else {
            console.warn('Countdown not started. Enabled:', countdownEnabled.value, 'Target:', targetDate.value);
        }
    }, 100);
    
    // Poll every 10 seconds to see if maintenance is lifted
    healthTimer = window.setInterval(pollHealth, 10000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
    if (healthTimer) clearInterval(healthTimer);
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;600;700&display=swap');

.font-sans {
  font-family: 'Instrument Sans', sans-serif;
}

.glass-card {
  background: rgba(255, 255, 255, 0.02);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.05);
  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
}

.gradient-text {
  background: linear-gradient(135deg, #60a5fa 0%, #818cf8 50%, #c084fc 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-12px); }
  100% { transform: translateY(0px); }
}

.animate-float {
  animation: float 5s ease-in-out infinite;
}
</style>
