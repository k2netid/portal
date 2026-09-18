<template>
  <SareupnaPageGate
    setting-key="enable_pricing"
    :title="t('pages.pricing.title', 'Paket & Layanan')"
  >
    <div class="sareupna-page flex-1 w-full min-h-0 py-16 md:py-24">
      <div class="container mx-auto px-4 md:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <span class="text-xs font-mono uppercase tracking-widest text-primary font-bold mb-4 block">
            {{ t('pages.pricing.sectionLabel', 'Pricing & Cloud Subscriptions') }}
          </span>
          <h1 class="text-4xl md:text-6xl font-heading font-black uppercase tracking-tight text-foreground mb-6">
            {{ t('pages.pricing.headline', 'Pilihan Paket Transparan') }}
          </h1>
          <p class="text-muted-foreground text-base leading-relaxed">
            {{
              t(
                'pages.pricing.subtitle',
                'Pilih skala komputasi dan kapabilitas yang sesuai dengan tahap perkembangan platform Anda.',
              )
            }}
          </p>

      <PluginSlot name="after_hero" class="w-full" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto mb-20">
          <div
            v-for="tier in tiers"
            :key="tier.id"
            class="p-8 rounded-3xl flex flex-col justify-between"
            :class="
              tier.featured
                ? 'relative bg-gradient-to-b from-primary/10 to-card border border-primary/50 shadow-2xl shadow-primary/20'
                : 'bg-card/60 border border-border/60'
            "
          >
            <div
              v-if="tier.badge"
              class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-primary text-primary-foreground text-[10px] font-mono uppercase tracking-widest font-bold shadow-md"
            >
              {{ tier.badge }}
            </div>
            <div>
              <span
                class="text-xs font-mono uppercase tracking-widest font-bold block mb-3"
                :class="tier.featured ? 'text-primary' : 'text-muted-foreground'"
              >{{ tier.name }}</span>
              <div class="flex items-baseline gap-1 mb-4">
                <span
                  class="font-black text-foreground font-heading"
                  :class="tier.priceClass"
                >{{ tier.price }}</span>
                <span class="text-xs text-muted-foreground">{{ tier.period }}</span>
              </div>
              <p class="text-xs text-muted-foreground leading-relaxed mb-6">
                {{ tier.description }}
              </p>
              <ul
                class="space-y-3 text-xs mb-8 border-t border-border/40 pt-6"
                :class="tier.featured ? 'text-foreground/90' : 'text-foreground/80'"
              >
                <li
                  v-for="(feature, idx) in tier.features"
                  :key="idx"
                  class="flex items-center gap-2.5"
                >
                  <Check
                    class="w-4 h-4 shrink-0"
                    :class="tier.featured ? 'text-primary' : 'text-emerald-400'"
                  />
                  {{ feature }}
                </li>
              </ul>
            </div>
            <router-link
              to="/contact"
              class="w-full py-3 rounded-xl text-xs font-bold uppercase tracking-wider text-center transition-all"
              :class="
                tier.featured
                  ? 'bg-primary text-primary-foreground shadow-md shadow-primary/30 hover:bg-primary/90'
                  : 'border border-border bg-card hover:border-primary/50 text-foreground'
              "
            >
              {{ tier.cta }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </SareupnaPageGate>
</template>

<script setup lang="ts">
import { PluginSlot } from '@/shared/components'
import { computed } from 'vue'
import { Check } from 'lucide-vue-next'
import SareupnaPageGate from '../components/shared/SareupnaPageGate.vue'
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'

const { t } = useThemeI18n('sareupna')

type Tier = {
  id: string
  name: string
  price: string
  period: string
  description: string
  features: string[]
  cta: string
  badge?: string
  featured?: boolean
  priceClass: string
}

const tiers = computed<Tier[]>(() => [
  {
    id: 'starter',
    name: t('pages.pricing.tiers.starter.name', 'Starter'),
    price: t('pages.pricing.tiers.starter.price', 'IDR 0'),
    period: t('pages.pricing.tiers.starter.period', '/ selamanya'),
    description: t(
      'pages.pricing.tiers.starter.description',
      'Untuk pengembang independen dan eksperimen arsitektur lokal.',
    ),
    features: [
      t('pages.pricing.tiers.starter.f1', 'Single Instance Deployment'),
      t('pages.pricing.tiers.starter.f2', 'Core CMS & Visual Canvas'),
      t('pages.pricing.tiers.starter.f3', 'Community Discord Support'),
    ],
    cta: t('pages.pricing.tiers.starter.cta', 'Mulai Gratis'),
    priceClass: 'text-4xl',
  },
  {
    id: 'pro',
    name: t('pages.pricing.tiers.pro.name', 'Professional'),
    price: t('pages.pricing.tiers.pro.price', 'IDR 499K'),
    period: t('pages.pricing.tiers.pro.period', '/ bulan'),
    description: t(
      'pages.pricing.tiers.pro.description',
      'Untuk bisnis berkembang yang membutuhkan edge delivery dan performa teruji.',
    ),
    features: [
      t('pages.pricing.tiers.pro.f1', 'Multi-Tenant Ready'),
      t('pages.pricing.tiers.pro.f2', 'CDN & Edge Asset Optimization'),
      t('pages.pricing.tiers.pro.f3', 'Granular RBAC & Security Audit'),
      t('pages.pricing.tiers.pro.f4', 'Email Support 8x5'),
    ],
    cta: t('pages.pricing.tiers.pro.cta', 'Pilih Paket Pro'),
    badge: t('pages.pricing.tiers.pro.badge', 'Paling Populer'),
    featured: true,
    priceClass: 'text-4xl',
  },
  {
    id: 'enterprise',
    name: t('pages.pricing.tiers.enterprise.name', 'Enterprise'),
    price: t('pages.pricing.tiers.enterprise.price', 'Custom'),
    period: t('pages.pricing.tiers.enterprise.period', '/ SLA Berbasis Kontrak'),
    description: t(
      'pages.pricing.tiers.enterprise.description',
      'Infrastruktur terdedikasi penuh dengan audit regulasi dan dukungan 24/7.',
    ),
    features: [
      t('pages.pricing.tiers.enterprise.f1', 'Dedicated Cloud Cluster'),
      t('pages.pricing.tiers.enterprise.f2', '99.99% Uptime SLA Garansi'),
      t('pages.pricing.tiers.enterprise.f3', 'Dedicated Technical Account Manager'),
      t('pages.pricing.tiers.enterprise.f4', 'On-Premise / Hybrid Deployment'),
    ],
    cta: t('pages.pricing.tiers.enterprise.cta', 'Hubungi Sales'),
    priceClass: 'text-3xl',
  },
])
</script>
