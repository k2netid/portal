<template>
  <section class="py-12 sm:py-14 bg-muted/20 border-t border-border/60">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-10 space-y-3">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
          <HelpCircle class="w-3.5 h-3.5" />
          {{ t('faq.badge', 'Tanya Jawab') }}
        </span>
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-foreground font-heading tracking-tight">
          {{ t('faq.title', 'Pertanyaan Seputar PPDB & Sekolah') }}
        </h2>
        <p class="text-muted-foreground text-sm sm:text-base">
          {{ t('faq.subtitle', 'Jawaban atas hal-hal yang sering ditanyakan oleh calon siswa dan orang tua.') }}
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
            class="w-full p-5 sm:p-6 text-left font-bold text-base sm:text-lg text-foreground flex items-center justify-between gap-4 focus:outline-none"
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
            class="px-5 sm:px-6 pb-5 sm:pb-6 text-sm text-muted-foreground leading-relaxed border-t border-border/40 pt-4 animate-in fade-in-50 duration-150"
          >
            {{ faq.a }}
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { HelpCircle, ChevronDown } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';

const { t } = useThemeI18n('sarangenge');
const openIdx = ref<number | null>(0);

const toggleFaq = (idx: number) => {
  openIdx.value = openIdx.value === idx ? null : idx;
};

const faqs = computed(() => [
  { q: t('faq.q1'), a: t('faq.a1') },
  { q: t('faq.q2'), a: t('faq.a2') },
  { q: t('faq.q3'), a: t('faq.a3') },
  { q: t('faq.q4'), a: t('faq.a4') },
]);
</script>
