<template>
  <section
    data-ja-customizer-target="packages"
    class="py-20 sm:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12"
  >
    <div class="text-center max-w-3xl mx-auto space-y-4">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider font-mono">
        {{ t('packages.badge', 'Skema Paket & Kapasitas') }}
      </span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
        {{ t('packages.title', 'Pilihan Paket Konektivitas Sesuai Kebutuhan Industri') }}
      </h2>
      <p class="text-muted-foreground text-sm sm:text-base leading-relaxed">
        {{ t('packages.subtitle', 'Mulai dari bisnis berkembang hingga skala enterprise multi-cabang dengan alokasi bandwidth terjamin.') }}
      </p>

      <!-- Category Tabs -->
      <div class="inline-flex p-1.5 rounded-2xl bg-muted/80 border border-border text-xs font-bold gap-1 mt-4">
        <button
          type="button"
          class="px-4 py-2 rounded-xl transition-all"
          :class="activeTab === 'enterprise' ? 'bg-primary text-white shadow-md' : 'text-muted-foreground hover:text-foreground'"
          @click="activeTab = 'enterprise'"
        >
          {{ t('packages.enterpriseTab', 'Dedicated Internet (DIA)') }}
        </button>
        <button
          type="button"
          class="px-4 py-2 rounded-xl transition-all"
          :class="activeTab === 'biz' ? 'bg-primary text-white shadow-md' : 'text-muted-foreground hover:text-foreground'"
          @click="activeTab = 'biz'"
        >
          {{ t('packages.bizTab', 'Business Broadband') }}
        </button>
        <button
          type="button"
          class="px-4 py-2 rounded-xl transition-all"
          :class="activeTab === 'managed' ? 'bg-primary text-white shadow-md' : 'text-muted-foreground hover:text-foreground'"
          @click="activeTab = 'managed'"
        >
          {{ t('packages.managedTab', 'Managed Cloud & SOC') }}
        </button>
      </div>
    </div>

    <!-- Package Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div
        v-for="(pkg, idx) in currentPlans"
        :key="idx"
        class="layung-panel p-8 space-y-6 flex flex-col justify-between relative transition-all hover:-translate-y-1"
        :class="{ 'border-2 border-orange-500 shadow-xl bg-gradient-to-b from-card to-orange-500/5': pkg.popular }"
      >
        <div
          v-if="pkg.popular"
          class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full text-[10px] font-black bg-orange-500 text-white uppercase tracking-widest shadow-md"
        >
          Pilihan Korporat
        </div>

        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-orange-500 font-mono">
              {{ pkg.tier }}
            </span>
            <span class="text-xs font-mono px-2 py-0.5 rounded bg-muted text-muted-foreground">
              {{ pkg.sla }}
            </span>
          </div>

          <h3 class="text-2xl font-bold text-foreground font-heading">
            {{ pkg.name }}
          </h3>
          <p class="text-xs text-muted-foreground">
            {{ pkg.description }}
          </p>

          <div class="pt-4 border-t border-border/80 space-y-1">
            <span class="text-xs text-muted-foreground block">Mulai dari</span>
            <div class="flex items-baseline gap-1">
              <strong class="text-2xl sm:text-3xl font-black text-foreground font-heading">{{ pkg.price }}</strong>
              <span class="text-xs text-muted-foreground">/ bulan</span>
            </div>
          </div>

          <ul class="space-y-2.5 pt-4 text-xs text-muted-foreground">
            <li
              v-for="(feat, fIdx) in pkg.features"
              :key="fIdx"
              class="flex items-center gap-2"
            >
              <Check class="w-4 h-4 text-emerald-500 shrink-0" />
              <span>{{ feat }}</span>
            </li>
          </ul>
        </div>

        <Button
          as="router-link"
          to="/contact"
          :variant="pkg.popular ? 'primary' : 'outline'"
          size="md"
          class="w-full font-bold"
        >
          {{ t('packages.selectPlan', 'Pilih Layanan Ini') }}
        </Button>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Check } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/layung/ui';

const { t } = useThemeI18n('layung');
const activeTab = ref('enterprise');

const enterprisePlans = [
  {
    tier: 'DIA 100M',
    name: 'Enterprise Fiber 100',
    description: 'Koneksi dedicated 1:1 simetris untuk kantor cabang menengah.',
    price: 'Rp 4.500.000',
    sla: 'SLA 99.9%',
    popular: false,
    features: ['100 Mbps Simetris 1:1', 'IP Publik Statis /29', 'Multi-Upstream Redundan', 'NOC 24/7 Monitoring', '15 Menit MTTR Response'],
  },
  {
    tier: 'DIA 500M',
    name: 'Enterprise Core 500',
    description: 'Backbone utama untuk kantor pusat, fintech & sistem perbankan.',
    price: 'Rp 14.000.000',
    sla: 'SLA 99.999%',
    popular: true,
    features: ['500 Mbps Simetris 1:1', 'IP Publik Statis /28', 'DDoS Protection L3/L4', 'Direct Cloud Peering AWS/GCP', 'Dedicated Account Manager'],
  },
  {
    tier: 'DIA 1G+',
    name: 'Metropolitan Gigabit',
    description: 'Kapasitas gigabit tanpa batas untuk data center & platform digital.',
    price: 'Rp 26.000.000',
    sla: 'SLA 99.999%',
    popular: false,
    features: ['1 Gbps - 10 Gbps Symmetrical', 'BGP Peering & ASN Announcement', 'Dark Fiber Backup Link', 'SOC Cyber Security Alert', 'Custom MTTR SLA 10 Mnt'],
  },
];

const bizPlans = [
  {
    tier: 'BIZ 100M',
    name: 'Business Pro 100',
    description: 'Internet stabil untuk ruko dan kafe bertrafik tinggi.',
    price: 'Rp 850.000',
    sla: 'SLA 99.0%',
    popular: false,
    features: ['100 Mbps Up to Symmetrical', '1 IP Publik Statis', 'Gratis Sewa Router AC Gigabit', 'Dukungan On-Site 8 Jam'],
  },
  {
    tier: 'BIZ 300M',
    name: 'Business Prime 300',
    description: 'Solusi terbaik untuk startup dan co-working space.',
    price: 'Rp 1.650.000',
    sla: 'SLA 99.5%',
    popular: true,
    features: ['300 Mbps Up to Symmetrical', '2 IP Publik Statis', 'Prioritas Bandwidth Jam Kerja', 'Dukungan On-Site 4 Jam'],
  },
  {
    tier: 'BIZ 500M',
    name: 'Business Elite 500',
    description: 'Kapasitas besar untuk video conference & streaming lancar.',
    price: 'Rp 2.750.000',
    sla: 'SLA 99.5%',
    popular: false,
    features: ['500 Mbps Up to Symmetrical', 'IP Publik /29', 'Dual-WAN Router Support', 'Dukungan Prioritas 24/7'],
  },
];

const managedPlans = [
  {
    tier: 'SD-WAN',
    name: 'Managed SD-WAN Core',
    description: 'Manajemen jaringan multi-cabang terpusat dan otomatis.',
    price: 'Rp 3.500.000',
    sla: 'SLA 99.99%',
    popular: false,
    features: ['Zero-Touch Provisioning', 'Dynamic Traffic Steering', 'Cloud Dashboard Analytics', 'Auto-Failover 4G/5G Backup'],
  },
  {
    tier: 'CYBER SOC',
    name: '24/7 Managed SOC & WAF',
    description: 'Pemantauan keamanan siber dan mitigasi serangan DDoS proaktif.',
    price: 'Rp 7.500.000',
    sla: 'SLA 99.999%',
    popular: true,
    features: ['Next-Gen Firewall (NGFW)', '1.2 Tbps DDoS Mitigation', 'Monthly Vulnerability Scan', 'Incident Response Team 24/7'],
  },
  {
    tier: 'HYBRID CLOUD',
    name: 'Cloud Direct Connect',
    description: 'Koneksi privat dedicated ke cloud data center tanpa lewat internet publik.',
    price: 'Hubungi Sales',
    sla: 'SLA 99.999%',
    popular: false,
    features: ['AWS Direct Connect / ExpressRoute', 'Google Cloud Interconnect', 'Private BGP Routing', 'End-to-End Encryption Layer 2'],
  },
];

const currentPlans = computed(() => {
  if (activeTab.value === 'biz') return bizPlans;
  if (activeTab.value === 'managed') return managedPlans;
  return enterprisePlans;
});
</script>
