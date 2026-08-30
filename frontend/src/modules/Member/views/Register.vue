<template>
  <div class="sarangenge-theme flex-1 flex flex-col py-16">
    <div class="max-w-md mx-auto w-full px-4 space-y-8">
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
            minlength="8"
            autocomplete="new-password"
            class="w-full h-10 rounded-xl border border-border bg-background px-3"
          >
        </label>
        <label class="block space-y-1.5 text-sm">
          <span class="font-medium">{{ t('member.fields.confirmPassword', 'Confirm password') }}</span>
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
          :disabled="pending"
        >
          {{ pending ? t('member.register.pending', 'Creating…') : t('member.register.submit', 'Register') }}
        </Button>
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
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { isAxiosError } from 'axios';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useMemberStore } from '@/modules/Member/stores/member';

const { t } = useI18n();
const router = useRouter();
const memberStore = useMemberStore();

const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const pending = ref(false);
const error = ref('');

const submit = async (): Promise<void> => {
    pending.value = true;
    error.value = '';
    try {
        await memberStore.register({
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });
        await router.replace('/member/account');
    } catch (err: unknown) {
        error.value = isAxiosError(err)
            ? String(err.response?.data?.message || t('member.register.failed', 'Registration failed'))
            : t('member.register.failed', 'Registration failed');
    } finally {
        pending.value = false;
    }
};
</script>
