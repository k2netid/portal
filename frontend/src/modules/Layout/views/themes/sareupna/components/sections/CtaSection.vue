<template>
  <section
    id="section-cta"
    class="py-20 md:py-28 relative z-10 overflow-hidden"
  >
    <div class="container mx-auto px-4 md:px-8">
      <div class="relative rounded-3xl p-10 md:p-16 bg-gradient-to-b from-card/90 to-card/50 border border-primary/40 shadow-2xl shadow-primary/10 overflow-hidden backdrop-blur-2xl text-center max-w-4xl mx-auto">
        <!-- Ambient Internal Glow -->
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-primary/20 rounded-full blur-3xl pointer-events-none" />

        <div class="relative z-10">
          <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/15 text-primary text-[11px] font-mono uppercase tracking-widest mb-6">
            <Sparkles class="w-3.5 h-3.5" />
            <span>{{ t('cta.badge') }}</span>
          </span>

          <h2 class="text-3xl sm:text-4xl md:text-5xl font-heading font-black uppercase tracking-tight text-foreground mb-6 max-w-2xl mx-auto leading-tight">
            {{ t('cta.title') }}
          </h2>

          <p class="text-muted-foreground text-base md:text-lg max-w-xl mx-auto mb-10 leading-relaxed">
            {{ t('cta.subtitle') }}
          </p>

          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <router-link
              to="/contact"
              class="w-full sm:w-auto px-8 py-4 text-xs font-bold uppercase tracking-wider rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 shadow-lg shadow-primary/30 hover:shadow-primary/50 hover:scale-[1.02] active:scale-95 transition-all duration-200 inline-flex items-center justify-center gap-2"
            >
              <span>{{ t('cta.primary') }}</span>
              <ArrowRight class="w-4 h-4" />
            </router-link>

            <router-link
              to="/solusi"
              class="w-full sm:w-auto px-8 py-4 text-xs font-bold uppercase tracking-wider rounded-xl border border-border bg-card/60 hover:bg-card hover:border-primary/50 text-foreground transition-all duration-200 inline-flex items-center justify-center gap-2"
            >
              <span>{{ t('cta.secondary') }}</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Sparkles, ArrowRight } from 'lucide-vue-next';

const { t } = useThemeI18n('sareupna');
</script>
