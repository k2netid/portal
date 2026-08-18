<template>
  <div class="space-y-8">
    <PageHeader
      :title="t('system.integrations.hub.title')"
      :subtitle="t('system.integrations.hub.subtitle')"
      borderless
    />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <RouterLink
        v-for="card in hubCards"
        :key="card.title"
        :to="card.to"
        class="group block rounded-xl border border-border bg-card p-5 hover:border-primary/50 hover:shadow-md transition-all"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
              <component :is="card.icon" class="h-5 w-5" />
            </div>
            <div>
              <h3 class="font-semibold text-foreground group-hover:text-primary">{{ card.title }}</h3>
              <p class="text-sm text-muted-foreground mt-1">{{ card.description }}</p>
            </div>
          </div>
          <span class="text-xs font-medium text-primary shrink-0">{{ t('system.integrations.hub.open') }} →</span>
        </div>
      </RouterLink>
    </div>

    <ConsoleListCard>
      <div class="p-6 space-y-8">
        <section>
          <div class="flex items-center gap-2 mb-4">
            <Users class="w-5 h-5 text-primary" />
            <h3 class="text-lg font-medium">{{ t('system.integrations.scim.title') }}</h3>
          </div>
          <p class="text-sm text-muted-foreground mb-4">{{ t('system.integrations.scim.description') }}</p>
          <div class="bg-muted p-4 rounded-lg space-y-4 font-mono text-sm">
            <div class="flex justify-between items-center gap-4">
              <span class="text-muted-foreground shrink-0">{{ t('system.integrations.scim.baseUrl') }}:</span>
              <span class="flex-1 select-all break-all">{{ scimBaseUrl }}</span>
            </div>
            <div class="flex justify-between items-center gap-4">
              <span class="text-muted-foreground shrink-0">{{ t('system.integrations.scim.bearer') }}:</span>
              <span class="flex-1 text-yellow-600 dark:text-yellow-400">{{ t('system.integrations.scim.bearerHint') }}</span>
            </div>
          </div>
        </section>

        <Separator />

        <section>
          <div class="flex items-center gap-2 mb-4">
            <Server class="w-5 h-5 text-primary" />
            <h3 class="text-lg font-medium">{{ t('system.integrations.config.title') }}</h3>
          </div>
          <p class="text-sm text-muted-foreground mb-4">{{ t('system.integrations.config.description') }}</p>
          <div class="bg-muted p-4 rounded-lg space-y-4 font-mono text-sm">
            <div class="flex justify-between items-center gap-4">
              <span class="text-muted-foreground shrink-0">{{ t('system.integrations.config.resolve') }}:</span>
              <span class="flex-1 select-all break-all">GET {{ configResolveUrl }}</span>
            </div>
            <div class="flex justify-between items-center gap-4">
              <span class="text-muted-foreground shrink-0">{{ t('system.integrations.config.sync') }}:</span>
              <span class="flex-1 select-all break-all">POST {{ configSyncUrl }}</span>
            </div>
          </div>
        </section>
      </div>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink } from 'vue-router';
import { Shield, Webhook, Users, Server, Scale, Radio } from 'lucide-vue-next';
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';
import { Separator } from '@/shared/components/ui';

const { t } = useI18n();

import { consoleNamedRoute } from '@/shared/utils/consoleRoute';

const hubCards = computed(() => [
  {
    to: consoleNamedRoute('oauth-clients'),
    title: t('system.integrations.hub.oauthCard'),
    description: t('system.integrations.hub.oauthDesc'),
    icon: Shield,
  },
  {
    to: consoleNamedRoute('webhooks'),
    title: t('system.integrations.hub.webhooksCard'),
    description: t('system.integrations.hub.webhooksDesc'),
    icon: Webhook,
  },
  {
    to: consoleNamedRoute('settings', {}, { tab: 'abac-policies' }),
    title: t('system.integrations.hub.abacCard'),
    description: t('system.integrations.hub.abacDesc'),
    icon: Scale,
  },
  {
    to: consoleNamedRoute('settings', {}, { tab: 'siem-exports' }),
    title: t('system.integrations.hub.siemCard'),
    description: t('system.integrations.hub.siemDesc'),
    icon: Radio,
  },
]);

const scimBaseUrl = computed(() => `${window.location.origin}/api/scim/v2/`);
const configResolveUrl = computed(() => `${window.location.origin}/api/v1/config/resolve`);
const configSyncUrl = computed(() => `${window.location.origin}/api/v1/config/sync`);
</script>
