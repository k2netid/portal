<template>
  <div class="py-10 md:py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
    <Breadcrumb :items="[{ name: t('pages.about.title', 'Tentang Kami') }]" />

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
      <!-- Page Header -->
      <div
        id="profil"
        class="scroll-mt-28 space-y-4 max-w-3xl"
      >
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 font-mono uppercase">
          {{ t('pages.about.badge', 'Profil Perusahaan & Infrastruktur') }}
        </span>
        <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
          {{ t('pages.about.storyTitle', 'Dedikasi Menghubungkan Indonesia dengan Kecepatan Cahaya') }}
        </h1>
        <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
          {{ t('pages.about.subtitle', 'Mengenal Layung Network: Penyedia konektivitas serat optik berstandar global dengan komitmen transparansi dan keandalan tinggi.') }}
        </p>
      </div>

      <!-- Philosophy & Core Values -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
        <div class="space-y-6 text-sm text-muted-foreground leading-relaxed">
          <p>
            {{ t('pages.about.storyText', 'Berakar dari filosofi cahaya Layung yang menyinari langit dengan keindahan dan kehangatan fajar hingga senja, kami membangun jaringan serat optik mandiri yang menjangkau pusat-pusat bisnis terpenting.') }}
          </p>
          <p>
            Sejak didirikan, kami mendedikasikan diri untuk menyediakan transmisi data berkecepatan tinggi tanpa kompromi (*Zero Packet Loss*) dengan rasio simetris murni 1:1, didukung oleh tim Network Operations Center (NOC) bersertifikasi internasional.
          </p>

          <div class="grid grid-cols-2 gap-4 pt-4 font-mono text-xs text-foreground">
            <div class="p-4 rounded-xl bg-card border border-border space-y-1">
              <span class="text-muted-foreground block text-[10px]">Nomor ASN BGP</span>
              <strong class="text-orange-500 text-sm font-bold">{{ displayAsn }}</strong>
            </div>
            <div class="p-4 rounded-xl bg-card border border-border space-y-1">
              <span class="text-muted-foreground block text-[10px]">Jaminan Uptime</span>
              <strong class="text-emerald-500 text-sm font-bold">{{ displaySla }}</strong>
            </div>
          </div>
        </div>

        <div
          id="colocation"
          class="scroll-mt-28 layung-panel p-8 bg-slate-950 text-white border border-slate-800 space-y-6"
        >
          <h3 class="text-xl font-bold font-heading text-white flex items-center gap-2">
            <Server class="w-5 h-5 text-cyan-400" />
            <span>{{ t('pages.about.datacenterTitle', 'Data Center & Point of Presence (PoP)') }}</span>
          </h3>
          <ul class="space-y-3.5 text-xs text-slate-300 font-mono">
            <li class="flex items-center justify-between pb-2 border-b border-slate-800">
              <span>PoP Cyber 1 Tower, Kuningan</span>
              <span class="text-emerald-400 font-bold">100G Active</span>
            </li>
            <li class="flex items-center justify-between pb-2 border-b border-slate-800">
              <span>PoP IDC Duren Tiga 3D, Jakarta</span>
              <span class="text-emerald-400 font-bold">100G Active</span>
            </li>
            <li class="flex items-center justify-between pb-2 border-b border-slate-800">
              <span>PoP APJII Gedung Cyber, Lt. 7</span>
              <span class="text-emerald-400 font-bold">100G Active</span>
            </li>
            <li class="flex items-center justify-between">
              <span>PoP Equinix SG1, Singapore</span>
              <span class="text-cyan-400 font-bold">40G Direct</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- CTA Quote -->
      <CtaSection />
    </template>
  </div>
</template>

<script setup lang="ts">
import { Server } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import { useLayungIdentity } from '../composables/useLayungIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';

const { t } = useThemeI18n('layung');
const { displayCompanyName, displayAsn, displaySla } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('about');

useThemeHashScroll(128);
</script>
