<template>
  <div class="sarangenge-theme flex-1 flex flex-col py-16">
    <div class="max-w-md mx-auto w-full px-4 space-y-8">
      <div class="space-y-2 text-center">
        <h1 class="text-3xl font-extrabold font-heading">
          {{ t('member.reset.title', 'Choose a new password') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ t('member.reset.subtitle', 'Enter a new password for your reader account.') }}
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
          v-if="done"
          class="text-sm text-emerald-600"
        >
          {{ t('member.reset.success', 'Password updated. You can sign in now.') }}
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
            minlength="8"
            autocomplete="new-password"
            class="w-full h-10 rounded-xl border border-border bg-background px-3"
          >
        </label>
        <label class="block space-y-1.5 text-sm">
          <span class="font-medium">{{ t('member.reset.confirm', 'Confirm password') }}</span>
          <input
            v-model="passwordConfirmation"
            type="password"
            required
            minlength="8"
            autocomplete="new-password"
            class="w-full h-10 rounded-xl border border-border bg-background px-3"
          >
        </label>
        <Button
          type="submit"
          variant="primary"
          class="w-full"
          :disabled="pending || !token"
        >
          {{ pending ? t('member.reset.pending', 'Saving…') : t('member.reset.submit', 'Reset password') }}
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
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { isAxiosError } from 'axios';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useMemberStore } from '@/modules/Member/stores/member';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const memberStore = useMemberStore();

const email = ref(typeof route.query.email === 'string' ? route.query.email : '');
const token = ref(typeof route.query.token === 'string' ? route.query.token : '');
const password = ref('');
const passwordConfirmation = ref('');
const pending = ref(false);
const error = ref('');
const done = ref(false);

const submit = async (): Promise<void> => {
    pending.value = true;
    error.value = '';
    done.value = false;
    try {
        await memberStore.resetPassword({
            email: email.value,
            token: token.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });
        done.value = true;
        await router.replace('/member/login');
    } catch (err: unknown) {
        error.value = isAxiosError(err)
            ? String(err.response?.data?.message || t('member.reset.failed', 'Could not reset password.'))
            : t('member.reset.failed', 'Could not reset password.');
    } finally {
        pending.value = false;
    }
};
</script>
