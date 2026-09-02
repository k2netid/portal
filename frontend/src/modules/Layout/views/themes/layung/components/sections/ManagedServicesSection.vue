<template>
  <section
    id="layanan"
    class="py-12 sm:py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 scroll-mt-24"
  >
    <span id="isp" class="sr-only">ISP</span>
    <span id="msp" class="sr-only">MSP</span>
    <div class="text-center max-w-3xl mx-auto space-y-4">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 uppercase tracking-wider font-mono">
        {{ t('servicesTabs.badge', 'Layanan ISP & MSP') }}
      </span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
        {{ t('servicesTabs.title', 'Konektivitas dan operasional IT untuk sekolah & institusi') }}
      </h2>
      <p class="text-muted-foreground text-sm sm:text-base leading-relaxed">
        {{ t('servicesTabs.subtitle', 'Pilih tab sesuai kebutuhan: internet dari K2NET, atau pendampingan IT harian di lingkungan sekolah.') }}
      </p>

      <div class="inline-flex p-1.5 rounded-2xl bg-muted/80 border border-border text-xs font-bold gap-1 mt-2">
        <button
          type="button"
          class="px-4 py-2 rounded-xl transition-all"
          :class="activeTab === 'isp' ? 'bg-primary text-primary-foreground shadow-md' : 'text-muted-foreground hover:text-foreground'"
          @click="selectTab('isp')"
        >
          {{ t('servicesTabs.ispTab', 'Internet (ISP)') }}
        </button>
        <button
          type="button"
          class="px-4 py-2 rounded-xl transition-all"
          :class="activeTab === 'msp' ? 'bg-primary text-primary-foreground shadow-md' : 'text-muted-foreground hover:text-foreground'"
          @click="selectTab('msp')"
        >
          {{ t('servicesTabs.mspTab', 'Managed Services (MSP)') }}
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="item in currentItems"
        :key="item.title"
        class="layung-panel p-6 sm:p-8 space-y-4 flex flex-col"
      >
        <div class="w-12 h-12 rounded-xl bg-sky-500/10 text-sky-500 flex items-center justify-center">
          <component
            :is="item.icon"
            class="w-6 h-6"
          />
        </div>
        <h3 class="text-lg font-bold text-foreground font-heading">
          {{ item.title }}
        </h3>
        <p class="text-sm text-muted-foreground leading-relaxed flex-1">
          {{ item.description }}
        </p>
      </div>
    </div>

    <div class="flex justify-center">
      <Button
        :as="RouterLink"
        :to="activeTab === 'isp' ? '/pricing/isp' : '/pricing/msp'"
        variant="primary"
        size="md"
        class="font-bold"
      >
        <Package class="w-4 h-4" />
        {{ activeTab === 'isp'
          ? t('bento.ispPricingCta', 'Lihat paket internet')
          : t('bento.mspPricingCta', 'Lihat paket MSP') }}
      </Button>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import {
  Network, Wifi, Home, MapPin, Globe, Headset,
  Cable, Wrench, Server, Cctv, CalendarCheck, Briefcase,
  Package,
} from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Button } from '@/modules/Layout/views/themes/layung/ui';

type TabId = 'isp' | 'msp';

const { t } = useThemeI18n('layung');
const route = useRoute();
const router = useRouter();
const activeTab = ref<TabId>('isp');

const hashToTab = (hash: string): TabId | null => {
  const id = hash.replace(/^#/, '').trim();
  if (id === 'isp' || id === 'dia') return 'isp';
  if (id === 'msp' || id === 'soc' || id === 'sdwan' || id === 'solusi') return 'msp';
  return null;
};

const syncFromRoute = () => {
  const fromHash = hashToTab(route.hash || '');
  if (fromHash) {
    activeTab.value = fromHash;
    return;
  }
  if (route.path.startsWith('/solusi') || route.path.startsWith('/services')) {
    activeTab.value = 'msp';
  }
};

const selectTab = (tab: TabId) => {
  activeTab.value = tab;
  if (route.path === '/' || route.path === '') {
    void router.replace({ hash: `#${tab}` });
  }
};

const ispItems = computed(() => [
  {
    icon: Network,
    title: t('servicesTabs.isp1Title', 'Dedicated Internet'),
    description: t('servicesTabs.isp1Desc', 'Koneksi dedicated untuk kantor dan institusi. Kapasitas dan SLA disepakati di kontrak setelah survei lokasi.'),
  },
  {
    icon: Wifi,
    title: t('servicesTabs.isp2Title', 'Broadband Bisnis (SOHO)'),
    description: t('servicesTabs.isp2Desc', 'Internet 50–100 Mbps untuk ruko, kantor kecil, dan unit sekolah yang butuh kapasitas lebih dari paket rumah.'),
  },
  {
    icon: Home,
    title: t('servicesTabs.isp3Title', 'Retail Broadband'),
    description: t('servicesTabs.isp3Desc', 'Paket rumah tangga dan ritel di area jangkauan K2NET, mulai 10 Mbps (up to 15 Mbps).'),
  },
  {
    icon: MapPin,
    title: t('servicesTabs.isp4Title', 'Survei & aktivasi last-mile'),
    description: t('servicesTabs.isp4Desc', 'Pengecekan jangkauan di Bandung dan sekitarnya, lalu instalasi sesuai kesiapan infrastruktur di lokasi.'),
  },
  {
    icon: Globe,
    title: t('servicesTabs.isp5Title', 'IP publik sesuai paket'),
    description: t('servicesTabs.isp5Desc', 'Alokasi IP publik untuk Dedicated Internet mengikuti kebutuhan layanan, bukan janji blok /29 untuk semua paket.'),
  },
  {
    icon: Headset,
    title: t('servicesTabs.isp6Title', 'NOC & eskalasi gangguan'),
    description: t('servicesTabs.isp6Desc', 'Laporan gangguan ditangani NOC dan Service Desk. Waktu respon mengikuti klausul kontrak, bukan klaim 15 menit universal.'),
  },
]);

const mspItems = computed(() => [
  {
    icon: Cable,
    title: t('servicesTabs.msp1Title', 'Instalasi jaringan'),
    description: t('servicesTabs.msp1Desc', 'Pemasangan LAN, WiFi, dan dokumentasi dasar untuk gedung sekolah atau kantor — sesuai denah dan jumlah titik.'),
  },
  {
    icon: Wrench,
    title: t('servicesTabs.msp2Title', 'Perawatan jaringan'),
    description: t('servicesTabs.msp2Desc', 'Kunjungan berkala, perbaikan gangguan, dan pengecekan perangkat aktif agar lab dan administrasi tetap terhubung.'),
  },
  {
    icon: Server,
    title: t('servicesTabs.msp3Title', 'Server & aplikasi sekolah'),
    description: t('servicesTabs.msp3Desc', 'Instalasi dan perawatan server lokal serta aplikasi yang dipakai sekolah (administrasi, e-learning, ujian).'),
  },
  {
    icon: Cctv,
    title: t('servicesTabs.msp4Title', 'Instalasi & perawatan CCTV'),
    description: t('servicesTabs.msp4Desc', 'Pemasangan kamera, rekaman, dan perawatan berkala untuk area sekolah yang disepakati dalam kontrak.'),
  },
  {
    icon: CalendarCheck,
    title: t('servicesTabs.msp5Title', 'Pendampingan kegiatan IT'),
    description: t('servicesTabs.msp5Desc', 'Dukungan saat ujian berbasis komputer, penerimaan peserta didik, atau acara sekolah yang membutuhkan jaringan stabil.'),
  },
  {
    icon: Briefcase,
    title: t('servicesTabs.msp6Title', 'Pekerjaan IT lainnya'),
    description: t('servicesTabs.msp6Desc', 'Lingkup tambahan sesuai kontrak Custom: multi-gedung, lab, atau proyek yang tidak masuk paket Basic/Standard.'),
  },
]);

const currentItems = computed(() => (activeTab.value === 'msp' ? mspItems.value : ispItems.value));

onMounted(syncFromRoute);
watch(() => route.hash, syncFromRoute);
watch(() => route.path, syncFromRoute);
</script>
