<template>
  <section
    id="faq"
    class="py-12 sm:py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 scroll-mt-24 w-full"
  >
    <div class="text-center max-w-3xl mx-auto space-y-3">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 uppercase tracking-wider font-mono">
        {{ t('faq.badge', 'Tanya Jawab Teknis & Layanan') }}
      </span>
      <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-foreground font-heading tracking-tight">
        {{ t('faq.title', 'Pertanyaan Umum Seputar Penyediaan & Migrasi Jaringan') }}
      </h2>
      <p class="text-muted-foreground text-sm sm:text-base leading-relaxed">
        {{ t('faq.subtitle', 'Jawaban lengkap mengenai proses aktivasi, alokasi IP publik, jaminan SLA, dan prosedur eskalasi tiket darurat.') }}
      </p>
    </div>

    <div class="space-y-4 max-w-4xl mx-auto">
      <div
        v-for="(faq, idx) in faqs"
        :key="idx"
        class="layung-panel overflow-hidden transition-all"
      >
        <button
          type="button"
          class="w-full p-5 sm:p-6 text-left font-bold text-foreground flex items-center justify-between gap-4 focus:outline-none"
          @click="toggleFaq(idx)"
        >
          <span class="text-base font-heading">{{ faq.q }}</span>
          <ChevronDown
            class="w-5 h-5 text-sky-500 shrink-0 transition-transform duration-200"
            :class="{ 'rotate-180': openIndex === idx }"
          />
        </button>
        <div
          v-if="openIndex === idx"
          class="px-5 sm:px-6 pb-5 sm:pb-6 text-sm text-muted-foreground leading-relaxed border-t border-border/40 pt-4"
        >
          {{ faq.a }}
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';

const { t } = useThemeI18n('layung');
const openIndex = ref<number | null>(0);

const toggleFaq = (idx: number) => {
  openIndex.value = openIndex.value === idx ? null : idx;
};

const faqs = computed(() => [
  { q: t('faq.q1'), a: t('faq.a1') },
  { q: t('faq.q2'), a: t('faq.a2') },
  { q: t('faq.q3'), a: t('faq.a3') },
  { q: t('faq.q4'), a: t('faq.a4') },
]);
</script>
