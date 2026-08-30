<template>
  <section class="py-24 bg-muted/20 border-y border-border/50">
    <div class="container mx-auto px-6">
      <div class="text-center max-w-3xl mx-auto mb-16">
        <span class="text-[9px] font-black tracking-[0.4em] uppercase text-primary mb-4 block">
          {{ badgeText }}
        </span>
        <h2 class="text-3xl md:text-4xl font-heading font-black uppercase tracking-tight text-foreground mb-4">
          {{ titleText }}
        </h2>
        <p class="text-muted-foreground text-lg leading-relaxed">
          {{ subtitleText }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <article
          v-for="item in items"
          :key="item.key"
          class="p-8 rounded-2xl border border-border bg-card flex flex-col"
        >
          <component :is="item.icon" class="w-10 h-10 text-primary mb-5" stroke-width="1.5" />
          <h3 class="text-xl font-bold text-foreground mb-2">
            {{ item.title }}
          </h3>
          <p class="text-sm text-muted-foreground leading-relaxed flex-1 mb-6">
            {{ item.description }}
          </p>
          <ul
            v-if="item.bullets.length"
            class="space-y-2 mb-6 text-sm text-muted-foreground"
          >
            <li
              v-for="(bullet, idx) in item.bullets"
              :key="idx"
              class="flex gap-2"
            >
              <span class="text-primary">•</span>
              <span>{{ bullet }}</span>
            </li>
          </ul>
          <router-link
            :to="item.to"
            class="text-xs font-bold uppercase tracking-widest text-primary hover:underline inline-flex items-center gap-2"
          >
            {{ item.linkLabel }}
            <ArrowRight class="w-4 h-4" />
          </router-link>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, type Component } from 'vue'
import { ArrowRight, FileInput, Headphones, Server, Users } from 'lucide-vue-next'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'

const { t } = useThemeI18n('janari')
const { getSetting } = useTheme()
const { localizedString } = useLocalizedThemeSetting()

type ServiceKey = 'managed' | 'forms' | 'member' | 'onprem'

const ICON_BY_KEY: Record<string, Component> = {
  managed: Server,
  forms: FileInput,
  member: Users,
  onprem: Headphones,
}

const DEFAULTS: Array<{ key: ServiceKey; to: string; bulletIds: string[] }> = [
  { key: 'managed', to: '/contact', bulletIds: ['0', '1', '2'] },
  { key: 'forms', to: '/contact', bulletIds: ['0', '1'] },
  { key: 'member', to: '/member/register', bulletIds: ['0', '1'] },
  { key: 'onprem', to: '/contact', bulletIds: ['0', '1', '2'] },
]

const splitLines = (value: unknown): string[] =>
  String(value ?? '')
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean)

const badgeText = computed(() => localizedString('page_solusi_services_badge') || t('pages.services.badge'))
const titleText = computed(() => localizedString('page_solusi_services_title') || t('pages.services.title'))
const subtitleText = computed(() => localizedString('page_solusi_services_subtitle') || t('pages.services.subtitle'))

const items = computed(() => {
  const raw = getSetting('page_solusi_services_items')
  if (Array.isArray(raw) && raw.length > 0) {
    return raw.map((item) => {
      const row = (item && typeof item === 'object' ? item : {}) as Record<string, unknown>
      const key = String(row.key || 'service')
      return {
        key,
        icon: ICON_BY_KEY[key] || Server,
        to: String(row.url || '/contact'),
        title: String(row.title || ''),
        description: String(row.description || ''),
        bullets: splitLines(row.bullets),
        linkLabel: String(row.link_label || row.linkLabel || ''),
      }
    })
  }

  return DEFAULTS.map((def) => ({
    key: def.key,
    icon: ICON_BY_KEY[def.key],
    to: def.to,
    title: t(`pages.services.items.${def.key}.title`),
    description: t(`pages.services.items.${def.key}.description`),
    bullets: def.bulletIds.map((id) => t(`pages.services.items.${def.key}.bullets.${id}`)),
    linkLabel: t(`pages.services.items.${def.key}.cta`),
  }))
})
</script>
