<template>
  <LayungPageGate
    setting-key="enable_tim"
    :title="t('pages.team.title', 'Tim Operasional')"
  >
    <div
      class="layung-page flex-1 flex flex-col space-y-10 sm:space-y-14 w-full py-8 sm:py-10 md:py-12 overflow-x-clip"
      data-ja-customizer-target="team"
    >
      <!-- Hero / Header Section -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-6">
        <Breadcrumb :items="[{ name: t('pages.team.title', 'Tim Operasional') }]" />

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
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 font-mono uppercase">
              {{ t('pages.team.badge', 'Struktur Operasional') }}
            </span>
            <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
              {{ t('pages.team.mainTitle', 'Tim & Keandalan Operasional K2NET') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.team.mainSubtitle', 'Didukung teknisi jaringan tersertifikasi, pemantauan 24/7 Network Operations Center (NOC), dan tim lapangan tanggap darurat di Bandung dan Jawa Barat.') }}
            </p>
          </div>
        </template>
      </div>

      <template v-if="!hasBuilderBlocks && !cmsBody">
        <!-- 4 Pilar Operasional -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-8">
          <div class="space-y-2">
            <h2 class="text-2xl sm:text-3xl font-bold text-foreground font-heading">
              {{ t('pages.team.pillarsTitle', '4 Pilar Operasional Layanan') }}
            </h2>
            <p class="text-sm text-muted-foreground max-w-2xl">
              {{ t('pages.team.pillarsSubtitle', 'Setiap aspek konektivitas dan sistem ditangani divisi spesialis untuk menjamin SLA hingga 99.5%.') }}
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
              v-for="(pillar, idx) in operationalPillars"
              :key="idx"
              class="layung-panel p-6 sm:p-7 space-y-4 border border-border flex flex-col justify-between hover:border-sky-500/40 transition-colors"
            >
              <div class="space-y-3">
                <div class="w-12 h-12 rounded-xl bg-sky-500/10 text-sky-500 dark:text-sky-400 flex items-center justify-center">
                  <component :is="pillar.icon" class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-bold text-foreground font-heading">
                  {{ pillar.title }}
                </h3>
                <p class="text-xs text-muted-foreground leading-relaxed">
                  {{ pillar.description }}
                </p>
              </div>
              <ul class="space-y-1.5 pt-2 border-t border-border/60 text-xs text-foreground/80 font-medium">
                <li
                  v-for="(item, itemIdx) in pillar.points"
                  :key="itemIdx"
                  class="flex items-center gap-1.5"
                >
                  <CheckCircle2 class="w-3.5 h-3.5 text-sky-500 flex-shrink-0" />
                  <span>{{ item }}</span>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Matriks Eskalasi & Waktu Tanggap -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-6">
          <div class="space-y-2">
            <h2 class="text-2xl sm:text-3xl font-bold text-foreground font-heading">
              {{ t('pages.team.escalationTitle', 'Matriks Eskalasi & Waktu Tanggap') }}
            </h2>
            <p class="text-sm text-muted-foreground max-w-2xl">
              {{ t('pages.team.escalationSubtitle', 'Alur penanganan insiden bertingkat untuk menjamin penanganan tuntas sesuai urgensi teknis.') }}
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
              v-for="(tier, idx) in escalationTiers"
              :key="idx"
              class="layung-panel p-6 space-y-4 border border-border"
            >
              <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 rounded text-xs font-bold font-mono uppercase bg-muted text-foreground">
                  {{ tier.tier }}
                </span>
                <span class="text-xs font-mono text-sky-600 dark:text-sky-400 font-semibold">
                  {{ tier.responseTarget }}
                </span>
              </div>
              <h3 class="text-base font-bold text-foreground font-heading">
                {{ tier.name }}
              </h3>
              <p class="text-xs text-muted-foreground leading-relaxed">
                {{ tier.description }}
              </p>
              <div class="text-xs font-mono text-muted-foreground pt-2 border-t border-border/60">
                Scope: <span class="text-foreground">{{ tier.scope }}</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Lokasi Kantor & Hub Operasional -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-6">
          <div class="space-y-2">
            <h2 class="text-2xl sm:text-3xl font-bold text-foreground font-heading">
              {{ t('pages.team.hubsTitle', 'Pusat Operasional & Lapangan') }}
            </h2>
            <p class="text-sm text-muted-foreground max-w-2xl">
              {{ t('pages.team.hubsSubtitle', 'Kantor koordinasi dan titik persebaran armada teknis K2NET.') }}
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="layung-panel p-6 sm:p-8 space-y-4 border border-border">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-sky-500/10 text-sky-500 flex items-center justify-center">
                  <MapPin class="w-5 h-5" />
                </div>
                <div>
                  <h3 class="text-lg font-bold text-foreground font-heading">
                    {{ t('pages.team.bandungTitle', 'Kantor Pusat & NOC Bandung') }}
                  </h3>
                  <span class="text-xs font-mono text-sky-600 dark:text-sky-400">Hub Utama</span>
                </div>
              </div>
              <p class="text-xs text-muted-foreground leading-relaxed">
                Jl. Cikutra No. 62, Sukapada, Kec. Cibeunying Kidul, Kota Bandung, Jawa Barat 40125.
                Pusat monitoring NOC 24/7, routing BGP mandiri AS153992, dan administrasi pelanggan korporasi.
              </p>
              <div class="pt-2">
                <Button
                  as="router-link"
                  to="/contact"
                  variant="outline"
                  size="sm"
                  class="gap-1.5 text-xs font-semibold"
                >
                  <span>{{ t('pages.team.seeContact', 'Lihat kontak & peta') }}</span>
                  <ArrowRight class="w-3.5 h-3.5" />
                </Button>
              </div>
            </div>

            <div class="layung-panel p-6 sm:p-8 space-y-4 border border-border">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                  <MapPin class="w-5 h-5" />
                </div>
                <div>
                  <h3 class="text-lg font-bold text-foreground font-heading">
                    {{ t('pages.team.garutTitle', 'Kantor Operasional & PoP Garut') }}
                  </h3>
                  <span class="text-xs font-mono text-emerald-600 dark:text-emerald-400">Point of Presence</span>
                </div>
              </div>
              <p class="text-xs text-muted-foreground leading-relaxed">
                Pusat distribusi jaringan dan dispatch armada teknisi lapangan untuk wilayah Garut dan Priangan Timur.
                Siap untuk instalasi cepat, maintenance kabel optik, dan penanganan gangguan lokal.
              </p>
              <div class="pt-2">
                <Button
                  as="router-link"
                  to="/contact"
                  variant="outline"
                  size="sm"
                  class="gap-1.5 text-xs font-semibold"
                >
                  <span>{{ t('pages.team.seeContact', 'Lihat kontak & peta') }}</span>
                  <ArrowRight class="w-3.5 h-3.5" />
                </Button>
              </div>
            </div>
          </div>
        </section>

        <!-- CTA Section -->
        <CtaSection />
      </template>
    </div>
  </LayungPageGate>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import { useLayungIdentity } from '../composables/useLayungIdentity';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import LayungPageGate from '../components/shared/LayungPageGate.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import { Button } from '@/modules/Layout/views/themes/layung/ui';
import {
  Server,
  Radio,
  Wrench,
  Headphones,
  MapPin,
  CheckCircle2,
  ArrowRight,
} from 'lucide-vue-next';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('tim');

const operationalPillars = computed(() => [
  {
    icon: Server,
    title: t('pages.team.p1Title', 'Network Operations Center (NOC)'),
    description: t('pages.team.p1Desc', 'Pengawasan proaktif 24 jam sehari terhadap utilisasi bandwidth, BGP routing, dan stabilitas backbone.'),
    points: [
      t('pages.team.p1Pt1', 'Monitoring 24/7/365'),
      t('pages.team.p1Pt2', 'Multi-upstream BGP AS153992'),
      t('pages.team.p1Pt3', 'DDoS mitigation & alerting'),
    ],
  },
  {
    icon: Wrench,
    title: t('pages.team.p2Title', 'Field & Fiber Engineering'),
    description: t('pages.team.p2Desc', 'Tim instalasi dan restorasi kabel optik jalur udara dan bawah tanah dengan peralatan fusion splicer presisi tinggi.'),
    points: [
      t('pages.team.p2Pt1', 'Emergency dispatch 2-4 jam'),
      t('pages.team.p2Pt2', 'Penyambungan OTDR terkalibrasi'),
      t('pages.team.p2Pt3', 'Pemeliharaan jalur rute FO'),
    ],
  },
  {
    icon: Radio,
    title: t('pages.team.p3Title', 'Solutions Architecture'),
    description: t('pages.team.p3Desc', 'Perancangan topologi jaringan bisnis, instalasi router MikroTik/Cisco, SD-WAN, dan managed Wi-Fi kantor.'),
    points: [
      t('pages.team.p3Pt1', 'Desain arsitektur jaringan'),
      t('pages.team.p3Pt2', 'Multi-WAN failover & QoS'),
      t('pages.team.p3Pt3', 'Audit keamanan perimeter'),
    ],
  },
  {
    icon: Headphones,
    title: t('pages.team.p4Title', 'Customer Experience & Desk'),
    description: t('pages.team.p4Desc', 'Single Point of Contact (SPOC) untuk eskalasi tiket gangguan, konsultasi paket, dan administrasi SLA pelanggan.'),
    points: [
      t('pages.team.p4Pt1', 'Ticketing terintegrasi'),
      t('pages.team.p4Pt2', 'Update status periodik insiden'),
      t('pages.team.p4Pt3', 'Customer success & reporting'),
    ],
  },
]);

const escalationTiers = computed(() => [
  {
    tier: 'Tier 1',
    name: t('pages.team.t1Name', 'Customer Helpdesk & Dispatcher'),
    responseTarget: '< 15 Menit',
    description: t('pages.team.t1Desc', 'Penerimaan tiket, verifikasi identitas layanan, panduan diagnostik dasar, dan penugasan awal.'),
    scope: t('pages.team.t1Scope', 'Triase kendala & cek status OLT/CPE'),
  },
  {
    tier: 'Tier 2',
    name: t('pages.team.t2Name', 'NOC Engineer & Core Admin'),
    responseTarget: '< 30 Menit',
    description: t('pages.team.t2Desc', 'Analisis rute BGP, isolasi segmen trunking VLAN, investigasi latency, dan rekonfigurasi perangkat jaringan.'),
    scope: t('pages.team.t2Scope', 'Routing core, backbone, & firewall'),
  },
  {
    tier: 'Tier 3',
    name: t('pages.team.t3Name', 'Field Specialist & Solutions Lead'),
    responseTarget: '< 2 Jam On-site',
    description: t('pages.team.t3Desc', 'Penanganan kendala kabel optik putus di lapangan, penggantian modul hardware, dan asistensi vendor upstream.'),
    scope: t('pages.team.t3Scope', 'Restorasi kabel optik & hardware core'),
  },
]);
</script>
