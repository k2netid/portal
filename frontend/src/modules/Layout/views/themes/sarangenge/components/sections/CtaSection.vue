<template>
  <section
    data-ja-customizer-target="cta"
    class="py-12 sm:py-14"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="sarangenge-bento__cell sarangenge-bento__cell--navy !p-8 sm:!p-12 text-center rounded-[var(--sarangenge-radius-lg,1.5rem)] relative overflow-hidden shadow-2xl">
        <div class="relative z-10 max-w-3xl mx-auto space-y-5">
          <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-black bg-amber-500 text-slate-950 uppercase tracking-wider shadow-sm">
            <GraduationCap class="w-4 h-4" />
            {{ t('cta.badge', 'Mekar Bersama Cahaya Pagi') }}
          </span>

          <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white font-heading tracking-tight">
            {{ ctaTitle }}
          </h2>

          <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-2xl mx-auto">
            {{ t('cta.subtitle', 'Dapatkan informasi lengkap mengenai kuota pendaftaran, jadwal seleksi, beasiswa, dan konsultasi kurikulum bersama tim konselor kami.') }}
          </p>

          <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a
              :href="ppdbPortalUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-[var(--sarangenge-radius-sm)] bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold shadow-xl transition-colors text-base"
            >
              <ExternalLink class="w-5 h-5 mr-1" />
              {{ t('cta.primary', 'Portal Resmi PPDB Jabar') }}
            </a>

            <a
              v-if="whatsAppUrl"
              :href="whatsAppUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-[var(--sarangenge-radius-sm)] bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-base transition-colors shadow-lg"
            >
              <MessageCircle class="w-5 h-5" />
              {{ t('cta.whatsapp', 'Konsultasi Jurusan (WhatsApp)') }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { GraduationCap, MessageCircle, ExternalLink } from 'lucide-vue-next';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';

const { whatsAppUrl, displaySchoolName, ppdbPortalUrl } = useSarangengeIdentity();
const { t } = useThemeI18n('sarangenge');

const ctaTitle = computed(() => {
  return t('cta.title', { school: displaySchoolName.value }, `Siap Bergabung dengan Keluarga Besar ${displaySchoolName.value}?`);
});
</script>
