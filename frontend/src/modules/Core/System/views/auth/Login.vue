<template>
  <AuthLayout>
    <template #title>
      {{ requiresTwoFactor ? t('system.auth.twoFactor.title') : t('system.auth.login.welcomeBack') }}
    </template>
    <template #subtitle>
      {{ requiresTwoFactor ? t('system.auth.twoFactor.subtitle') : t('system.auth.login.subtitle') }}
    </template>

    <!-- 2FA Verification Form -->
    <div v-if="requiresTwoFactor">
      <form
        class="space-y-3"
        @submit.prevent="verifyTwoFactor"
      >
        <div class="space-y-2">
          <Label
            for="two-factor-code"
            class="text-[10px] uppercase tracking-wider font-bold ml-1 text-muted-foreground/80"
          >
            {{ t('system.auth.twoFactor.codeLabel') }}
          </Label>
          <Input
            id="two-factor-code"
            v-model="twoFactorCode"
            name="two-factor-code"
            type="text"
            autocomplete="one-time-code"
            required
            autofocus
            :placeholder="t('common.placeholders.otp')"
            class="text-center text-xl tracking-[0.5em] font-mono h-12 auth-input"
            maxlength="6"
            @input="twoFactorCode = twoFactorCode.replace(/\D/g, '')"
          />
        </div>

        <div
          v-if="message"
          class="rounded-xl p-4 text-sm border animate-fade"
          :class="messageType === 'error' ? 'bg-destructive/10 text-destructive border-destructive/20' : 'bg-success/10 text-success border-success/20'"
        >
          {{ message }}
        </div>

        <Button
          type="submit"
          class="w-full h-9 auth-button-gradient"
          :disabled="loading || twoFactorCode.length !== 6"
        >
          <Loader2
            v-if="loading"
            class="mr-2 h-4 w-4 animate-spin"
          />
          {{ loading ? t('system.auth.twoFactor.verifying') : t('system.auth.twoFactor.verify') }}
        </Button>

        <Button
          type="button"
          variant="ghost"
          class="w-full h-8 hover:bg-muted/50 transition-colors text-xs"
          @click="cancelTwoFactor"
        >
          {{ t('common.actions.cancel') }}
        </Button>
      </form>
    </div>

    <!-- Standard Login Form -->
    <div v-else>
      <form
        class="space-y-2"
        @submit.prevent="handleLogin"
      >
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
            {{ Array.isArray(errors.email) ? errors.email[0] : errors.email }}
          </p>
        </div>
        <div class="space-y-1">
          <div class="flex items-center justify-between ml-1">
            <Label
              for="password"
              class="text-[10px] uppercase tracking-wider font-bold text-muted-foreground/80"
            >{{ t('common.labels.password') }}</Label>
            <router-link
              :to="{ name: 'forgot-password' }"
              class="text-[10px] font-bold text-primary hover:text-primary/80 transition-colors uppercase tracking-wider"
            >
              {{ t('system.auth.login.forgotPassword') }}
            </router-link>
          </div>
          <div class="relative">
            <Input
              id="password"
              ref="passwordInput"
              v-model="form.password"
              name="password"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="current-password"
              required
              class="auth-input h-9 text-sm pr-10"
              :class="errors.password ? 'border-destructive/50 ring-destructive/20 focus:border-destructive' : ''"
              :placeholder="t('system.auth.login.passwordPlaceholder')"
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
          <p
            v-if="errors.password"
            class="text-[10px] text-destructive font-medium ml-1"
          >
            {{ Array.isArray(errors.password) ? errors.password[0] : errors.password }}
          </p>
        </div>

        <div class="flex items-center justify-between px-1">
          <div class="flex items-center space-x-2">
            <Checkbox 
              id="remember-me" 
              name="remember-me"
              :checked="form.remember" 
              class="h-4 w-4 rounded border-border/60 transition-colors data-[state=checked]:bg-primary"
              @update:checked="(v) => form.remember = v"
            />
            <label
              for="remember-me"
              class="text-xs font-medium leading-none text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
            >
              {{ t('system.auth.login.rememberMe') }}
            </label>
          </div>
        </div>

        <!-- Captcha (only when enabled for login) -->
        <div
          v-show="captchaVisible"
          class="rounded-xl overflow-hidden border border-border/40 bg-muted/5"
        >
          <CaptchaWrapper 
            ref="captchaRef"
            action="login"
            @settings="onCaptchaSettings"
            @verified="onCaptchaVerified"
          />
        </div>

        <div
          v-if="timeoutMessage"
          class="rounded-xl bg-warning/10 p-2 text-[10px] text-warning-foreground border border-warning/20 animate-fade"
        >
          {{ timeoutMessage }}
        </div>

        <div
          v-if="rateLimited"
          class="rounded-xl bg-destructive/10 p-2 text-[10px] text-destructive border border-destructive/20 animate-fade"
        >
          <p class="font-bold mb-0.5">
            {{ t('system.auth.messages.tooManyAttempts') }}
          </p>
          <p>{{ t('system.auth.messages.retryDetails', { time: formatRetryTime(retryAfter) }) }}</p>
        </div>

        <div
          v-if="message && !errors.email && !errors.password && !rateLimited"
          class="rounded-xl p-2 text-[10px] border animate-fade"
          :class="messageType === 'error' ? 'bg-destructive/10 text-destructive border-destructive/20' : 'bg-success/10 text-success border-success/20'"
        >
          {{ message }}
        </div>

        <Button
          type="submit"
          class="w-full h-9 auth-button-gradient mt-1"
          :disabled="loading || rateLimited || !isValid || (captchaEnabled && !captchaVerified)"
        >
          <Loader2
            v-if="loading"
            class="mr-2 h-3 w-3 animate-spin"
          />
          <span
            v-if="loading"
            class="text-xs"
          >{{ t('system.auth.login.submit') }}...</span>
          <span
            v-else-if="rateLimited"
            class="text-xs"
          >Please wait...</span>
          <span
            v-else
            class="text-xs"
          >{{ t('system.auth.login.submit') }}</span>
        </Button>

        <!-- Social/Passkeys Logins Placeholder -->
        <div class="relative my-3">
          <div class="absolute inset-0 flex items-center">
            <span class="w-full border-t border-border/40" />
          </div>
          <div class="relative flex justify-center text-[10px] uppercase tracking-wider">
            <span class="bg-card px-2 text-muted-foreground font-bold">Or</span>
          </div>
        </div>

        <Button
          type="button"
          variant="outline"
          class="w-full h-9 mb-2 flex items-center justify-center gap-2 border-border/40 hover:bg-muted/50 transition-colors"
          @click="loginWithPasskey"
          :disabled="loading"
        >
          <Fingerprint class="h-4 w-4" />
          {{ t('system.auth.login.passkey', 'Sign in with Passkey') }}
        </Button>

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

        <div
          v-if="registrationEnabled"
          class="text-center text-[10px] text-muted-foreground mt-3"
        >
          {{ t('system.auth.login.noAccount') }} 
          <router-link
            :to="{ name: 'register' }"
            class="font-bold text-primary hover:text-primary/80 transition-all ml-1"
          >
            {{ t('system.auth.login.register') }}
          </router-link>
        </div>
      </form>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, reactive, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useRouter, useRoute, type RouteLocationRaw, type LocationQueryRaw } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useConsoleContextStore } from '@/engine/stores/consoleContext';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { loginSchema } from '@/modules/Core/System/schemas/auth';
import {
  Eye,
  EyeOff,
  Github,
  Loader2,
  Fingerprint,
} from 'lucide-vue-next';
import { Passkeys } from '@laravel/passkeys';
import { resetLockdown } from '@/engine/api/client';
import api from '@/engine/api/client';
import AuthLayout from '../../components/auth/AuthLayout.vue';
import { resolveConsoleDashboardLocation } from '@/config/console';
import { useAuthScreenTracking } from '@/shared/composables/useAuthScreenTracking';
import {
    Button,
    Input,
    Label,
    Checkbox
} from '@/shared/components/ui';
import CaptchaWrapper from '@/modules/Core/System/components/captcha/CaptchaWrapper.vue';
import type { CaptchaPayload, CaptchaSettingsState } from '@/modules/Core/System/components/captcha/CaptchaWrapper.vue';
import type { LoginCredentials } from '@/engine/types/auth';

useAuthScreenTracking();

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { t } = useI18n();
const systemStore = useSystemStore();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(loginSchema);

const captchaRef = ref<{ refresh: () => void } | null>(null);
const captchaVerified = ref(false);
const captchaToken = ref('');
const captchaAnswer = ref('');
const captchaEnabled = ref(false);
const captchaVisible = ref(false);

const onCaptchaSettings = (state: CaptchaSettingsState) => {
    captchaEnabled.value = state.enabled;
    captchaVisible.value = state.enabled;
    if (!state.enabled) {
        captchaVerified.value = false;
        captchaToken.value = '';
        captchaAnswer.value = '';
    }
};

// Input Refs for focusing
const emailInput = ref<{ $el: HTMLElement; focus: () => void } | null>(null);
const passwordInput = ref<{ $el: HTMLElement; focus: () => void } | null>(null);

// 2FA State
const requiresTwoFactor = ref(false);
const twoFactorUserId = ref<number | string | null>(null);
const twoFactorCode = ref('');

const showPassword = ref(false);
const form = reactive({
    email: '',
    password: '',
    remember: false,
});

// CaptchaPayload imported from CaptchaWrapper.vue

const onCaptchaVerified = (payload: CaptchaPayload) => {
    captchaToken.value = payload.token;
    captchaAnswer.value = payload.answer;
    captchaVerified.value = true;
};

const isValid = computed(() => {
    return !!form.email && !!form.password;
});

const message = ref('');
const messageType = ref('');
const loading = ref(false);
const rateLimited = ref(false);
const retryAfter = ref(0);
let retryTimer: ReturnType<typeof setInterval> | null = null;
let registrationInfoTimer: ReturnType<typeof setTimeout> | null = null;
let timeoutQueryTimer: ReturnType<typeof setTimeout> | null = null;
const registrationEnabled = ref(false); // Default: hidden until API confirms enabled
const registrationDisabledMessage = ref('');
const isCompletingLogin = ref(false);
const hasTriggeredPostLoginRedirect = ref(false);

// Check for session timeout
const timeoutMessage = computed(() => {
    if (route.query.timeout === '1') {
        return t('system.auth.messages.timeout');
    }
    return null;
});

const navigateToLoginQuery = (query: LocationQueryRaw) => {
    if (router.currentRoute.value.name !== 'login') return;
    const resolvedCurrent = router.resolve(router.currentRoute.value);
    const resolvedTarget = router.resolve({ name: 'login', query });
    if (resolvedCurrent.fullPath === resolvedTarget.fullPath) return;
    void router.replace({ name: 'login', query });
};

onMounted(async () => {
    // Stale Pinia/localStorage after session timeout — clear without probing /me (avoids 401 noise).
    if (window.__isSessionTerminated || route.query.redirect) {
        authStore.clearAuth();
    }

    resetLockdown();

    // Fetch latest public settings (logo, name, etc)
    await systemStore.fetchPublicSettings();

    // Check if system is in post-reset state
    try {
        const installStatus = await api.get('/install/status', { _skipManualRedirect: true } as any);
        const setupToken = installStatus.data?.setup_token;
        if (installStatus.data?.is_post_reset && typeof setupToken === 'string' && setupToken.length > 0) {
            window.location.replace(`/setup?token=${encodeURIComponent(setupToken)}`);
            return;
        }
    } catch (e) {
        // Ignore, probably API not available
    }

    // Check if registration is enabled from the store we just updated
    registrationEnabled.value = systemStore.siteSettings.enable_registration === true;

    // Check for registration_disabled redirect info
    if (route.query.info === 'registration_disabled') {
        registrationDisabledMessage.value = 'Registration is currently disabled.';
        registrationEnabled.value = false; // Immediately hide the register link
        // Clear the query param
        registrationInfoTimer = setTimeout(() => {
            navigateToLoginQuery({});
        }, 5000);
    }

    // Clear timeout query param after displaying message
    if (route.query.timeout) {
        timeoutQueryTimer = setTimeout(() => {
            if (router.currentRoute.value.name !== 'login') return;
            const sanitizedQuery = { ...router.currentRoute.value.query };
            delete sanitizedQuery.timeout;
            navigateToLoginQuery(sanitizedQuery as LocationQueryRaw);
        }, 5000);
    }
});

const formatRetryTime = (seconds: number) => {
    if (seconds <= 0) return t('system.auth.retry.moment');
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    
    if (minutes > 0) {
        if (secs > 0) {
            return t('system.auth.retry.minutesSeconds', { minutes, seconds: secs });
        }
        return t('system.auth.retry.minutes', minutes);
    }
    return t('system.auth.retry.seconds', secs);
};

const startRetryTimer = (initialSeconds: number) => {
    if (retryTimer) {
        clearInterval(retryTimer);
    }
    
    retryAfter.value = initialSeconds;
    
    retryTimer = setInterval(() => {
        retryAfter.value--;
        if (retryAfter.value <= 0) {
            if (retryTimer) clearInterval(retryTimer);
            retryTimer = null;
            rateLimited.value = false;
        }
    }, 1000);
};

const cancelTwoFactor = () => {
    requiresTwoFactor.value = false;
    twoFactorUserId.value = null;
    twoFactorCode.value = '';
    message.value = '';
    
    // Clear password for security
    form.password = '';
};

const verifyTwoFactor = async () => {
    if (!twoFactorCode.value || twoFactorCode.value.length !== 6) return;
    
    loading.value = true;
    message.value = '';
    
    try {
        // Optimized: We don't need to call /two-factor/verify-code separately.
        // The AuthController.login method handles two_factor_code if provided.
        // This avoids redundant requests and potential 429 Rate Limit errors.
        
        const payload: LoginCredentials & { two_factor_code?: string; captcha_token?: string; captcha_answer?: string } = {
            email: form.email.trim(),
            password: form.password,
            remember: form.remember,
            two_factor_code: twoFactorCode.value,
        };
        
        if (captchaEnabled.value) {
            payload.captcha_token = captchaToken.value;
            payload.captcha_answer = captchaAnswer.value;
        }
        
        const result = await authStore.login(payload);
        
        if (result.success) {
            completeLogin();
        } else {
             message.value = result.message || t('system.auth.twoFactor.invalidCode');
             messageType.value = 'error';
             
             // Reset code on failure so user can try again easily
             twoFactorCode.value = '';
        }

    } catch (e) {
        logger.error('2FA Error:', e);
        message.value = t('system.auth.messages.error');
        messageType.value = 'error';
    } finally {
        loading.value = false;
    }
};

const completeLogin = async () => {
    if (isCompletingLogin.value || hasTriggeredPostLoginRedirect.value) return;
    isCompletingLogin.value = true;
    hasTriggeredPostLoginRedirect.value = true;
    
    const consoleStore = useConsoleContextStore();
    const roleRank = authStore.getRoleRank();
    logger.info('[Login] Completing login', { roleRank, userId: authStore.user?.id });

    if (roleRank >= 100) {
        // Force system context for super admins (silent — avoid reload loop)
        await consoleStore.setSystemContext(true);
        logger.info('[Login] Set system context for super admin');
    } else {
        sessionStorage.removeItem('active_organization_id');
        sessionStorage.removeItem('active_organization_context');
        sessionStorage.removeItem('active_organization_name');
    }

    message.value = t('system.auth.messages.success');
    messageType.value = 'success';

    // Check if there's a redirect query parameter
    const redirectPath = route.query.redirect;
    const slug = systemStore.consoleDashboardSlug || 'dash';
    const defaultDashboard = resolveConsoleDashboardLocation(slug);
    const target: RouteLocationRaw = (redirectPath && typeof redirectPath === 'string' && !redirectPath.includes('/login') && !redirectPath.includes('/419'))
        ? redirectPath
        : defaultDashboard;

    await nextTick();

    try {
        const current = router.resolve(router.currentRoute.value);
        const resolvedTarget = router.resolve(target);
        const shouldFallbackToDashboard =
            resolvedTarget.name === 'login' ||
            resolvedTarget.path === router.resolve({ name: 'login' }).path ||
            resolvedTarget.fullPath === current.fullPath;

        const finalTarget = shouldFallbackToDashboard ? defaultDashboard : target;

        if (typeof finalTarget === 'string') {
            window.location.assign(finalTarget);
        } else {
            window.location.assign(router.resolve(finalTarget).fullPath);
        }
    } catch (error) {
        logger.error('Post-login redirect failed:', error);
        window.location.assign(typeof defaultDashboard === 'string' ? defaultDashboard : '/dash');
    } finally {
        isCompletingLogin.value = false;
    }
}

const handleLogin = async () => {
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
    messageType.value = '';
    rateLimited.value = false;
    
    if (retryTimer) {
        clearInterval(retryTimer);
        retryTimer = null;
    }

    try {
        const payload: LoginCredentials & { captcha_token?: string; captcha_answer?: string } = {
            email: form.email.trim(),
            password: form.password,
            remember: form.remember,
        };
        
        if (captchaEnabled.value) {
            payload.captcha_token = captchaToken.value;
            payload.captcha_answer = captchaAnswer.value;
        }
        
        const result = await authStore.login(payload);

        if (result.success) {
            if (result.requiresTwoFactor) {
                requiresTwoFactor.value = true;
                twoFactorUserId.value = result.userId || null;
                message.value = ''; // Clear any success message
                // Do not clear password here, we need it for the second attempt!
            } else {
                completeLogin();
            }
        } else {
            // Handle rate limiting
            if (result.rateLimited && result.retryAfter) {
                rateLimited.value = true;
                startRetryTimer(result.retryAfter);
                message.value = '';
            } else {
                rateLimited.value = false;
                
                // Refresh captcha on any failure to prevent "stale" tokens
                if (captchaEnabled.value) {
                    captchaRef.value?.refresh();
                    captchaVerified.value = false;
                    captchaToken.value = '';
                    captchaAnswer.value = '';
                }

                // Handle validation errors from server
                if (result.errors && Object.keys(result.errors).length > 0) {
                    const errorKeys = Object.keys(result.errors);
                    const firstKey = errorKeys[0];
                    const firstErrorMsg = firstKey ? (result.errors[firstKey]?.[0] || '') : '';
                    
                    // Detect if this is a general "Credential Mismatch" error 
                    // (Laravel often keys this to 'email' but it's an auth failure)
                    const isAuthFailure = firstErrorMsg.toLowerCase().includes('credential') || 
                                        firstErrorMsg.toLowerCase().includes('password') ||
                                        errorKeys.length === 1 && errorKeys[0] === 'email' && form.password.length > 0;

                    if (isAuthFailure) {
                        message.value = result.message || firstErrorMsg;
                        messageType.value = 'error';
                        // Clear specific field errors so it doesn't show red under Email
                        clearErrors();
                        // Focus Password field as requested
                        if (passwordInput.value?.$el) passwordInput.value.$el.focus();
                        else passwordInput.value?.focus();
                    } else {
                        setErrors(result.errors);
                        // Auto-focus the first erroneous field for standard validation errors
                        if (result.errors.email) {
                            if (emailInput.value?.$el) emailInput.value.$el.focus();
                            else emailInput.value?.focus();
                        } else if (result.errors.password) {
                            if (passwordInput.value?.$el) passwordInput.value.$el.focus();
                            else passwordInput.value?.focus();
                        }
                        message.value = '';
                    }
                } else {
                    // General error message (no field-specific errors)
                    message.value = result.message || t('system.auth.messages.failed');
                    messageType.value = 'error';
                    
                    // Focus password field on general failure (usually wrong creds)
                    if (passwordInput.value?.$el) passwordInput.value.$el.focus();
                    else passwordInput.value?.focus();
                }
            }
        }
    } catch (error) {
        logger.error('Login error:', error);
        message.value = t('system.auth.messages.error');
        messageType.value = 'error';
        rateLimited.value = false;
    } finally {
        loading.value = false;
    }
};

const loginWithPasskey = async () => {
    loading.value = true;
    message.value = '';
    messageType.value = '';
    
    try {
        await Passkeys.verify({
            routes: {
                submit: '/passkeys/login',
                options: '/passkeys/login/options',
            }
        });
        
        completeLogin();
    } catch (error: any) {
        logger.error('Passkey login error:', error);
        message.value = error?.response?.data?.message || t('system.auth.messages.passkeyError', 'Passkey login failed');
        messageType.value = 'error';
    } finally {
        loading.value = false;
    }
};

// Cleanup on unmount
onUnmounted(() => {
    if (retryTimer) {
        clearInterval(retryTimer);
    }
    if (registrationInfoTimer) {
        clearTimeout(registrationInfoTimer);
    }
    if (timeoutQueryTimer) {
        clearTimeout(timeoutQueryTimer);
    }
});
</script>
