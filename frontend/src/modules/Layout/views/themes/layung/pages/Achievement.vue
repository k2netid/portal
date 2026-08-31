<template>
  <div class="py-10 md:py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
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
          Bukti Keandalan & Standar Mutu
        </span>
        <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
          {{ t('pages.achievements.title', 'Pencapaian SLA & Rekam Jejak Keandalan') }}
        </h1>
        <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
          {{ t('pages.achievements.subtitle', 'Sertifikasi ISO 27001, ISO 9001, dan rekor uptime 99.999% selama lebih dari 5 tahun berturut-turut.') }}
        </p>
      </div>

      <!-- SLA Guarantee Details -->
      <SlaGuaranteeSection />

      <!-- ISO Certifications Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="layung-panel p-8 space-y-4 border border-border">
          <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
            <Award class="w-6 h-6" />
          </div>
          <h3 class="text-xl font-bold text-foreground font-heading">
            ISO / IEC 27001:2022
          </h3>
          <p class="text-xs text-muted-foreground leading-relaxed">
            Standar internasional Sistem Manajemen Keamanan Informasi (ISMS) yang memastikan data jaringan klien terproteksi ketat.
          </p>
        </div>

        <div class="layung-panel p-8 space-y-4 border border-border">
          <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
            <Award class="w-6 h-6" />
          </div>
          <h3 class="text-xl font-bold text-foreground font-heading">
            ISO 9001:2015
          </h3>
          <p class="text-xs text-muted-foreground leading-relaxed">
            Sistem Manajemen Mutu berstandar global dalam penyediaan instalasi fiber optik dan respon operasional tiket NOC.
          </p>
        </div>

        <div class="layung-panel p-8 space-y-4 border border-border">
          <div class="w-12 h-12 rounded-xl bg-orange-500/10 text-orange-500 flex items-center justify-center">
            <Award class="w-6 h-6" />
          </div>
          <h3 class="text-xl font-bold text-foreground font-heading">
            APJII & BGP Certified
          </h3>
          <p class="text-xs text-muted-foreground leading-relaxed">
            Anggota resmi Asosiasi Penyelenggara Jasa Internet Indonesia dengan otorisasi BGP routing mandiri.
          </p>
        </div>
      </div>

      <CtaSection />
    </template>
  </div>
</template>

<script setup lang="ts">
import { Award } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import SlaGuaranteeSection from '../components/sections/SlaGuaranteeSection.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import { useLayungIdentity } from '../composables/useLayungIdentity';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('achievement');
</script>
