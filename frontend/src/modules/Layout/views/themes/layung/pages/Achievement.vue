<template>
  <LayungPageGate
    setting-key="enable_achievement"
    :title="t('pages.achievements.title', 'SLA & Sertifikasi')"
  >
  <div
    class="layung-page flex-1 flex flex-col space-y-8 sm:space-y-12 w-full py-8 sm:py-10 md:py-12 overflow-x-clip"
    data-ja-customizer-target="achievement"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-6">
      <Breadcrumb :items="[{ name: t('pages.achievements.title', 'SLA & Sertifikasi') }]" />

      <template v-if="hasBuilderBlocks">
        <BlockRenderer
          :blocks="builderBlocks"
          :context="{ post: pageData, site: { name: displayCompanyName } }"
        />
      </template>

      <template v-else-if="cmsBody">
        <div class="prose dark:prose-invert max-w-none text-muted-foreground leading-relaxed">
          <ThemeSafeHtml :html="cmsBody" />
        </div>
      </template>

      <template v-else>
        <div class="space-y-4 max-w-3xl">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-mono uppercase">
            {{ t('sla.badge', 'SLA berbasis kontrak') }}
          </span>
          <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
            {{ t('pages.achievements.title', 'SLA & Komitmen Layanan') }}
          </h1>
          <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
            {{ t('pages.achievements.subtitle', 'Anggota korporat IDNIC, operasional BGP mandiri AS153992, dan komitmen SLA sesuai kontrak layanan.') }}
          </p>
        </div>
      </template>
    </div>

    <template v-if="!hasBuilderBlocks && !cmsBody">
      <!-- Sla Guarantee Section (handles its own max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full) -->
      <SlaGuaranteeSection />

      <!-- Additional Certification Panels -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="layung-panel p-8 space-y-4 border border-border">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
              <Award class="w-6 h-6" />
            </div>
            <h3 class="text-xl font-bold text-foreground font-heading">
              {{ t('pages.achievements.idnicTitle', 'Anggota Resmi IDNIC') }}
            </h3>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ t('pages.achievements.idnicDesc', 'Terdaftar resmi sebagai anggota IDNIC (APJII) dengan alokasi blok IP Address publik mandiri untuk kebutuhan routing BGP enterprise.') }}
            </p>
          </div>

          <div class="layung-panel p-8 space-y-4 border border-border">
            <div class="w-12 h-12 rounded-xl bg-sky-500/10 text-sky-500 flex items-center justify-center">
              <Globe class="w-6 h-6" />
            </div>
            <h3 class="text-xl font-bold text-foreground font-heading">
              {{ t('pages.achievements.bgpTitle', 'Autonomous System AS153992') }}
            </h3>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ t('pages.achievements.bgpDesc', 'Mengoperasikan Autonomous System Number (ASN) mandiri untuk interkoneksi langsung (peering) ke IIX, OpenIXP, dan upstream tier-1 global.') }}
            </p>
          </div>

          <div class="layung-panel p-8 space-y-4 border border-border">
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
              <ShieldCheck class="w-6 h-6" />
            </div>
            <h3 class="text-xl font-bold text-foreground font-heading">
              {{ t('pages.achievements.contractTitle', 'Kontrak Berbasis SLA') }}
            </h3>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ t('pages.achievements.contractDesc', 'Setiap komitmen uptime, jendela pemeliharaan, serta skema eskalasi tiket darurat diatur secara transparan dalam Service Level Agreement resmi.') }}
            </p>
          </div>
        </div>
      </div>
    </template>
  </div>
  </LayungPageGate>
</template>

<script setup lang="ts">
import { Award, Globe, ShieldCheck } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import LayungPageGate from '../components/shared/LayungPageGate.vue';
import SlaGuaranteeSection from '../components/sections/SlaGuaranteeSection.vue';
import { useLayungIdentity } from '../composables/useLayungIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('achievement');

useThemeHashScroll(72);
</script>
