<template>
  <section class="py-24 bg-background border-y border-border/50">
    <div class="container mx-auto px-6">
      <div class="text-center max-w-3xl mx-auto mb-16">
        <span class="inline-flex items-center px-4 py-2 rounded-full border border-primary/20 bg-primary/5 text-[9px] font-black tracking-[0.4em] uppercase text-primary mb-6">
          {{ t('products.badge') }}
        </span>
        <h2 class="text-4xl md:text-5xl font-heading font-black uppercase tracking-tight text-foreground mb-4">
          {{ t('products.title') }}
        </h2>
        <p class="text-muted-foreground text-lg leading-relaxed">
          {{ t('products.subtitle') }}
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
            {{ t(`products.cards.${card.key}.title`) }}
          </h3>
          <p class="text-muted-foreground text-sm leading-relaxed flex-1 mb-6">
            {{ t(`products.cards.${card.key}.description`) }}
          </p>
          <router-link
            :to="card.to"
            class="text-xs font-bold uppercase tracking-widest text-primary hover:underline inline-flex items-center gap-2"
          >
            {{ t(`products.cards.${card.key}.linkLabel`) }}
            <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
          </router-link>
        </article>
      </div>

      <div class="text-center mt-12">
        <router-link
          to="/solusi"
          class="inline-flex items-center gap-2 px-8 py-3 text-xs font-bold uppercase tracking-widest bg-primary text-primary-foreground rounded-lg hover:opacity-90 transition-opacity"
        >
          {{ t('products.viewAll') }}
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
import { useThemeI18n } from '@/modules/Content/Layout/composables/useThemeI18n'

const { t } = useThemeI18n('janari')

type ProductKey = 'publishing' | 'reach' | 'intelligence' | 'platform' | 'organization' | 'member'

const cards = computed(() => {
  const defs: Array<{ key: ProductKey; icon: Component; to: string }> = [
    { key: 'publishing', icon: BookOpen, to: '/blog' },
    { key: 'reach', icon: MessageSquare, to: '/contact' },
    { key: 'intelligence', icon: Brain, to: '/search' },
    { key: 'platform', icon: Server, to: '/pricing' },
    { key: 'organization', icon: Cloud, to: '/solusi#organization-roadmap' },
    { key: 'member', icon: LayoutDashboard, to: '/member/register' },
  ]
  return defs
})
</script>
