<template>
  <AuthLayout>
    <template #title>
      {{ t('system.auth.forgotPassword.title') }}
    </template>
    <template #subtitle>
      {{ t('system.auth.forgotPassword.subtitle') }}
    </template>

    <form
      class="space-y-2"
      @submit.prevent="handleSubmit"
    >
      <div class="space-y-1">
        <Label
          for="email"
          class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80"
        >{{ t('common.labels.email') }}</Label>
        <Input
          id="email"
          v-model="form.email"
          name="email"
          type="email"
          autocomplete="email"
          required
          class="auth-input h-9 text-sm"
          :class="errors.email ? 'border-destructive/50 ring-destructive/20 focus:border-destructive' : ''"
          :placeholder="t('system.auth.login.emailPlaceholder')"
        />
        <p
          v-if="errors.email"
          class="text-[10px] text-destructive font-medium ml-1"
        >
          {{ errors.email[0] }}
        </p>
      </div>

      <!-- Captcha -->
      <div v-show="captchaEnabled" class="rounded-xl overflow-hidden border border-border/40 bg-muted/5">
        <CaptchaWrapper 
          ref="captchaRef"
          action="forgot-password"
          @verified="onCaptchaVerified"
        />
      </div>

      <div
        v-if="message"
        class="rounded-xl p-2 text-[10px] border animate-fade"
        :class="messageType === 'error' ? 'bg-destructive/10 text-destructive border-destructive/20' : 'bg-success/10 text-success border-success/20'"
      >
        {{ message }}
      </div>

      <Button
        type="submit"
        class="w-full h-9 auth-button-gradient mt-1"
        :disabled="loading || !isValid || (captchaEnabled && !captchaVerified)"
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
          v-else
          class="text-xs"
        >{{ t('system.auth.forgotPassword.submit') }}</span>
      </Button>

      <div class="text-center text-[10px] text-muted-foreground mt-3">
        <router-link
          :to="{ name: 'login' }"
          class="inline-flex items-center font-bold text-primary hover:text-primary/80 transition-all group"
        >
          <ArrowLeft class="mr-2 h-3 w-3 transition-transform group-hover:-translate-x-1" />
          {{ t('system.auth.forgotPassword.backToLogin') }}
        </router-link>
      </div>
    </form>
  </AuthLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { forgotPasswordSchema } from '@/modules/Core/System/schemas/auth';
import {
  ArrowLeft,
  Loader2,
} from 'lucide-vue-next';
import CaptchaWrapper, { type CaptchaPayload } from '@/modules/Core/System/components/captcha/CaptchaWrapper.vue';
import AuthLayout from '../../components/auth/AuthLayout.vue';
import { useAuthScreenTracking } from '@/shared/composables/useAuthScreenTracking';

// Shadcn Components
import {
    Button,
    Input,
    Label
} from '@/shared/components/ui';

useAuthScreenTracking();

const { t } = useI18n();
const authStore = useAuthStore();
const { errors, validateWithZod, clearErrors } = useFormValidation(forgotPasswordSchema);

interface CaptchaWrapperInstance {
    enabled: boolean;
    method: string;
}

const captchaRef = ref<CaptchaWrapperInstance | null>(null);
const captchaVerified = ref(false);
const captchaToken = ref('');
const captchaAnswer = ref('');
const captchaEnabled = computed(() => captchaRef.value?.enabled || false);

const onCaptchaVerified = (payload: CaptchaPayload) => {
    captchaToken.value = payload.token;
    captchaAnswer.value = payload.answer;
    captchaVerified.value = true;
};

const form = reactive({
    email: '',
});

const isValid = computed(() => {
    return !!form.email;
});

const message = ref('');
const messageType = ref('');
const loading = ref(false);

const handleSubmit = async () => {
    // Client-side validation first
    if (!validateWithZod(form)) {
        return;
    }

    loading.value = true;
    clearErrors();
    message.value = '';

    const payload: { email: string; captcha_token?: string; captcha_answer?: string } = {
        email: form.email,
    };

    if (captchaEnabled.value) {
        payload.captcha_token = captchaToken.value;
        payload.captcha_answer = captchaAnswer.value;
    }

    const result = await authStore.forgotPassword(payload);

    if (result.success) {
        message.value = result.message || '';
        messageType.value = 'success';
    } else {
        message.value = result.message || '';
        messageType.value = 'error';

        // Reset captcha on failure
        if (captchaRef.value?.method === 'slider' || captchaRef.value?.method === 'math' || captchaRef.value?.method === 'image') {
            captchaVerified.value = false;
            captchaToken.value = '';
            captchaAnswer.value = '';
        }
    }

    loading.value = false;
};
</script>
