<template>
  <section class="py-20 border-t border-border bg-muted/10">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold text-foreground text-center mb-4">
        {{ titleText }}
      </h2>
      <p class="text-muted-foreground text-center max-w-2xl mx-auto mb-12">
        {{ subtitleText }}
      </p>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
        <div
          v-for="item in offerings"
          :key="item.key"
          class="p-6 rounded-2xl border border-border bg-card"
        >
          <h3 class="font-bold text-lg text-foreground mb-2">
            {{ item.title }}
          </h3>
          <p class="text-sm text-muted-foreground mb-4">
            {{ item.description }}
          </p>
          <router-link :to="item.to" class="text-xs font-bold text-primary uppercase tracking-widest hover:underline">
            {{ item.linkLabel }} →
          </router-link>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'

const { t } = useThemeI18n('janari')
const { getSetting } = useTheme()
const { localizedString } = useLocalizedThemeSetting()

const DEFAULTS = [
  { key: 'hub', to: '/solusi' },
  { key: 'commercial', to: '/pricing' },
  { key: 'onprem', to: '/contact' },
  { key: 'organization', to: '/solusi#organization-roadmap' },
] as const

const titleText = computed(() => localizedString('page_about_offerings_title') || t('pages.about.offeringsTitle'))
const subtitleText = computed(() => localizedString('page_about_offerings_subtitle') || t('pages.about.offeringsSubtitle'))

const offerings = computed(() => {
  const raw = getSetting('page_about_offerings_items')
  if (Array.isArray(raw) && raw.length > 0) {
    return raw.map((item) => {
      const row = (item && typeof item === 'object' ? item : {}) as Record<string, unknown>
      return {
        key: String(row.key || 'item'),
        to: String(row.url || '/'),
        title: String(row.title || ''),
        description: String(row.description || ''),
        linkLabel: String(row.link_label || row.linkLabel || ''),
      }
    })
  }
  return DEFAULTS.map((def) => ({
    key: def.key,
    to: def.to,
    title: t(`pages.about.offerings.${def.key}.title`),
    description: t(`pages.about.offerings.${def.key}.description`),
    linkLabel: t(`pages.about.offerings.${def.key}.link`),
  }))
})
</script>
