<template>
  <div class="py-12 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
    <Breadcrumb :items="[{ name: t('pages.careers.title', 'Karir NOC') }]" />

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
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 font-mono uppercase">
          Pusat Karir & Rekrutmen
        </span>
        <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
          {{ t('pages.careers.title', 'Pusat Karir & Rekrutmen Talenta IT') }}
        </h1>
        <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
          {{ t('pages.careers.subtitle', 'Bergabunglah bersama kami membangun infrastruktur digital masa depan Indonesia.') }}
        </p>
      </div>

      <!-- Career Positions Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div
          v-for="(job, idx) in jobs"
          :key="idx"
          class="layung-panel p-8 space-y-6 flex flex-col justify-between group hover:-translate-y-1 transition-all"
        >
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-mono font-bold px-2.5 py-0.5 rounded-full bg-orange-500/10 text-orange-500">
                {{ job.type }}
              </span>
              <span class="text-xs text-muted-foreground">{{ job.location }}</span>
            </div>

            <h3 class="text-xl font-bold font-heading text-foreground">
              {{ job.title }}
            </h3>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ job.desc }}
            </p>

            <div class="pt-2 flex flex-wrap gap-1.5 font-mono text-[10px]">
              <span
                v-for="(req, rIdx) in job.requirements"
                :key="rIdx"
                class="px-2 py-0.5 rounded bg-muted text-muted-foreground"
              >
                {{ req }}
              </span>
            </div>
          </div>

          <Button
            as="router-link"
            to="/contact"
            variant="outline"
            size="sm"
            class="w-full font-bold"
          >
            Lamar Posisi Ini
          </Button>
        </div>
      </div>

      <CtaSection />
    </template>
  </div>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import { Button } from '@/modules/Layout/views/themes/layung/ui';
import { useLayungIdentity } from '../composables/useLayungIdentity';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('careers');

const jobs = [
  {
    title: 'Lead Network Operations Engineer (NOC)',
    type: 'Full-Time · Shift',
    location: 'Jakarta Cyber 1',
    desc: 'Memimpin pemantauan core router BGP, penanganan insiden fiber link, dan koordinasi eskalasi level 2/3.',
    requirements: ['CCNP / JNCIP', 'BGP & MPLS', 'Linux Admin', '3+ Thn Pengalaman'],
  },
  {
    title: 'Cyber Security Operations Analyst (SOC)',
    type: 'Full-Time',
    location: 'Jakarta / Hybrid',
    desc: 'Menganalisis anomali lalu lintas data, investigasi serangan DDoS, dan pemeliharaan aturan Next-Gen Firewall.',
    requirements: ['CEH / CompTIA CySA+', 'SIEM / Splunk', 'NGFW Fortinet', 'TCP/IP Packet Analysis'],
  },
  {
    title: 'Fiber Optic Outside Plant (OSP) Specialist',
    type: 'Full-Time · On-Site',
    location: 'Bandung / Surabaya',
    desc: 'Pengawasan instalasi rute kabel fiber last-mile, uji splicing fusi serat, dan kalibrasi OTDR lapangan.',
    requirements: ['Sertifikasi K3 FO', 'OTDR & Power Meter', 'Splicing 0.01dB', 'SIM A Aktif'],
  },
];
</script>
