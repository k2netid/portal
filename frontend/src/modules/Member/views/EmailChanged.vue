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
        to="/member/login"
        class="text-primary font-semibold"
      >
        {{ t('member.login.link', 'Sign in') }}
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

const title = computed(() => (
    status.value === 'ok'
        ? t('member.emailChanged.okTitle', 'Email updated')
        : t('member.emailChanged.invalidTitle', 'Link invalid or expired')
));

const body = computed(() => (
    status.value === 'ok'
        ? t('member.emailChanged.okBody', 'Sign in again with your new email address.')
        : t('member.emailChanged.invalidBody', 'Request a new email change from Security settings.')
));
</script>
