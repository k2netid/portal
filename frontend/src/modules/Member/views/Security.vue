<template>
  <div class="space-y-8">
    <div class="space-y-1">
      <h2 class="text-xl font-bold">
        {{ t('member.portal.security.title', 'Security') }}
      </h2>
      <p class="text-sm text-muted-foreground">
        {{ t('member.portal.security.subtitle', 'Password, email, and account deletion for your reader identity.') }}
      </p>
    </div>

    <!-- Password -->
    <form
      class="rounded-3xl border border-border/60 bg-card/70 p-6 space-y-4 max-w-lg"
      @submit.prevent="submitPassword"
    >
      <h3 class="font-semibold">
        {{ t('member.portal.security.passwordSection', 'Change password') }}
      </h3>
      <p
        v-if="passwordError"
        class="text-sm text-destructive"
      >
        {{ passwordError }}
      </p>
      <p
        v-if="passwordSuccess"
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
        >
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
        >
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
        >
      </label>
      <Button
        type="submit"
        :disabled="passwordPending"
      >
        {{ passwordPending ? t('member.portal.security.pending', 'Saving…') : t('member.portal.security.submit', 'Update password') }}
      </Button>
    </form>

    <!-- Email change -->
    <form
      class="rounded-3xl border border-border/60 bg-card/70 p-6 space-y-4 max-w-lg"
      @submit.prevent="submitEmail"
    >
      <h3 class="font-semibold">
        {{ t('member.portal.security.emailSection', 'Change email') }}
      </h3>
      <p class="text-sm text-muted-foreground">
        {{ t('member.portal.security.emailHint', 'We send a confirmation link to the new address. You must sign in again after confirming.') }}
      </p>
      <p
        v-if="memberStore.member?.pending_email"
        class="text-sm rounded-xl border border-amber-500/40 bg-amber-500/10 px-3 py-2"
      >
        {{ t('member.portal.security.pendingEmail', 'Pending confirmation:') }}
        <strong>{{ memberStore.member.pending_email }}</strong>
      </p>
      <p
        v-if="emailError"
        class="text-sm text-destructive"
      >
        {{ emailError }}
      </p>
      <p
        v-if="emailSuccess"
        class="text-sm text-emerald-600"
      >
        {{ t('member.portal.security.emailSent', 'Confirmation email sent.') }}
      </p>
      <label class="block space-y-1.5 text-sm">
        <span class="font-medium">{{ t('member.portal.security.newEmail', 'New email') }}</span>
        <input
          v-model="newEmail"
          type="email"
          required
          autocomplete="email"
          class="w-full h-10 rounded-xl border border-border bg-background px-3"
        >
      </label>
      <label class="block space-y-1.5 text-sm">
        <span class="font-medium">{{ t('member.portal.security.current', 'Current password') }}</span>
        <input
          v-model="emailPassword"
          type="password"
          required
          autocomplete="current-password"
          class="w-full h-10 rounded-xl border border-border bg-background px-3"
        >
      </label>
      <Button
        type="submit"
        :disabled="emailPending"
      >
        {{ emailPending ? t('member.portal.security.pending', 'Saving…') : t('member.portal.security.emailSubmit', 'Send confirmation') }}
      </Button>
    </form>

    <!-- Delete -->
    <form
      class="rounded-3xl border border-destructive/40 bg-destructive/5 p-6 space-y-4 max-w-lg"
      @submit.prevent="submitDelete"
    >
      <h3 class="font-semibold text-destructive">
        {{ t('member.portal.security.deleteSection', 'Delete account') }}
      </h3>
      <p class="text-sm text-muted-foreground">
        {{ t('member.portal.security.deleteHint', 'Permanently removes your reader account. Bookmarks are deleted; comments stay anonymous.') }}
      </p>
      <p
        v-if="deleteError"
        class="text-sm text-destructive"
      >
        {{ deleteError }}
      </p>
      <label class="block space-y-1.5 text-sm">
        <span class="font-medium">{{ t('member.portal.security.current', 'Current password') }}</span>
        <input
          v-model="deletePassword"
          type="password"
          required
          autocomplete="current-password"
          class="w-full h-10 rounded-xl border border-border bg-background px-3"
        >
      </label>
      <label class="block space-y-1.5 text-sm">
        <span class="font-medium">{{ t('member.portal.security.deleteConfirm', 'Type DELETE to confirm') }}</span>
        <input
          v-model="deleteConfirm"
          type="text"
          required
          class="w-full h-10 rounded-xl border border-border bg-background px-3"
        >
      </label>
      <Button
        type="submit"
        variant="outline"
        class="border-destructive text-destructive hover:bg-destructive/10"
        :disabled="deletePending || deleteConfirm !== 'DELETE'"
      >
        {{ deletePending ? t('member.portal.security.pending', 'Saving…') : t('member.portal.security.deleteSubmit', 'Delete my account') }}
      </Button>
    </form>
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

const currentPassword = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const passwordPending = ref(false);
const passwordError = ref('');
const passwordSuccess = ref(false);

const newEmail = ref('');
const emailPassword = ref('');
const emailPending = ref(false);
const emailError = ref('');
const emailSuccess = ref(false);

const deletePassword = ref('');
const deleteConfirm = ref('');
const deletePending = ref(false);
const deleteError = ref('');

const fieldError = (err: unknown, fallback: string): string => {
    if (isAxiosError(err) && err.response?.status === 422) {
        const errors = err.response.data?.errors as Record<string, string[]> | undefined;
        return errors?.current_password?.[0]
            ?? errors?.password?.[0]
            ?? errors?.email?.[0]
            ?? errors?.confirm?.[0]
            ?? fallback;
    }
    return isAxiosError(err)
        ? String(err.response?.data?.message || fallback)
        : fallback;
};

const submitPassword = async (): Promise<void> => {
    passwordPending.value = true;
    passwordError.value = '';
    passwordSuccess.value = false;
    try {
        await memberStore.updatePassword({
            current_password: currentPassword.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });
        currentPassword.value = '';
        password.value = '';
        passwordConfirmation.value = '';
        passwordSuccess.value = true;
    } catch (err: unknown) {
        passwordError.value = fieldError(err, t('member.portal.security.failed', 'Could not update password.'));
    } finally {
        passwordPending.value = false;
    }
};

const submitEmail = async (): Promise<void> => {
    emailPending.value = true;
    emailError.value = '';
    emailSuccess.value = false;
    try {
        await memberStore.requestEmailChange({
            email: newEmail.value.trim(),
            current_password: emailPassword.value,
        });
        emailPassword.value = '';
        emailSuccess.value = true;
    } catch (err: unknown) {
        emailError.value = fieldError(err, t('member.portal.security.emailFailed', 'Could not start email change.'));
    } finally {
        emailPending.value = false;
    }
};

const submitDelete = async (): Promise<void> => {
    deletePending.value = true;
    deleteError.value = '';
    try {
        await memberStore.deleteAccount({
            current_password: deletePassword.value,
            confirm: 'DELETE',
        });
        await router.replace('/member/login');
    } catch (err: unknown) {
        deleteError.value = fieldError(err, t('member.portal.security.deleteFailed', 'Could not delete account.'));
    } finally {
        deletePending.value = false;
    }
};
</script>
