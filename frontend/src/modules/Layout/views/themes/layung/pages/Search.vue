<template>
  <div class="py-10 md:py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    <Breadcrumb :items="[{ name: t('pages.search.title', 'Pencarian') }]" />

    <div class="space-y-4">
      <h1 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
        {{ t('pages.search.title', 'Pencarian Layanan & Dokumentasi') }}
      </h1>
      <p class="text-sm text-muted-foreground">
        {{ t('pages.search.subtitle', 'Temukan informasi paket, panduan konfigurasi, dan artikel teknis Layung dengan cepat.') }}
      </p>
    </div>

    <div class="relative">
      <Search class="w-5 h-5 text-muted-foreground absolute left-4 top-1/2 -translate-y-1/2" />
      <input
        v-model="query"
        type="text"
        placeholder="Ketik kata kunci (misal: Dedicated, SLA, Peering, SOC, BGP)..."
        class="w-full pl-12 pr-4 py-3 rounded-2xl border border-border bg-card text-foreground placeholder:text-muted-foreground/60 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/50 shadow-sm"
      >
    </div>

    <!-- Quick Filter Tags -->
    <div class="flex flex-wrap gap-2 text-xs font-mono">
      <span class="text-muted-foreground py-1">Pencarian Populer:</span>
      <button
        type="button"
        class="px-2.5 py-1 rounded-md bg-muted text-foreground hover:bg-orange-500/10 hover:text-orange-500 transition-colors"
        @click="query = 'Dedicated 1:1'"
      >
        Dedicated 1:1
      </button>
      <button
        type="button"
        class="px-2.5 py-1 rounded-md bg-muted text-foreground hover:bg-orange-500/10 hover:text-orange-500 transition-colors"
        @click="query = 'SLA 99.999%'"
      >
        SLA 99.999%
      </button>
      <button
        type="button"
        class="px-2.5 py-1 rounded-md bg-muted text-foreground hover:bg-orange-500/10 hover:text-orange-500 transition-colors"
        @click="query = 'Managed SD-WAN'"
      >
        Managed SD-WAN
      </button>
      <button
        type="button"
        class="px-2.5 py-1 rounded-md bg-muted text-foreground hover:bg-orange-500/10 hover:text-orange-500 transition-colors"
        @click="query = 'ISO 27001'"
      >
        ISO 27001
      </button>
    </div>

    <!-- Search Results / Directory Links -->
    <div class="space-y-4 pt-4">
      <div
        v-for="(item, idx) in filteredItems"
        :key="idx"
        class="layung-panel p-5 hover:border-orange-500/40 transition-all flex items-center justify-between"
      >
        <div class="space-y-1">
          <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-orange-500 px-2 py-0.5 rounded bg-orange-500/10">
            {{ item.category }}
          </span>
          <h3 class="text-base font-bold text-foreground font-heading">
            {{ item.title }}
          </h3>
          <p class="text-xs text-muted-foreground">
            {{ item.desc }}
          </p>
        </div>
        <router-link
          :to="item.path"
          class="p-2 rounded-xl bg-muted/60 text-muted-foreground hover:text-primary hover:bg-primary/10 transition-colors shrink-0 ml-4"
        >
          <ArrowRight class="w-4 h-4" />
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Search, ArrowRight } from 'lucide-vue-next';
import Breadcrumb from '../components/shared/Breadcrumb.vue';

const { t } = useThemeI18n('layung');
const query = ref('');

const allItems = [
  {
    title: 'Dedicated Internet Access (DIA 1:1 Symmetrical)',
    category: 'Layanan Fiber',
    desc: 'Konektivitas serat optik tanpa batas dengan rasio upload dan download simetris murni serta IP Statis.',
    path: '/services',
  },
  {
    title: '24/7 Cyber Security SOC & DDoS Mitigation',
    category: 'Managed Services',
    desc: 'Pemantauan lalu lintas data real-time dan mitigasi serangan DDoS otomatis hingga kapasitas multi-terabit.',
    path: '/solusi',
  },
  {
    title: 'Jaminan Service Level Agreement (SLA) 99.999%',
    category: 'Standar & Mutu',
    desc: 'Komitmen ketersediaan jaringan tertinggi dengan respon tiket NOC 15 menit dan ganti rugi potongan tagihan.',
    path: '/achievement',
  },
  {
    title: 'Paket Bandwidth SME & Corporate Office',
    category: 'Tarif & Paket',
    desc: 'Daftar rincian harga paket Business Broadband dan Dedicated Fiber sesuai kebutuhan kapasitas gedung.',
    path: '/pricing',
  },
  {
    title: 'Pusat Karir & Rekrutmen NOC Engineer',
    category: 'Karir',
    desc: 'Lowongan pekerjaan posisi Network Lead, DevOps Engineer, dan Outside Plant Fiber Specialist.',
    path: '/career-center',
  },
];

const filteredItems = computed(() => {
  if (!query.value.trim()) return allItems;
  const q = query.value.toLowerCase();
  return allItems.filter(
    (i) => i.title.toLowerCase().includes(q) || i.desc.toLowerCase().includes(q) || i.category.toLowerCase().includes(q)
  );
});
</script>
