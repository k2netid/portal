<template>
  <div class="space-y-6">
    <div class="space-y-1">
      <h2 class="text-xl font-bold">
        {{ t('member.nav.newsletter', 'Newsletter') }}
      </h2>
      <p class="text-sm text-muted-foreground">
        {{ t('member.portal.newsletter.subtitle', 'Manage email updates for your reader account.') }}
      </p>
    </div>

    <section class="rounded-3xl border border-border/60 bg-card/70 p-6 space-y-4 max-w-lg">
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
        {{ success }}
      </p>

      <dl class="grid gap-3 text-sm">
        <div class="space-y-1">
          <dt class="text-muted-foreground">
            {{ t('member.portal.newsletter.email', 'Delivery email') }}
          </dt>
          <dd class="font-medium">
            {{ memberStore.member?.email }}
          </dd>
        </div>
        <div class="space-y-1">
          <dt class="text-muted-foreground">
            {{ t('member.portal.newsletter.status', 'Status') }}
          </dt>
          <dd class="font-medium">
            {{
              subscribed
                ? t('member.portal.newsletter.subscribed', 'Subscribed')
                : t('member.portal.newsletter.notSubscribed', 'Not subscribed')
            }}
          </dd>
        </div>
      </dl>

      <Button
        :disabled="pending"
        @click="toggle"
      >
        {{
          pending
            ? t('member.portal.newsletter.pending', 'Saving…')
            : subscribed
              ? t('member.portal.newsletter.unsubscribe', 'Unsubscribe')
              : t('member.portal.newsletter.subscribe', 'Subscribe')
        }}
      </Button>
    </section>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { isAxiosError } from 'axios';
import api from '@/engine/api/client';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useMemberStore } from '@/modules/Member/stores/member';

interface NewsletterStatus {
    subscribed?: boolean;
    status?: string | null;
}

const { t } = useI18n();
const memberStore = useMemberStore();

const subscribed = ref(false);
const pending = ref(false);
const loading = ref(true);
const error = ref('');
const success = ref('');

const load = async (): Promise<void> => {
    loading.value = true;
    error.value = '';
    try {
        const response = await api.get('/member/newsletter');
        const payload = response.data as NewsletterStatus;
        subscribed.value = payload.subscribed === true;
    } catch {
        error.value = t('member.portal.newsletter.loadFailed', 'Could not load newsletter preferences.');
    } finally {
        loading.value = false;
    }
};

const toggle = async (): Promise<void> => {
    pending.value = true;
    error.value = '';
    success.value = '';
    try {
        const response = await api.put('/member/newsletter', { subscribe: !subscribed.value });
        const payload = response.data as NewsletterStatus;
        subscribed.value = payload.subscribed === true;
        success.value = subscribed.value
            ? t('member.portal.newsletter.subscribedSuccess', 'You are subscribed.')
            : t('member.portal.newsletter.unsubscribedSuccess', 'You are unsubscribed.');
    } catch (err: unknown) {
        error.value = isAxiosError(err)
            ? String(err.response?.data?.message || t('member.portal.newsletter.failed', 'Could not update preferences.'))
            : t('member.portal.newsletter.failed', 'Could not update preferences.');
    } finally {
        pending.value = false;
    }
};

onMounted(() => {
    void load();
});
</script>
