<template>
  <div class="min-h-screen bg-background">
    <header class="py-20 bg-gradient-to-b from-primary/10 to-background border-b border-border/50">
      <div class="container mx-auto px-4 text-center">
        <span class="text-primary font-bold tracking-wider uppercase text-sm mb-4 block">{{ t('pages.team.sectionLabel') }}</span>
        <h1 class="text-4xl md:text-6xl font-extrabold text-foreground mb-6">
          {{ t('pages.team.title') }}
        </h1>
        <p class="text-xl text-muted-foreground max-w-2xl mx-auto leading-relaxed">
          {{ t('pages.team.subtitle') }}
        </p>
      </div>
    </header>

    <section class="py-20">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div
            v-for="pillar in pillars"
            :key="pillar.key"
            class="p-8 rounded-2xl border border-border bg-card text-center"
          >
            <component :is="pillar.icon" class="w-12 h-12 text-primary mx-auto mb-6" stroke-width="1.5" />
            <h3 class="text-lg font-bold text-foreground mb-3">
              {{ t(`pages.team.pillars.${pillar.key}.title`) }}
            </h3>
            <p class="text-sm text-muted-foreground leading-relaxed">
              {{ t(`pages.team.pillars.${pillar.key}.description`) }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 border-t border-border">
      <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-center text-foreground mb-12">
          {{ t('pages.team.areasTitle') }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="area in areas"
            :key="area.key"
            class="p-6 rounded-xl border border-border"
          >
            <h3 class="font-bold text-foreground mb-2">
              {{ t(`pages.team.areas.${area.key}.title`) }}
            </h3>
            <p class="text-sm text-muted-foreground">
              {{ t(`pages.team.areas.${area.key}.description`) }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 border-t border-border bg-muted/20">
      <div class="container mx-auto px-4 max-w-3xl text-center space-y-6">
        <p class="text-muted-foreground leading-relaxed">
          {{ t('pages.team.closing') }}
        </p>
        <div class="flex flex-wrap justify-center gap-4">
          <router-link to="/about" class="text-xs font-bold uppercase tracking-widest text-primary hover:underline">
            {{ t('pages.team.aboutLink') }}
          </router-link>
          <router-link to="/careers" class="text-xs font-bold uppercase tracking-widest text-primary hover:underline">
            {{ t('pages.team.careersLink') }}
          </router-link>
          <router-link to="/contact" class="text-xs font-bold uppercase tracking-widest text-primary hover:underline">
            {{ t('pages.team.contactLink') }}
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">

import { Heart, Rocket, Shield, Code, PenLine, Settings, Headphones } from 'lucide-vue-next'
import { useThemeI18n } from '@/modules/Content/Layout/composables/useThemeI18n'

const { t } = useThemeI18n('janari')

const pillars = [
  { key: 'craft' as const, icon: Rocket },
  { key: 'trust' as const, icon: Shield },
  { key: 'scale' as const, icon: Heart },
]

const areas = [
  { key: 'engineering', icon: Code },
  { key: 'content', icon: PenLine },
  { key: 'platform', icon: Settings },
  { key: 'success', icon: Headphones },
]
</script>
