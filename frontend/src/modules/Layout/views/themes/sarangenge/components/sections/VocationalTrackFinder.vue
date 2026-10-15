<template>
  <section
    v-if="trackFinderEnabled"
    id="section-track-finder"
    data-ja-customizer-target="track-finder"
    class="py-16 sm:py-20 px-4 sm:px-6 lg:px-8 bg-background relative overflow-hidden"
  >
    <!-- Background accents -->
    <div class="absolute inset-0 pointer-events-none z-0">
      <div class="absolute top-1/2 left-0 w-96 h-96 bg-[var(--sarangenge-teal,#0f766e)]/5 rounded-full blur-3xl -translate-y-1/2 -translate-x-1/2" />
      <div class="absolute bottom-0 right-0 w-96 h-96 bg-[var(--sarangenge-sun,#e8a317)]/5 rounded-full blur-3xl translate-y-1/3 translate-x-1/3" />
    </div>

    <div class="max-w-7xl mx-auto relative z-10 space-y-10">
      <!-- Section Header -->
      <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-300 border border-[var(--sarangenge-teal,#0f766e)]/20 shadow-sm">
          <Compass class="w-4 h-4 text-[var(--sarangenge-sun,#e8a317)]" />
          {{ t('trackFinder.badge', 'Panduan Peminatan & Rekomendasi Jurusan') }}
        </span>
        <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
          {{ t('trackFinder.title', 'Temukan Program Keahlian Impianmu') }}
        </h2>
        <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
          {{ t('trackFinder.subtitle', 'Eksplorasi program keahlian vokasi berstandar industri yang sesuai dengan passion, bakat, dan masa depan karirmu.') }}
        </p>
      </div>

      <!-- Main Interactive Box -->
      <div class="sarangenge-panel p-6 sm:p-10 border border-border/80 shadow-2xl bg-card/95 backdrop-blur-md">
        <!-- Step 1: Minat Utama -->
        <div class="space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
            <span class="text-xs font-black uppercase tracking-wider text-muted-foreground font-mono">
              1. {{ t('trackFinder.step1Label', 'Pilih Bidang yang Paling Kamu Sukai:') }}
            </span>
            <span class="text-xs font-semibold text-[var(--sarangenge-teal,#0f766e)]">
              {{ selectedInterestData.label }}
            </span>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5">
            <button
              v-for="item in interestOptions"
              :key="item.id"
              type="button"
              class="p-3 sm:p-4 rounded-xl border text-left flex flex-col items-center sm:items-start gap-2.5 transition-all duration-200 group"
              :class="selectedInterest === item.id
                ? 'bg-[var(--sarangenge-teal,#0f766e)]/10 border-[var(--sarangenge-teal,#0f766e)] text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200 shadow-md font-bold ring-2 ring-[var(--sarangenge-teal,#0f766e)]/20'
                : 'bg-muted/30 border-border/70 text-muted-foreground hover:border-[var(--sarangenge-teal,#0f766e)]/40 hover:text-foreground'"
              @click="selectedInterest = item.id"
            >
              <component
                :is="item.icon"
                class="w-6 h-6 shrink-0 transition-transform group-hover:scale-110"
                :class="selectedInterest === item.id ? 'text-[var(--sarangenge-teal,#0f766e)] dark:text-teal-300' : 'text-muted-foreground'"
              />
              <span class="text-xs text-center sm:text-left leading-tight font-medium">
                {{ item.shortLabel }}
              </span>
            </button>
          </div>
        </div>

        <!-- Step 2: Gaya Belajar Prioritas -->
        <div class="pt-8 mt-8 border-t border-border/60 space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
            <span class="text-xs font-black uppercase tracking-wider text-muted-foreground font-mono">
              2. {{ t('trackFinder.step2Label', 'Gaya Belajar Paling Nyaman:') }}
            </span>
            <span class="text-xs font-semibold text-[var(--sarangenge-sun,#e8a317)]">
              {{ selectedStyleData.label }}
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <button
              v-for="style in styleOptions"
              :key="style.id"
              type="button"
              class="p-4 rounded-xl border text-left flex items-start gap-3.5 transition-all duration-200"
              :class="selectedStyle === style.id
                ? 'bg-[var(--sarangenge-sun,#e8a317)]/15 border-[var(--sarangenge-sun,#e8a317)] text-foreground shadow-sm font-semibold'
                : 'bg-muted/30 border-border/70 text-muted-foreground hover:border-[var(--sarangenge-sun,#e8a317)]/40 hover:text-foreground'"
              @click="selectedStyle = style.id"
            >
              <component
                :is="style.icon"
                class="w-5 h-5 shrink-0 mt-0.5"
                :class="selectedStyle === style.id ? 'text-[var(--sarangenge-sun,#e8a317)]' : 'text-muted-foreground'"
              />
              <div class="space-y-0.5">
                <div class="text-sm font-bold text-foreground">
                  {{ style.label }}
                </div>
                <div class="text-xs text-muted-foreground">
                  {{ style.desc }}
                </div>
              </div>
            </button>
          </div>
        </div>

        <!-- Step 3: Hasil Rekomendasi Jurusan -->
        <div class="pt-8 mt-8 border-t border-border/60">
          <div class="rounded-2xl bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 text-white p-6 sm:p-8 border border-slate-800 shadow-xl space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-6">
              <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                  <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-400 text-slate-950">
                    {{ currentRecommendation.code }}
                  </span>
                  <span class="text-xs font-mono text-emerald-400 flex items-center gap-1">
                    <CheckCircle2 class="w-3.5 h-3.5" />
                    {{ t('trackFinder.matchRate', '98% Kecocokan Profil') }}
                  </span>
                </div>
                <h3 class="text-2xl sm:text-3xl font-extrabold font-heading text-white">
                  {{ currentRecommendation.name }}
                </h3>
              </div>

              <div class="flex items-center gap-2">
                <Button
                  as="router-link"
                  :to="currentRecommendation.slug ? `/blog/${currentRecommendation.slug}` : '/programs'"
                  variant="primary"
                  size="md"
                  class="!bg-amber-500 hover:!bg-amber-400 !text-slate-950 font-bold"
                >
                  {{ t('trackFinder.exploreMajor', 'Detail Kurikulum') }}
                  <ArrowRight class="w-4 h-4 ml-1" />
                </Button>
                <Button
                  as="router-link"
                  to="/contact"
                  variant="outline"
                  size="md"
                  class="!border-slate-700 !bg-slate-800/80 !text-slate-200 hover:!bg-slate-700"
                >
                  {{ t('trackFinder.askCounselor', 'Konsultasi BK') }}
                </Button>
              </div>
            </div>

            <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-3xl">
              {{ currentRecommendation.description }}
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
              <!-- Kompetensi Inti -->
              <div class="space-y-3">
                <h4 class="text-xs font-black uppercase tracking-wider text-amber-400 font-mono flex items-center gap-1.5">
                  <Sparkles class="w-3.5 h-3.5" />
                  {{ t('trackFinder.competencies', 'Kompetensi Inti yang Dipelajari:') }}
                </h4>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="(comp, cIdx) in currentRecommendation.competencies"
                    :key="cIdx"
                    class="px-3 py-1 rounded-lg bg-slate-800/90 border border-slate-700 text-xs font-medium text-slate-200"
                  >
                    {{ comp }}
                  </span>
                </div>
              </div>

              <!-- Prospek Karir & Industri -->
              <div class="space-y-3">
                <h4 class="text-xs font-black uppercase tracking-wider text-emerald-400 font-mono flex items-center gap-1.5">
                  <Briefcase class="w-3.5 h-3.5" />
                  {{ t('trackFinder.careers', 'Peluang Karir & Industri:') }}
                </h4>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="(career, crIdx) in currentRecommendation.careers"
                    :key="crIdx"
                    class="px-3 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-xs font-medium text-emerald-300"
                  >
                    {{ career }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import {
  Compass,
  Code2,
  CarFront,
  Bike,
  Network,
  Palette,
  Briefcase,
  Calculator,
  Utensils,
  Zap,
  Building,
  Cog,
  Cpu,
  Flame,
  Laptop,
  Hammer,
  Layers,
  Sparkles,
  CheckCircle2,
  ArrowRight
} from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();

const trackFinderEnabled = computed(() => getSetting('track_finder_enabled', true) !== false);

const dynamicPrograms = ref<any[]>([]);
const selectedInterest = ref('digital');
const selectedStyle = ref('system');

interface InterestOption {
  id: string;
  shortLabel: string;
  label: string;
  icon: any;
}

interface StyleOption {
  id: string;
  label: string;
  desc: string;
  icon: any;
}

interface Recommendation {
  code: string;
  name: string;
  slug?: string;
  description: string;
  competencies: string[];
  careers: string[];
}

const defaultStyle: StyleOption = {
  id: 'system',
  label: 'Komputer, Logika & Sistem',
  desc: 'Suka memecahkan masalah, coding algoritma, sistem jaringan, atau kontrol otomatis.',
  icon: Laptop,
};

const styleOptions: StyleOption[] = [
  { id: 'workshop', label: 'Praktik Bengkel Fisik', desc: 'Dominan mengasah keterampilan motorik, perkakas mesin, dan alat mekanik.', icon: Hammer },
  { id: 'design', label: 'Kreativitas Visual & Manajemen', desc: 'Suka merancang visual, media kreatif, tata kelola data, atau tata hidang.', icon: Layers },
  defaultStyle,
];

const selectedStyleData = computed<StyleOption>(() => {
  return styleOptions.find((s) => s.id === selectedStyle.value) ?? defaultStyle;
});

const defaultRecommendation: Recommendation = {
  code: 'VOKASI',
  name: 'Program Keahlian Vokasi Unggulan',
  description: 'Program keahlian vokasi berstandar industri dengan sertifikasi kompetensi nasional, sarana bengkel modern, dan kemitraan dunia usaha & dunia industri (DUDI).',
  competencies: ['Standar Operasional Prosedur (SOP) Industri', 'Praktik Kerja Industri & Teaching Factory', 'Sertifikasi Profesi Kompetensi BNSP', 'Kewirausahaan Mandiri'],
  careers: ['Teknisi Profesional Industri', 'Supervisor & Service Advisor', 'Wirausahawan Mandiri', 'Lanjut Studi Perguruan Tinggi Vokasi'],
};

const resolveProgramProfile = (title: string, slug: string, _raw?: any): { icon: any; shortLabel: string; code: string; defaultComps: string[]; defaultCareers: string[] } => {
  const tLower = title.toLowerCase();
  const sLower = slug.toLowerCase();

  if (sLower.includes('tbsm') || tLower.includes('sepeda motor')) {
    return {
      icon: Bike,
      shortLabel: 'Sepeda Motor',
      code: 'TBSM',
      defaultComps: ['Servis Mesin Injeksi (FI/PGM-FI)', 'Sistem Transmisi Matic & CVT', 'Kelistrikan Bodi & Rem Hidrolik', 'Manajemen Usaha Bengkel Motor'],
      defaultCareers: ['Mekanik Resmi Dealer Motor (AHASS/Yamaha)', 'Kepala Bengkel Servis Motor', 'Part Advisor & Sparepart Specialist', 'Wirausaha Bengkel Motor Mandiri'],
    };
  }

  if (sLower.includes('tkro') || sLower.includes('to') || tLower.includes('kendaraan ringan') || tLower.includes('otomotif')) {
    return {
      icon: CarFront,
      shortLabel: 'Otomotif Mobil',
      code: 'TKRO',
      defaultComps: ['Tune-up & Diagnostic Scanner Engine (OBD-II)', 'Overhaul Mesin Bensin & Diesel', 'Chasis, Suspensi & Transmisi Otomatis', 'Kelistrikan Bodi & AC Mobil Modern'],
      defaultCareers: ['Teknisi Bengkel Resmi (APM / Astra)', 'Service Advisor Otomotif', 'Quality Control Inspector Otomotif', 'Wirausaha Bengkel Mobil Mandiri'],
    };
  }

  if (sLower.includes('tjkt') || sLower.includes('tkj') || tLower.includes('jaringan') || tLower.includes('telekomunikasi')) {
    return {
      icon: Network,
      shortLabel: 'Jaringan & IT',
      code: 'TJKT',
      defaultComps: ['Instalasi & Routing Jaringan (MikroTik/Cisco)', 'Fiber Optic Splicing & OTDR Testing', 'Linux Server & Cloud Computing', 'Keamanan Jaringan & Cyber Security Basics'],
      defaultCareers: ['Network Administrator & Engineer', 'Fiber Optic Technician Specialist', 'Technical Support Specialist', 'Cloud & System Administrator'],
    };
  }

  if (sLower.includes('dkv') || tLower.includes('desain komunikasi') || tLower.includes('multimedia')) {
    return {
      icon: Palette,
      shortLabel: 'Desain Visual',
      code: 'DKV',
      defaultComps: ['Desain Grafis & Vektor Digital', 'Fotografi Komersial & Tata Cahaya Studio', 'Videografi & Editing Sinematografi', 'Animasi 2D & Motion Graphic'],
      defaultCareers: ['Graphic Designer & Brand Specialist', 'Videographer & Video Editor', 'Fotografer Komersial Studio', 'Digital Content Creator & Animator'],
    };
  }

  if (sLower.includes('mplb') || sLower.includes('otkp') || tLower.includes('perkantoran') || tLower.includes('layanan bisnis')) {
    return {
      icon: Briefcase,
      shortLabel: 'Perkantoran',
      code: 'MPLB',
      defaultComps: ['Tata Kelola Kearsipan Digital & E-Office', 'Korespondensi & Komunikasi Bisnis Efektif', 'Aplikasi Perkantoran Terpadu & Spreadsheet', 'Manajemen Rapat, Event & Keprotokolan'],
      defaultCareers: ['Staf Administrasi Perkantoran & E-Office', 'Sekretaris Junior / Executive Assistant', 'Customer Service & Public Relations', 'Staf Personalia (HRD Assistant)'],
    };
  }

  if (sLower.includes('akl') || tLower.includes('akuntansi') || tLower.includes('keuangan')) {
    return {
      icon: Calculator,
      shortLabel: 'Akuntansi',
      code: 'AKL',
      defaultComps: ['Pencatatan Akuntansi Manual & Siklus Finansial', 'Komputer Akuntansi (MYOB & Accurate)', 'Pengelolaan Kas Kecil & Administrasi Pajak', 'Simulasi Pelayanan Bank Mini & Transaksi Kasir'],
      defaultCareers: ['Staf Akuntansi & Pembukuan Perusahaan', 'Kasir & Customer Service Perbankan', 'Staf Perpajakan & Verifikasi Keuangan', 'Asisten Auditor Finansial'],
    };
  }

  if (sLower.includes('kuliner') || tLower.includes('boga') || tLower.includes('makanan')) {
    return {
      icon: Utensils,
      shortLabel: 'Kuliner',
      code: 'Kuliner',
      defaultComps: ['Pengolahan Makanan Nusantara, Kontinental & Oriental', 'Pastry, Bakery & Pembuatan Kue Komersial', 'Higiene Sanitasi & Keselamatan Kerja Kuliner', 'Tata Hidang Restoran & Table Manner'],
      defaultCareers: ['Commis Chef di Hotel & Restoran Berbintang', 'Pastry & Bakery Chef', 'Pengusaha Restoran / Catering Mandiri', 'Food Stylist & Barista'],
    };
  }

  if (sLower.includes('titl') || tLower.includes('listrik')) {
    return {
      icon: Zap,
      shortLabel: 'Listrik',
      code: 'TITL',
      defaultComps: ['Instalasi Penerangan & Tenaga Gedung', 'Wiring Panel Distribusi 3 Fasa', 'Sistem Kendali Motor & Inverter', 'Pemrograman PLC & Sensor Industri'],
      defaultCareers: ['Teknisi Listrik Industri / PLN', 'Operator Panel Listrik Gedung (ME)', 'Maintenance Automation Engineer', 'Kontraktor Kelistrikan'],
    };
  }

  if (sLower.includes('dpib') || tLower.includes('arsitektur') || tLower.includes('bangunan')) {
    return {
      icon: Building,
      shortLabel: 'Arsitektur',
      code: 'DPIB',
      defaultComps: ['Gambar Teknik Arsitektur 2D/3D', 'Building Information Modeling (BIM)', 'Estimasi Biaya & RAB Proyek', 'Perancangan Struktur Beton & Baja'],
      defaultCareers: ['BIM Modeler & Arsitektur Drafter', 'Quantity Surveyor / Estimator', 'Surveyor Pengukuran Tanah', 'Konsultan Desain Interior'],
    };
  }

  if (sLower.includes('tpm') || sLower.includes('mesin')) {
    return {
      icon: Cog,
      shortLabel: 'Mesin Presisi',
      code: 'TPM',
      defaultComps: ['Pemesinan Bubut & Frais Konvensional', 'Pemrograman CNC Turning & Milling', 'CAD/CAM Manufaktur Simulation', 'Pengukuran Presisi & Metrologi Industri'],
      defaultCareers: ['CNC Machinist & Programmer', 'Toolmaker Industri Manufaktur', 'Quality Control Inspector', 'Teknisi Bubut & Fabrikasi Presisi'],
    };
  }

  if (sLower.includes('tei') || sLower.includes('elektronika')) {
    return {
      icon: Cpu,
      shortLabel: 'Elektronika',
      code: 'TEI',
      defaultComps: ['Desain Skematik & PCB Layout', 'Mikrokontroler & IoT Programming', 'Sistem Audio Video Digital', 'Pemeliharaan Robotika & Sensor Industri'],
      defaultCareers: ['Teknisi Elektronika & Audio Video', 'IoT Hardware Specialist', 'Teknisi Pemeliharaan Instrumen Pabrik', 'Pemasang Perangkat Smart Home'],
    };
  }

  if (sLower.includes('tpfl') || tLower.includes('las') || tLower.includes('pengelasan')) {
    return {
      icon: Flame,
      shortLabel: 'Las & Fabrikasi',
      code: 'TPFL',
      defaultComps: ['Las SMAW 1G s.d. 3G/4G Pelat & Pipa', 'Las GMAW / MIG Industri Karoseri', 'Las TIG / Argon Stainless & Aluminium', 'Fabrikasi & Perakitan Struktur Logam'],
      defaultCareers: ['Welder Bersertifikat BNSP / MIGAS', 'Welding Inspector & Supervisor', 'Fabrikator Struktur Baja & Pipa', 'Wirausaha Fabrikasi & Konstruksi'],
    };
  }

  return {
    icon: Code2,
    shortLabel: title.slice(0, 14),
    code: slug.toUpperCase(),
    defaultComps: ['Kurikulum Merdeka Kejuruan', 'Praktik Industri & Bengkel Standar', 'Sertifikasi Kompetensi Keahlian', 'Kewirausahaan Berbasis Vokasi'],
    defaultCareers: ['Teknisi Spesialis Bidang Keahlian', 'Supervisor Operasional', 'Tenaga Kerja Mandiri & Wirausaha', 'Melanjutkan ke Pendidikan Tinggi Vokasi'],
  };
};

const staticInterestOptions: InterestOption[] = [
  { id: 'digital', shortLabel: 'Software & AI', label: 'Teknologi Software, Pemrograman & AI', icon: Code2 },
  { id: 'otomotif', shortLabel: 'Otomotif Modern', label: 'Teknologi Otomotif & Kendaraan Ringan', icon: CarFront },
  { id: 'motor', shortLabel: 'Sepeda Motor', label: 'Teknik Bisnis Sepeda Motor & Injeksi', icon: Bike },
  { id: 'jaringan', shortLabel: 'Jaringan & Cyber', label: 'Teknik Komputer, Jaringan & Telekomunikasi', icon: Network },
  { id: 'desain', shortLabel: 'Desain Visual', label: 'Desain Komunikasi Visual & Multimedia', icon: Palette },
  { id: 'perkantoran', shortLabel: 'Perkantoran', label: 'Manajemen Perkantoran & Bisnis Digital', icon: Briefcase },
  { id: 'akuntansi', shortLabel: 'Akuntansi', label: 'Akuntansi & Layanan Keuangan Lembaga', icon: Calculator },
];

const interestOptions = computed<InterestOption[]>(() => {
  if (dynamicPrograms.value.length > 0) {
    return dynamicPrograms.value.map((item: any) => {
      const raw = item._raw || item;
      const profile = resolveProgramProfile(item.title || '', item.slug || '', raw);
      return {
        id: item.slug,
        shortLabel: profile.shortLabel,
        label: item.title,
        icon: profile.icon,
      };
    });
  }
  return staticInterestOptions;
});

const fallbackInterest: InterestOption = staticInterestOptions[0]!;

const selectedInterestData = computed<InterestOption>(() => {
  return interestOptions.value.find((i) => i.id === selectedInterest.value) ?? interestOptions.value[0] ?? fallbackInterest;
});

const currentRecommendation = computed<Recommendation>(() => {
  if (dynamicPrograms.value.length > 0) {
    const item = dynamicPrograms.value.find((p: any) => p.slug === selectedInterest.value) || dynamicPrograms.value[0];
    if (item) {
      const raw = item._raw || item;
      const meta = raw.meta || {};
      const profile = resolveProgramProfile(item.title || '', item.slug || '', raw);
      return {
        code: meta.code || profile.code,
        name: item.title,
        slug: item.slug,
        description: item.excerpt || item.intro || raw.description || defaultRecommendation.description,
        competencies: Array.isArray(meta.competencies) && meta.competencies.length > 0 ? meta.competencies : profile.defaultComps,
        careers: Array.isArray(meta.careers) && meta.careers.length > 0 ? meta.careers : profile.defaultCareers,
      };
    }
  }

  return defaultRecommendation;
});

onMounted(async () => {
  try {
    const res = await api.get(publishingPaths.publicContents, {
      params: { category: 'program-keahlian', status: 'published', sort: 'title' },
    });
    const data = res?.data;
    const items = Array.isArray(data)
      ? data
      : Array.isArray(data?.data)
        ? data.data
        : Array.isArray(data?.data?.data)
          ? data.data.data
          : [];

    if (items.length > 0) {
      dynamicPrograms.value = items;
      if (items[0]?.slug) {
        selectedInterest.value = items[0].slug;
      }
    }
  } catch {
    dynamicPrograms.value = [];
  }
});
</script>
