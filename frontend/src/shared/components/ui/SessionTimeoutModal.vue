<template>
  <teleport to="body">
    <transition name="modal">
      <div
        v-if="isVisible"
        class="fixed inset-0 z-[9999] overflow-y-auto"
        @click.self="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" />

        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
          <div
            class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-card shadow-2xl transition-[opacity,transform]"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-title"
          >
            <!-- Icon -->
            <div class="flex items-center justify-center pt-8 pb-4">
              <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                <Clock class="h-8 w-8 text-amber-600 dark:text-amber-400 animate-pulse" />
              </div>
            </div>

            <!-- Content -->
            <div class="px-6 pb-6">
              <h3
                id="modal-title"
                class="text-center text-xl font-semibold text-foreground mb-2"
              >
                {{ t('common.auth.sessionTimeout.title') }}
              </h3>
              
              <p class="text-center text-muted-foreground mb-6">
                {{ t('common.auth.sessionTimeout.expiresIn') }}
              </p>

              <!-- Countdown Timer -->
              <div class="flex items-center justify-center mb-6">
                <div class="text-center">
                  <div class="text-5xl font-bold text-amber-600 dark:text-amber-400 tabular-nums">
                    {{ formatTime(timeRemaining) }}
                  </div>
                  <div class="text-sm text-muted-foreground mt-1">
                    {{ timeRemaining > 60 ? t('common.auth.sessionTimeout.timeFormatMinutes') : t('common.auth.sessionTimeout.timeFormatSeconds') }}
                  </div>
                </div>
              </div>

              <!-- Warning Message -->
              <div class="mb-6 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4">
                <div class="flex">
                  <Info class="h-5 w-5 text-amber-600 dark:text-amber-400 mt-0.5 mr-3 flex-shrink-0" />
                  <div class="text-sm text-amber-800 dark:text-amber-400">
                    <p class="font-medium mb-1">
                      {{ t('common.auth.sessionTimeout.unsavedTitle') }}
                    </p>
                    <p class="text-amber-700 dark:text-amber-500">
                      {{ t('common.auth.sessionTimeout.unsavedMessage') }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex flex-col sm:flex-row gap-3">
                <button
                  :disabled="extending"
                  class="flex-1 inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="extendSession"
                >
                  <Loader2
                    v-if="extending"
                    class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                  />
                  <RotateCcw
                    v-else
                    class="h-5 w-5 mr-2"
                  />
                  {{ extending ? t('common.auth.sessionTimeout.extending') : t('common.auth.sessionTimeout.extend') }}
                </button>
                
                <button
                  class="flex-1 inline-flex items-center justify-center px-6 py-3 border border-input text-base font-medium rounded-lg text-foreground bg-card hover:bg-muted focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors"
                  @click="logout"
                >
                  <LogOut class="h-5 w-5 mr-2" />
                  {{ t('common.auth.sessionTimeout.logoutNow') }}
                </button>
              </div>

              <!-- Additional Info -->
              <div class="mt-4 text-center text-xs text-muted-foreground">
                <p>{{ t('common.auth.sessionTimeout.autoLogoutHint') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Clock,
  Info,
  Loader2,
  LogOut,
  RotateCcw,
} from 'lucide-vue-next';

withDefaults(defineProps<{
  isVisible?: boolean;
  timeRemaining?: number;
}>(), {
  isVisible: false,
  timeRemaining: 300,
});

const emit = defineEmits<{
  'extend': [];
  'logout': [];
  'close': [];
}>();

const { t } = useI18n();
const extending = ref(false);

const formatTime = (seconds: number) => {
  if (seconds <= 0) return '0:00';
  
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  
  if (mins > 0) {
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  }
  
  return `${secs}`;
};

const extendSession = async () => {
  extending.value = true;
  try {
    emit('extend');
    // Small delay for UX
    await new Promise(resolve => setTimeout(resolve, 500));
  } finally {
    extending.value = false;
  }
};

const logout = () => {
  emit('logout');
};

const handleBackdropClick = () => {
  // Don't allow closing by clicking backdrop for critical modal
  // User must choose an action
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: all 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95) translateY(-20px);
  opacity: 0;
}
</style>
