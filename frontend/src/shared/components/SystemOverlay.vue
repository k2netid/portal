<template>
  <div
    v-if="state.isSystemDown"
    class="fixed inset-0 z-[9999] bg-background/95 backdrop-blur-md flex flex-col items-center justify-center p-4 text-center"
  >
    <div class="max-w-md w-full space-y-8">
      <!-- Icon based on reason -->
      <div class="flex justify-center">
        <div class="relative">
          <div class="absolute inset-0 bg-primary/20 rounded-full animate-ping" />
          <div class="relative bg-card p-4 rounded-full border border-border shadow-2xl">
            <Wrench
              v-if="state.downReason === 'maintenance'"
              class="w-12 h-12 text-primary"
            />
            <AlertTriangle
              v-else
              class="w-12 h-12 text-yellow-500"
            />
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="space-y-4">
        <h2 class="text-2xl font-bold tracking-tight">
          {{ title }}
        </h2>
        <p class="text-muted-foreground">
          {{ message }}
        </p>
                
        <!-- Progress/Status -->
        <div class="flex items-center justify-center gap-2 text-sm text-primary font-medium">
          <LoaderCircle class="w-4 h-4 animate-spin" />
          <span>{{ t('system.systemOverlay.waitingConnection') }}</span>
        </div>
      </div>
            
      <button
        class="text-xs text-muted-foreground hover:text-primary transition-colors"
        @click="forceReload"
      >
        {{ t('system.systemOverlay.forceReload') }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { systemState } from '@/modules/Core/System/services/SystemMonitor';
import {
  AlertTriangle,
  LoaderCircle,
  Wrench,
} from 'lucide-vue-next';

const { t } = useI18n();
const state = systemState;

const title = computed(() => {
    if (state.downReason === 'maintenance') return t('system.systemOverlay.titles.maintenance');
    if (state.downReason === 'chunk_error') return t('system.systemOverlay.titles.chunkError');
    return t('system.systemOverlay.titles.connectionInterrupted');
});

const message = computed(() => {
    if (state.downReason === 'maintenance') return t('system.systemOverlay.messages.maintenance');
    if (state.downReason === 'chunk_error') return t('system.systemOverlay.messages.chunkError');
    return t('system.systemOverlay.messages.connectionInterrupted');
});

const forceReload = () => {
    window.location.reload();
};
</script>
