<template>
  <section
    id="calculator"
    data-ja-customizer-target="calculator"
    class="py-20 bg-slate-950 text-white relative overflow-hidden scroll-mt-24"
  >
    <span id="simulator" class="sr-only">Simulator</span>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 relative z-10">
      <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/20 text-cyan-400 border border-cyan-500/40 uppercase tracking-wider font-mono">
          {{ t('calculator.badge', 'Simulator Bandwidth Interaktif') }}
        </span>
        <h2 class="text-3xl sm:text-4xl font-extrabold font-heading tracking-tight text-white">
          {{ t('calculator.title', 'Hitung Kebutuhan Bandwidth & Solusi Ideal Kantor Anda') }}
        </h2>
        <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
          {{ t('calculator.subtitle', 'Sesuaikan jumlah perangkat aktif, tipe operasional cloud, dan dapatkan rekomendasi paket dedicated internet terbaik.') }}
        </p>
      </div>

      <!-- Interactive Calculator Card -->
      <div class="max-w-4xl mx-auto bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Input 1: User Count Slider/Options -->
          <div class="space-y-4">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 font-mono">
              {{ t('calculator.usersLabel', 'Jumlah Pengguna / Perangkat:') }}
              <strong class="text-sky-400 text-sm ml-2">{{ userCount }} Users</strong>
            </label>
            <input
              v-model.number="userCount"
              type="range"
              min="5"
              max="500"
              step="5"
              class="w-full accent-sky-500 bg-slate-950 h-2 rounded-lg cursor-pointer"
            >
            <div class="flex justify-between text-[11px] text-slate-500 font-mono">
              <span>5 Users</span>
              <span>100</span>
              <span>250</span>
              <span>500+ Users</span>
            </div>
          </div>

          <!-- Input 2: Workload Select -->
          <div class="space-y-4">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 font-mono">
              {{ t('calculator.cloudUsageLabel', 'Aktivitas Bisnis:') }}
            </label>
            <div class="grid grid-cols-2 gap-2 text-xs">
              <button
                v-for="w in workloads"
                :key="w.id"
                type="button"
                class="p-2.5 rounded-xl border text-left font-medium transition-all"
                :class="selectedWorkload === w.id ? 'bg-sky-500/20 border-sky-500 text-sky-300 font-bold' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'"
                @click="selectedWorkload = w.id"
              >
                <div class="text-white">{{ w.label }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5">{{ w.sub }}</div>
              </button>
            </div>
          </div>
        </div>

        <!-- Calculated Output Banner -->
        <div class="bg-gradient-to-r from-sky-950/60 to-slate-950 border border-sky-500/40 rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-6">
          <div class="space-y-2 text-center sm:text-left">
            <span class="text-xs font-mono text-slate-400 uppercase tracking-wider block">
              {{ t('calculator.recommendedPlan', 'Rekomendasi Paket:') }}
            </span>
            <div class="flex items-baseline gap-3 justify-center sm:justify-start">
              <span class="text-3xl sm:text-4xl font-black text-sky-400 font-heading">
                {{ calculatedSpeed }}
              </span>
              <span class="text-sm font-mono text-emerald-400 font-bold">
                1:1 Symmetrical Dedicated
              </span>
            </div>
            <p class="text-xs text-slate-400">
              Termasuk alokasi IP publik sesuai paket, SLA tertulis di kontrak, dan dukungan NOC.
            </p>
          </div>

          <Button
            as="router-link"
            to="/contact"
            variant="primary"
            size="md"
            class="font-bold shrink-0 shadow-lg shadow-sky-500/20"
          >
            {{ t('calculator.getQuote', 'Ajukan Proposal Langsung') }}
          </Button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Button } from '@/modules/Layout/views/themes/layung/ui';

const { t } = useThemeI18n('layung');

const userCount = ref(35);
const selectedWorkload = ref('standard');

const workloads = [
  { id: 'standard', label: 'Office & Browsing', sub: 'Email, Docs & Web' },
  { id: 'video', label: 'Video Call & CCTV', sub: 'Zoom HD & Streaming' },
  { id: 'cloud', label: 'Cloud ERP & Server', sub: 'SAP, AWS & Database' },
  { id: 'heavy', label: 'High-Demand & AI', sub: 'Data Center & Big Data' },
];

const calculatedSpeed = computed(() => {
  let multiplier = 1.5;
  if (selectedWorkload.value === 'video') multiplier = 3.0;
  if (selectedWorkload.value === 'cloud') multiplier = 5.0;
  if (selectedWorkload.value === 'heavy') multiplier = 8.0;

  const mbps = Math.ceil((userCount.value * multiplier) / 10) * 10;
  if (mbps >= 1000) {
    return `${(mbps / 1000).toFixed(1)} Gbps DIA`;
  }
  return `${Math.max(50, mbps)} Mbps DIA`;
});
</script>
