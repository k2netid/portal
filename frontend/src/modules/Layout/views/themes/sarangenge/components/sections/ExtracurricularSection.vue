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
        <router-link
          v-for="(ekskul, idx) in resolvedEkskul"
          :key="ekskul.id || idx"
          :to="ekskul.slug ? `/blog/${ekskul.slug}` : '#'"
          class="sarangenge-panel p-4 text-center space-y-2 hover:border-[var(--sarangenge-teal,#0f766e)]/40 hover:-translate-y-1 transition-all duration-200 group cursor-pointer block"
        >
          <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
            <component
              :is="ekskul.iconComponent"
              class="w-5 h-5"
            />
          </div>
          <h4 class="text-xs sm:text-sm font-bold text-foreground font-heading group-hover:text-[var(--sarangenge-teal,#0f766e)] transition-colors">
            {{ ekskul.name }}
          </h4>
          <span class="text-[10px] text-muted-foreground block">{{ ekskul.category }}</span>
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import type { Content } from '@/modules/Publishing/types/content';
import { Palette, Bot, Music, ShieldAlert, Award, Globe2, Camera, Sparkles } from 'lucide-vue-next';

const ekskulContents = ref<Content[]>([]);

const iconMap: Record<string, any> = {
  Bot,
  Globe2,
  ShieldAlert,
  Award,
  Music,
  Camera,
  Palette,
  Sparkles,
};

const resolveIcon = (iconName?: string, title = ''): any => {
  if (iconName && iconMap[iconName]) return iconMap[iconName];
  const lower = title.toLowerCase();
  if (lower.includes('robot') || lower.includes('ai') || lower.includes('it')) return Bot;
  if (lower.includes('inggris') || lower.includes('debat') || lower.includes('bahasa')) return Globe2;
  if (lower.includes('pramuka') || lower.includes('pmi') || lower.includes('palang')) return ShieldAlert;
  if (lower.includes('paskibra') || lower.includes('baris')) return Award;
  if (lower.includes('musik') || lower.includes('suara') || lower.includes('orkestra')) return Music;
  if (lower.includes('foto') || lower.includes('jurnal') || lower.includes('media')) return Camera;
  return Palette;
};

const defaultEkskulList = [
  { id: 'ekskul-1', slug: 'klub-robotika-ai-vokasi', name: 'Klub Robotika & AI', category: 'Sains & Teknologi', iconComponent: Bot },
  { id: 'ekskul-2', slug: 'english-debate-mun-society', name: 'English Debate & MUN', category: 'Bahasa & Diplomasi', iconComponent: Globe2 },
  { id: 'ekskul-3', slug: 'pramuka-garuda-kejuruan', name: 'Pramuka Garuda', category: 'Kepemimpinan', iconComponent: ShieldAlert },
  { id: 'ekskul-4', slug: 'paskibra-satuan-utama', name: 'Paskibra Sekolah', category: 'Kedisiplinan', iconComponent: Award },
  { id: 'ekskul-5', slug: 'orkestra-paduan-suara-gita', name: 'Orkestra & Paduan Suara', category: 'Seni Musik', iconComponent: Music },
  { id: 'ekskul-6', slug: 'fotografi-jurnalistik-multimedia', name: 'Fotografi & Jurnalistik', category: 'Media & Kreatif', iconComponent: Camera },
];

const resolvedEkskul = computed(() => {
  if (ekskulContents.value.length > 0) {
    return ekskulContents.value.map((item: any) => {
      const raw = item._raw || item;
      const meta = raw.meta || {};
      return {
        id: item.id,
        slug: item.slug || '',
        name: item.title,
        category: meta.category || item.excerpt || 'Ekstrakurikuler',
        iconComponent: resolveIcon(meta.icon, item.title),
      };
    });
  }

  return defaultEkskulList;
});

onMounted(async () => {
  try {
    const res = await api.get(publishingPaths.publicContents, {
      params: { category: 'ekstrakurikuler', status: 'published', sort: 'title' },
    });
    const data = res.data;
    const items = Array.isArray(data)
      ? data
      : Array.isArray(data?.data)
        ? data.data
        : Array.isArray(data?.data?.data)
          ? data.data.data
          : [];
    ekskulContents.value = items;
  } catch {
    ekskulContents.value = [];
  }
});
</script>
