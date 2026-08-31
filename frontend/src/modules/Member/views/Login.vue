<template>
  <div class="sarangenge-theme flex-1 flex flex-col py-16">
    <div class="max-w-md mx-auto w-full px-4 space-y-8">
      <div class="space-y-2 text-center">
        <h1 class="text-3xl font-extrabold font-heading">
          {{ t('member.login.title', 'Sign in') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ t('member.login.subtitle', 'Reader account — not the operator console.') }}
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
        <Button
          type="submit"
          variant="primary"
          class="w-full"
          :disabled="pending"
        >
          {{ pending ? t('member.login.pending', 'Signing in…') : t('member.login.submit', 'Sign in') }}
        </Button>
      </form>

      <p class="text-center text-sm text-muted-foreground">
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

const email = ref('');
const password = ref('');
const pending = ref(false);
const error = ref('');

const submit = async (): Promise<void> => {
    pending.value = true;
    error.value = '';
    try {
        await memberStore.login(email.value, password.value);
        const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/member';
        await router.replace(redirect);
    } catch (err: unknown) {
        error.value = isAxiosError(err)
            ? String(err.response?.data?.message || t('member.login.failed', 'Invalid credentials'))
            : t('member.login.failed', 'Invalid credentials');
    } finally {
        pending.value = false;
    }
};
</script>
