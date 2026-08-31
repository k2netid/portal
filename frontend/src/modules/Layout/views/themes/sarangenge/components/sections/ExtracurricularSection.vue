<template>
  <section class="py-12 sm:py-14 bg-muted/20 border-t border-border/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-2xl mx-auto mb-14 space-y-3">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-sun,#e8a317)]/15 text-amber-800 dark:text-amber-200 border border-[var(--sarangenge-sun)]/30">
          <Palette class="w-3.5 h-3.5" />
          Minat, Bakat & Kepemimpinan
        </span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
          Ekstrakurikuler & Pengembangan Diri
        </h2>
        <p class="text-muted-foreground text-base sm:text-lg leading-relaxed">
          Lebih dari 20 klub dan wadah kreasi untuk mengeksplorasi potensi kepemimpinan, sains, seni, dan ketahanan fisik.
        </p>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div
          v-for="(ekskul, idx) in ekskulList"
          :key="idx"
          class="sarangenge-panel p-4 text-center space-y-2 hover:border-[var(--sarangenge-teal,#0f766e)]/40 hover:-translate-y-1 transition-all duration-200"
        >
          <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mx-auto">
            <component
              :is="ekskul.icon"
              class="w-5 h-5"
            />
          </div>
          <h4 class="text-xs sm:text-sm font-bold text-foreground font-heading">
            {{ ekskul.name }}
          </h4>
          <span class="text-[10px] text-muted-foreground block">{{ ekskul.category }}</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { Palette, Bot, Music, ShieldAlert, Award, Globe2, Camera } from 'lucide-vue-next';

const ekskulList = [
  { name: 'Klub Robotika & AI', category: 'Sains & Teknologi', icon: Bot },
  { name: 'English Debate & MUN', category: 'Bahasa & Diplomasi', icon: Globe2 },
  { name: 'Pramuka Garuda', category: 'Kepemimpinan', icon: ShieldAlert },
  { name: 'Paskibra Sekolah', category: 'Kedisiplinan', icon: Award },
  { name: 'Orkestra & Paduan Suara', category: 'Seni Musik', icon: Music },
  { name: 'Fotografi & Jurnalistik', category: 'Media & Kreatif', icon: Camera },
];
</script>
