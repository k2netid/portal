<template>
  <div class="sareupna-page flex-1 w-full min-h-0">
    <header class="py-20 md:py-28 bg-gradient-to-b from-card to-background border-b border-border/40 relative overflow-hidden">
      <div class="sareupna-ambient-mesh w-96 h-96 bg-primary/20 top-0 left-1/4 -translate-y-1/2" />
      <div class="container mx-auto px-4 text-center relative z-10">
        <span class="text-xs font-mono uppercase tracking-widest text-primary font-bold mb-4 block">
          Cloud Architecture & Solutions
        </span>
        <h1 class="text-4xl md:text-6xl font-heading font-black uppercase tracking-tight text-foreground mb-6">
          Solusi Skalabilitas Platform
        </h1>
        <p class="text-base md:text-lg text-muted-foreground max-w-2xl mx-auto leading-relaxed">
          Infrastruktur terdistribusi dan solusi rekayasa cerdas untuk mentransformasi keandalan sistem digital Anda.
        </p>
      </div>
    </header>

    <ProductsSection />
    <BentoGridSection />
    <CtaSection />
  </div>
</template>

<script setup lang="ts">
import ProductsSection from '../components/sections/ProductsSection.vue';
import BentoGridSection from '../components/sections/BentoGridSection.vue';
import CtaSection from '../components/sections/CtaSection.vue';
</script>
