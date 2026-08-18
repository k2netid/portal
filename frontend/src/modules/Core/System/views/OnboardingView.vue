<template>
  <div class="mx-auto max-w-5xl space-y-6 p-6">
    <PageHeader
      :title="t('system.onboarding.title')"
      :subtitle="t('system.onboarding.subtitle')"
      borderless
    >
      <template #actions>
        <RouterLink
          :to="consoleNamedRoute('system.dashboard')"
          class="inline-flex h-8 items-center justify-center rounded-lg border border-border/60 bg-background px-2.5 text-sm font-medium hover:bg-accent/50"
        >
          {{ t('common.actions.back') }}
        </RouterLink>
      </template>
    </PageHeader>
    <HubOnboardingWizard />
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import HubOnboardingWizard from '@/modules/Core/System/components/onboarding/HubOnboardingWizard.vue';
import { PageHeader } from '@/shared/components/shell';
import { consoleNamedRoute } from '@/shared/utils/consoleRoute';

const { t } = useI18n();
</script>
