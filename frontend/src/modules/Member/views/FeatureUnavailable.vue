<template>
  <div class="space-y-4 rounded-3xl border border-border/60 bg-card/70 p-6 text-center">
    <h2 class="text-xl font-bold">
      {{ t('member.portal.unavailable.title', 'Feature unavailable') }}
    </h2>
    <p class="text-sm text-muted-foreground">
      {{ t('member.portal.unavailable.body', 'This section is not available right now. The site module may be turned off or your account may need verification.') }}
    </p>
    <router-link
      :to="{ name: 'member.dashboard' }"
      class="inline-flex text-sm font-semibold text-primary"
    >
      {{ t('member.portal.unavailable.back', 'Back to dashboard') }}
    </router-link>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>
