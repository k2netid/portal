<template>
  <div class="min-h-screen bg-background">
    <header class="py-20 bg-gradient-to-b from-primary/10 to-background border-b border-border/50">
      <div class="container mx-auto px-4 text-center">
        <span class="text-primary font-bold tracking-wider uppercase text-sm mb-4 block">
          {{ t('pages.pricing.sectionLabel') }}
        </span>
        <h1 class="text-4xl md:text-6xl font-extrabold text-foreground mb-6">
          {{ t('pages.pricing.title') }}
        </h1>
        <p class="text-xl text-muted-foreground max-w-2xl mx-auto leading-relaxed">
          {{ t('pages.pricing.subtitle') }}
        </p>
      </div>
    </header>

    <section class="py-16">
      <div class="container mx-auto px-4">
        <div v-if="loading" class="flex justify-center py-20">
          <div class="h-10 w-10 animate-spin rounded-full border-b-2 border-primary" />
        </div>

        <template v-else-if="live && products.length > 0">
          <div v-for="product in products" :key="product.id" class="mb-16 last:mb-0">
            <div class="text-center mb-10">
              <span class="text-primary font-bold tracking-wider uppercase text-xs mb-2 block">
                {{ productSectionLabel(product.id) }}
              </span>
              <h2 class="text-2xl font-bold text-foreground">{{ product.name }}</h2>
              <p v-if="product.description" class="text-muted-foreground mt-2 max-w-2xl mx-auto">
                {{ product.description }}
              </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
              <article
                v-for="pkg in product.packages"
                :key="pkg.id"
                class="flex flex-col p-8 rounded-2xl border border-border bg-card hover:border-primary/40 transition-colors"
                :class="isFeaturedPackage(pkg) ? 'border-primary/50 ring-1 ring-primary/20' : ''"
              >
                <span
                  v-if="isFeaturedPackage(pkg)"
                  class="text-[10px] font-black uppercase tracking-widest text-primary mb-3"
                >
                  {{ t('pages.pricing.recommended') }}
                </span>
                <h3 class="text-lg font-bold text-foreground mb-4">{{ pkg.name }}</h3>
                <div class="mb-6">
                  <template v-if="pkg.price_monthly > 0">
                    <div class="text-3xl font-black text-primary">{{ formatIdr(pkg.price_monthly) }}</div>
                    <div class="text-xs text-muted-foreground uppercase tracking-widest mt-1">
                      {{ t('pages.pricing.perMonth') }}
                    </div>
                  </template>
                  <template v-else-if="pkg.price_yearly > 0">
                    <div class="text-3xl font-black text-primary">{{ formatIdr(pkg.price_yearly) }}</div>
                    <div class="text-xs text-muted-foreground uppercase tracking-widest mt-1">
                      {{ t('pages.pricing.yearlyOnly') }}
                    </div>
                  </template>
                  <template v-else>
                    <div class="text-lg font-bold text-foreground">{{ t('pages.pricing.contactForPrice') }}</div>
                  </template>
                  <div v-if="pkg.price_monthly > 0 && pkg.price_yearly > 0" class="text-sm text-muted-foreground mt-2">
                    {{ t('pages.pricing.yearly', { price: formatIdr(pkg.price_yearly) }) }}
                  </div>
                </div>
                <ul class="space-y-2 text-sm text-muted-foreground flex-1 mb-8">
                  <li>{{ t('pages.pricing.users', { n: formatLimit(pkg.user_limit) }) }}</li>
                  <li>{{ t('pages.pricing.storage', { n: formatStorage(pkg.storage_limit_mb) }) }}</li>
                  <li v-for="(feat, idx) in pkg.feature_highlights.slice(0, 6)" :key="`${pkg.id}-f-${idx}`">
                    {{ feat }}
                  </li>
                </ul>
                <router-link
                  to="/contact"
                  class="w-full text-center px-6 py-3 text-xs font-bold uppercase tracking-widest border border-border rounded-lg hover:bg-muted/50 transition-colors"
                >
                  {{ t('pages.pricing.cta') }}
                </router-link>
              </article>
            </div>
          </div>
        </template>

        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
          <article
            v-for="tier in staticTiers"
            :key="tier.key"
            class="flex flex-col p-8 rounded-2xl border border-border bg-card"
            :class="tier.featured ? 'border-primary/50 ring-1 ring-primary/20' : ''"
          >
            <span
              v-if="tier.featured"
              class="text-[10px] font-black uppercase tracking-widest text-primary mb-3"
            >
              {{ t('pages.pricing.recommended') }}
            </span>
            <h3 class="text-xl font-bold text-foreground mb-2">
              {{ t(`pages.pricing.tiers.${tier.key}.name`) }}
            </h3>
            <p class="text-sm text-muted-foreground mb-6 flex-1">
              {{ t(`pages.pricing.tiers.${tier.key}.description`) }}
            </p>
            <ul class="space-y-2 text-sm text-muted-foreground mb-8">
              <li v-for="b in ['0','1','2']" :key="b">
                • {{ t(`pages.pricing.tiers.${tier.key}.bullets.${b}`) }}
              </li>
            </ul>
            <router-link
              to="/contact"
              class="w-full text-center px-6 py-3 text-xs font-bold uppercase tracking-widest bg-primary text-primary-foreground rounded-lg hover:opacity-90"
            >
              {{ t('pages.pricing.cta') }}
            </router-link>
          </article>
        </div>

        <p class="text-center text-sm text-muted-foreground mt-12 max-w-xl mx-auto">
          {{ t('pages.pricing.footnote') }}
        </p>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Content/Layout/composables/useThemeI18n'
import { usePlatformCatalog } from '@/modules/Content/Layout/composables/usePlatformCatalog'

const { t } = useThemeI18n('janari')
const { live, products, loading } = usePlatformCatalog()

const FEATURED_PACKAGE_ID = 'hub-growth'

const isFeaturedPackage = (pkg: { id: string }): boolean => pkg.id === FEATURED_PACKAGE_ID

const productSectionLabel = (productId: string): string => {
  if (productId === 'platform') return t('pages.pricing.platformLabel')
  if (productId === 'hub') return t('pages.pricing.hubLabel')
  return t('pages.pricing.sectionLabel')
}

const staticTiers = [
  { key: 'starter' as const, featured: false },
  { key: 'growth' as const, featured: true },
  { key: 'enterprise' as const, featured: false },
]

const formatIdr = (amount: number): string => {
  if (!Number.isFinite(amount) || amount <= 0) return '—'
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount)
}
const formatLimit = (n: number): string => (n >= 999999 ? t('pages.pricing.unlimited') : String(n))
const formatStorage = (mb: number): string => (mb >= 1024 ? `${Math.round(mb / 1024)} GB` : `${mb} MB`)
</script>
