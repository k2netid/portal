<template>
  <div class="sarangenge-theme flex-1 flex flex-col py-16">
    <div class="max-w-md mx-auto w-full px-4 space-y-8">
      <div
        v-if="checking"
        class="text-center text-sm text-muted-foreground"
      >
        {{ t('member.register.checking', 'Checking registration…') }}
      </div>

      <template v-else>
        <div class="space-y-2 text-center">
          <h1 class="text-3xl font-extrabold font-heading">
            {{ t('member.register.title', 'Create a reader account') }}
          </h1>
          <p class="text-sm text-muted-foreground">
            {{ t('member.register.subtitle', 'Comments and bookmarks use this identity, not console IAM. We send a confirmation link to your email.') }}
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
          <label class="block space-y-1.5 text-sm">
            <span class="font-medium">{{ t('member.fields.name', 'Name') }}</span>
            <input
              v-model="name"
              type="text"
              required
              autocomplete="name"
              class="w-full h-10 rounded-xl border border-border bg-background px-3"
            >
          </label>
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
              :minlength="passwordMinLength"
              autocomplete="new-password"
              class="w-full h-10 rounded-xl border border-border bg-background px-3"
            >
            <span class="block text-xs text-muted-foreground">{{ passwordPolicyHint }}</span>
          </label>
          <label class="block space-y-1.5 text-sm">
            <span class="font-medium">{{ t('member.fields.confirmPassword', 'Confirm password') }}</span>
            <input
              v-model="passwordConfirmation"
              type="password"
              required
              :minlength="passwordMinLength"
              autocomplete="new-password"
              class="w-full h-10 rounded-xl border border-border bg-background px-3"
            >
          </label>

          <div
            v-show="captchaVisible"
            class="rounded-xl overflow-hidden border border-border/40 bg-muted/5"
          >
            <CaptchaWrapper
              ref="captchaRef"
              action="register"
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
            {{ pending ? t('member.register.pending', 'Creating…') : t('member.register.submit', 'Register') }}
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
              :disabled="pending"
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
        </form>

        <p class="text-center text-sm text-muted-foreground">
          {{ t('member.register.hasAccount', 'Already registered?') }}
          <router-link
            to="/member/login"
            class="text-primary font-semibold"
          >
            {{ t('member.login.link', 'Sign in') }}
          </router-link>
        </p>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineAsyncComponent, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { isAxiosError } from 'axios';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useMemberStore } from '@/modules/Member/stores/member';
import { usePasswordPolicy } from '@/modules/Member/composables/usePasswordPolicy';
import type { CaptchaPayload, CaptchaSettingsState } from '@/modules/Core/System/components/captcha/CaptchaWrapper.vue';

const CaptchaWrapper = defineAsyncComponent(() => import('@/modules/Core/System/components/captcha/CaptchaWrapper.vue'));

const { t } = useI18n();
const router = useRouter();
const systemStore = useSystemStore();
const memberStore = useMemberStore();
const { passwordMinLength, passwordPolicyHint } = usePasswordPolicy();

const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const pending = ref(false);
const checking = ref(true);
const error = ref('');
const googleLoginEnabled = ref(false);

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

const isMemberRegistrationEnabled = (): boolean => {
    const value = systemStore.siteSettings.enable_member_registration;
    return value !== false;
};

const startGoogleLogin = (): void => {
    const params = new URLSearchParams({ redirect: '/member' });
    window.location.href = `/api/v1/public/member/auth/google/redirect?${params.toString()}`;
};

onMounted(async () => {
    try {
        await systemStore.fetchPublicSettings({ force: true });
        googleLoginEnabled.value = systemStore.siteSettings.enable_member_google_login === true;
        if (!isMemberRegistrationEnabled()) {
            await router.replace({
                path: '/member/login',
                query: { info: 'registration_disabled' },
            });
            return;
        }
    } catch {
        // Fail-open if public settings cannot be loaded.
    } finally {
        checking.value = false;
    }
});

const submit = async (): Promise<void> => {
    if (captchaEnabled.value && !captchaVerified.value) {
        error.value = t('member.register.captchaRequired', 'Please complete the captcha.');
        return;
    }

    pending.value = true;
    error.value = '';
    try {
        await memberStore.register({
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
            ...(captchaEnabled.value
                ? { captcha_token: captchaToken.value, captcha_answer: captchaAnswer.value }
                : {}),
        });
        await router.replace('/member');
    } catch (err: unknown) {
        if (isAxiosError(err) && err.response?.status === 403) {
            await router.replace({
                path: '/member/login',
                query: { info: 'registration_disabled' },
            });
            return;
        }
        if (captchaEnabled.value) {
            captchaRef.value?.refresh();
            captchaVerified.value = false;
            captchaToken.value = '';
            captchaAnswer.value = '';
        }
        error.value = isAxiosError(err)
            ? String(err.response?.data?.message || t('member.register.failed', 'Registration failed'))
            : t('member.register.failed', 'Registration failed');
    } finally {
        pending.value = false;
    }
};
</script>
