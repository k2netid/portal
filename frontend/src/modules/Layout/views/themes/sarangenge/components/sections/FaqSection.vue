<template>
  <section class="py-16 sm:py-24 bg-muted/20 border-t border-border/60">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-14 space-y-3">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
          <HelpCircle class="w-3.5 h-3.5" />
          Tanya Jawab
        </span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
          Pertanyaan Seputar PPDB & Sekolah
        </h2>
        <p class="text-muted-foreground text-base sm:text-lg">
          Jawaban atas hal-hal yang sering ditanyakan oleh calon siswa dan orang tua.
        </p>
      </div>

      <div class="space-y-4">
        <div
          v-for="(faq, idx) in faqs"
          :key="idx"
          class="sarangenge-panel overflow-hidden transition-all duration-200"
        >
          <button
            type="button"
            class="w-full p-6 text-left font-bold text-base sm:text-lg text-foreground flex items-center justify-between gap-4 focus:outline-none"
            @click="toggleFaq(idx)"
          >
            <span>{{ faq.q }}</span>
            <ChevronDown
              class="w-5 h-5 text-[var(--sarangenge-teal,#0f766e)] shrink-0 transition-transform duration-200"
              :class="{ 'rotate-180': openIdx === idx }"
            />
          </button>
          <div
            v-if="openIdx === idx"
            class="px-6 pb-6 text-sm text-muted-foreground leading-relaxed border-t border-border/40 pt-4 animate-in fade-in-50 duration-150"
          >
            {{ faq.a }}
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { HelpCircle, ChevronDown } from 'lucide-vue-next';

const openIdx = ref<number | null>(0);

const toggleFaq = (idx: number) => {
  openIdx.value = openIdx.value === idx ? null : idx;
};

const faqs = [
  {
    q: 'Kapan gelombang pendaftaran PPDB 2026/2027 dibuka?',
    a: 'Gelombang 1 (Jalur Prestasi & Early Bird) dibuka mulai 15 Oktober 2026. Gelombang 2 (Jalur Reguler) dibuka mulai 15 Januari 2027. Pendaftaran dapat dilakukan secara online melalui website ini atau langsung di sekretariat sekolah.',
  },
  {
    q: 'Apakah tersedia skema beasiswa bagi siswa berprestasi?',
    a: 'Ya, kami menyediakan Beasiswa Prestasi Akademik & Non-Akademik (potongan SPP 50%–100%) bagi peraih juara olimpiade sains, tahfidz Quran minimal 3 juz, atau juara kejuaraan olahraga/seni minimal tingkat kota/provinsi.',
  },
  {
    q: 'Bagaimana kurikulum dan metode pembelajaran yang diterapkan?',
    a: 'Kami mengimplementasikan Kurikulum Merdeka yang diperkaya dengan muatan internasional Cambridge, pembelajaran berbasis riset (P5), serta integrasi teknologi AI, robotika, dan pembinaan karakter akhlakul karimah.',
  },
  {
    q: 'Apakah orang tua bisa melakukan School Tour / Kunjungan Kampus?',
    a: 'Tentu saja. Kami menyambut kunjungan orang tua dan calon siswa setiap hari kerja (Senin–Jumat pukul 08.00–14.00 WIB) atau pada hari Sabtu dengan membuat janji temu melalui WhatsApp hotline kami.',
  },
];
</script>
