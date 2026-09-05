<template>
  <section
    id="calculator"
    data-ja-customizer-target="calculator"
    class="scroll-mt-24 my-6 w-full max-w-full overflow-hidden relative z-10"
  >
    <span id="simulator" class="sr-only">Simulator</span>

    <!-- Single Unified Main Card (No double wrapper, 100% width matching top section) -->
    <div class="relative overflow-hidden w-full max-w-full bg-slate-950 text-white border border-slate-800 rounded-2xl sm:rounded-3xl p-4 sm:p-8 lg:p-10 shadow-2xl space-y-6 sm:space-y-8">
      <!-- Glow ambient background inside the card -->
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none" />
      <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl pointer-events-none" />

      <!-- 1. Card header -->
      <div class="space-y-3 relative z-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
          <span class="inline-flex items-center self-start gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/15 text-cyan-400 border border-cyan-500/30 font-mono uppercase tracking-wider">
            <Sparkles class="w-3.5 h-3.5 shrink-0" />
            <span>{{ t('calculator.badge', 'Simulator Bandwidth Kami') }}</span>
          </span>
          <div class="inline-flex items-center self-start sm:self-auto gap-1.5 text-[10px] sm:text-[11px] font-mono text-slate-400 bg-slate-900/90 px-2.5 py-1 rounded-lg border border-slate-800 max-w-full">
            <BarChart2 class="w-3.5 h-3.5 text-cyan-400 shrink-0" />
            <span class="truncate">{{ t('calculator.methodology', 'Estimasi internal Kami') }}</span>
          </div>
        </div>

        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black font-heading tracking-tight text-white">
          {{ t('calculator.title', 'Hitung Kebutuhan Bandwidth & Rekomendasi Paket') }}
        </h2>
        <p class="text-slate-400 text-xs sm:text-sm leading-relaxed max-w-3xl">
          {{ t('calculator.subtitle', 'Estimasi internal Kami berdasarkan jumlah perangkat, profil beban, rasio pemakaian bersamaan, dan cadangan 25%. Hasil ini bukan quotation dan bukan standar merek pihak ketiga.') }}
        </p>
      </div>

      <!-- 2. Interactive Input Controls -->
      <div class="space-y-6 relative z-10 pt-2 border-t border-slate-800/80">
        <!-- Step 1: Segment Filter -->
        <div class="space-y-2.5">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">
            <span>1. Pilih Segmen Lingkungan:</span>
            <span class="text-cyan-400 font-normal lowercase text-[11px] sm:text-xs">{{ currentSegmentNote }}</span>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <button
              v-for="seg in segments"
              :key="seg.id"
              type="button"
              class="px-3 py-2.5 rounded-xl border text-xs font-semibold flex items-center justify-center gap-1.5 sm:gap-2 transition-all"
              :class="activeSegment === seg.id
                ? 'bg-cyan-500/20 border-cyan-400 text-cyan-300 font-bold shadow-sm shadow-cyan-500/20'
                : 'bg-slate-900/70 border-slate-800 text-slate-400 hover:border-slate-700 hover:text-slate-200'"
              @click="setSegment(seg.id)"
            >
              <component
                :is="seg.icon"
                class="w-4 h-4 shrink-0"
              />
              <span class="truncate">{{ seg.label }}</span>
            </button>
          </div>
        </div>

        <!-- Step 2: Device Slider & Presets -->
        <div class="space-y-3 pt-2 border-t border-slate-800/60">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">
              2. Estimasi Jumlah Perangkat Terhubung:
            </label>
            <div class="inline-flex items-center self-start sm:self-auto gap-1.5 px-3 py-1 rounded-lg bg-cyan-500/10 border border-cyan-500/30 text-cyan-300 font-mono font-bold text-sm">
              <Users class="w-4 h-4 shrink-0" />
              <span>{{ userCount }} Perangkat</span>
            </div>
          </div>

          <div class="space-y-1.5">
            <input
              v-model.number="userCount"
              type="range"
              :min="sliderMin"
              :max="sliderMax"
              :step="sliderStep"
              class="w-full accent-cyan-400 bg-slate-900 h-2.5 rounded-lg cursor-pointer transition-all"
            >
            <div class="flex justify-between text-[10px] sm:text-[11px] text-slate-500 font-mono">
              <span>Min: {{ sliderMin }} Unit</span>
              <span>Rata-rata: {{ Math.round((sliderMin + sliderMax) / 2) }}</span>
              <span>Max: {{ sliderMax }}+ Unit</span>
            </div>
          </div>

          <!-- Quick Presets -->
          <div class="flex flex-wrap items-center gap-1.5 pt-1">
            <span class="text-[10px] text-slate-500 font-mono uppercase tracking-wider mr-1 shrink-0">Preset Cepat:</span>
            <button
              v-for="p in currentPresets"
              :key="p.count"
              type="button"
              class="px-2 py-1 rounded-md text-[10px] sm:text-[11px] font-mono border transition-all text-center"
              :class="userCount === p.count
                ? 'bg-cyan-500 text-slate-950 font-bold border-cyan-400'
                : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700 hover:text-slate-300'"
              @click="userCount = p.count"
            >
              {{ p.label }}
            </button>
          </div>
        </div>

        <!-- Step 3: Workload profile (internal assumptions) -->
        <div class="space-y-2.5 pt-2 border-t border-slate-800/60">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">
            <span>3. Karakteristik Beban Aktivitas Utama:</span>
            <span class="text-slate-400 font-normal text-[11px] sm:text-xs">{{ t('calculator.workloadHint', 'Asumsi beban') }}: {{ currentWorkloadThroughput }}</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 text-xs">
            <button
              v-for="w in workloads"
              :key="w.id"
              type="button"
              class="p-3 rounded-xl border text-left font-medium transition-all flex flex-col justify-between"
              :class="selectedWorkload === w.id
                ? 'bg-cyan-500/15 border-cyan-400 text-cyan-300 font-bold shadow-sm'
                : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:border-slate-700 hover:text-slate-300'"
              @click="selectedWorkload = w.id"
            >
              <div class="flex items-center gap-2">
                <component
                  :is="w.icon"
                  class="w-4 h-4 shrink-0 text-cyan-400"
                />
                <span class="text-white font-semibold">{{ w.label }}</span>
              </div>
              <div class="text-[11px] text-slate-500 mt-1.5 leading-tight">{{ w.sub }}</div>
            </button>
          </div>
        </div>
      </div>

      <!-- 3. Estimate summary -->
      <div class="bg-slate-900/90 border border-slate-800/80 rounded-2xl p-3 sm:p-4 grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-3 text-xs font-mono overflow-hidden">
        <div>
          <span class="text-slate-500 block text-[10px] uppercase">{{ t('calculator.loadLabel', 'Beban per perangkat') }}</span>
          <strong class="text-cyan-400 text-xs sm:text-sm">{{ currentWorkloadRate }} Mbps</strong>
        </div>
        <div>
          <span class="text-slate-500 block text-[10px] uppercase">{{ t('calculator.concurrencyLabel', 'Rasio bersamaan') }}</span>
          <strong class="text-white text-xs sm:text-sm">{{ (concurrencyRatio * 100).toFixed(0) }}% (~{{ activeConcurrentUsers }} user)</strong>
        </div>
        <div>
          <span class="text-slate-500 block text-[10px] uppercase">{{ t('calculator.headroomLabel', 'Cadangan kapasitas') }}</span>
          <strong class="text-emerald-400 text-xs sm:text-sm">+25% Margin</strong>
        </div>
        <div>
          <span class="text-slate-500 block text-[10px] uppercase">{{ t('calculator.needLabel', 'Perkiraan kebutuhan') }}</span>
          <strong class="text-cyan-300 text-xs sm:text-sm">{{ calculatedRequirementText }}</strong>
        </div>
      </div>

      <!-- 4. Suggested plan range -->
      <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-900 to-sky-950/80 border-2 border-cyan-500/50 rounded-2xl p-4 sm:p-6 lg:p-8 space-y-5 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
          <div class="space-y-3 flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
              <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold tracking-wider uppercase bg-cyan-500/20 text-cyan-300 border border-cyan-500/40">
                {{ recommendedPlan.tierBadge }}
              </span>
              <span
                v-if="recommendedPlan.isPopular"
                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold tracking-wider uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/40"
              >
                Pilihan Terpopuler
              </span>
              <span class="text-xs text-slate-400 font-mono block sm:inline">
                Kapasitas Paket: <strong class="text-white">{{ recommendedPlan.speedLabel }}</strong>
              </span>
            </div>

            <div>
              <h3 class="text-xl sm:text-2xl lg:text-3xl font-black text-white font-heading tracking-tight break-words">
                {{ recommendedPlan.name }}
              </h3>
              <p class="text-xs sm:text-sm text-slate-300 mt-1 leading-relaxed">
                {{ recommendedPlan.description }}
              </p>
            </div>

            <!-- Key Package Features -->
            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 text-xs text-slate-300 font-mono">
              <li
                v-for="(f, idx) in recommendedPlan.features"
                :key="idx"
                class="flex items-center gap-2"
              >
                <CheckCircle2 class="w-3.5 h-3.5 text-emerald-400 shrink-0" />
                <span class="break-words">{{ f }}</span>
              </li>
            </ul>
          </div>

          <!-- Price & Call to Action -->
          <div class="shrink-0 flex flex-col items-center md:items-end justify-between space-y-4 pt-4 md:pt-0 border-t md:border-t-0 border-slate-800 w-full md:w-auto">
            <div class="text-center md:text-right">
              <span class="text-[11px] font-mono uppercase tracking-wider text-slate-400 block">{{ t('calculator.priceCaption', 'Indikasi tarif') }}</span>
              <strong class="text-2xl sm:text-3xl font-black text-cyan-300 font-heading block">
                {{ recommendedPlan.price }}
              </strong>
              <span class="text-xs text-slate-400">{{ recommendedPlan.priceNote }}</span>
            </div>

            <div class="flex flex-col sm:flex-row gap-2.5 w-full md:w-auto">
              <Button
                as="router-link"
                :to="recommendedPlan.ctaUrl"
                variant="primary"
                size="md"
                class="font-bold w-full sm:w-auto shadow-lg shadow-cyan-500/20 gap-2 justify-center"
              >
                <span>{{ recommendedPlan.ctaLabel }}</span>
                <ArrowRight class="w-4 h-4" />
              </Button>
              <a
                v-if="hasPackagesSectionAbove"
                href="#packages"
                class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl text-xs font-semibold border border-slate-700 bg-slate-800/80 text-slate-300 hover:bg-slate-800 hover:text-white transition-colors w-full sm:w-auto text-center"
              >
                Lihat Semua Paket
              </a>
            </div>
          </div>
        </div>
        <p class="text-[11px] sm:text-xs text-slate-500 leading-relaxed relative z-10">
          {{ t('calculator.disclaimer', 'Ini perkiraan awal, bukan penawaran resmi. Harga, ketersediaan, dan kapasitas dikonfirmasi setelah survei.') }}
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import {
  Sparkles, Users, Home, Building2, ShieldCheck, CheckCircle2,
  ArrowRight, Globe, Video, Database, Zap, BarChart2,
} from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Button } from '@/modules/Layout/views/themes/layung/ui';
import {
  WORKLOAD_MBPS,
  activeConcurrentUsers as countConcurrent,
  concurrencyRatio as concurrencyFor,
  diaSpeedLabel,
  estimatePeakMbps,
  recommendPlanId,
  type BandwidthPlanId,
  type BandwidthSegment,
  type BandwidthWorkload,
} from '@/modules/Layout/views/themes/layung/composables/layungBandwidthEstimate';
import {
  LAYUNG_ISP_DEDICATED,
  LAYUNG_ISP_RETAIL_PLANS,
  LAYUNG_ISP_SOHO_PLANS,
} from '@/modules/Layout/views/themes/layung/composables/layungPricingPlans';

const { t } = useThemeI18n('layung');
const route = useRoute();

type SegmentId = BandwidthSegment;
type WorkloadId = BandwidthWorkload;

const activeSegment = ref<SegmentId>('auto');
const userCount = ref(15);
const selectedWorkload = ref<WorkloadId>('standard');

const hasPackagesSectionAbove = computed(() => route.path.includes('/pricing'));

const segments = computed(() => [
  { id: 'auto' as const, label: t('calculator.segmentAuto', 'Otomatis Pintar'), icon: Sparkles },
  { id: 'retail' as const, label: t('calculator.segmentRetail', 'Rumah & Retail'), icon: Home },
  { id: 'soho' as const, label: t('calculator.segmentSoho', 'Bisnis SOHO'), icon: Building2 },
  { id: 'dia' as const, label: t('calculator.segmentDia', 'Dedicated DIA'), icon: ShieldCheck },
]);

const currentSegmentNote = computed(() => {
  switch (activeSegment.value) {
    case 'retail':
      return t('calculator.noteRetail', 'rentang 2–20 perangkat (paket retail 10–20 Mbps)');
    case 'soho':
      return t('calculator.noteSoho', 'rentang 10–80 perangkat (broadband bisnis 50–100 Mbps)');
    case 'dia':
      return t('calculator.noteDia', 'rentang 30–500+ perangkat (dedicated internet access 1:1)');
    default:
      return t('calculator.noteAuto', 'pemilihan cerdas otomatis sesuai jumlah perangkat');
  }
});

const setSegment = (seg: SegmentId) => {
  activeSegment.value = seg;
  if (seg === 'retail') {
    if (userCount.value > 20 || userCount.value < 2) userCount.value = 6;
  } else if (seg === 'soho') {
    if (userCount.value < 10 || userCount.value > 80) userCount.value = 25;
  } else if (seg === 'dia') {
    if (userCount.value < 30) userCount.value = 80;
  }
};

const sliderMin = computed(() => {
  if (activeSegment.value === 'retail') return 2;
  if (activeSegment.value === 'soho') return 10;
  if (activeSegment.value === 'dia') return 30;
  return 2;
});

const sliderMax = computed(() => {
  if (activeSegment.value === 'retail') return 20;
  if (activeSegment.value === 'soho') return 80;
  if (activeSegment.value === 'dia') return 500;
  return 300;
});

const sliderStep = computed(() => {
  if (activeSegment.value === 'retail') return 1;
  if (activeSegment.value === 'soho') return 5;
  return 10;
});

const currentPresets = computed(() => {
  if (activeSegment.value === 'retail') {
    return [
      { label: '3 Unit (Keluarga Kecil)', count: 3 },
      { label: '6 Unit (Keluarga Menengah)', count: 6 },
      { label: '12 Unit (Multi-Device WFH)', count: 12 },
    ];
  }
  if (activeSegment.value === 'soho') {
    return [
      { label: '15 Unit (Ruko / Startup)', count: 15 },
      { label: '35 Unit (Kantor Menengah)', count: 35 },
      { label: '60 Unit (Co-Working Space)', count: 60 },
    ];
  }
  if (activeSegment.value === 'dia') {
    return [
      { label: '50 User (Kantor Cabang)', count: 50 },
      { label: '120 User (Sekolah / Kampus)', count: 120 },
      { label: '300+ User (Kantor Pusat)', count: 300 },
    ];
  }
  return [
    { label: '4 Unit (Rumah)', count: 4 },
    { label: '12 Unit (Ritel / WFH)', count: 12 },
    { label: '30 Unit (SOHO)', count: 30 },
    { label: '60 Unit (Bisnis)', count: 60 },
    { label: '150+ Unit (Enterprise)', count: 150 },
  ];
});

/**
 * Internal planning assumptions (not a third-party benchmark):
 * - 2 Mbps: browsing, email, chat
 * - 4.5 Mbps: HD calls, streaming, IP cameras
 * - 6.5 Mbps: cloud ERP, POS, backups
 * - 10 Mbps: labs, concurrent exams, 4K
 */
const workloads = computed(() => [
  {
    id: 'standard' as const,
    label: t('calculator.workloadStandard', 'Office & Browsing'),
    sub: t('calculator.workloadStandardSub', 'Web, Email, Chat, Media Sosial'),
    icon: Globe,
    mbpsPerUser: WORKLOAD_MBPS.standard,
  },
  {
    id: 'video' as const,
    label: t('calculator.workloadVideo', 'Video HD & CCTV'),
    sub: t('calculator.workloadVideoSub', 'Zoom/Teams HD, Streaming, IP Camera'),
    icon: Video,
    mbpsPerUser: WORKLOAD_MBPS.video,
  },
  {
    id: 'cloud' as const,
    label: t('calculator.workloadCloud', 'Cloud ERP & Server'),
    sub: t('calculator.workloadCloudSub', 'Database, POS Kasir, SAP, Cloud Backup'),
    icon: Database,
    mbpsPerUser: WORKLOAD_MBPS.cloud,
  },
  {
    id: 'heavy' as const,
    label: t('calculator.workloadHeavy', 'High-Demand & Lab'),
    sub: t('calculator.workloadHeavySub', 'Ujian Daring, Lab Komputer, Server'),
    icon: Zap,
    mbpsPerUser: WORKLOAD_MBPS.heavy,
  },
]);

const currentWorkload = computed(() => {
  return workloads.value.find((w) => w.id === selectedWorkload.value) || workloads.value[0]!;
});

const currentWorkloadRate = computed(() => currentWorkload.value.mbpsPerUser.toFixed(1));
const currentWorkloadThroughput = computed(() => `~${currentWorkloadRate.value} Mbps/user`);

const concurrencyRatio = computed(() => concurrencyFor(userCount.value));
const activeConcurrentUsers = computed(() => countConcurrent(userCount.value));
const calculatedRequirementMbps = computed(() =>
  estimatePeakMbps(userCount.value, currentWorkload.value.mbpsPerUser),
);

const calculatedRequirementText = computed(() => {
  const mbps = calculatedRequirementMbps.value;
  if (mbps >= 1000) return `${(mbps / 1000).toFixed(1)} Gbps`;
  return `${mbps} Mbps`;
});

interface PlanRecommendation {
  tierBadge: string;
  name: string;
  description: string;
  speedLabel: string;
  price: string;
  priceNote: string;
  features: string[];
  isPopular?: boolean;
  ctaLabel: string;
  ctaUrl: string;
}

const retail10 = LAYUNG_ISP_RETAIL_PLANS[0]!;
const retail15 = LAYUNG_ISP_RETAIL_PLANS[1]!;
const retail20 = LAYUNG_ISP_RETAIL_PLANS[2]!;
const soho50 = LAYUNG_ISP_SOHO_PLANS[0]!;
const soho100 = LAYUNG_ISP_SOHO_PLANS[1]!;

const indicativePriceNote = (note?: string): string => {
  const base = (note ?? '+ PPN / bulan').trim();
  return /indikasi/i.test(base) ? base : `${base} (indikasi)`;
};

const PLAN_COPY: Record<Exclude<BandwidthPlanId, 'dia'>, PlanRecommendation> = {
  'retail-10': {
    tierBadge: 'RETAIL BROADBAND',
    name: retail10.name,
    description: retail10.description,
    speedLabel: retail10.speed ?? '10 Mbps (Up to 15 Mbps)',
    price: retail10.price,
    priceNote: indicativePriceNote(retail10.priceNote),
    features: [
      'Kapasitas 10 Mbps (up to 15 Mbps)',
      'Ideal untuk 2–5 perangkat aktif',
      'Instalasi standar coverage Kami',
      'Konfirmasi tarif saat survei',
    ],
    ctaLabel: 'Pilih Retail 10',
    ctaUrl: '/contact?plan=retail-10',
  },
  'retail-15': {
    tierBadge: 'RETAIL BROADBAND',
    name: retail15.name,
    description: retail15.description,
    speedLabel: retail15.speed ?? '15 Mbps (Up to 20 Mbps)',
    price: retail15.price,
    priceNote: indicativePriceNote(retail15.priceNote),
    isPopular: true,
    features: [
      'Kapasitas 15 Mbps (up to 20 Mbps)',
      'Ideal untuk 5–8 perangkat aktif',
      'Video call & streaming HD lancar',
      'Konfirmasi tarif saat survei',
    ],
    ctaLabel: 'Pilih Retail 15',
    ctaUrl: '/contact?plan=retail-15',
  },
  'retail-20': {
    tierBadge: 'RETAIL BROADBAND',
    name: retail20.name,
    description: retail20.description,
    speedLabel: retail20.speed ?? '20 Mbps (Up to 25 Mbps)',
    price: retail20.price,
    priceNote: indicativePriceNote(retail20.priceNote),
    features: [
      'Kapasitas 20 Mbps (up to 25 Mbps)',
      'Ideal untuk 8–15 perangkat aktif',
      'Kapasitas lega untuk streaming & kerja',
      'Konfirmasi tarif saat survei',
    ],
    ctaLabel: 'Pilih Retail 20',
    ctaUrl: '/contact?plan=retail-20',
  },
  'soho-50': {
    tierBadge: 'BROADBAND BISNIS SOHO',
    name: soho50.name,
    description: soho50.description,
    speedLabel: soho50.speed ?? 'Up to 50 Mbps (Bisnis SOHO)',
    price: soho50.price,
    priceNote: indicativePriceNote(soho50.priceNote),
    features: [
      'Up to 50 Mbps rasio bisnis',
      '1 IP Publik Statis included',
      'Cocok untuk 10–30 perangkat',
      'Dukungan teknis jam operasional',
    ],
    ctaLabel: 'Pilih SOHO 50',
    ctaUrl: '/contact?plan=soho-50',
  },
  'soho-100': {
    tierBadge: 'BROADBAND BISNIS SOHO',
    name: soho100.name,
    description: soho100.description,
    speedLabel: soho100.speed ?? 'Up to 100 Mbps (Bisnis SOHO)',
    price: soho100.price,
    priceNote: indicativePriceNote(soho100.priceNote),
    isPopular: true,
    features: [
      'Up to 100 Mbps prioritas bandwidth',
      '1–2 IP Publik Statis included',
      'Cocok untuk 30–60 perangkat',
      'Tiket eskalasi prioritas NOC',
    ],
    ctaLabel: 'Pilih SOHO 100',
    ctaUrl: '/contact?plan=soho-100',
  },
};

const recommendedPlan = computed<PlanRecommendation>(() => {
  const mbps = calculatedRequirementMbps.value;
  const planId = recommendPlanId({
    segment: activeSegment.value,
    userCount: userCount.value,
    mbps,
    workload: selectedWorkload.value,
  });

  if (planId !== 'dia') {
    return PLAN_COPY[planId];
  }

  const diaSpeed = diaSpeedLabel(mbps);
  return {
    tierBadge: 'DEDICATED 1:1 ENTERPRISE',
    name: LAYUNG_ISP_DEDICATED.name,
    description: LAYUNG_ISP_DEDICATED.description,
    speedLabel: `${diaSpeed} Simetris 1:1 Dedicated`,
    price: LAYUNG_ISP_DEDICATED.price,
    priceNote: `Perkiraan kapasitas ${diaSpeed}`,
    features: [
      `Bandwidth ${diaSpeed} simetris (upload = download 1:1)`,
      'IP Publik Statis sesuai kebutuhan sistem',
      'Target layanan mengikuti SLA kontrak',
      'Monitoring NOC 24/7 & Service Desk',
    ],
    ctaLabel: 'Ajukan Proposal DIA',
    ctaUrl: '/contact?plan=dia',
  };
});
</script>
