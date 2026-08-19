<template>
  <div class="min-h-screen bg-background">
    <header class="py-20 bg-gradient-to-b from-primary/10 to-background border-b border-border/50">
      <div class="container mx-auto px-4 text-center">
        <span class="text-primary font-bold tracking-wider uppercase text-sm mb-4 block">{{ t('pages.solusi.sectionLabel') }}</span>
        <h1 class="text-4xl md:text-6xl font-extrabold text-foreground mb-6">
          {{ pageTitle }}
        </h1>
        <p class="text-xl text-muted-foreground max-w-2xl mx-auto leading-relaxed">
          {{ pageSubtitle }}
        </p>
      </div>
    </header>

    <PluginSlot name="after_hero" class="w-full" />

    <HubStackSection />
    <ProductsSection />
    <ServicesSection />

    <section class="py-16 bg-muted/20 border-t border-border">
      <div class="container mx-auto px-4 max-w-3xl text-center space-y-6">
        <h2 class="text-2xl font-bold text-foreground">
          {{ t('pages.solusi.ctaTitle') }}
        </h2>
        <p class="text-muted-foreground leading-relaxed">
          {{ t('pages.solusi.ctaBody') }}
        </p>
        <div class="flex flex-wrap justify-center gap-4 pt-4">
          <router-link
            to="/contact"
            class="px-8 py-3 text-xs font-bold uppercase tracking-widest bg-primary text-primary-foreground rounded-lg"
          >
            {{ t('pages.solusi.ctaContact') }}
          </router-link>
          <router-link
            to="/pricing"
            class="px-8 py-3 text-xs font-bold uppercase tracking-widest border border-border rounded-lg hover:bg-muted/50"
          >
            {{ t('pages.solusi.ctaPricing') }}
          </router-link>
          <router-link
            to="/auth/console-sign-up"
            class="px-8 py-3 text-xs font-bold uppercase tracking-widest border border-border rounded-lg hover:bg-muted/50"
          >
            {{ t('pages.solusi.ctaMember') }}
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import ProductsSection from '../components/sections/ProductsSection.vue'
import ServicesSection from '../components/sections/ServicesSection.vue'
import HubStackSection from '../components/sections/HubStackSection.vue'
import { PluginSlot } from '@/shared/components'
import { useThemeI18n } from '@/modules/Content/Layout/composables/useThemeI18n'
import { useLocalizedThemeSetting } from '@/modules/Content/Layout/composables/useLocalizedThemeSetting'

const { t } = useThemeI18n('janari')
const { localizedString } = useLocalizedThemeSetting()

const pageTitle = computed(() => localizedString('page_solusi_title') || t('pages.solusi.title'))
const pageSubtitle = computed(() => localizedString('page_solusi_subtitle') || t('pages.solusi.subtitle'))
</script>
