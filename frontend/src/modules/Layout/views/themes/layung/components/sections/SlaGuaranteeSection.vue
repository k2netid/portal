<template>
  <section class="py-12 sm:py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
    <div class="layung-panel p-8 sm:p-12 border-2 border-emerald-500/40 bg-gradient-to-br from-card via-card to-emerald-500/5 shadow-2xl relative overflow-hidden">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <div class="lg:col-span-8 space-y-4">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 uppercase tracking-wider font-mono">
            Service Level Agreement (SLA) Guarantee
          </span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
            Komitmen Uptime 99.999% & Jaminan Respon 15 Menit
          </h2>
          <p class="text-muted-foreground text-sm sm:text-base leading-relaxed max-w-2xl">
            Kami mengikat komitmen layanan dengan Service Level Agreement (SLA) legal. Apabila terjadi penurunan performa atau gangguan melebihi toleransi kontrak, kompensasi potongan tagihan otomatis langsung diberlakukan.
          </p>

          <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 font-mono text-xs text-foreground">
            <div class="p-3 rounded-xl bg-muted/60 border border-border">
              <span class="text-muted-foreground block text-[10px]">MTTR Respon NOC</span>
              <strong class="text-emerald-500 text-sm font-bold">15 Menit</strong>
            </div>
            <div class="p-3 rounded-xl bg-muted/60 border border-border">
              <span class="text-muted-foreground block text-[10px]">Resolusi Lapangan</span>
              <strong class="text-orange-500 text-sm font-bold">&lt; 4 Jam Max</strong>
            </div>
            <div class="p-3 rounded-xl bg-muted/60 border border-border">
              <span class="text-muted-foreground block text-[10px]">Sertifikasi Keamanan</span>
              <strong class="text-cyan-500 text-sm font-bold">ISO 27001</strong>
            </div>
          </div>
        </div>

        <div class="lg:col-span-4 flex flex-col items-center justify-center p-6 rounded-2xl bg-slate-900 border border-slate-800 text-center space-y-3 text-white">
          <ShieldCheck class="w-14 h-14 text-emerald-400" />
          <div class="text-3xl font-black font-heading text-emerald-400">
            99.999%
          </div>
          <p class="text-xs text-slate-400">
            Ketersediaan Jaringan Tertinggi dengan Rute Redundan Otomatis
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ShieldCheck } from 'lucide-vue-next';
</script>
