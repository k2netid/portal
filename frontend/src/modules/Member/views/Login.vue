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
    error.value = '';
    password.value = '';
    refreshCaptcha();
};

onMounted(async () => {
    try {
        await systemStore.fetchPublicSettings();
        registrationEnabled.value = systemStore.siteSettings.enable_member_registration !== false;
    } catch {
        registrationEnabled.value = true;
    }

    if (route.query.info === 'registration_disabled') {
        registrationEnabled.value = false;
        registrationDisabledMessage.value = t(
            'member.register.disabled',
            'Reader registration is currently disabled.',
        );
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
