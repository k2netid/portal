<template>
  <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-background via-muted/20 to-background px-4 py-2 sm:px-6 lg:px-8">
    <div class="w-full max-w-lg bg-card rounded-3xl shadow-2xl shadow-primary/5 overflow-hidden border border-border/40 min-h-0 animate-fade-up">
      <!-- Authentication Content -->
      <div class="w-full p-6 sm:p-8 flex flex-col justify-center animate-fade">
        <!-- Dynamic Branding -->
        <div class="flex items-center gap-2 mb-3 group justify-center">
          <div 
            class="rounded-lg p-2 transition-transform group-hover:scale-110 bg-primary/10"
          >
            <img
              v-if="branding.app_logo"
              :src="branding.app_logo"
              :alt="branding.app_name"
              class="h-6 w-auto object-contain"
            >
            <LayoutTemplate
              v-else
              class="h-6 w-6 text-primary"
            />
          </div>
          <span class="text-xl font-black tracking-tight text-foreground">{{ branding.app_name }}</span>
        </div>

        <!-- Slot for Form Content -->
        <div class="mb-3 text-center md:text-left">
          <h2 class="text-xl font-black tracking-tight text-foreground">
            <slot name="title">{{ t('system.auth.login.welcomeBack') }}</slot>
          </h2>
          <p class="mt-0.5 text-muted-foreground text-[10px]">
            <slot name="subtitle">{{ t('system.auth.login.subtitle') }}</slot>
          </p>
        </div>

        <slot />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useSystemStore } from '@/modules/Core/System/stores/system';
import {
  LayoutTemplate,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const systemStore = useSystemStore();
const branding = computed(() => systemStore.appIdentity);
</script>

<style scoped>
.animate-fade-up {
  animation: fade-up 0.5s ease-out;
}

.animate-fade {
  animation: fade 0.3s ease-out;
}

@keyframes fade-up {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fade {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
