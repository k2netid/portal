<template>
  <section class="py-20 bg-slate-900 text-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 relative z-10">
      <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/20 text-cyan-400 border border-cyan-500/40 uppercase tracking-wider font-mono">
          Routing &amp; Peering
        </span>
        <h2 class="text-3xl sm:text-4xl font-extrabold font-heading tracking-tight text-white">
          Identitas Jaringan K2NET
        </h2>
        <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
          PT Kirana Karina Network mengoperasikan AS153992 sebagai anggota korporat IDNIC, dengan prefix mandiri untuk layanan ISP dan managed network.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 font-mono">
        <div class="p-6 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
          <div class="flex items-center justify-between text-xs text-slate-400">
            <span>IDNIC / APNIC</span>
            <span class="text-emerald-400 font-bold">AS153992</span>
          </div>
          <div class="text-xl font-bold text-white font-heading">
            BGP Mandiri
          </div>
          <p class="text-xs text-slate-400 font-sans">
            AS-Name IDNIC-K2NET-ID untuk routing dedicated internet.
          </p>
        </div>

        <div class="p-6 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
          <div class="flex items-center justify-between text-xs text-slate-400">
            <span>IPv4 Prefix</span>
            <span class="text-emerald-400 font-bold">/24</span>
          </div>
          <div class="text-xl font-bold text-white font-heading">
            165.99.252.0/24
          </div>
          <p class="text-xs text-slate-400 font-sans">
            Alokasi IP publik untuk layanan klien dedicated.
          </p>
        </div>

        <div class="p-6 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
          <div class="flex items-center justify-between text-xs text-slate-400">
            <span>Upstream</span>
            <span class="text-cyan-400 font-bold">AS55655</span>
          </div>
          <div class="text-xl font-bold text-white font-heading">
            Transit Partner
          </div>
          <p class="text-xs text-slate-400 font-sans">
            Default transit ke Mitra Visioner Pratama.
          </p>
        </div>

        <div class="p-6 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
          <div class="flex items-center justify-between text-xs text-slate-400">
            <span>HQ Bandung</span>
            <span class="text-sky-400 font-bold">40124</span>
          </div>
          <div class="text-xl font-bold text-white font-heading">
            Cikutra NOC
          </div>
          <p class="text-xs text-slate-400 font-sans">
            Kantor operasional di Cibeunying Kidul, Bandung.
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
// Topologi peering K2NET (AS153992)
</script>
