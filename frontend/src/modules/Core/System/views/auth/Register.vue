<template>
  <AuthLayout>
    <template #title>
      Create account
    </template>
    <template #subtitle>
      {{ t('system.auth.register.subtitle') }}
    </template>

    <form
      class="space-y-2"
      @submit.prevent="handleRegister"
    >
      <div class="space-y-1">
        <Label
          for="name"
          class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80"
        >{{ t('common.labels.name') }}</Label>
        <Input
          id="name"
          ref="nameInput"
          v-model="form.name"
          name="name"
          type="text"
          required
          class="auth-input h-9 text-sm"
          :class="errors.name ? 'border-destructive/50 ring-destructive/20 focus:border-destructive' : ''"
          :placeholder="t('system.auth.register.namePlaceholder')"
        />
        <p
          v-if="errors.name"
          class="text-[10px] text-destructive font-medium ml-1"
        >
          {{ errors.name[0] }}
        </p>
      </div>

      <div class="space-y-1">
        <Label
          for="email"
          class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80"
        >{{ t('common.labels.email') }}</Label>
        <Input
          id="email"
          ref="emailInput"
          v-model="form.email"
          name="email"
          type="email"
          required
          class="auth-input h-9 text-sm"
          :class="errors.email ? 'border-destructive/50 ring-destructive/20 focus:border-destructive' : ''"
          :placeholder="t('system.auth.register.emailPlaceholder')"
        />
        <p
          v-if="errors.email"
          class="text-[10px] text-destructive font-medium ml-1"
        >
          {{ errors.email[0] }}
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <div class="space-y-1">
          <Label
            for="password"
            class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80"
          >{{ t('common.labels.password') }}</Label>
          <div class="relative">
            <Input
              id="password"
              ref="passwordInput"
              v-model="form.password"
              name="password"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="new-password"
              required
              class="auth-input h-9 text-sm pr-10"
              :class="errors.password ? 'border-destructive/50 ring-destructive/20 focus:border-destructive' : ''"
              :placeholder="t('system.auth.register.passwordPlaceholder')"
            />
            <button 
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
              @click="showPassword = !showPassword"
            >
              <Eye
                v-if="!showPassword"
                class="h-4 w-4"
              />
              <EyeOff
                v-else
                class="h-4 w-4"
              />
            </button>
          </div>
        </div>
        <div class="space-y-1">
          <Label
            for="password_confirmation"
            class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80"
          >{{ t('common.labels.confirmPassword') }}</Label>
          <Input
            id="password_confirmation"
            v-model="form.password_confirmation"
            name="password_confirmation"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="new-password"
            required
            class="auth-input h-9 text-sm"
            :placeholder="t('system.auth.register.confirmPasswordPlaceholder')"
          />
        </div>
        <p
          v-if="errors.password"
          class="col-span-full text-[10px] text-destructive font-medium ml-1"
        >
          {{ errors.password[0] }}
        </p>
      </div>

      <div class="flex items-center space-x-2 px-1">
        <Checkbox 
          id="terms" 
          name="terms"
          :checked="form.terms" 
          class="h-4 w-4 rounded border-border/60 transition-colors data-[state=checked]:bg-primary"
          @update:checked="(v) => form.terms = v"
        />
        <label
          for="terms"
          class="text-xs font-medium leading-none text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
        >
          {{ t('system.auth.register.terms_prefix') }} <router-link
            :to="{ name: 'terms' }"
            class="text-primary hover:underline"
          >{{ t('system.auth.register.terms_link') }}</router-link> {{ t('system.auth.register.terms_and') }} <router-link
            :to="{ name: 'privacy' }"
            class="text-primary hover:underline"
          >{{ t('system.auth.register.privacy_link') }}</router-link>
        </label>
      </div>

      <div v-show="captchaEnabled" class="rounded-xl overflow-hidden border border-border/40 bg-muted/5">
        <CaptchaWrapper
          ref="captchaRef"
          action="register"
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
        >{{ t('common.messages.loading.processing') }}</span>
        <span
          v-else
          class="text-xs"
        >{{ t('system.auth.register.submit') }}</span>
      </Button>

      <div class="relative my-3">
        <div class="absolute inset-0 flex items-center">
          <span class="w-full border-t border-border/40" />
        </div>
        <div class="relative flex justify-center text-[10px] uppercase tracking-wider">
          <span class="bg-card px-2 text-muted-foreground font-bold">Or</span>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <Button
          variant="outline"
          type="button"
          class="h-9 rounded-lg hover:bg-muted/50 transition-colors border-border/40"
        >
          <svg
            viewBox="0 0 24 24"
            class="h-4 w-4 fill-current"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path d="M12.48 10.92v3.28h7.84c-.24 1.84-.9 3.28-2.12 4.41-1.37 1.34-3.26 2.4-6.4 2.4-5.34 0-9.6-4.32-9.6-9.6s4.26-9.6 9.6-9.6c3.15 0 5.48 1.25 7.18 2.87l2.29-2.29C18.9 1.5 15.86 0 12.48 0 5.58 0 0 5.58 0 12.48s5.58 12.48 12.48 12.48c3.7 0 6.64-1.21 8.87-3.56 2.3-2.3 3-5.5 3-8.1 0-.6-.05-1.12-.15-1.57z" />
          </svg>
        </Button>
        <Button
          variant="outline"
          type="button"
          class="h-9 rounded-lg hover:bg-muted/50 transition-colors border-border/40"
        >
          <Github class="h-4 w-4" />
        </Button>
      </div>

      <div class="text-center text-[10px] text-muted-foreground mt-3">
        {{ t('system.auth.register.alreadyHaveAccount') }} 
        <router-link
          :to="{ name: 'login' }"
          class="font-bold text-primary hover:text-primary/80 transition-all ml-1"
        >
          {{ t('system.auth.register.login') }}
        </router-link>
      </div>
    </form>
  </AuthLayout>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { resolveConsoleDashboardLocation } from '@/config/console';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { registerSchema } from '@/modules/Core/System/schemas/auth';
import { useAuthScreenTracking } from '@/shared/composables/useAuthScreenTracking';
import {
  Eye,
  EyeOff,
  Github,
  Loader2,
} from 'lucide-vue-next';
import api from '@/engine/api/client';
import AuthLayout from '../../components/auth/AuthLayout.vue';


// Shadcn Components
import {
    Button,
    Input,
    Label,
    Checkbox
} from '@/shared/components/ui';
import CaptchaWrapper from '@/modules/Core/System/components/captcha/CaptchaWrapper.vue';
import type { CaptchaPayload } from '@/modules/Core/System/components/captcha/CaptchaWrapper.vue';
import type { RegisterData } from '@/engine/types/auth';

useAuthScreenTracking();

const router = useRouter();
const { t } = useI18n();
const authStore = useAuthStore();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(registerSchema);

const captchaRef = ref<{ enabled: boolean; refresh: () => void } | null>(null);
const captchaVerified = ref(false);
const captchaToken = ref('');
const captchaAnswer = ref('');
const captchaEnabled = computed(() => captchaRef.value?.enabled || false);

// Input Refs for focusing
const nameInput = ref<{ $el: HTMLElement; focus: () => void } | null>(null);
const emailInput = ref<{ $el: HTMLElement; focus: () => void } | null>(null);
const passwordInput = ref<{ $el: HTMLElement; focus: () => void } | null>(null);

const showPassword = ref(false);
const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

// CaptchaPayload imported from CaptchaWrapper.vue

const onCaptchaVerified = (payload: CaptchaPayload) => {
    captchaToken.value = payload.token;
    captchaAnswer.value = payload.answer;
    captchaVerified.value = true;
};

const isValid = computed(() => {
    return !!form.name && 
           !!form.email && 
           !!form.password && 
           !!form.password_confirmation && 
           form.password === form.password_confirmation;
});

const message = ref('');
const messageType = ref('');
const loading = ref(false);
const checkingSettings = ref(true);

// Check if registration is enabled on mount
onMounted(async () => {
    try {
        const response = await api.get('/public/settings');
        const settings = response.data;
        
        if (!settings.enable_registration) {
            // Redirect to login with info message
            router.replace({ name: 'login', query: { info: 'registration_disabled' } });
            return;
        }
    } catch (error) {
        logger.error('Failed to check registration status:', error);
        // Allow registration if we can't check settings (fail-open)
    } finally {
        checkingSettings.value = false;
    }
});

const handleRegister = async () => {
    // Client-side validation first (instant feedback)
    if (!validateWithZod(form)) {
        return;
    }
    
    if (captchaEnabled.value && !captchaVerified.value) {
        message.value = t('system.auth.captcha.required');
        messageType.value = 'error';
        return;
    }

    loading.value = true;
    clearErrors();
    message.value = '';

    const payload: RegisterData & { captcha_token?: string; captcha_answer?: string } = { 
        name: form.name,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
    };
    
    if (captchaEnabled.value) {
        payload.captcha_token = captchaToken.value;
        payload.captcha_answer = captchaAnswer.value;
    }

    const result = await authStore.register(payload);

    if (result.success) {
        message.value = 'Registration successful! Please verify your email.';
        messageType.value = 'success';
        setTimeout(() => {
            router.push(resolveConsoleDashboardLocation());
        }, 2000);
    } else {
        message.value = result.message || '';
        messageType.value = 'error';
        setErrors(result.errors || {});
        
        // Refresh captcha on any failure
        if (captchaEnabled.value) {
            captchaRef.value?.refresh();
            captchaVerified.value = false;
            captchaToken.value = '';
            captchaAnswer.value = '';
        }

        // Auto-focus the first erroneous field
        if (result.errors) {
            if (result.errors.name) {
                if (nameInput.value?.$el) nameInput.value.$el.focus();
                else nameInput.value?.focus();
            } else if (result.errors.email) {
                if (emailInput.value?.$el) emailInput.value.$el.focus();
                else emailInput.value?.focus();
            } else if (result.errors.password) {
                if (passwordInput.value?.$el) passwordInput.value.$el.focus();
                else passwordInput.value?.focus();
            }
        }
    }

    loading.value = false;
};
</script>
