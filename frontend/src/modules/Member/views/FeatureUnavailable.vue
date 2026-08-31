<template>
  <div class="space-y-4 rounded-2xl border border-border/60 px-5 py-8 text-center">
    <h2 class="text-xl font-bold tracking-tight">
      {{ t('member.portal.unavailable.title', 'Feature unavailable') }}
    </h2>
    <p class="text-sm text-muted-foreground max-w-md mx-auto">
      {{ t('member.portal.unavailable.body', 'This section is not available right now. The site module may be turned off or your account may need verification.') }}
    </p>
    <router-link
      :to="{ name: 'member.dashboard' }"
      class="inline-flex text-sm font-semibold text-primary hover:underline underline-offset-4"
    >
      {{ t('member.portal.unavailable.back', 'Back to overview') }}
    </router-link>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>
