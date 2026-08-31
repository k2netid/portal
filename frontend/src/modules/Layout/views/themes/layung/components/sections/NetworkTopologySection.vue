<template>
  <section class="py-20 bg-slate-900 text-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 relative z-10">
      <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/20 text-cyan-400 border border-cyan-500/40 uppercase tracking-wider font-mono">
          Topologi & Peering Global
        </span>
        <h2 class="text-3xl sm:text-4xl font-extrabold font-heading tracking-tight text-white">
          Infrastruktur Backbone Redundan Tanpa Titik Lemah Tunggal
        </h2>
        <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
          Tersambung langsung ke Internet Exchange terkemuka di Indonesia dan hub global di Singapura dan Hong Kong.
        </p>
      </div>

      <!-- Infrastructure Route Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 font-mono">
        <div class="p-6 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
          <div class="flex items-center justify-between text-xs text-slate-400">
            <span>IIX / APJII Jakarta</span>
            <span class="text-emerald-400 font-bold">100 Gbps</span>
          </div>
          <div class="text-xl font-bold text-white font-heading">
            Peering Domestik
          </div>
          <p class="text-xs text-slate-400 font-sans">
            Latensi < 2ms ke seluruh ISP lokal dan CDN nasional.
          </p>
        </div>

        <div class="p-6 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
          <div class="flex items-center justify-between text-xs text-slate-400">
            <span>OpenIXP Cyber Tower</span>
            <span class="text-emerald-400 font-bold">100 Gbps</span>
          </div>
          <div class="text-xl font-bold text-white font-heading">
            Cyber 1 Interconnect
          </div>
          <p class="text-xs text-slate-400 font-sans">
            Koneksi langsung ke server perbankan dan e-commerce.
          </p>
        </div>

        <div class="p-6 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
          <div class="flex items-center justify-between text-xs text-slate-400">
            <span>Equinix SG1 Singapore</span>
            <span class="text-cyan-400 font-bold">40 Gbps</span>
          </div>
          <div class="text-xl font-bold text-white font-heading">
            Global Gateway SG
          </div>
          <p class="text-xs text-slate-400 font-sans">
            Akses langsung ke AWS, Google, Cloudflare & Fastly.
          </p>
        </div>

        <div class="p-6 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
          <div class="flex items-center justify-between text-xs text-slate-400">
            <span>Subsea Cable Ring</span>
            <span class="text-orange-400 font-bold">Redundan</span>
          </div>
          <div class="text-xl font-bold text-white font-heading">
            Kabel Bawah Laut
          </div>
          <p class="text-xs text-slate-400 font-sans">
            Multi-kabel bawah laut anti putus jalur internasional.
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
// Topologi Peering visual
</script>
