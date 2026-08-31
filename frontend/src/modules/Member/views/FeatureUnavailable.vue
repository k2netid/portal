<template>
  <MemberPage :title="t('member.portal.unavailable.title', 'Feature unavailable')">
    <template #subtitle>
      {{ t('member.portal.unavailable.body', 'This section is not available right now. The site module may be turned off or your account may need verification.') }}
    </template>

    <ConsoleListCard>
      <EmptyState
        :title="t('member.portal.unavailable.title', 'Feature unavailable')"
        compact
      >
        <template #action>
          <router-link
            :to="{ name: 'member.dashboard' }"
            class="inline-flex text-sm font-semibold text-primary hover:underline underline-offset-4"
          >
            {{ t('member.portal.unavailable.back', 'Back to overview') }}
          </router-link>
        </template>
      </EmptyState>
    </ConsoleListCard>
  </MemberPage>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import MemberPage from '@/modules/Member/components/MemberPage.vue';
import { EmptyState } from '@/shared/components/feedback';
import { ConsoleListCard } from '@/shared/components/shell';

const { t } = useI18n();
</script>
