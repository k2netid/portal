<template>
  <LayungPageGate
    setting-key="enable_career"
    :title="t('pages.careers.title', 'Karir & Peluang')"
  >
    <div
      class="layung-page flex-1 flex flex-col space-y-10 sm:space-y-14 w-full py-8 sm:py-10 md:py-12 overflow-x-clip"
      data-ja-customizer-target="careers"
    >
      <!-- Hero / Header Section -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-6">
        <Breadcrumb :items="[{ name: t('pages.careers.title', 'Karir & Peluang') }]" />

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
              {{ t('pages.careers.badge', 'Peluang Karir') }}
            </span>
            <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
              {{ t('pages.careers.mainTitle', 'Berkarya Membangun Jaringan Bersama Kami') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.careers.mainSubtitle', 'Bergabunglah dengan tim engineering dan operasional kami di Bandung. Kembangkan keahlian infrastruktur internet carrier-grade, routing BGP, dan solusi enterprise.') }}
            </p>
          </div>
        </template>
      </div>

      <template v-if="!hasBuilderBlocks && !cmsBody">
        <!-- Keuntungan & Budaya Kerja -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-8">
          <div class="space-y-2">
            <h2 class="text-2xl sm:text-3xl font-bold text-foreground font-heading">
              {{ t('pages.careers.benefitsTitle', 'Mengapa Bergabung di Kami?') }}
            </h2>
            <p class="text-sm text-muted-foreground max-w-2xl">
              {{ t('pages.careers.benefitsSubtitle', 'Kami mengutamakan pertumbuhan kompetensi teknis, kesejahteraan tim, dan lingkungan kerja yang saling mendukung.') }}
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
              v-for="(benefit, idx) in companyBenefits"
              :key="idx"
              class="layung-panel p-6 space-y-3 border border-border"
            >
              <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-500 flex items-center justify-center">
                <component :is="benefit.icon" class="w-5 h-5" />
              </div>
              <h3 class="text-base font-bold text-foreground font-heading">
                {{ benefit.title }}
              </h3>
              <p class="text-xs text-muted-foreground leading-relaxed">
                {{ benefit.description }}
              </p>
            </div>
          </div>
        </section>

        <!-- Posisi Terbuka -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-8">
          <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div class="space-y-2">
              <h2 class="text-2xl sm:text-3xl font-bold text-foreground font-heading">
                {{ t('pages.careers.jobsTitle', 'Posisi yang Sedang Dibuka') }}
              </h2>
              <p class="text-sm text-muted-foreground max-w-2xl">
                {{ t('pages.careers.jobsSubtitle', 'Temukan peran yang sesuai dengan spesialisasi dan tujuan karir Anda.') }}
              </p>
            </div>
            <span class="text-xs font-mono text-muted-foreground bg-muted px-3 py-1.5 rounded-full self-start sm:self-auto">
              {{ openJobs.length }} Posisi Tersedia
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
              v-for="(job, idx) in openJobs"
              :key="idx"
              class="layung-panel p-6 sm:p-7 space-y-5 border border-border flex flex-col justify-between hover:border-sky-500/40 transition-colors"
            >
              <div class="space-y-3">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                  <span class="px-2.5 py-0.5 rounded text-[11px] font-bold font-mono uppercase bg-sky-500/10 text-sky-600 dark:text-sky-400">
                    {{ job.department }}
                  </span>
                  <div class="flex items-center gap-3 text-xs text-muted-foreground font-mono">
                    <span class="flex items-center gap-1">
                      <MapPin class="w-3.5 h-3.5" />
                      {{ job.location }}
                    </span>
                    <span>•</span>
                    <span>{{ job.type }}</span>
                  </div>
                </div>

                <h3 class="text-xl font-bold text-foreground font-heading">
                  {{ job.title }}
                </h3>

                <p class="text-xs text-muted-foreground leading-relaxed">
                  {{ job.summary }}
                </p>

                <div class="space-y-2 pt-2 border-t border-border/60">
                  <span class="text-xs font-bold text-foreground block font-mono uppercase tracking-wider">
                    Persyaratan Utama:
                  </span>
                  <ul class="space-y-1 text-xs text-foreground/85">
                    <li
                      v-for="(req, reqIdx) in job.requirements"
                      :key="reqIdx"
                      class="flex items-center gap-2"
                    >
                      <CheckCircle2 class="w-3.5 h-3.5 text-sky-500 flex-shrink-0" />
                      <span>{{ req }}</span>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="pt-4 border-t border-border/60 flex items-center justify-between">
                <span class="text-xs text-muted-foreground font-mono">
                  Pengalaman: {{ job.experience }}
                </span>
                <Button
                  as="router-link"
                  to="/contact"
                  variant="primary"
                  size="sm"
                  class="gap-1 text-xs font-bold"
                >
                  <span>Lamar Sekarang</span>
                  <ArrowRight class="w-3.5 h-3.5" />
                </Button>
              </div>
            </div>
          </div>
        </section>

        <!-- Panduan Pengiriman Lamaran -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
          <div class="layung-panel p-6 sm:p-8 space-y-4 border border-border bg-card/60">
            <h3 class="text-lg font-bold text-foreground font-heading">
              {{ t('pages.careers.howToApplyTitle', 'Cara Mengirimkan Berkas Lamaran') }}
            </h3>
            <p class="text-xs text-muted-foreground leading-relaxed max-w-3xl">
              Kirimkan berkas CV terbaru beserta portofolio/sertifikasi teknis (jika ada) melalui formulir kontak kami atau email ke HRD dengan format subjek:
              <code class="bg-muted px-2 py-0.5 rounded text-foreground font-mono font-bold text-xs ml-1">
                [LAMARAN] - [Nama Anda] - [Posisi yang Dilamar]
              </code>. Tim rekrutmen kami akan meninjau dan menghubungi kandidat yang sesuai dalam waktu 3–5 hari kerja.
            </p>
            <div class="pt-2">
              <Button
                as="router-link"
                to="/contact"
                variant="outline"
                size="md"
                class="font-bold"
              >
                {{ t('pages.careers.contactCta', 'Buka Formulir Pengiriman Lamaran') }}
              </Button>
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
  Award,
  BookOpen,
  HeartHandshake,
  Shield,
  MapPin,
  CheckCircle2,
  ArrowRight,
} from 'lucide-vue-next';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('careers');

const companyBenefits = computed(() => [
  {
    icon: Award,
    title: t('pages.careers.b1Title', 'Sertifikasi Didukung'),
    description: t('pages.careers.b1Desc', 'Subsidi dan fasilitas ujian sertifikasi industri (MikroTik MTCNA/MTCRE, Cisco CCNA, dan FO Splicer).'),
  },
  {
    icon: BookOpen,
    title: t('pages.careers.b2Title', 'Hands-On Real Infra'),
    description: t('pages.careers.b2Desc', 'Belajar dan beroperasi langsung dengan perangkat enterprise, multi-homed BGP AS153992, dan core OLT.'),
  },
  {
    icon: Shield,
    title: t('pages.careers.b3Title', 'Jaminan Kesejahteraan'),
    description: t('pages.careers.b3Desc', 'Paket BPJS Kesehatan & Ketenagakerjaan lengkap, tunjangan dinas luar, serta perlengkapan K3 standar.'),
  },
  {
    icon: HeartHandshake,
    title: t('pages.careers.b4Title', 'Kultur Kolaboratif'),
    description: t('pages.careers.b4Desc', 'Lingkungan kerja tanpa birokrasi kaku, mengedepankan keterbukaan ide, dan saling berbagi ilmu teknis.'),
  },
]);

const openJobs = computed(() => [
  {
    title: t('pages.careers.job1Title', 'NOC Network Engineer (L2)'),
    department: 'NOC & Infrastructure',
    location: 'Bandung',
    type: 'Full-time (Shift)',
    experience: 'Min. 1-2 Tahun',
    summary: t('pages.careers.job1Summary', 'Bertanggung jawab atas pemantauan jaringan 24/7, mitigasi gangguan routing BGP/OSPF, dan koordinasi upstream link.'),
    requirements: [
      'Memahami TCP/IP, Subnetting, VLAN, OSPF, dan BGP routing',
      'Pengalaman konfigurasi RouterOS MikroTik & switch manageable',
      'Memiliki sertifikasi MTCNA / CCNA menjadi nilai tambah',
    ],
  },
  {
    title: t('pages.careers.job2Title', 'Fiber Optic Field Technician'),
    department: 'Field Engineering',
    location: 'Bandung / Garut',
    type: 'Full-time',
    experience: 'Min. 1 Tahun',
    summary: t('pages.careers.job2Summary', 'Menangani penarikan kabel optik udara/bawah tanah, terminasi OTB/ODP, dan penyambungan menggunakan fusion splicer.'),
    requirements: [
      'Terbiasa mengoperasikan Fusion Splicer dan alat ukur OTDR/OPM',
      'Siap mobilisasi penanganan gangguan jaringan kabel putus',
      'Memiliki SIM C / SIM A aktif',
    ],
  },
  {
    title: t('pages.careers.job3Title', 'B2B Enterprise Account Executive'),
    department: 'Commercial Sales',
    location: 'Bandung',
    type: 'Full-time',
    experience: 'Min. 2 Tahun',
    summary: t('pages.careers.job3Summary', 'Mengembangkan portofolio klien korporasi, kantor pemerintahan, perhotelan, dan kawasan industri di Jawa Barat.'),
    requirements: [
      'Pengalaman penjualan solusi IT, internet dedicated, atau telekomunikasi',
      'Kemampuan presentasi proposal dan negosiasi kontrak korporat',
      'Memiliki networking luas di area Bandung dan sekitarnya',
    ],
  },
  {
    title: t('pages.careers.job4Title', 'Fullstack / Frontend Engineer'),
    department: 'Software & Platform',
    location: 'Bandung (Hybrid)',
    type: 'Full-time',
    experience: 'Min. 2 Tahun',
    summary: t('pages.careers.job4Summary', 'Mengembangkan portal pelanggan, customizer tema, dan integrasi API billing/monitoring internal Kami.'),
    requirements: [
      'Mahir Vue.js 3 (Composition API), TypeScript, dan Tailwind CSS',
      'Familiar dengan ekosistem PHP/Laravel atau REST API integration',
      'Peduli pada clean architecture, testing (Vitest/Playwright), & a11y',
    ],
  },
]);
</script>
