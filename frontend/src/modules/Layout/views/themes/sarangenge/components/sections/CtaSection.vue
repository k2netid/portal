<template>
  <section
    data-ja-customizer-target="cta"
    class="py-16 sm:py-24"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="sarangenge-bento__cell sarangenge-bento__cell--navy !p-10 sm:!p-16 text-center rounded-[var(--sarangenge-radius-lg,1.5rem)] relative overflow-hidden shadow-2xl">
        <div class="relative z-10 max-w-3xl mx-auto space-y-6">
          <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-black bg-amber-500 text-slate-950 uppercase tracking-wider shadow-sm">
            <GraduationCap class="w-4 h-4" />
            Mekar Bersama Cahaya Pagi
          </span>

          <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white font-heading tracking-tight">
            Siap Bergabung dengan Keluarga Besar Sarangenge?
          </h2>

          <p class="text-slate-300 text-base sm:text-lg leading-relaxed max-w-2xl mx-auto">
            Dapatkan informasi lengkap mengenai kuota pendaftaran, jadwal seleksi, beasiswa, dan konsultasi kurikulum bersama tim konselor kami.
          </p>

          <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
            <Button
              as="router-link"
              to="/contact"
              variant="secondary"
              size="lg"
              class="w-full sm:w-auto !bg-amber-500 hover:!bg-amber-400 !text-slate-950 font-bold shadow-xl border-none"
            >
              <PhoneCall class="w-5 h-5 mr-1" />
              Daftar PPDB / Konsultasi
            </Button>

            <a
              v-if="whatsAppUrl"
              :href="whatsAppUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-[var(--sarangenge-radius-sm)] bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-base transition-colors shadow-lg"
            >
              <MessageCircle class="w-5 h-5" />
              Chat WhatsApp Hotline
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { GraduationCap, PhoneCall, MessageCircle } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';

const { whatsAppUrl } = useSarangengeIdentity();
</script>
