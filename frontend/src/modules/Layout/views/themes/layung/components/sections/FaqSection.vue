<template>
  <section class="py-20 sm:py-24 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
    <div class="text-center space-y-4">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider font-mono">
        Tanya Jawab Teknis & Layanan
      </span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
        Pertanyaan Umum Seputar Penyediaan & Migrasi Jaringan
      </h2>
      <p class="text-muted-foreground text-sm sm:text-base leading-relaxed">
        Jawaban lengkap mengenai proses aktivasi, alokasi IP publik, jaminan SLA, dan prosedur eskalasi tiket darurat.
      </p>
    </div>

    <div class="space-y-4">
      <div
        v-for="(faq, idx) in faqs"
        :key="idx"
        class="layung-panel overflow-hidden transition-all"
      >
        <button
          type="button"
          class="w-full p-6 text-left font-bold text-foreground flex items-center justify-between gap-4 focus:outline-none"
          @click="toggleFaq(idx)"
        >
          <span class="text-base font-heading">{{ faq.q }}</span>
          <ChevronDown
            class="w-5 h-5 text-orange-500 shrink-0 transition-transform duration-200"
            :class="{ 'rotate-180': openIndex === idx }"
          />
        </button>
        <div
          v-if="openIndex === idx"
          class="px-6 pb-6 text-sm text-muted-foreground leading-relaxed border-t border-border/40 pt-4"
        >
          {{ faq.a }}
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { ChevronDown } from 'lucide-vue-next';

const openIndex = ref<number | null>(0);

const toggleFaq = (idx: number) => {
  openIndex.value = openIndex.value === idx ? null : idx;
};

const faqs = [
  {
    q: 'Berapa lama estimasi proses penarikan kabel dan instalasi Dedicated Internet (DIA)?',
    a: 'Untuk area on-net (gedung atau kawasan yang sudah terpasang Fiber Termination Layung), instalasi selesai dalam 1–3 hari kerja. Untuk area off-net yang memerlukan penarikan last-mile baru, waktu pengerjaan berkisar 7–14 hari kerja termasuk uji tes OTDR.',
  },
  {
    q: 'Apakah paket Dedicated Internet mendapatkan alokasi IP Publik Statis?',
    a: 'Ya, seluruh paket Dedicated Internet mendapatkan alokasi blok IP Publik Statis (/29 atau 5 IP usable) secara gratis, dan dapat ditambah sesuai kebutuhan server atau router BGP enterprise Anda.',
  },
  {
    q: 'Bagaimana prosedur klaim penalti kompensasi jika SLA tidak terpenuhi?',
    a: 'Setiap gangguan otomatis dicatat oleh sistem ticketing NOC kami. Jika ketersediaan bulanan berada di bawah batas kontrak SLA (misal < 99.999%), sistem billing kami secara otomatis mengkreditkan potongan tagihan pada invoice bulan berikutnya tanpa proses birokrasi berbelit.',
  },
  {
    q: 'Apakah tersedia opsi koneksi redundan (Backup Link) dengan media berbeda?',
    a: 'Tersedia. Kami menyediakan dual-homing dengan rute fisik kabel serat optik terpisah (diverse path) serta opsi backup nirkabel berlisensi (Wireless Microwave 5.8GHz/E-Band) untuk memastikan nol downtime.',
  },
];
</script>
