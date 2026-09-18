<template>
  <SareupnaPageGate
    setting-key="enable_about"
    :title="t('pages.about.title', 'Tentang Platform')"
  >
    <div
      class="sareupna-page flex-1 w-full min-h-0"
      data-ja-customizer-target="about"
    >
      <header class="py-20 md:py-28 bg-gradient-to-b from-card to-background border-b border-border/40 relative overflow-hidden">
        <div class="sareupna-ambient-mesh w-96 h-96 bg-primary/20 top-0 left-1/4 -translate-y-1/2" />
        <div class="container mx-auto px-4 text-center relative z-10">
          <span class="text-xs font-mono uppercase tracking-widest text-primary font-bold mb-4 block">
            {{ t('pages.about.sectionLabel', 'Platform Identity') }}
          </span>
          <h1 class="text-4xl md:text-6xl font-heading font-black uppercase tracking-tight text-foreground mb-6">
            {{ t('pages.about.title', 'Tentang Platform') }}
          </h1>
          <p class="text-base md:text-lg text-muted-foreground max-w-2xl mx-auto leading-relaxed">
            {{ t('pages.about.subtitle', 'Visi teknologi dan arsitektur cloud untuk control plane modern.') }}
          </p>
        </div>
      </header>

      <PluginSlot name="after_hero" class="w-full" />

      <section class="py-14 md:py-20 bg-background">
        <div class="container mx-auto px-4">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 md:gap-14 items-start">
            <div class="space-y-6">
              <h2 class="text-2xl md:text-3xl font-heading font-bold text-foreground">
                {{ t('pages.about.missionTitle', 'Misi Rekayasa') }}
              </h2>
              <p class="text-muted-foreground text-base md:text-lg leading-relaxed">
                {{
                  t(
                    'pages.about.missionP1',
                    'Kami membangun control plane modular agar tim produk dapat merilis, mengkurasi, dan mengoperasikan portal tanpa mengorbankan isolasi tema.',
                  )
                }}
              </p>
              <p class="text-muted-foreground text-base md:text-lg leading-relaxed">
                {{
                  t(
                    'pages.about.missionP2',
                    'Sareupna menampilkan kontrak CMS yang sama dengan tema lain — About, Contact Reach, Blog, menus — dengan chrome cyber khas developer platform.',
                  )
                }}
              </p>
              <router-link
                to="/solutions"
                class="inline-flex text-sm font-bold text-primary hover:underline font-mono uppercase tracking-wider"
              >
                {{ t('pages.about.solutionsCta', 'Lihat Solusi Platform') }} →
              </router-link>
            </div>

            <div class="rounded-2xl border border-border/60 bg-card/60 p-6 md:p-8 space-y-6">
              <div class="space-y-1">
                <p class="text-xs font-mono uppercase tracking-widest text-primary font-bold">
                  {{ t('pages.about.identityLabel', 'Operator') }}
                </p>
                <p class="text-xl font-heading font-black text-foreground">
                  {{ displaySiteName }}
                </p>
                <p
                  v-if="displaySiteDescription"
                  class="text-sm text-muted-foreground leading-relaxed"
                >
                  {{ displaySiteDescription }}
                </p>
              </div>

              <dl class="space-y-4 text-sm">
                <div v-if="displayAddress" class="space-y-1">
                  <dt class="font-mono text-[11px] uppercase tracking-wider text-muted-foreground">
                    {{ t('pages.contact.labelAddress', 'Alamat') }}
                  </dt>
                  <dd class="text-foreground leading-relaxed">{{ displayAddress }}</dd>
                </div>
                <div v-if="displayEmail" class="space-y-1">
                  <dt class="font-mono text-[11px] uppercase tracking-wider text-muted-foreground">
                    {{ t('pages.contact.labelEmail', 'Email') }}
                  </dt>
                  <dd>
                    <a
                      :href="`mailto:${displayEmail}`"
                      class="text-primary hover:underline"
                    >{{ displayEmail }}</a>
                  </dd>
                </div>
                <div v-if="displayPhone" class="space-y-1">
                  <dt class="font-mono text-[11px] uppercase tracking-wider text-muted-foreground">
                    {{ t('pages.contact.labelPhone', 'Telepon') }}
                  </dt>
                  <dd>
                    <a
                      v-if="phoneDialHref"
                      :href="phoneDialHref"
                      class="text-primary hover:underline"
                    >{{ displayPhone }}</a>
                    <span v-else>{{ displayPhone }}</span>
                  </dd>
                </div>
              </dl>

              <router-link
                to="/contact"
                class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-primary-foreground hover:opacity-90 transition-opacity"
              >
                {{ t('pages.about.contactCta', 'Hubungi Tim Arsitek') }}
              </router-link>
            </div>
          </div>
        </div>
      </section>

      <CtaSection />
    </div>
  </SareupnaPageGate>
</template>

<script setup lang="ts">
import SareupnaPageGate from '../components/shared/SareupnaPageGate.vue'
import CtaSection from '../components/sections/CtaSection.vue'
import PluginSlot from '@/shared/components/PluginSlot.vue'
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'
import { useSareupnaIdentity } from '../composables/useSareupnaIdentity'

const { t } = useThemeI18n('sareupna')
const {
  displaySiteName,
  displaySiteDescription,
  displayAddress,
  displayEmail,
  displayPhone,
  phoneDialHref,
} = useSareupnaIdentity()
</script>
