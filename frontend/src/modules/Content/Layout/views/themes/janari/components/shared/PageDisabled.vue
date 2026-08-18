<template>
  <div class="min-h-[80vh] flex items-center justify-center p-6 bg-background relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute inset-0 z-0">
      <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-primary/5 blur-[120px] -translate-x-1/2 -translate-y-1/2" />
      <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-primary/5 blur-[100px] translate-x-1/3 translate-y-1/3" />
    </div>

    <!-- Content -->
    <div class="relative z-10 max-w-2xl w-full">
      <div class="bg-card border border-border p-8 md:p-16 text-center backdrop-blur-3xl shadow-2xl relative group">
        <!-- Top Accents -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-24 h-[2px] bg-primary" />
        <div class="absolute top-0 left-0 w-2 h-2 border-t border-l border-primary" />
        <div class="absolute top-0 right-0 w-2 h-2 border-t border-r border-primary" />
        
        <!-- Icon -->
        <div class="mb-12 relative">
          <div class="w-20 h-20 border border-primary/30 rounded-full flex items-center justify-center mx-auto group-hover:scale-110 transition-transform duration-700">
            <div class="w-14 h-14 border border-primary/60 rounded-full flex items-center justify-center animate-pulse">
              <Construction class="w-6 h-6 text-primary" />
            </div>
          </div>
          <div class="absolute inset-0 bg-primary/20 blur-2xl rounded-full scale-50 opacity-0 group-hover:opacity-100 transition-opacity duration-1001" />
        </div>

        <!-- Text -->
        <span class="text-[10px] font-black tracking-[0.6em] uppercase text-primary block mb-6">{{ t('theme.janari.pages.disabled.statusLabel') }}</span>
        <h1 class="text-3xl md:text-5xl font-heading font-black mb-8 leading-tight tracking-tighter uppercase text-foreground">
          {{ title }}
        </h1>
        <p class="text-foreground/50 text-base md:text-lg mb-12 leading-relaxed">
          {{ message || t('theme.janari.pages.disabled.defaultMessage') }}
        </p>

        <!-- Action -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
          <router-link
            to="/"
            class="px-10 py-4 bg-foreground text-background font-black text-[10px] tracking-[0.4em] uppercase hover:bg-primary hover:text-primary-foreground transition-all shadow-xl"
          >
            {{ t('theme.janari.pages.disabled.backHome') }}
          </router-link>
          <router-link
            to="/contact"
            class="text-[10px] font-black tracking-[0.3em] uppercase text-foreground/40 hover:text-primary transition-colors"
          >
            {{ t('theme.janari.pages.disabled.contactAdmin') }}
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Construction } from 'lucide-vue-next';

const { t } = useI18n();

defineProps<{
  title: string;
  message?: string;
}>();
</script>
