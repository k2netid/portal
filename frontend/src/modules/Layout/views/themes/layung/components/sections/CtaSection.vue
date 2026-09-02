<template>
  <section class="py-12 sm:py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div
      ref="ctaCardRef"
      class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-slate-900 to-sky-950/80 border border-sky-500/40 p-6 sm:p-10 text-center shadow-2xl space-y-6"
    >
      <div class="relative z-10 max-w-3xl mx-auto space-y-3">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/20 text-sky-400 border border-sky-500/40 font-mono">
          {{ t('cta.badge', 'Konsultasi layanan') }}
        </span>
        <h2
          ref="ctaTitleRef"
          class="text-2xl sm:text-4xl font-black text-white font-heading tracking-tight leading-tight"
        >
          <LayungSplitText :text="t('cta.title', 'Siap diskusikan kebutuhan internet dan IT Anda?')" />
        </h2>
        <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-2xl mx-auto">
          {{ t('cta.subtitle', 'Hubungi tim K2NET untuk survei lokasi dan penawaran sesuai kontrak.') }}
        </p>
      </div>

      <div class="relative z-10 flex flex-wrap items-center justify-center gap-3">
        <Button
          as="router-link"
          to="/contact"
          variant="primary"
          size="lg"
          class="font-bold shadow-xl shadow-sky-500/25"
        >
          <PhoneCall class="w-5 h-5 mr-2" />
          <span>{{ t('cta.primary', 'Hubungi kami') }}</span>
        </Button>

        <a
          v-if="nocWhatsAppUrl"
          :href="nocWhatsAppUrl"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-2 px-6 py-3 rounded-[var(--layung-radius-sm)] bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-base shadow-lg transition-colors"
        >
          <MessageCircle class="w-5 h-5" />
          <span>{{ t('cta.whatsapp', 'Chat WhatsApp') }}</span>
        </a>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import { PhoneCall, MessageCircle } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/layung/ui';
import { useLayungIdentity } from '@/modules/Layout/views/themes/layung/composables/useLayungIdentity';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion';
import LayungSplitText from '@/modules/Layout/views/themes/layung/components/shared/LayungSplitText.vue';

const { nocWhatsAppUrl } = useLayungIdentity();
const { t } = useThemeI18n('layung');
const { scaleReveal, splitTextRevealSafe } = useThemeMotion();
const ctaCardRef = ref<HTMLElement>();
const ctaTitleRef = ref<HTMLElement>();

onMounted(async () => {
  await nextTick();
  if (ctaCardRef.value) scaleReveal(ctaCardRef.value);
  if (ctaTitleRef.value) splitTextRevealSafe(ctaTitleRef.value, { delay: 0.15, stagger: 0.04 });
});
</script>
