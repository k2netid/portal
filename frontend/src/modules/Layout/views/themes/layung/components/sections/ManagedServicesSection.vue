<template>
  <section class="py-20 sm:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
    <div class="text-center max-w-3xl mx-auto space-y-4">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 uppercase tracking-wider font-mono">
        Managed IT & SOC Services
      </span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
        Serahkan Operasional Infrastruktur IT pada Tim Ahli Bersertifikasi
      </h2>
      <p class="text-muted-foreground text-sm sm:text-base leading-relaxed">
        Fokus kembangkan bisnis Anda, biarkan tim Network & Cyber Security Engineer kami yang mengelola server, firewall, dan bandwidth 24 jam non-stop.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div
        v-for="(svc, idx) in services"
        :key="idx"
        class="layung-panel p-8 space-y-4 hover:border-cyan-500/50 transition-all group"
      >
        <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center group-hover:scale-105 transition-transform">
          <component
            :is="svc.icon"
            class="w-6 h-6"
          />
        </div>
        <h3 class="text-xl font-bold text-foreground font-heading">
          {{ svc.title }}
        </h3>
        <p class="text-xs sm:text-sm text-muted-foreground leading-relaxed">
          {{ svc.description }}
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { Lock, Cloud, ShieldAlert, Cpu, HardDrive, Terminal } from 'lucide-vue-next';

const services = [
  {
    icon: Lock,
    title: 'Security Operations Center (SOC)',
    description: 'Pemantauan lalu lintas data real-time, deteksi intrusi, dan mitigasi DDoS otomatis hingga kapasitas terabit.',
  },
  {
    icon: Cloud,
    title: 'Hybrid Cloud Direct Connect',
    description: 'Koneksi privat berkecepatan tinggi ke AWS, Google Cloud, dan Microsoft Azure tanpa melewati jalur publik.',
  },
  {
    icon: ShieldAlert,
    title: 'Managed Next-Gen Firewall (NGFW)',
    description: 'Penyaringan aplikasi, inspeksi SSL, pencegahan malware tingkat lanjut, dan VPN kantor terenkripsi kuat.',
  },
  {
    icon: Cpu,
    title: 'Managed SD-WAN & Multi-Branch',
    description: 'Pengaturan cerdas koneksi multi-cabang dengan failover instan tanpa gangguan saat kabel utama putus.',
  },
  {
    icon: HardDrive,
    title: 'Disaster Recovery & Backup',
    description: 'Replikasi data otomatis antar data center lokal dengan Recovery Time Objective (RTO) hitungan detik.',
  },
  {
    icon: Terminal,
    title: 'IT Infrastructure Automation',
    description: 'Otomasi konfigurasi switch, router BGP, dan monitoring SNMP yang terintegrasi dengan dashboard klien.',
  },
];
</script>
