<template>
  <div class="space-y-6">
    <div class="space-y-1">
      <h2 class="text-xl font-bold">
        {{ t('member.portal.profile.title', 'Profile') }}
      </h2>
      <p class="text-sm text-muted-foreground">
        {{ t('member.portal.profile.subtitle', 'Your reader identity on this site.') }}
      </p>
    </div>

    <form
      class="rounded-xl border border-border/70 bg-background/80 p-5 sm:p-6 space-y-4 max-w-lg"
      @submit.prevent="submit"
    >
      <p
        v-if="error"
        class="text-sm text-destructive"
      >
        {{ error }}
      </p>
      <p
        v-if="saved"
        class="text-sm text-emerald-600"
      >
        {{ t('member.portal.profile.saved', 'Profile saved.') }}
      </p>

      <label class="block space-y-1.5 text-sm">
        <span class="font-medium">{{ t('member.portal.profile.name', 'Name') }}</span>
        <input
          v-model="name"
          type="text"
          required
          maxlength="255"
          class="w-full h-10 rounded-xl border border-border bg-background px-3"
        />
      </label>

      <label class="block space-y-1.5 text-sm">
        <span class="font-medium">{{ t('member.portal.profile.email', 'Email') }}</span>
        <input
          :value="memberStore.member?.email || ''"
          type="email"
          disabled
          class="w-full h-10 rounded-xl border border-border bg-muted/40 px-3 text-muted-foreground"
        />
      </label>
      <p
        v-if="memberStore.member?.pending_email"
        class="text-sm rounded-xl border border-amber-500/40 bg-amber-500/10 px-3 py-2"
      >
        {{ t('member.portal.security.pendingEmail', 'Pending confirmation:') }}
        <strong>{{ memberStore.member.pending_email }}</strong>
      </p>

      <div class="grid gap-4 sm:grid-cols-2 text-sm pt-2">
        <div class="space-y-1">
          <p class="text-muted-foreground">
            {{ t('member.portal.profile.status', 'Status') }}
          </p>
          <p class="font-medium capitalize">
            {{ memberStore.member?.status || '—' }}
          </p>
        </div>
        <div class="space-y-1">
          <p class="text-muted-foreground">
            {{ t('member.portal.profile.verified', 'Email verified') }}
          </p>
          <p class="font-medium">
            {{
              memberStore.member?.email_verified === true
                ? t('member.verified.yes', 'Verified')
                : t('member.verified.no', 'Unverified')
            }}
          </p>
        </div>
      </div>

      <Button
        type="submit"
        :disabled="pending"
      >
        {{ pending ? t('member.portal.profile.pending', 'Saving…') : t('member.portal.profile.save', 'Save profile') }}
      </Button>
    </form>

    <p
      v-if="memberStore.member && memberStore.member.email_verified !== true"
      class="text-sm rounded-2xl border border-amber-500/40 bg-amber-500/10 px-4 py-3"
    >
      {{ t('member.account.verifyHint', 'Confirm your email to finish setting up this reader account.') }}
      <button
        type="button"
        class="ml-2 font-semibold text-primary"
        :disabled="resending"
        @click="resend"
      >
        {{ resendLabel }}
      </button>
    </p>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { isAxiosError } from 'axios';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useMemberStore } from '@/modules/Member/stores/member';

const { t } = useI18n();
const memberStore = useMemberStore();

const name = ref(memberStore.member?.name ?? '');
const pending = ref(false);
const error = ref('');
const saved = ref(false);
const resending = ref(false);
const resent = ref(false);

watch(
    () => memberStore.member?.name,
    (value) => {
        if (value) {
            name.value = value;
        }
    },
);

const resendLabel = computed(() => (
    resent.value
        ? t('member.account.resent', 'Sent')
        : t('member.account.resend', 'Resend email')
));

const resend = async (): Promise<void> => {
    resending.value = true;
    try {
        await memberStore.resendVerification();
        resent.value = true;
    } finally {
        resending.value = false;
    }
};

const submit = async (): Promise<void> => {
    pending.value = true;
    error.value = '';
    saved.value = false;
    try {
        await memberStore.updateProfile(name.value.trim());
        saved.value = true;
    } catch (err: unknown) {
        error.value = isAxiosError(err)
            ? String(err.response?.data?.message || t('member.portal.profile.failed', 'Could not save profile.'))
            : t('member.portal.profile.failed', 'Could not save profile.');
    } finally {
        pending.value = false;
    }
};
</script>
