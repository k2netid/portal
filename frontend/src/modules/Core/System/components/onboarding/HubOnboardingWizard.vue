<template>
  <Card
    v-if="showCard"
    class="border-primary/20 bg-primary/5 shadow-none rounded-xl"
    data-testid="hub-onboarding-wizard"
  >
    <CardHeader class="pb-3">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-1">
          <CardTitle class="text-lg">{{ t('system.onboarding.title') }}</CardTitle>
          <CardDescription>{{ t('system.onboarding.subtitle') }}</CardDescription>
          <p class="text-xs text-muted-foreground">
            {{ t('system.onboarding.progress', { completed: completedCount, total: totalSteps }) }}
          </p>
        </div>
        <Button
          variant="ghost"
          size="sm"
          :disabled="dismissing"
          data-testid="hub-onboarding-dismiss"
          @click="dismiss"
        >
          {{ t('system.onboarding.dismiss') }}
        </Button>
      </div>
      <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-muted">
        <div
          class="h-full rounded-full bg-primary transition-all duration-300"
          :style="{ width: `${status.progress_percent}%` }"
        />
      </div>
    </CardHeader>
    <CardContent v-if="loading" class="py-6 text-sm text-muted-foreground">{{ t('common.labels.loading') }}</CardContent>
    <CardContent v-else class="grid gap-3 md:grid-cols-3">
      <div
        v-for="step in stepItems"
        :key="step.key"
        class="flex flex-col gap-2 rounded-lg border border-border/50 bg-card/80 p-4"
      >
        <div class="flex items-start gap-2">
          <CheckCircle2
            v-if="step.done"
            class="h-5 w-5 shrink-0 text-primary"
          />
          <Circle
            v-else
            class="h-5 w-5 shrink-0 text-muted-foreground"
          />
          <div class="space-y-1">
            <p class="font-medium text-sm text-foreground">{{ step.title }}</p>
            <p class="text-xs text-muted-foreground">{{ step.description }}</p>
          </div>
        </div>
        <RouterLink
          v-if="!step.done"
          :to="step.to"
          class="mt-auto w-full inline-flex h-8 items-center justify-center rounded-lg border border-border/60 bg-background px-2.5 text-sm font-medium hover:bg-accent/50"
        >
          {{ step.action }}
        </RouterLink>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { CheckCircle2, Circle } from 'lucide-vue-next';
import { useHubOnboarding } from '@/modules/Core/System/composables/useHubOnboarding';
import { consoleNamedRoute } from '@/shared/utils/consoleRoute';
import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/shared/components/ui';

const { t } = useI18n();
const { showCard, status, completedCount, totalSteps, dismiss, dismissing, loading } = useHubOnboarding();

const stepItems = computed(() => {
    const steps = status.value.steps;
    return [
        {
            key: 'identity',
            done: steps.identity,
            title: t('system.onboarding.steps.identity.title'),
            description: t('system.onboarding.steps.identity.description'),
            action: t('system.onboarding.steps.identity.action'),
            to: consoleNamedRoute('settings'),
        },
        {
            key: 'theme',
            done: steps.theme,
            title: t('system.onboarding.steps.theme.title'),
            description: t('system.onboarding.steps.theme.description'),
            action: t('system.onboarding.steps.theme.action'),
            to: consoleNamedRoute('themes'),
        },
        {
            key: 'first_page',
            done: steps.first_page,
            title: t('system.onboarding.steps.firstPage.title'),
            description: t('system.onboarding.steps.firstPage.description'),
            action: t('system.onboarding.steps.firstPage.action'),
            to: consoleNamedRoute('contents.create'),
        },
    ];
});
</script>
