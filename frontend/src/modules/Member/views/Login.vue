<template>
  <div class="sarangenge-theme flex-1 flex flex-col py-16">
    <div class="max-w-md mx-auto w-full px-4 space-y-8">
      <div class="space-y-2 text-center">
        <h1 class="text-3xl font-extrabold font-heading">
          {{ requiresTwoFactor
            ? t('member.login.twoFactor.title', 'Two-factor authentication')
            : t('member.login.title', 'Sign in') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ requiresTwoFactor
            ? t('member.login.twoFactor.subtitle', 'Enter the 6-digit code from your authenticator app.')
            : t('member.login.subtitle', 'Reader account — not the operator console.') }}
        </p>
      </div>

      <p
        v-if="registrationDisabledMessage"
        class="text-sm rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-center"
      >
        {{ registrationDisabledMessage }}
      </p>

      <!-- Step 2: 2FA code -->
      <form
        v-if="requiresTwoFactor"
        class="rounded-3xl border border-border/60 bg-card/70 p-6 space-y-4 shadow-sm"
        @submit.prevent="submitTwoFactor"
      >
        <p
          v-if="error"
          class="text-sm text-destructive"
        >
          {{ error }}
        </p>
        <label class="block space-y-1.5 text-sm">
          <span class="font-medium">{{ t('member.login.twoFactor.codeLabel', 'Authentication code') }}</span>
          <input
            v-model="twoFactorCode"
            type="text"
            inputmode="numeric"
            autocomplete="one-time-code"
            required
            maxlength="6"
            class="w-full h-12 rounded-xl border border-border bg-background px-3 text-center text-xl tracking-[0.5em] font-mono"
            :placeholder="t('member.login.twoFactor.placeholder', '000000')"
            @input="twoFactorCode = twoFactorCode.replace(/\D/g, '')"
          >
        </label>
        <Button
          type="submit"
          variant="primary"
          class="w-full"
          :disabled="pending || twoFactorCode.length !== 6"
        >
          {{ pending
            ? t('member.login.twoFactor.verifying', 'Verifying…')
            : t('member.login.twoFactor.verify', 'Verify') }}
        </Button>
        <Button
          type="button"
          variant="outline"
          class="w-full"
          :disabled="pending"
          @click="cancelTwoFactor"
        >
          {{ t('member.login.twoFactor.cancel', 'Back') }}
        </Button>
      </form>

      <!-- Step 1: email / password -->
      <form
        v-else
        class="rounded-3xl border border-border/60 bg-card/70 p-6 space-y-4 shadow-sm"
        @submit.prevent="submit"
      >
        <p
          v-if="rateLimited"
          class="text-sm rounded-xl border border-destructive/30 bg-destructive/10 px-3 py-2 text-destructive"
        >
          {{ throttleMessage }}
        </p>
        <p
          v-else-if="error"
          class="text-sm text-destructive"
        >
          {{ error }}
        </p>
        <label class="block space-y-1.5 text-sm">
          <span class="font-medium">{{ t('member.fields.email', 'Email') }}</span>
          <input
            v-model="email"
            type="email"
            required
            autocomplete="email"
            class="w-full h-10 rounded-xl border border-border bg-background px-3"
          >
        </label>
        <label class="block space-y-1.5 text-sm">
          <span class="font-medium">{{ t('member.fields.password', 'Password') }}</span>
          <input
            v-model="password"
            type="password"
            required
            autocomplete="current-password"
            class="w-full h-10 rounded-xl border border-border bg-background px-3"
          >
        </label>

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

        <Button
          type="submit"
          variant="primary"
          class="w-full"
          :disabled="pending || rateLimited || (captchaEnabled && !captchaVerified)"
        >
          {{ pending ? t('member.login.pending', 'Signing in…') : t('member.login.submit', 'Sign in') }}
        </Button>

        <template v-if="googleLoginEnabled">
          <div class="relative my-1">
            <div class="absolute inset-0 flex items-center">
              <span class="w-full border-t border-border/40" />
            </div>
            <div class="relative flex justify-center text-[10px] uppercase tracking-wider">
              <span class="bg-card px-2 text-muted-foreground font-bold">
                {{ t('member.login.or', 'Or') }}
              </span>
            </div>
          </div>
          <Button
            type="button"
            variant="outline"
            class="w-full gap-2"
            :disabled="pending || rateLimited"
            @click="startGoogleLogin"
          >
            <svg
              viewBox="0 0 24 24"
              class="h-4 w-4 fill-current"
              xmlns="http://www.w3.org/2000/svg"
              aria-hidden="true"
            >
              <path d="M12.48 10.92v3.28h7.84c-.24 1.84-.9 3.28-2.12 4.41-1.37 1.34-3.26 2.4-6.4 2.4-5.34 0-9.6-4.32-9.6-9.6s4.26-9.6 9.6-9.6c3.15 0 5.48 1.25 7.18 2.87l2.29-2.29C18.9 1.5 15.86 0 12.48 0 5.58 0 0 5.58 0 12.48s5.58 12.48 12.48 12.48c3.7 0 6.64-1.21 8.87-3.56 2.3-2.3 3-5.5 3-8.1 0-.6-.05-1.12-.15-1.57z" />
            </svg>
            {{ t('member.login.google', 'Sign in with Google') }}
          </Button>
        </template>

        <p class="text-center text-sm">
          <router-link
            to="/member/forgot-password"
            class="text-primary font-semibold"
          >
            {{ t('member.forgot.link', 'Forgot password?') }}
          </router-link>
        </p>
      </form>

      <p
        v-if="registrationEnabled && !requiresTwoFactor"
        class="text-center text-sm text-muted-foreground"
      >
        {{ t('member.login.noAccount', 'No account?') }}
        <router-link
          to="/member/register"
          class="text-primary font-semibold"
        >
          {{ t('member.register.link', 'Create one') }}
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { isAxiosError } from 'axios';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useMemberStore } from '@/modules/Member/stores/member';
import type { CaptchaPayload, CaptchaSettingsState } from '@/modules/Core/System/components/captcha/CaptchaWrapper.vue';

const CaptchaWrapper = defineAsyncComponent(() => import('@/modules/Core/System/components/captcha/CaptchaWrapper.vue'));

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const systemStore = useSystemStore();
const memberStore = useMemberStore();

const email = ref('');
const password = ref('');
const pending = ref(false);
const error = ref('');
const registrationEnabled = ref(true);
const registrationDisabledMessage = ref('');

const requiresTwoFactor = ref(false);
const twoFactorCode = ref('');
const googlePending = ref('');
const googleLoginEnabled = ref(false);

const captchaRef = ref<{ refresh: () => void } | null>(null);
const captchaVerified = ref(false);
const captchaToken = ref('');
const captchaAnswer = ref('');
const captchaEnabled = ref(false);
const captchaVisible = ref(false);

const rateLimited = ref(false);
const retryAfter = ref(0);
let retryTimer: ReturnType<typeof setInterval> | null = null;

const throttleMessage = computed(() => {
    if (retryAfter.value > 0) {
        return t('member.login.throttledRetry', {
            seconds: retryAfter.value,
            default: `Too many attempts. Try again in ${retryAfter.value}s.`,
        });
    }
    return t('member.login.throttled', 'Too many attempts. Please try again later.');
});

const onCaptchaSettings = (state: CaptchaSettingsState): void => {
    captchaEnabled.value = state.enabled;
    captchaVisible.value = state.enabled;
    if (!state.enabled) {
        captchaVerified.value = false;
        captchaToken.value = '';
        captchaAnswer.value = '';
    }
};

const onCaptchaVerified = (payload: CaptchaPayload): void => {
    captchaToken.value = payload.token;
    captchaAnswer.value = payload.answer;
    captchaVerified.value = true;
};

const refreshCaptcha = (): void => {
    if (!captchaEnabled.value) {
        return;
    }
    captchaRef.value?.refresh();
    captchaVerified.value = false;
    captchaToken.value = '';
    captchaAnswer.value = '';
};

const startRetryTimer = (seconds: number): void => {
    if (retryTimer) {
        clearInterval(retryTimer);
    }
    rateLimited.value = true;
    retryAfter.value = seconds;
    retryTimer = setInterval(() => {
        retryAfter.value -= 1;
        if (retryAfter.value <= 0) {
            if (retryTimer) {
                clearInterval(retryTimer);
            }
            retryTimer = null;
            rateLimited.value = false;
        }
    }, 1000);
};

const completeLogin = async (): Promise<void> => {
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/member';
    await router.replace(redirect || '/member');
};

const cancelTwoFactor = (): void => {
    requiresTwoFactor.value = false;
    twoFactorCode.value = '';
    googlePending.value = '';
    error.value = '';
    password.value = '';
    refreshCaptcha();
};

const startGoogleLogin = (): void => {
    const redirect = typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
        ? route.query.redirect
        : '/member';
    const params = new URLSearchParams({ redirect });
    window.location.href = `/api/v1/public/member/auth/google/redirect?${params.toString()}`;
};

const oauthErrorMessage = (code: string): string => {
    switch (code) {
        case 'registration_disabled':
            return t('member.oauth.registrationDisabled', 'Reader registration is disabled. Ask an operator to create your account, or sign in with email if you already have one.');
        case 'inactive':
            return t('member.oauth.inactive', 'This member account is not active.');
        case 'unavailable':
            return t('member.oauth.unavailable', 'Google sign-in is not available right now.');
        case 'invalid_state':
            return t('member.oauth.invalidState', 'Google sign-in expired. Please try again.');
        default:
            return t('member.oauth.failed', 'Google sign-in failed. Please try again.');
    }
};

onMounted(async () => {
    try {
        await systemStore.fetchPublicSettings();
        registrationEnabled.value = systemStore.siteSettings.enable_member_registration !== false;
        googleLoginEnabled.value = systemStore.siteSettings.enable_member_google_login === true;
    } catch {
        registrationEnabled.value = true;
        googleLoginEnabled.value = false;
    }

    if (route.query.info === 'registration_disabled') {
        registrationEnabled.value = false;
        registrationDisabledMessage.value = t(
            'member.register.disabled',
            'Reader registration is currently disabled.',
        );
    }

    if (typeof route.query.oauth_error === 'string' && route.query.oauth_error) {
        error.value = oauthErrorMessage(route.query.oauth_error);
    }

    if (route.query.google_2fa === '1' && typeof route.query.pending === 'string' && route.query.pending) {
        googlePending.value = route.query.pending;
        requiresTwoFactor.value = true;
    }
});

onUnmounted(() => {
    if (retryTimer) {
        clearInterval(retryTimer);
    }
});

const handleLoginError = (err: unknown): void => {
    if (isAxiosError(err) && err.response?.status === 429) {
        const retry = Number(err.response.data?.retry_after ?? 60);
        startRetryTimer(Number.isFinite(retry) && retry > 0 ? retry : 60);
        error.value = '';
        refreshCaptcha();
        return;
    }

    error.value = isAxiosError(err)
        ? String(err.response?.data?.message || t('member.login.failed', 'Invalid credentials'))
        : t('member.login.failed', 'Invalid credentials');
    refreshCaptcha();
};

const submit = async (): Promise<void> => {
    if (captchaEnabled.value && !captchaVerified.value) {
        error.value = t('member.login.captchaRequired', 'Please complete the captcha.');
        return;
    }

    pending.value = true;
    error.value = '';
    rateLimited.value = false;

    try {
        const result = await memberStore.login(email.value, password.value, {
            ...(captchaEnabled.value
                ? { captcha_token: captchaToken.value, captcha_answer: captchaAnswer.value }
                : {}),
        });

        if (result.requires_two_factor) {
            requiresTwoFactor.value = true;
            twoFactorCode.value = '';
            error.value = '';
            return;
        }

        await completeLogin();
    } catch (err: unknown) {
        handleLoginError(err);
    } finally {
        pending.value = false;
    }
};

const submitTwoFactor = async (): Promise<void> => {
    if (twoFactorCode.value.length !== 6) {
        return;
    }

    pending.value = true;
    error.value = '';

    try {
        if (googlePending.value) {
            await memberStore.completeGoogleTwoFactor(googlePending.value, twoFactorCode.value);
            await completeLogin();
            return;
        }

        // Step 2: same email/password + code; no captcha
        const result = await memberStore.login(email.value, password.value, {
            two_factor_code: twoFactorCode.value,
        });

        if (result.requires_two_factor) {
            error.value = t('member.login.twoFactor.invalid', 'Invalid authentication code.');
            twoFactorCode.value = '';
            return;
        }

        await completeLogin();
    } catch (err: unknown) {
        if (isAxiosError(err) && err.response?.status === 429) {
            const retry = Number(err.response.data?.retry_after ?? 60);
            startRetryTimer(Number.isFinite(retry) && retry > 0 ? retry : 60);
            error.value = throttleMessage.value;
            return;
        }

        const fieldErrors = isAxiosError(err)
            ? (err.response?.data?.errors as Record<string, string[]> | undefined)
            : undefined;
        error.value = fieldErrors?.two_factor_code?.[0]
            ?? (isAxiosError(err)
                ? String(err.response?.data?.message || t('member.login.twoFactor.invalid', 'Invalid authentication code.'))
                : t('member.login.twoFactor.invalid', 'Invalid authentication code.'));
        twoFactorCode.value = '';
    } finally {
        pending.value = false;
    }
};
</script>
