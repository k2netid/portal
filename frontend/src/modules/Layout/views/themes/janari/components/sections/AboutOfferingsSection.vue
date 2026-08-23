<template>
  <section class="py-20 border-t border-border bg-muted/10">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold text-foreground text-center mb-4">
        {{ t('pages.about.offeringsTitle') }}
      </h2>
      <p class="text-muted-foreground text-center max-w-2xl mx-auto mb-12">
        {{ t('pages.about.offeringsSubtitle') }}
      </p>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
        <div
          v-for="item in offerings"
          :key="item.key"
          class="p-6 rounded-2xl border border-border bg-card"
        >
          <h3 class="font-bold text-lg text-foreground mb-2">
            {{ t(`pages.about.offerings.${item.key}.title`) }}
          </h3>
          <p class="text-sm text-muted-foreground mb-4">
            {{ t(`pages.about.offerings.${item.key}.description`) }}
          </p>
          <router-link :to="item.to" class="text-xs font-bold text-primary uppercase tracking-widest hover:underline">
            {{ t(`pages.about.offerings.${item.key}.link`) }} →
          </router-link>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'

const { t } = useThemeI18n('janari')

const offerings = [
  { key: 'hub', to: '/solusi' },
  { key: 'commercial', to: '/pricing' },
  { key: 'onprem', to: '/contact' },
  { key: 'organization', to: '/solusi#organization-roadmap' },
]
</script>
