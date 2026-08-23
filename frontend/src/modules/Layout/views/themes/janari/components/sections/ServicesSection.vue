<template>
  <section class="py-24 bg-muted/20 border-y border-border/50">
    <div class="container mx-auto px-6">
      <div class="text-center max-w-3xl mx-auto mb-16">
        <span class="text-[9px] font-black tracking-[0.4em] uppercase text-primary mb-4 block">
          {{ t('pages.services.badge') }}
        </span>
        <h2 class="text-3xl md:text-4xl font-heading font-black uppercase tracking-tight text-foreground mb-4">
          {{ t('pages.services.title') }}
        </h2>
        <p class="text-muted-foreground text-lg leading-relaxed">
          {{ t('pages.services.subtitle') }}
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
            {{ t(`pages.services.items.${item.key}.title`) }}
          </h3>
          <p class="text-sm text-muted-foreground leading-relaxed flex-1 mb-6">
            {{ t(`pages.services.items.${item.key}.description`) }}
          </p>
          <ul class="space-y-2 mb-6 text-sm text-muted-foreground">
            <li
              v-for="(bullet, idx) in bulletKeys(item.key)"
              :key="idx"
              class="flex gap-2"
            >
              <span class="text-primary">•</span>
              <span>{{ t(`pages.services.items.${item.key}.bullets.${bullet}`) }}</span>
            </li>
          </ul>
          <router-link
            :to="item.to"
            class="text-xs font-bold uppercase tracking-widest text-primary hover:underline inline-flex items-center gap-2"
          >
            {{ t(`pages.services.items.${item.key}.cta`) }}
            <ArrowRight class="w-4 h-4" />
          </router-link>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { type Component } from 'vue'
import { ArrowRight, FileInput, Headphones, Server, Users } from 'lucide-vue-next'
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'

const { t } = useThemeI18n('janari')

type ServiceKey = 'managed' | 'forms' | 'member' | 'onprem'

const items: Array<{ key: ServiceKey; icon: Component; to: string }> = [
  { key: 'managed', icon: Server, to: '/contact' },
  { key: 'forms', icon: FileInput, to: '/contact' },
  { key: 'member', icon: Users, to: '/member/register' },
  { key: 'onprem', icon: Headphones, to: '/contact' },
]

const bulletKeys = (key: ServiceKey): string[] => {
  const map: Record<ServiceKey, string[]> = {
    managed: ['0', '1', '2'],
    forms: ['0', '1'],
    member: ['0', '1'],
    onprem: ['0', '1', '2'],
  }
  return map[key]
}
</script>
