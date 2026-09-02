<template>
  <section
    id="calculator"
    data-ja-customizer-target="calculator"
    class="scroll-mt-24 my-8 max-w-5xl mx-auto px-4 sm:px-6 relative z-10"
  >
    <span id="simulator" class="sr-only">Simulator</span>

    <!-- Single Unified Main Card (No double wrapper) -->
    <div class="relative overflow-hidden bg-slate-950 text-white border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl space-y-8">
      <!-- Glow ambient background inside the card -->
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none" />
      <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl pointer-events-none" />

      <!-- 1. Card Header: Title & Scientific Methodology Benchmark -->
      <div class="space-y-3 relative z-10">
        <div class="flex flex-wrap items-center justify-between gap-2.5">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/15 text-cyan-400 border border-cyan-500/30 font-mono uppercase tracking-wider">
            <Sparkles class="w-3.5 h-3.5" />
            {{ t('calculator.badge', 'Simulator Bandwidth K2NET') }}
          </span>
          <div class="inline-flex items-center gap-1.5 text-[11px] font-mono text-slate-400 bg-slate-900/90 px-3 py-1 rounded-lg border border-slate-800">
            <BarChart2 class="w-3.5 h-3.5 text-cyan-400 shrink-0" />
            <span>Formula Konkurensi Cisco & Standar Ookla® Speedtest</span>
          </div>
        </div>

        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black font-heading tracking-tight text-white">
          {{ t('calculator.title', 'Hitung Kebutuhan Bandwidth & Rekomendasi Paket') }}
        </h2>
        <p class="text-slate-400 text-xs sm:text-sm leading-relaxed max-w-3xl">
          Kalkulator bandwidth interaktif dengan algoritma perhitungan terstandar (estimasi aktivitas 2–10 Mbps/user, rasio konkurensi serentak 50–80%, serta 25% safety headroom) untuk menghasilkan rekomendasi paket internet K2NET yang paling efisien dan stabil.
        </p>
      </div>

      <!-- 2. Interactive Input Controls -->
      <div class="space-y-6 relative z-10 pt-2 border-t border-slate-800/80">
        <!-- Step 1: Segment Filter -->
        <div class="space-y-2.5">
          <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">
            <span>1. Pilih Segmen Lingkungan:</span>
            <span class="text-cyan-400 font-normal lowercase">{{ currentSegmentNote }}</span>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <button
              v-for="seg in segments"
              :key="seg.id"
              type="button"
              class="px-3.5 py-2.5 rounded-xl border text-xs font-semibold flex items-center justify-center gap-2 transition-all"
              :class="activeSegment === seg.id
                ? 'bg-cyan-500/20 border-cyan-400 text-cyan-300 font-bold shadow-sm shadow-cyan-500/20'
                : 'bg-slate-900/70 border-slate-800 text-slate-400 hover:border-slate-700 hover:text-slate-200'"
              @click="setSegment(seg.id)"
            >
              <component
                :is="seg.icon"
                class="w-4 h-4 shrink-0"
              />
              <span>{{ seg.label }}</span>
            </button>
          </div>
        </div>

        <!-- Step 2: Device Slider & Presets -->
        <div class="space-y-3 pt-2 border-t border-slate-800/60">
          <div class="flex items-center justify-between">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">
              2. Estimasi Jumlah Perangkat Terhubung:
            </label>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-cyan-500/10 border border-cyan-500/30 text-cyan-300 font-mono font-bold text-sm">
              <Users class="w-4 h-4" />
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
            <div class="flex justify-between text-[11px] text-slate-500 font-mono">
              <span>Min: {{ sliderMin }} Unit</span>
              <span>Rata-rata: {{ Math.round((sliderMin + sliderMax) / 2) }}</span>
              <span>Max: {{ sliderMax }}+ Unit</span>
            </div>
          </div>

          <!-- Quick Presets -->
          <div class="flex flex-wrap items-center gap-1.5 pt-1">
            <span class="text-[10px] text-slate-500 font-mono uppercase tracking-wider mr-1">Preset Cepat:</span>
            <button
              v-for="p in currentPresets"
              :key="p.count"
              type="button"
              class="px-2.5 py-1 rounded-md text-[11px] font-mono border transition-all"
              :class="userCount === p.count
                ? 'bg-cyan-500 text-slate-950 font-bold border-cyan-400'
                : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700 hover:text-slate-300'"
              @click="userCount = p.count"
            >
              {{ p.label }}
            </button>
          </div>
        </div>

        <!-- Step 3: Workload Profiling (Ookla Benchmarks) -->
        <div class="space-y-2.5 pt-2 border-t border-slate-800/60">
          <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">
            <span>3. Karakteristik Beban Aktivitas Utama:</span>
            <span class="text-slate-500 font-normal">Standar Ookla: {{ currentWorkloadThroughput }}</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
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

      <!-- 3. Scientific Calculation Summary Bar -->
      <div class="bg-slate-900/90 border border-slate-800/80 rounded-2xl p-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs font-mono">
        <div>
          <span class="text-slate-500 block text-[10px] uppercase">Beban per User (Ookla)</span>
          <strong class="text-cyan-400 text-sm">{{ currentWorkloadRate }} Mbps</strong>
        </div>
        <div>
          <span class="text-slate-500 block text-[10px] uppercase">Rasio Konkurensi (Cisco)</span>
          <strong class="text-white text-sm">{{ (concurrencyRatio * 100).toFixed(0) }}% (~{{ activeConcurrentUsers }} user aktif)</strong>
        </div>
        <div>
          <span class="text-slate-500 block text-[10px] uppercase">Safety Headroom</span>
          <strong class="text-emerald-400 text-sm">+25% Buffer</strong>
        </div>
        <div>
          <span class="text-slate-500 block text-[10px] uppercase">Kebutuhan Bandwidth</span>
          <strong class="text-cyan-300 text-sm">{{ calculatedRequirementText }}</strong>
        </div>
      </div>

      <!-- 4. Real K2NET Calculated Recommendation Card -->
      <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-900 to-sky-950/80 border-2 border-cyan-500/50 rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
          <div class="space-y-3 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold tracking-wider uppercase bg-cyan-500/20 text-cyan-300 border border-cyan-500/40">
                {{ recommendedPlan.tierBadge }}
              </span>
              <span
                v-if="recommendedPlan.isPopular"
                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold tracking-wider uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/40"
              >
                Pilihan Terpopuler
              </span>
              <span class="text-xs text-slate-400 font-mono">
                Kapasitas Paket: <strong class="text-white">{{ recommendedPlan.speedLabel }}</strong>
              </span>
            </div>

            <div>
              <h3 class="text-2xl sm:text-3xl font-black text-white font-heading tracking-tight">
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
                <span>{{ f }}</span>
              </li>
            </ul>
          </div>

          <!-- Price & Call to Action -->
          <div class="shrink-0 flex flex-col items-center md:items-end justify-between space-y-4 pt-4 md:pt-0 border-t md:border-t-0 border-slate-800">
            <div class="text-center md:text-right">
              <span class="text-[11px] font-mono uppercase tracking-wider text-slate-400 block">Biaya Langganan</span>
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
                class="font-bold w-full sm:w-auto shadow-lg shadow-cyan-500/20 gap-2"
              >
                <span>{{ recommendedPlan.ctaLabel }}</span>
                <ArrowRight class="w-4 h-4" />
              </Button>
              <a
                v-if="hasPackagesSectionAbove"
                href="#packages"
                class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl text-xs font-semibold border border-slate-700 bg-slate-800/80 text-slate-300 hover:bg-slate-800 hover:text-white transition-colors"
              >
                Lihat Semua Paket
              </a>
            </div>
          </div>
        </div>
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

const { t } = useThemeI18n('layung');
const route = useRoute();

type SegmentId = 'auto' | 'retail' | 'soho' | 'dia';
type WorkloadId = 'standard' | 'video' | 'cloud' | 'heavy';

const activeSegment = ref<SegmentId>('auto');
const userCount = ref(15);
const selectedWorkload = ref<WorkloadId>('standard');

const hasPackagesSectionAbove = computed(() => route.path.includes('/pricing'));

const segments = [
  { id: 'auto' as const, label: 'Otomatis Pintar', icon: Sparkles },
  { id: 'retail' as const, label: 'Rumah & Retail', icon: Home },
  { id: 'soho' as const, label: 'Bisnis SOHO', icon: Building2 },
  { id: 'dia' as const, label: 'Dedicated DIA', icon: ShieldCheck },
];

const currentSegmentNote = computed(() => {
  switch (activeSegment.value) {
    case 'retail':
      return 'rentang 2–20 perangkat (paket retail 10–20 Mbps)';
    case 'soho':
      return 'rentang 10–80 perangkat (broadband bisnis 50–100 Mbps)';
    case 'dia':
      return 'rentang 30–500+ perangkat (dedicated internet access 1:1)';
    default:
      return 'pemilihan cerdas otomatis sesuai jumlah perangkat';
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
 * Throughput benchmarks based on Ookla Speedtest Guidelines:
 * - 2 Mbps: Email, web browsing, basic chat, audio streaming
 * - 4-5 Mbps: HD video conferencing (Zoom HD 1080p, Teams), cloud sync
 * - 6-8 Mbps: Enterprise ERP, cloud database, simultaneous multi-device sync
 * - 10+ Mbps: High-concurrency computer labs, online exam proctoring, 4K streams
 */
const workloads = [
  {
    id: 'standard' as const,
    label: 'Office & Browsing',
    sub: 'Web, Email, Chat, Media Sosial',
    icon: Globe,
    mbpsPerUser: 2.0,
  },
  {
    id: 'video' as const,
    label: 'Video HD & CCTV',
    sub: 'Zoom/Teams HD, Streaming, IP Camera',
    icon: Video,
    mbpsPerUser: 4.5,
  },
  {
    id: 'cloud' as const,
    label: 'Cloud ERP & Server',
    sub: 'Database, POS Kasir, SAP, Cloud Backup',
    icon: Database,
    mbpsPerUser: 6.5,
  },
  {
    id: 'heavy' as const,
    label: 'High-Demand & Lab',
    sub: 'Ujian Daring, Lab Komputer, Server',
    icon: Zap,
    mbpsPerUser: 10.0,
  },
];

const currentWorkload = computed(() => {
  return workloads.find((w) => w.id === selectedWorkload.value) || workloads[0]!;
});

const currentWorkloadRate = computed(() => currentWorkload.value.mbpsPerUser.toFixed(1));
const currentWorkloadThroughput = computed(() => `~${currentWorkloadRate.value} Mbps/user`);

/**
 * Concurrency factor based on Cisco Enterprise Capacity Planning:
 * In networks with larger user pools, the probability of 100% simultaneous peak transfer drops.
 * - 2-10 users: 85% concurrent
 * - 11-30 users: 75% concurrent
 * - 31-70 users: 65% concurrent
 * - 71-150 users: 55% concurrent
 * - >150 users: 45% concurrent
 */
const concurrencyRatio = computed(() => {
  const n = userCount.value;
  if (n <= 10) return 0.85;
  if (n <= 30) return 0.75;
  if (n <= 70) return 0.65;
  if (n <= 150) return 0.55;
  return 0.45;
});

const activeConcurrentUsers = computed(() => {
  return Math.max(1, Math.round(userCount.value * concurrencyRatio.value));
});

/**
 * Scientific Formula:
 * Peak Bandwidth Required = (Active Concurrent Users * Workload Rate) * (1 + 25% Headroom Buffer)
 */
const calculatedRequirementMbps = computed(() => {
  const rawLoad = activeConcurrentUsers.value * currentWorkload.value.mbpsPerUser;
  const withHeadroom = rawLoad * 1.25; // 25% safety margin
  return Math.max(10, Math.round(withHeadroom));
});

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

const recommendedPlan = computed<PlanRecommendation>(() => {
  const seg = activeSegment.value;
  const count = userCount.value;
  const mbps = calculatedRequirementMbps.value;

  // 1. Forced Retail Segment
  if (seg === 'retail') {
    if (count <= 5 || mbps <= 14) {
      return {
        tierBadge: 'RETAIL BROADBAND',
        name: 'Retail Broadband 10',
        description: 'Paket internet rumah tangga dan ritel dengan kebutuhan browsing, streaming, dan belajar daring.',
        speedLabel: '10 Mbps (Up to 15 Mbps)',
        price: 'Rp 150.000',
        priceNote: '+ PPN / bulan',
        features: [
          'Kapasitas 10 Mbps (up to 15 Mbps)',
          'Ideal untuk 2–5 perangkat aktif',
          'Instalasi standar coverage K2NET',
          'Biaya bulanan hemat & transparan',
        ],
        ctaLabel: 'Pilih Retail 10',
        ctaUrl: '/contact?plan=retail-10',
      };
    }
    if (count <= 8 || mbps <= 22) {
      return {
        tierBadge: 'RETAIL BROADBAND',
        name: 'Retail Broadband 15',
        description: 'Pilihan menengah paling populer untuk keluarga atau usaha rumahan dengan aktivitas daring lebih intens.',
        speedLabel: '15 Mbps (Up to 20 Mbps)',
        price: 'Rp 200.000',
        priceNote: '+ PPN / bulan',
        isPopular: true,
        features: [
          'Kapasitas 15 Mbps (up to 20 Mbps)',
          'Ideal untuk 5–8 perangkat aktif',
          'Video call & streaming HD lancar',
          'Instalasi standar coverage K2NET',
        ],
        ctaLabel: 'Pilih Retail 15',
        ctaUrl: '/contact?plan=retail-15',
      };
    }
    return {
      tierBadge: 'RETAIL BROADBAND',
      name: 'Retail Broadband 20',
      description: 'Paket retail tertinggi untuk kebutuhan multi-perangkat keluarga besar atau WFH intensif.',
      speedLabel: '20 Mbps (Up to 25 Mbps)',
      price: 'Rp 250.000',
      priceNote: '+ PPN / bulan',
      features: [
        'Kapasitas 20 Mbps (up to 25 Mbps)',
        'Ideal untuk 8–15 perangkat aktif',
        'Kapasitas lega untuk streaming & kerja',
        'Instalasi standar coverage K2NET',
      ],
      ctaLabel: 'Pilih Retail 20',
      ctaUrl: '/contact?plan=retail-20',
    };
  }

  // 2. Forced SOHO Segment
  if (seg === 'soho') {
    if (count <= 35 && mbps <= 65) {
      return {
        tierBadge: 'BROADBAND BISNIS SOHO',
        name: 'Broadband Bisnis 50 Mbps',
        description: 'Internet stabil untuk SOHO, ruko, dan usaha kecil dengan kebutuhan cloud, POS kasir, dan operasional rutin.',
        speedLabel: 'Up to 50 Mbps (Bisnis SOHO)',
        price: 'Mulai Rp 1.200.000',
        priceNote: '+ PPN / bulan',
        features: [
          'Up to 50 Mbps rasio bisnis',
          '1 IP Publik Statis included',
          'Cocok untuk 10–30 perangkat',
          'Dukungan teknis jam operasional',
        ],
        ctaLabel: 'Pilih SOHO 50',
        ctaUrl: '/contact?plan=soho-50',
      };
    }
    return {
      tierBadge: 'BROADBAND BISNIS SOHO',
      name: 'Broadband Bisnis 100 Mbps',
      description: 'Kapasitas bisnis lebih besar untuk kantor menengah, co-working space, dan ruko dengan traffic tinggi.',
      speedLabel: 'Up to 100 Mbps (Bisnis SOHO)',
      price: 'Mulai Rp 2.000.000',
      priceNote: '+ PPN / bulan',
      isPopular: true,
      features: [
        'Up to 100 Mbps prioritas bandwidth',
        '1–2 IP Publik Statis included',
        'Cocok untuk 30–60 perangkat',
        'Tiket eskalasi prioritas NOC',
      ],
      ctaLabel: 'Pilih SOHO 100',
      ctaUrl: '/contact?plan=soho-100',
    };
  }

  // 3. Forced DIA Segment
  if (seg === 'dia') {
    const diaSpeed = mbps >= 1000 ? `${(mbps / 1000).toFixed(1)} Gbps` : `${Math.max(50, Math.ceil(mbps / 25) * 25)} Mbps`;
    return {
      tierBadge: 'DEDICATED 1:1 ENTERPRISE',
      name: 'Dedicated Internet Access (DIA)',
      description: 'Koneksi internet fiber dedicated murni dengan alokasi bandwidth simetris 1:1 untuk kantor pusat, kampus, dan institusi.',
      speedLabel: `${diaSpeed} Simetris 1:1 Dedicated`,
      price: 'Hubungi Sales',
      priceNote: `Kapasitas ${diaSpeed} Simetris 1:1`,
      features: [
        `Bandwidth ${diaSpeed} simetris (upload = download 1:1)`,
        'IP Publik Statis sesuai kebutuhan sistem',
        'Jaminan Uptime tertulis di SLA Kontrak',
        'Monitoring NOC 24/7 & Service Desk',
      ],
      ctaLabel: 'Ajukan Proposal DIA',
      ctaUrl: '/contact?plan=dia',
    };
  }

  // 4. Smart Auto Allocation ('auto')
  if (count <= 5 && mbps <= 14) {
    return {
      tierBadge: 'RETAIL BROADBAND',
      name: 'Retail Broadband 10',
      description: 'Pilihan hemat untuk rumah tangga atau ruko kecil dengan 2–5 perangkat aktif.',
      speedLabel: '10 Mbps (Up to 15 Mbps)',
      price: 'Rp 150.000',
      priceNote: '+ PPN / bulan',
      features: [
        'Kapasitas 10 Mbps (up to 15 Mbps)',
        'Ideal untuk 2–5 perangkat aktif',
        'Instalasi standar area K2NET',
        'Dukungan customer care',
      ],
      ctaLabel: 'Pilih Retail 10',
      ctaUrl: '/contact?plan=retail-10',
    };
  }

  if (count <= 8 && mbps <= 22) {
    return {
      tierBadge: 'RETAIL BROADBAND',
      name: 'Retail Broadband 15',
      description: 'Paket terlaris untuk keluarga dan UMKM rumahan dengan aktivitas daring harian yang aktif.',
      speedLabel: '15 Mbps (Up to 20 Mbps)',
      price: 'Rp 200.000',
      priceNote: '+ PPN / bulan',
      isPopular: true,
      features: [
        'Kapasitas 15 Mbps (up to 20 Mbps)',
        'Ideal untuk 5–8 perangkat aktif',
        'Streaming HD & video call lancar',
        'Instalasi standar area K2NET',
      ],
      ctaLabel: 'Pilih Retail 15',
      ctaUrl: '/contact?plan=retail-15',
    };
  }

  if (count <= 15 && mbps <= 35) {
    return {
      tierBadge: 'RETAIL BROADBAND',
      name: 'Retail Broadband 20',
      description: 'Paket retail dengan kapasitas paling lega untuk multi-perangkat dan bekerja dari rumah.',
      speedLabel: '20 Mbps (Up to 25 Mbps)',
      price: 'Rp 250.000',
      priceNote: '+ PPN / bulan',
      features: [
        'Kapasitas 20 Mbps (up to 25 Mbps)',
        'Ideal untuk 8–15 perangkat aktif',
        'Kapasitas lega untuk streaming bersamaan',
        'Instalasi standar area K2NET',
      ],
      ctaLabel: 'Pilih Retail 20',
      ctaUrl: '/contact?plan=retail-20',
    };
  }

  if (count <= 35 && mbps <= 70 && selectedWorkload.value !== 'heavy') {
    return {
      tierBadge: 'BROADBAND BISNIS SOHO',
      name: 'Broadband Bisnis 50 Mbps',
      description: 'Internet stabil untuk ruko, kantor kecil, dan operasional bisnis dengan IP publik statis.',
      speedLabel: 'Up to 50 Mbps (Bisnis SOHO)',
      price: 'Mulai Rp 1.200.000',
      priceNote: '+ PPN / bulan',
      features: [
        'Up to 50 Mbps rasio bisnis SOHO',
        '1 IP Publik Statis included',
        'Cocok untuk 10–30 perangkat kantor',
        'Dukungan teknis jam operasional',
      ],
      ctaLabel: 'Pilih SOHO 50',
      ctaUrl: '/contact?plan=soho-50',
    };
  }

  if (count <= 65 && mbps <= 120 && selectedWorkload.value !== 'heavy') {
    return {
      tierBadge: 'BROADBAND BISNIS SOHO',
      name: 'Broadband Bisnis 100 Mbps',
      description: 'Kapasitas bisnis lebih tangguh untuk kantor menengah, co-working, atau instansi pendidikan.',
      speedLabel: 'Up to 100 Mbps (Bisnis SOHO)',
      price: 'Mulai Rp 2.000.000',
      priceNote: '+ PPN / bulan',
      isPopular: true,
      features: [
        'Up to 100 Mbps prioritas jam kerja',
        '1–2 IP Publik Statis included',
        'Cocok untuk 30–60 perangkat aktif',
        'Dukungan teknis & tiket insiden',
      ],
      ctaLabel: 'Pilih SOHO 100',
      ctaUrl: '/contact?plan=soho-100',
    };
  }

  // Large traffic or high count -> Dedicated Internet Access (DIA)
  const diaSpeed = mbps >= 1000 ? `${(mbps / 1000).toFixed(1)} Gbps` : `${Math.max(50, Math.ceil(mbps / 25) * 25)} Mbps`;
  return {
    tierBadge: 'DEDICATED 1:1 ENTERPRISE',
    name: 'Dedicated Internet Access (DIA)',
    description: 'Koneksi internet dedicated murni tanpa pembagian bandwidth. Kapasitas simetris 1:1 dengan SLA resmi untuk kantor pusat, kampus, dan instansi.',
    speedLabel: `${diaSpeed} Simetris 1:1 Dedicated`,
    price: 'Hubungi Sales',
    priceNote: `Rekomendasi Kapasitas ${diaSpeed} Simetris`,
    features: [
      `Bandwidth ${diaSpeed} dedicated 1:1 (upload = download)`,
      'Alokasi IP Publik Statis sesuai kebutuhan',
      'Jaminan Uptime SLA berbasis kontrak resmi',
      'Monitoring NOC 24/7 & penanganan insiden',
    ],
    ctaLabel: 'Ajukan Proposal DIA',
    ctaUrl: '/contact?plan=dia',
  };
});
</script>
