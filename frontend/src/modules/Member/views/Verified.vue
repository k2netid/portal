<template>
  <div class="sarangenge-theme flex-1 flex flex-col py-16">
    <div class="max-w-md mx-auto w-full px-4 space-y-6 text-center">
      <h1 class="text-3xl font-extrabold font-heading">
        {{ title }}
      </h1>
      <p class="text-sm text-muted-foreground">
        {{ body }}
      </p>
      <router-link
        to="/member/account"
        class="text-primary font-semibold"
      >
        {{ t('member.verified.account', 'Go to account') }}
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const route = useRoute();

const status = computed(() => String(route.query.status || ''));

const title = computed(() => {
    if (status.value === 'ok') {
        return t('member.verified.okTitle', 'Email verified');
    }
    if (status.value === 'already') {
        return t('member.verified.alreadyTitle', 'Already verified');
    }
    return t('member.verified.invalidTitle', 'Link invalid or expired');
});

const body = computed(() => {
    if (status.value === 'ok') {
        return t('member.verified.okBody', 'Your reader email is confirmed.');
    }
    if (status.value === 'already') {
        return t('member.verified.alreadyBody', 'This address was already confirmed.');
    }
    return t('member.verified.invalidBody', 'Request a new verification email from your account.');
});
</script>
