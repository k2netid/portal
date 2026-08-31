<template>
  <div class="space-y-6">
    <div class="space-y-1">
      <h2 class="text-xl font-bold">
        {{ t('member.portal.security.title', 'Security') }}
      </h2>
      <p class="text-sm text-muted-foreground">
        {{ t('member.portal.security.subtitle', 'Update your reader account password.') }}
      </p>
    </div>

    <form
      class="rounded-3xl border border-border/60 bg-card/70 p-6 space-y-4 max-w-lg"
      @submit.prevent="submit"
    >
      <p
        v-if="error"
        class="text-sm text-destructive"
      >
        {{ error }}
      </p>
      <p
        v-if="success"
        class="text-sm text-emerald-600"
      >
        {{ t('member.portal.security.success', 'Password updated.') }}
      </p>

      <label class="block space-y-1.5 text-sm">
        <span class="font-medium">{{ t('member.portal.security.current', 'Current password') }}</span>
        <input
          v-model="currentPassword"
          type="password"
          required
          autocomplete="current-password"
          class="w-full h-10 rounded-xl border border-border bg-background px-3"
        />
      </label>

      <label class="block space-y-1.5 text-sm">
        <span class="font-medium">{{ t('member.portal.security.new', 'New password') }}</span>
        <input
          v-model="password"
          type="password"
          required
          minlength="8"
          autocomplete="new-password"
          class="w-full h-10 rounded-xl border border-border bg-background px-3"
        />
      </label>

      <label class="block space-y-1.5 text-sm">
        <span class="font-medium">{{ t('member.portal.security.confirm', 'Confirm new password') }}</span>
        <input
          v-model="passwordConfirmation"
          type="password"
          required
          minlength="8"
          autocomplete="new-password"
          class="w-full h-10 rounded-xl border border-border bg-background px-3"
        />
      </label>

      <Button
        type="submit"
        :disabled="pending"
      >
        {{ pending ? t('member.portal.security.pending', 'Saving…') : t('member.portal.security.submit', 'Update password') }}
      </Button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { isAxiosError } from 'axios';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useMemberStore } from '@/modules/Member/stores/member';

const { t } = useI18n();
const memberStore = useMemberStore();

const currentPassword = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const pending = ref(false);
const error = ref('');
const success = ref(false);

const submit = async (): Promise<void> => {
    pending.value = true;
    error.value = '';
    success.value = false;
    try {
        await memberStore.updatePassword({
            current_password: currentPassword.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });
        currentPassword.value = '';
        password.value = '';
        passwordConfirmation.value = '';
        success.value = true;
    } catch (err: unknown) {
        if (isAxiosError(err) && err.response?.status === 422) {
            const errors = err.response.data?.errors as Record<string, string[]> | undefined;
            error.value = errors?.current_password?.[0]
                ?? errors?.password?.[0]
                ?? t('member.portal.security.failed', 'Could not update password.');
        } else {
            error.value = t('member.portal.security.failed', 'Could not update password.');
        }
    } finally {
        pending.value = false;
    }
};
</script>
