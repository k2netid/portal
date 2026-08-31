<template>
  <ConsoleListCard>
    <template
      v-if="showHeader"
      #toolbar
    >
      <div class="flex w-full items-center justify-between gap-3">
        <h3 class="text-base font-semibold tracking-tight text-foreground">
          {{ t('member.nav.newsletter', 'Newsletter') }}
        </h3>
        <router-link
          :to="{ name: 'member.newsletter' }"
          class="text-sm font-semibold text-primary hover:underline underline-offset-4"
        >
          {{ t('member.portal.widgets.manage', 'Manage') }}
        </router-link>
      </div>
    </template>

    <div class="p-5 sm:p-6 space-y-4">
      <p
        v-if="loading"
        class="text-sm text-muted-foreground"
      >
        {{ t('member.account.loading', 'Loading…') }}
      </p>
      <template v-else>
        <p class="text-sm text-muted-foreground">
          {{
            subscribed
              ? t('member.portal.newsletter.subscribed', 'Subscribed')
              : t('member.portal.newsletter.notSubscribed', 'Not subscribed')
          }}
        </p>
        <Button
          size="sm"
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
      </template>
    </div>
  </ConsoleListCard>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { unwrapApiPayload } from '@/modules/Member/utils/memberApi';
import { ConsoleListCard } from '@/shared/components/shell';

interface NewsletterStatus {
    subscribed?: boolean;
}

withDefaults(defineProps<{
    showHeader?: boolean;
}>(), {
    showHeader: true,
});

const { t } = useI18n();
const subscribed = ref(false);
const loading = ref(true);
const pending = ref(false);

const load = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get('/member/newsletter');
        const payload = unwrapApiPayload<NewsletterStatus>(response);
        subscribed.value = payload.subscribed === true;
    } catch {
        subscribed.value = false;
    } finally {
        loading.value = false;
    }
};

const toggle = async (): Promise<void> => {
    pending.value = true;
    try {
        const response = await api.put('/member/newsletter', { subscribe: !subscribed.value });
        const payload = unwrapApiPayload<NewsletterStatus>(response);
        subscribed.value = payload.subscribed === true;
    } finally {
        pending.value = false;
    }
};

onMounted(() => {
    void load();
});
</script>
