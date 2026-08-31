<template>
  <section class="py-14 md:py-16 bg-background border-y border-border/50">
    <div class="container mx-auto px-6">
      <div class="text-center max-w-3xl mx-auto mb-8 md:mb-10">
        <span class="inline-flex items-center px-4 py-2 rounded-full border border-primary/20 bg-primary/5 text-[9px] font-black tracking-[0.4em] uppercase text-primary mb-6">
          {{ badgeText }}
        </span>
        <h2 class="text-4xl md:text-5xl font-heading font-black uppercase tracking-tight text-foreground mb-4">
          {{ titleText }}
        </h2>
        <p class="text-muted-foreground text-lg leading-relaxed">
          {{ subtitleText }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <article
          v-for="card in cards"
          :id="card.key === 'organization' ? 'organization-roadmap' : undefined"
          :key="card.key"
          class="group p-8 rounded-2xl border border-border bg-card/50 hover:border-primary/40 hover:shadow-lg transition-all duration-300 flex flex-col"
        >
          <component
            :is="card.icon"
            class="w-10 h-10 text-primary mb-6"
            stroke-width="1.5"
          />
          <h3 class="text-xl font-bold text-foreground mb-3">
            {{ card.title }}
          </h3>
          <p class="text-muted-foreground text-sm leading-relaxed flex-1 mb-6">
            {{ card.description }}
          </p>
          <router-link
            :to="card.to"
            class="text-xs font-bold uppercase tracking-widest text-primary hover:underline inline-flex items-center gap-2"
          >
            {{ card.linkLabel }}
            <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
          </router-link>
        </article>
      </div>

      <div class="text-center mt-12">
        <router-link
          :to="viewAllUrl"
          class="inline-flex items-center gap-2 px-8 py-3 text-xs font-bold uppercase tracking-widest bg-primary text-primary-foreground rounded-lg hover:opacity-90 transition-opacity"
        >
          {{ viewAllText }}
          <ArrowRight class="w-4 h-4" />
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, type Component } from 'vue'
import {
  ArrowRight,
  BookOpen,
  Brain,
  Cloud,
  LayoutDashboard,
  MessageSquare,
  Server,
} from 'lucide-vue-next'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'

const { t } = useThemeI18n('janari')
const { getSetting } = useTheme()
const { localizedString } = useLocalizedThemeSetting()

type ProductKey = 'publishing' | 'reach' | 'intelligence' | 'platform' | 'organization' | 'member'

const ICON_BY_KEY: Record<string, Component> = {
  publishing: BookOpen,
  reach: MessageSquare,
  intelligence: Brain,
  platform: Server,
  organization: Cloud,
  member: LayoutDashboard,
}

const DEFAULT_DEFS: Array<{ key: ProductKey; to: string }> = [
  { key: 'publishing', to: '/blog' },
  { key: 'reach', to: '/contact' },
  { key: 'intelligence', to: '/search' },
  { key: 'platform', to: '/pricing' },
  { key: 'organization', to: '/solusi#organization-roadmap' },
  { key: 'member', to: '/member/register' },
]

const badgeText = computed(() => localizedString('home_products_badge') || t('products.badge'))
const titleText = computed(() => localizedString('home_products_title') || t('products.title'))
const subtitleText = computed(() => localizedString('home_products_subtitle') || t('products.subtitle'))
const viewAllText = computed(() => localizedString('home_products_view_all') || t('products.viewAll'))
const viewAllUrl = computed(() => {
  const raw = getSetting('home_products_view_all_url', '/solusi')
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/solusi'
})

const cards = computed(() => {
  const raw = getSetting('home_products_items')
  if (Array.isArray(raw) && raw.length > 0) {
    return raw.map((item) => {
      const row = (item && typeof item === 'object' ? item : {}) as Record<string, unknown>
      const key = String(row.key || 'product')
      return {
        key,
        icon: ICON_BY_KEY[key] || LayoutDashboard,
        to: String(row.url || '/'),
        title: String(row.title || ''),
        description: String(row.description || ''),
        linkLabel: String(row.link_label || row.linkLabel || ''),
      }
    })
  }

  return DEFAULT_DEFS.map((def) => ({
    key: def.key,
    icon: ICON_BY_KEY[def.key] || LayoutDashboard,
    to: def.to,
    title: t(`products.cards.${def.key}.title`),
    description: t(`products.cards.${def.key}.description`),
    linkLabel: t(`products.cards.${def.key}.linkLabel`),
  }))
})
</script>
