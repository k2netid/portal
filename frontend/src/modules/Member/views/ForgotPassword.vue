<template>
  <div class="sarangenge-theme flex-1 flex flex-col py-16">
    <div class="max-w-md mx-auto w-full px-4 space-y-8">
      <div class="space-y-2 text-center">
        <h1 class="text-3xl font-extrabold font-heading">
          {{ t('member.forgot.title', 'Forgot password') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ t('member.forgot.subtitle', 'We will email a reset link for your reader account.') }}
        </p>
      </div>

      <form
        class="rounded-3xl border border-border/60 bg-card/70 p-6 space-y-4 shadow-sm"
        @submit.prevent="submit"
      >
        <p
          v-if="error"
          class="text-sm text-destructive"
        >
          {{ error }}
        </p>
        <p
          v-if="sent"
          class="text-sm text-emerald-600"
        >
          {{ t('member.forgot.sent', 'If that email exists, a reset link is on the way.') }}
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

        <div
          v-show="captchaVisible"
          class="rounded-xl overflow-hidden border border-border/40 bg-muted/5"
        >
          <CaptchaWrapper
            ref="captchaRef"
            action="forgot-password"
            @settings="onCaptchaSettings"
            @verified="onCaptchaVerified"
          />
        </div>

        <Button
          type="submit"
          variant="primary"
          class="w-full"
          :disabled="pending || (captchaEnabled && !captchaVerified)"
        >
          {{ pending ? t('member.forgot.pending', 'Sending…') : t('member.forgot.submit', 'Send reset link') }}
        </Button>
      </form>

      <p class="text-center text-sm text-muted-foreground">
        <router-link
          to="/member/login"
          class="text-primary font-semibold"
        >
          {{ t('member.login.link', 'Sign in') }}
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineAsyncComponent, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { isAxiosError } from 'axios';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useMemberStore } from '@/modules/Member/stores/member';
import type { CaptchaPayload, CaptchaSettingsState } from '@/modules/Core/System/components/captcha/CaptchaWrapper.vue';

const CaptchaWrapper = defineAsyncComponent(() => import('@/modules/Core/System/components/captcha/CaptchaWrapper.vue'));

const { t } = useI18n();
const memberStore = useMemberStore();

const email = ref('');
const pending = ref(false);
const error = ref('');
const sent = ref(false);

const captchaRef = ref<{ refresh: () => void } | null>(null);
const captchaVerified = ref(false);
const captchaToken = ref('');
const captchaAnswer = ref('');
const captchaEnabled = ref(false);
const captchaVisible = ref(false);

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

const submit = async (): Promise<void> => {
    if (captchaEnabled.value && !captchaVerified.value) {
        error.value = t('member.forgot.captchaRequired', 'Please complete the captcha.');
        return;
    }

    pending.value = true;
    error.value = '';
    sent.value = false;
    try {
        await memberStore.forgotPassword(email.value, captchaEnabled.value
            ? { captcha_token: captchaToken.value, captcha_answer: captchaAnswer.value }
            : undefined);
        sent.value = true;
    } catch (err: unknown) {
        if (captchaEnabled.value) {
            captchaRef.value?.refresh();
            captchaVerified.value = false;
            captchaToken.value = '';
            captchaAnswer.value = '';
        }
        error.value = isAxiosError(err)
            ? String(err.response?.data?.message || t('member.forgot.failed', 'Could not send reset link.'))
            : t('member.forgot.failed', 'Could not send reset link.');
    } finally {
        pending.value = false;
    }
};
</script>
