<template>
  <section
    data-ja-customizer-target="testimonials"
    class="py-12 sm:py-14"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-2xl mx-auto mb-14 space-y-3">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
          <MessageSquare class="w-3.5 h-3.5" />
          Suara Komunitas Kampus
        </span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
          Apa Kata Siswa, Orang Tua & Alumni?
        </h2>
        <p class="text-muted-foreground text-base sm:text-lg leading-relaxed">
          Pengalaman nyata menempuh pendidikan yang membentuk karakter, kecerdasan, dan masa depan.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div
          v-for="(testi, idx) in testimonials"
          :key="idx"
          class="sarangenge-panel p-8 flex flex-col justify-between space-y-6 hover:shadow-xl transition-all duration-300"
        >
          <div class="space-y-4">
            <Quote class="w-8 h-8 text-[var(--sarangenge-teal,#0f766e)]/30" />
            <p class="text-sm text-foreground/90 leading-relaxed italic">
              "{{ testi.quote }}"
            </p>
          </div>

          <div class="flex items-center gap-3.5 pt-4 border-t border-border/60">
            <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-700/80 text-amber-400 flex items-center justify-center font-bold text-sm shadow">
              {{ testi.name.charAt(0) }}
            </div>
            <div>
              <h4 class="text-sm font-bold text-foreground font-heading">
                {{ testi.name }}
              </h4>
              <p class="text-xs text-muted-foreground">
                {{ testi.role }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { MessageSquare, Quote } from 'lucide-vue-next';

const testimonials = [
  {
    name: 'Ir. Hendra Kusuma',
    role: 'Orang Tua Siswa Kelas X',
    quote: 'Sarangenge tidak hanya mengajarkan akademik tinggi, tapi sangat memperhatikan adab dan mental anak. Komunikasi guru dengan orang tua sangat transparan dan hangat.',
  },
  {
    name: 'Siti Sarah Nurhaliza',
    role: 'Alumni 2024 · Mahasiswi Kedokteran UI',
    quote: 'Bimbingan riset sains dan pembinaan guru di Sarangenge menjadi modal paling berharga saat saya menempuh seleksi masuk fakultas kedokteran.',
  },
  {
    name: 'Rian Pratama',
    role: 'Ketua OSIS Periode 2025/2026',
    quote: 'Fasilitas AI lab, klub debat, dan ruang diskusi di sekolah membuat kami bebas berinovasi dan percaya diri bersaing di kancah nasional.',
  },
];
</script>
