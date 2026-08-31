<template>
  <section class="py-12 sm:py-14 bg-muted/30 border-y border-border/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
      <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider font-mono">
          Kepercayaan Klien Korporat
        </span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
          Dipercaya Lebih dari 1.200+ Perusahaan Skala Nasional & Global
        </h2>
        <p class="text-muted-foreground text-sm sm:text-base leading-relaxed">
          Ulasan langsung dari para Chief Technology Officer, VP of Engineering, dan IT Manager yang mengandalkan jaringan Layung.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div
          v-for="(testi, idx) in testimonials"
          :key="idx"
          class="layung-panel p-8 space-y-6 flex flex-col justify-between"
        >
          <p class="text-sm text-muted-foreground leading-relaxed italic">
            "{{ testi.quote }}"
          </p>

          <div class="flex items-center gap-3.5 pt-4 border-t border-border/60">
            <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-700/80 text-orange-400 flex items-center justify-center font-bold text-sm shadow">
              {{ testi.name.charAt(0) }}
            </div>
            <div>
              <h4 class="text-sm font-bold text-foreground font-heading">
                {{ testi.name }}
              </h4>
              <p class="text-xs text-muted-foreground">
                {{ testi.role }} · {{ testi.company }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
const testimonials = [
  {
    name: 'Budi Hartono, S.T.',
    role: 'Chief Technology Officer',
    company: 'Fintech Nusantara',
    quote: 'SLA 99.999% Layung terbukti nyata. Sejak migrasi ke Dedicated Fiber 1Gbps, sistem transaksi core banking kami tidak pernah mengalami packet loss sama sekali.',
  },
  {
    name: 'Sarah Wijaya, M.Kom.',
    role: 'VP of Infrastructure',
    company: 'Mega E-Commerce Corp',
    quote: 'Tim NOC Layung sangat responsif. Ketika ada ancaman lonjakan traffic DDoS saat Big Campaign, proteksi SOC langsung memitigasi dalam hitungan detik tanpa downtime.',
  },
  {
    name: 'Hendra Gunawan',
    role: 'Head of IT Operation',
    company: 'RS Siloam Hospital Network',
    quote: 'Integrasi SD-WAN Layung di 15 cabang rumah sakit berjalan mulus. Pengiriman rekam medis digital dan video konsultasi dokter sekarang super cepat dan stabil.',
  },
];
</script>
