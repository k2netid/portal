<template>
  <section
    id="calculator"
    data-ja-customizer-target="calculator"
    class="py-16 sm:py-20 bg-slate-950 text-white relative overflow-hidden scroll-mt-24 rounded-3xl my-6 border border-slate-800 shadow-2xl"
  >
    <span id="simulator" class="sr-only">Simulator</span>
    <!-- Ambient glowing backdrop -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none" />
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl pointer-events-none" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 relative z-10">
      <!-- Section Header -->
      <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-cyan-500/15 text-cyan-400 border border-cyan-500/30 uppercase tracking-wider font-mono">
          <Sparkles class="w-3.5 h-3.5" />
          {{ t('calculator.badge', 'Simulator Bandwidth K2NET') }}
        </span>
        <h2 class="text-3xl sm:text-4xl font-extrabold font-heading tracking-tight text-white">
          {{ t('calculator.title', 'Hitung Kebutuhan Bandwidth & Solusi Ideal Anda') }}
        </h2>
        <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
          {{ t('calculator.subtitle', 'Sesuaikan segmen penggunaan, jumlah perangkat aktif, dan aktivitas harian untuk mendapatkan rekomendasi paket internet K2NET yang paling tepat dan efisien.') }}
        </p>
      </div>

      <!-- Main Interactive Simulator Card -->
      <div class="max-w-4xl mx-auto bg-slate-900/90 border border-slate-800/90 backdrop-blur-xl rounded-3xl p-6 sm:p-10 shadow-2xl space-y-8">
        <!-- 1. Segment Selector Pills -->
        <div class="space-y-2.5">
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 font-mono flex items-center justify-between">
            <span>1. Segmen Penggunaan:</span>
            <span class="text-cyan-400 font-normal lowercase">{{ currentSegmentNote }}</span>
          </label>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <button
              v-for="seg in segments"
              :key="seg.id"
              type="button"
              class="px-3.5 py-2.5 rounded-xl border text-xs font-semibold flex items-center justify-center gap-2 transition-all"
              :class="activeSegment === seg.id
                ? 'bg-cyan-500/20 border-cyan-500 text-cyan-300 font-bold shadow-sm shadow-cyan-500/20'
                : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:border-slate-700 hover:text-slate-200'"
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

        <!-- 2. Interactive User Count Slider & Presets -->
        <div class="space-y-4 pt-2 border-t border-slate-800/80">
          <div class="flex items-center justify-between">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">
              2. Jumlah Perangkat / Pengguna Aktif:
            </label>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-sky-500/10 border border-sky-500/30 text-sky-400 font-mono font-bold text-sm">
              <Users class="w-4 h-4" />
              <span>{{ userCount }} Perangkat</span>
            </div>
          </div>

          <div class="space-y-2">
            <input
              v-model.number="userCount"
              type="range"
              :min="sliderMin"
              :max="sliderMax"
              :step="sliderStep"
              class="w-full accent-cyan-400 bg-slate-950 h-2.5 rounded-lg cursor-pointer transition-all"
            >
            <div class="flex justify-between text-[11px] text-slate-500 font-mono">
              <span>{{ sliderMin }} Perangkat</span>
              <span>{{ Math.round((sliderMin + sliderMax) / 2) }}</span>
              <span>{{ sliderMax }}+ Perangkat</span>
            </div>
          </div>

          <!-- Quick presets buttons -->
          <div class="flex flex-wrap items-center gap-2 pt-1">
            <span class="text-[10px] text-slate-500 font-mono uppercase tracking-wider mr-1">Preset Cepat:</span>
            <button
              v-for="p in currentPresets"
              :key="p.count"
              type="button"
              class="px-2.5 py-1 rounded-md text-[11px] font-mono border transition-all"
              :class="userCount === p.count
                ? 'bg-sky-500 text-white border-sky-400 font-bold'
                : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700 hover:text-slate-300'"
              @click="userCount = p.count"
            >
              {{ p.label }}
            </button>
          </div>
        </div>

        <!-- 3. Workload / Business Activity -->
        <div class="space-y-3 pt-2 border-t border-slate-800/80">
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">
            3. Beban & Karakteristik Aktivitas:
          </label>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
            <button
              v-for="w in workloads"
              :key="w.id"
              type="button"
              class="p-3 rounded-xl border text-left font-medium transition-all flex flex-col justify-between"
              :class="selectedWorkload === w.id
                ? 'bg-cyan-500/15 border-cyan-400 text-cyan-300 font-bold shadow-sm'
                : 'bg-slate-950/70 border-slate-800 text-slate-400 hover:border-slate-700 hover:text-slate-300'"
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

        <!-- 4. Real K2NET Calculated Recommendation Card -->
        <div class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-sky-950/70 border-2 border-cyan-500/40 rounded-2xl p-6 sm:p-8 space-y-6 shadow-xl">
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
                  Pilihan Paling Populer
                </span>
                <span class="text-xs text-slate-400 font-mono">
                  Estimasi Bandwidth: <strong class="text-white">{{ estimatedBandwidthText }}</strong>
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
                  class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl text-xs font-semibold border border-slate-700 bg-slate-800/60 text-slate-300 hover:bg-slate-800 hover:text-white transition-colors"
                >
                  Lihat Semua Paket
                </a>
              </div>
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
  ArrowRight, Globe, Video, Database, Zap,
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
  { id: 'auto' as const, label: 'Otomatis', icon: Sparkles },
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
      return 'rekomendasi otomatis cerdas berdasarkan jumlah perangkat';
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
      { label: '12 Unit (Multi-Device)', count: 12 },
    ];
  }
  if (activeSegment.value === 'soho') {
    return [
      { label: '15 Unit (Ruko / Startup)', count: 15 },
      { label: '35 Unit (Kantor Menengah)', count: 35 },
      { label: '60 Unit (Co-Working / Lab)', count: 60 },
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
    { label: '150+ Unit (DIA)', count: 150 },
  ];
});

const workloads = [
  {
    id: 'standard' as const,
    label: 'Office & Browsing',
    sub: 'Web, Email, Chat, Media Sosial',
    icon: Globe,
    multiplier: 1.0,
  },
  {
    id: 'video' as const,
    label: 'Video HD & CCTV',
    sub: 'Zoom/Teams HD, Streaming, IP Camera',
    icon: Video,
    multiplier: 2.0,
  },
  {
    id: 'cloud' as const,
    label: 'Cloud ERP & Server',
    sub: 'Database, POS Kasir, SAP, Backup',
    icon: Database,
    multiplier: 3.5,
  },
  {
    id: 'heavy' as const,
    label: 'High-Demand & Lab',
    sub: 'Ujian Daring, Lab Komputer, Server',
    icon: Zap,
    multiplier: 5.0,
  },
];

const estimatedBandwidthThroughput = computed(() => {
  const currentWorkload = workloads.find((w) => w.id === selectedWorkload.value);
  const factor = currentWorkload ? currentWorkload.multiplier : 1.0;
  return Math.max(10, Math.round(userCount.value * factor));
});

const estimatedBandwidthText = computed(() => {
  const mbps = estimatedBandwidthThroughput.value;
  if (mbps >= 1000) return `${(mbps / 1000).toFixed(1)} Gbps`;
  return `~${mbps} Mbps`;
});

interface PlanRecommendation {
  tierBadge: string;
  name: string;
  description: string;
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
  const mbps = estimatedBandwidthThroughput.value;

  // 1. Forced Retail Segment
  if (seg === 'retail') {
    if (count <= 5 || mbps <= 12) {
      return {
        tierBadge: 'RETAIL BROADBAND',
        name: 'Retail Broadband 10',
        description: 'Paket internet rumah tangga dan ritel dengan kebutuhan browsing, streaming, dan belajar daring.',
        price: 'Rp 150.000',
        priceNote: '+ PPN / bulan',
        features: [
          'Kecepatan 10 Mbps (up to 15 Mbps)',
          'Ideal untuk 2–5 perangkat aktif',
          'Instalasi standar coverage K2NET',
          'Biaya bulanan hemat & transparan',
        ],
        ctaLabel: 'Pilih Retail 10',
        ctaUrl: '/contact?plan=retail-10',
      };
    }
    if (count <= 8 || mbps <= 20) {
      return {
        tierBadge: 'RETAIL BROADBAND',
        name: 'Retail Broadband 15',
        description: 'Pilihan menengah paling populer untuk keluarga atau usaha rumahan dengan aktivitas daring lebih intens.',
        price: 'Rp 200.000',
        priceNote: '+ PPN / bulan',
        isPopular: true,
        features: [
          'Kecepatan 15 Mbps (up to 20 Mbps)',
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
      price: 'Rp 250.000',
      priceNote: '+ PPN / bulan',
      features: [
        'Kecepatan 20 Mbps (up to 25 Mbps)',
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
      price: 'Rp 150.000',
      priceNote: '+ PPN / bulan',
      features: [
        'Kecepatan 10 Mbps (up to 15 Mbps)',
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
      price: 'Rp 200.000',
      priceNote: '+ PPN / bulan',
      isPopular: true,
      features: [
        'Kecepatan 15 Mbps (up to 20 Mbps)',
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
      price: 'Rp 250.000',
      priceNote: '+ PPN / bulan',
      features: [
        'Kecepatan 20 Mbps (up to 25 Mbps)',
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
