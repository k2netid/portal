<template>
  <AuthLayout>
    <template #title>
      {{ t('system.auth.verifyEmail.title') }}
    </template>
    <template #subtitle>
      {{ t('system.auth.verifyEmail.description') }}
    </template>

    <div
      v-if="message"
      class="rounded-xl p-2 text-[10px] mb-3 animate-fade"
      :class="messageType === 'error' ? 'bg-destructive/10 text-destructive border border-destructive/20' : 'bg-success/10 text-success border border-success/20'"
    >
      {{ message }}
    </div>

    <div class="space-y-2">
      <div class="text-center md:text-left">
        <div class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-primary/5 text-primary mb-1">
          <Mail class="h-5 w-5" />
        </div>
        <p class="text-muted-foreground text-[10px] leading-relaxed">
          {{ t('system.auth.verifyEmail.resendPrompt') }}
        </p>
      </div>

      <div class="space-y-1.5">
        <Button
          :disabled="loading || resendCooldown > 0"
          class="w-full h-9 auth-button-gradient py-2 px-4 shadow-lg shadow-primary/10"
          @click="handleResend"
        >
          <Loader2
            v-if="loading"
            class="mr-2 h-3 w-3 animate-spin"
          />
          <span
            v-if="loading"
            class="text-xs"
          >{{ t('system.auth.verifyEmail.sending') }}</span>
          <span
            v-else-if="resendCooldown > 0"
            class="text-xs"
          >{{ t('system.auth.verifyEmail.cooldown', { seconds: resendCooldown }) }}</span>
          <span
            v-else
            class="text-xs"
          >{{ t('system.auth.verifyEmail.resendButton') }}</span>
        </Button>

        <div class="text-center">
          <router-link
            :to="{ name: 'login' }"
            class="inline-flex items-center text-sm font-bold text-primary hover:text-primary/80 transition-colors group"
          >
            <ArrowLeft class="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1" />
            {{ t('system.auth.forgotPassword.backToLogin') }}
          </router-link>
        </div>
      </div>
    </div>

    <div
      v-if="verified"
      class="mt-3 bg-success/10 border border-success/20 rounded-xl p-3 text-center animate-fade-up"
    >
      <CheckCircle class="mx-auto h-8 w-8 text-success mb-2" />
      <p class="text-success font-black text-sm">
        {{ t('system.auth.verifyEmail.success') }}
      </p>
      <p class="text-[10px] text-success/70 mt-0.5">
        {{ t('system.auth.verifyEmail.redirecting') }}
      </p>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import {
  ArrowLeft,
  CheckCircle,
  Loader2,
  Mail,
} from 'lucide-vue-next';
import { useAuthScreenTracking } from '@/shared/composables/useAuthScreenTracking';
import AuthLayout from '../../components/auth/AuthLayout.vue';

// Shadcn Components
import { Button } from '@/shared/components/ui';

const { t } = useI18n();
useAuthScreenTracking();

const router = useRouter();
const route = useRoute();

const message = ref('');
const messageType = ref('');
const loading = ref(false);
const verified = ref(false);
const resendCooldown = ref(0);
let cooldownInterval: ReturnType<typeof setInterval> | null = null;

onMounted(async () => {
    // Check if there's a verification token in the URL
    if (route.query.token && route.query.email) {
        await handleVerify(route.query.token as string, route.query.email as string);
    }
});

onUnmounted(() => {
    if (cooldownInterval) {
        clearInterval(cooldownInterval);
    }
});

const handleVerify = async (token: string, email: string) => {
    loading.value = true;
    message.value = '';
    messageType.value = '';

    try {
        const response = await api.post('/verify-email', {
            token,
            email,
        });
        const payload = response.data as { success?: boolean; message?: string };

        if (payload.success !== false) {
            verified.value = true;
            message.value = t('system.auth.verifyEmail.success');
            messageType.value = 'success';
            
            setTimeout(() => {
                router.push({ name: 'login' });
            }, 2000);
        } else {
            message.value = payload.message || t('system.auth.verifyEmail.failed');
            messageType.value = 'error';
        }
    } catch (error: unknown) {
        if (typeof error === 'object' && error !== null && 'response' in error) {
            const err = error as { response?: { data?: { message?: string } } };
            message.value = err.response?.data?.message || t('system.auth.verifyEmail.failed');
        } else {
            message.value = t('system.auth.verifyEmail.failed');
        }
        messageType.value = 'error';
    } finally {
        loading.value = false;
    }
};

const handleResend = async () => {
    loading.value = true;
    message.value = '';
    messageType.value = '';

    try {
        const email = route.query.email || '';
        const response = await api.post('/resend-verification', {
            email,
        });
        const payload = response.data as { success?: boolean; message?: string };

        if (payload.success !== false) {
            message.value = t('system.auth.verifyEmail.resendSuccess');
            messageType.value = 'success';
            
            // Start cooldown timer (60 seconds)
            resendCooldown.value = 60;
            cooldownInterval = setInterval(() => {
                resendCooldown.value--;
                if (resendCooldown.value <= 0) {
                    if (cooldownInterval) clearInterval(cooldownInterval);
                    cooldownInterval = null;
                }
            }, 1000);
        } else {
            message.value = payload.message || t('system.auth.verifyEmail.failed');
            messageType.value = 'error';
        }
    } catch (error: unknown) {
        if (typeof error === 'object' && error !== null && 'response' in error) {
            const err = error as { response?: { data?: { message?: string } } };
            message.value = err.response?.data?.message || t('system.auth.verifyEmail.failed');
        } else {
            message.value = t('system.auth.verifyEmail.failed');
        }
        messageType.value = 'error';
    } finally {
        loading.value = false;
    }
};
</script>
