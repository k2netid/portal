<template>
  <section
    v-if="trackFinderEnabled"
    id="track-finder"
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
                  to="/programs"
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
import { ref, computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import {
  Compass,
  Code2,
  CarFront,
  Zap,
  Building,
  Cog,
  Cpu,
  Flame,
  Laptop,
  Hammer,
  Layers,
  Sparkles,
  Briefcase,
  CheckCircle2,
  ArrowRight
} from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();

const trackFinderEnabled = computed(() => getSetting('track_finder_enabled', true) !== false);

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
  description: string;
  competencies: string[];
  careers: string[];
}

const defaultInterest: InterestOption = {
  id: 'digital',
  shortLabel: 'Software & AI',
  label: 'Teknologi Software, Pemrograman & AI',
  icon: Code2,
};

const defaultStyle: StyleOption = {
  id: 'system',
  label: 'Komputer, Logika & Sistem',
  desc: 'Suka memecahkan masalah, coding algoritma, atau kontrol otomatis.',
  icon: Laptop,
};

const defaultRecommendation: Recommendation = {
  code: 'PPLG',
  name: 'Pengembangan Perangkat Lunak & Gim',
  description: 'Program keahlian unggulan dalam rekayasa perangkat lunak modern, pengembangan aplikasi web fullstack, mobile iOS/Android, pengembangan kecerdasan buatan (AI), serta perancangan video game interaktif.',
  competencies: ['Pemrograman Web & Framework Modern', 'Mobile App Development', 'Database & Cloud Computing', 'Game Logic & UI/UX Design'],
  careers: ['Software Engineer / Web Developer', 'Mobile Application Developer', 'Game Programmer', 'IT Support & QA Tester'],
};

const interestOptions: InterestOption[] = [
  defaultInterest,
  { id: 'otomotif', shortLabel: 'Otomotif Modern', label: 'Teknologi Otomotif & Kendaraan Ringan', icon: CarFront },
  { id: 'listrik', shortLabel: 'Listrik & Otomasi', label: 'Instalasi Tenaga Listrik & Otomasi Pabrik', icon: Zap },
  { id: 'arsitektur', shortLabel: 'Desain Bangunan', label: 'Desain Pemodelan Bangunan & Arsitektur BIM', icon: Building },
  { id: 'mesin', shortLabel: 'Mesin Presisi', label: 'Teknik Pemesinan & Manufaktur Presisi CNC', icon: Cog },
  { id: 'elektronika', shortLabel: 'Elektronika & IoT', label: 'Elektronika Industri & Internet of Things (IoT)', icon: Cpu },
  { id: 'pengelasan', shortLabel: 'Las & Fabrikasi', label: 'Fabrikasi Logam & Pengelasan Industri', icon: Flame },
];

const styleOptions: StyleOption[] = [
  { id: 'workshop', label: 'Praktik Bengkel Fisik', desc: 'Dominan mengasah keterampilan motorik dan alat mekanik.', icon: Hammer },
  { id: 'design', label: 'Rancang Bangun & Kreativitas', desc: 'Suka menggambar teknis, visualisasi 3D, dan sketsa arsitektur.', icon: Layers },
  defaultStyle,
];

const selectedInterestData = computed<InterestOption>(() => {
  return interestOptions.find((i) => i.id === selectedInterest.value) ?? defaultInterest;
});

const selectedStyleData = computed<StyleOption>(() => {
  return styleOptions.find((s) => s.id === selectedStyle.value) ?? defaultStyle;
});

const recommendationCatalog: Record<string, Recommendation> = {
  digital: defaultRecommendation,
  otomotif: {
    code: 'TO / TKRO',
    name: 'Teknik Kendaraan Ringan Otomotif',
    description: 'Mempersiapkan teknisi profesional dalam perawatan dan perbaikan kendaraan roda empat modern, sistem Electronic Fuel Injection (EFI), transmisi otomatis, rem ABS, dan teknologi kendaraan listrik (EV).',
    competencies: ['Tune-up & Diagnostic Scanner Engine', 'Overhaul Mesin Bensin & Diesel', 'Chasis, Suspensi & Transmisi Otomatis', 'Kelistrikan Bodi & AC Mobil'],
    careers: ['Teknisi Bengkel Resmi (APM)', 'Mekanik Service Advisor', 'Teknisi Kendaraan Listrik', 'Wirausaha Bengkel Otomotif'],
  },
  listrik: {
    code: 'TITL',
    name: 'Teknik Instalasi Tenaga Listrik',
    description: 'Mencetak ahli kelistrikan industri dan gedung bertingkat, penguasaan panel kendali motor listrik kontaktor, otomasi berbasis Programmable Logic Controller (PLC), serta pemasangan sistem PLTS Solar Cell.',
    competencies: ['Instalasi Penerangan & Tenaga Gedung', 'Wiring Panel Distribusi 3 Fasa', 'Sistem Kendali Motor & Inverter', 'Pemrograman PLC & Sensor Industri'],
    careers: ['Teknisi Listrik Industri / PLN', 'Operator Panel Listrik Gedung (ME)', 'Maintenance Automation Engineer', 'Kontraktor Kelistrikan'],
  },
  arsitektur: {
    code: 'DPIB',
    name: 'Desain Pemodelan & Informasi Bangunan',
    description: 'Keahlian dalam perancangan arsitektur dan konstruksi menggunakan software standar internasional CAD 2D/3D, SketchUp, Revit, dan metodologi Building Information Modeling (BIM) untuk proyek infrastruktur modern.',
    competencies: ['Gambar Teknik Arsitektur 2D/3D', 'Building Information Modeling (BIM)', 'Estimasi Biaya & RAB Proyek', 'Perancangan Struktur Beton & Baja'],
    careers: ['BIM Modeler & Arsitektur Drafter', 'Quantity Surveyor / Estimator', 'Surveyor Pengukuran Tanah', 'Konsultan Desain Interior'],
  },
  mesin: {
    code: 'TP / TPM',
    name: 'Teknik Pemesinan & Manufaktur',
    description: 'Pusat kompetensi permesinan logam presisi tinggi yang melatih siswa mengoperasikan mesin bubut, milling, gerinda presisi, serta pemrograman mesin Computer Numerical Control (CNC) standar industri kedirgantaraan & otomotif.',
    competencies: ['Pemesinan Bubut & Frais Konvensional', 'Pemrograman CNC Turning & Milling (G-Code)', 'CAD/CAM Manufaktur Simulation', 'Pengukuran Presisi & Metrologi Industri'],
    careers: ['CNC Machinist & Programmer', 'Toolmaker Industri Manufaktur', 'Quality Control Inspector', 'Teknisi Bubut & Fabrikasi Presisi'],
  },
  elektronika: {
    code: 'TEI / TAV',
    name: 'Teknik Elektronika Industri & Audio Video',
    description: 'Mengintegrasikan perancangan sirkuit mikroelektronika, mikrokontroler Arduino/ESP32, Internet of Things (IoT), sistem telekomunikasi nirkabel, instrumentasi sensor, dan pemeliharaan robotika pabrik.',
    competencies: ['Desain Skematik & PCB Layout', 'Mikrokontroler & IoT Programming', 'Sistem Audio Video Digital', 'Pemeliharaan Robotika & Sensor Industri'],
    careers: ['Teknisi Elektronika & Audio Video', 'IoT Hardware Specialist', 'Teknisi Pemeliharaan Instrumen Pabrik', 'Pemasang Perangkat Smart Home'],
  },
  pengelasan: {
    code: 'TPFL',
    name: 'Teknik Pengelasan & Fabrikasi Logam',
    description: 'Menghasilkan welder tersertifikasi nasional/internasional yang menguasai teknik las busur listrik (SMAW), las gas pelindung (GMAW/MIG/MAG), las argon (GTAW/TIG), serta fabrikasi konstruksi pipa dan baja berat.',
    competencies: ['Las SMAW 1G s.d. 3G/4G Pelat & Pipa', 'Las GMAW / MIG Industri Karoseri', 'Las TIG / Argon Stainless & Aluminium', 'Fabrikasi & Perakitan Struktur Logam'],
    careers: ['Welder Bersertifikat BNSP / MIGAS', 'Welding Inspector & Supervisor', 'Fabrikator Struktur Baja & Pipa', 'Wirausaha Fabrikasi & Konstruksi'],
  },
};

const currentRecommendation = computed<Recommendation>(() => {
  return recommendationCatalog[selectedInterest.value] ?? defaultRecommendation;
});
</script>
