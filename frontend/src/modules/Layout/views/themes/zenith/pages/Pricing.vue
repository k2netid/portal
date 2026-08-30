<template>
  <div class="zenith-theme flex-1 flex flex-col py-12 sm:py-16">
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ post: pageData, site: { name: 'Jejakawan' } }"
    />
    <SafeHtml
      v-else-if="cmsBody"
      class="container mx-auto px-4 py-16"
      :html="cmsBody"
      mode="publishing"
    />
    <template v-else>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 w-full">
      <!-- Header -->
      <div class="text-center space-y-4 max-w-3xl mx-auto">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-foreground font-heading">
          {{ t('theme.zenith.pages.pricing.title', 'Transparent Plans') }}
        </h1>
        <p class="text-lg text-muted-foreground">
          {{ t('theme.zenith.pages.pricing.subtitle', 'Choose the perfect plan for your publishing workflow.') }}
        </p>
      </div>

      <!-- Pricing Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <Card
          v-for="plan in plans"
          :key="plan.name"
          :hover="true"
          class="flex flex-col justify-between"
          :class="plan.popular ? 'border-primary shadow-xl ring-1 ring-primary/30' : ''"
        >
          <div class="space-y-6">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-foreground font-heading">
                {{ plan.name }}
              </h3>
              <span
                v-if="plan.popular"
                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-primary text-primary-foreground"
              >
                Popular
              </span>
            </div>

            <div class="flex items-baseline gap-1">
              <span class="text-4xl font-extrabold text-foreground">{{ plan.price }}</span>
              <span class="text-sm text-muted-foreground">/ month</span>
            </div>

            <p class="text-sm text-muted-foreground">
              {{ plan.description }}
            </p>

            <ul class="space-y-3 text-sm text-muted-foreground border-t border-border/40 pt-6">
              <li
                v-for="feature in plan.features"
                :key="feature"
                class="flex items-center gap-2.5"
              >
                <Check class="w-4 h-4 text-primary shrink-0" />
                <span>{{ feature }}</span>
              </li>
            </ul>
          </div>

          <div class="mt-8 pt-4">
            <Button
              as="router-link"
              to="/contact"
              :variant="plan.popular ? 'primary' : 'outline'"
              class="w-full justify-center"
            >
              Get Started
            </Button>
          </div>
        </Card>
      </div>
    </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue';
import { Card, Button } from '@/modules/Layout/views/themes/zenith/ui';
import { Check } from 'lucide-vue-next';

const { t } = useI18n();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('pricing');

const plans = [
  {
    name: 'Starter',
    price: '$19',
    description: 'Essential toolkit for independent creators and personal publications.',
    popular: false,
    features: ['Up to 10,000 monthly readers', 'Visual Page Builder', 'Custom domain support', 'Standard analytics'],
  },
  {
    name: 'Professional',
    price: '$49',
    description: 'Advanced capabilities for growing publications and modern agencies.',
    popular: true,
    features: ['Unlimited readers', 'Full theme customizer', 'AI Content Assistant', 'Priority CDN delivery', 'Newsletter automation'],
  },
  {
    name: 'Enterprise',
    price: '$149',
    description: 'Custom scalable infrastructure with high-availability support.',
    popular: false,
    features: ['Unlimited domains', 'Custom integrations & webhooks', 'SSO & Passkeys Authentication', 'Dedicated account manager', '99.99% SLA'],
  },
];
</script>
