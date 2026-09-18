<template>
  <section
    id="section-bento"
    class="py-20 md:py-28 relative z-10 border-t border-border/40"
  >
    <div class="container mx-auto px-4 md:px-8">
      <!-- Section Header -->
      <div class="max-w-2xl mb-16">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 border border-primary/30 text-primary text-[11px] font-mono uppercase tracking-widest mb-4">
          <Layers class="w-3.5 h-3.5" />
          <span>{{ t('bento.badge') }}</span>
        </div>
        <h2 class="text-3xl md:text-5xl font-heading font-black tracking-tight text-foreground uppercase mb-4">
          {{ t('bento.title') }}
        </h2>
        <p class="text-muted-foreground text-base md:text-lg leading-relaxed">
          {{ t('bento.subtitle') }}
        </p>
      </div>

      <!-- 12-Column Bento Grid -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Card 1: Core Engine (Span 8) -->
        <div class="sareupna-bento-card md:col-span-8 p-8 md:p-10 flex flex-col justify-between group">
          <div class="flex items-start justify-between mb-8">
            <div>
              <div class="w-12 h-12 rounded-2xl bg-primary/10 border border-primary/30 flex items-center justify-center text-primary mb-4 shadow-sm">
                <Cpu class="w-6 h-6" />
              </div>
              <h3 class="text-2xl font-bold text-foreground mb-2">
                {{ t('bento.card1Title') }}
              </h3>
              <p class="text-sm text-muted-foreground max-w-md">
                {{ t('bento.card1Desc') }}
              </p>
            </div>
            <span class="text-xs font-mono text-primary font-bold px-2.5 py-1 rounded-full bg-primary/10 border border-primary/20">
              Core Runtime
            </span>
          </div>

          <!-- Architecture Visual Representation -->
          <div class="w-full bg-card/90 border border-border/60 rounded-xl p-4 font-mono text-xs text-foreground/80 space-y-2">
            <div class="flex items-center justify-between border-b border-border/40 pb-2 text-[11px] text-muted-foreground">
              <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500" />
                <span>engine.daemon.active</span>
              </span>
              <span>latency: 12.4ms</span>
            </div>
            <div class="text-emerald-400">
              ➜ [CoreEngine] Dispatching event: ContentPipelineResolved (0.003s)
            </div>
            <div class="text-foreground/60 text-[11px]">
              ➜ [CacheStore] Hits: 99.8% | Memory: 42.1MB | Connection Pool: Stable
            </div>
          </div>
        </div>

        <!-- Card 2: Visual Canvas (Span 4) -->
        <div class="sareupna-bento-card md:col-span-4 p-8 flex flex-col justify-between group">
          <div>
            <div class="w-12 h-12 rounded-2xl bg-secondary/10 border border-secondary/30 flex items-center justify-center text-secondary mb-4 shadow-sm">
              <Layout class="w-6 h-6" />
            </div>
            <h3 class="text-xl font-bold text-foreground mb-2">
              {{ t('bento.card2Title') }}
            </h3>
            <p class="text-sm text-muted-foreground leading-relaxed mb-6">
              {{ t('bento.card2Desc') }}
            </p>
          </div>

          <div class="p-4 rounded-xl bg-card/80 border border-border/50 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
              <Sparkles class="w-4 h-4 text-secondary" />
              <span>Zero Build Delay</span>
            </div>
            <span class="font-mono text-secondary font-bold">100% Live</span>
          </div>
        </div>

        <!-- Card 3: Security & Compliance (Span 4) -->
        <div class="sareupna-bento-card md:col-span-4 p-8 flex flex-col justify-between group">
          <div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-500 mb-4 shadow-sm">
              <ShieldCheck class="w-6 h-6" />
            </div>
            <h3 class="text-xl font-bold text-foreground mb-2">
              {{ t('bento.card3Title') }}
            </h3>
            <p class="text-sm text-muted-foreground leading-relaxed mb-6">
              {{ t('bento.card3Desc') }}
            </p>
          </div>

          <div class="p-4 rounded-xl bg-card/80 border border-border/50 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
              <Lock class="w-4 h-4 text-emerald-500" />
              <span>Zero-Trust RBAC</span>
            </div>
            <span class="font-mono text-emerald-400 font-bold">Audited</span>
          </div>
        </div>

        <!-- Card 4: Multi-Tenant Cloud (Span 8) -->
        <div class="sareupna-bento-card md:col-span-8 p-8 md:p-10 flex flex-col justify-between group">
          <div class="flex items-start justify-between mb-8">
            <div>
              <div class="w-12 h-12 rounded-2xl bg-violet-500/10 border border-violet-500/30 flex items-center justify-center text-violet-400 mb-4 shadow-sm">
                <Cloud class="w-6 h-6" />
              </div>
              <h3 class="text-2xl font-bold text-foreground mb-2">
                {{ t('bento.card4Title') }}
              </h3>
              <p class="text-sm text-muted-foreground max-w-md">
                {{ t('bento.card4Desc') }}
              </p>
            </div>
            <span class="text-xs font-mono text-violet-400 font-bold px-2.5 py-1 rounded-full bg-violet-500/10 border border-violet-500/20">
              Cloud Native
            </span>
          </div>

          <!-- Node cluster status bar -->
          <div class="grid grid-cols-3 gap-3">
            <div class="p-3.5 rounded-xl bg-card/80 border border-border/50 flex flex-col">
              <span class="text-[10px] font-mono uppercase text-muted-foreground">Cluster Node A</span>
              <span class="text-xs font-mono font-bold text-foreground mt-1">JKT-ID-01 [Master]</span>
            </div>
            <div class="p-3.5 rounded-xl bg-card/80 border border-border/50 flex flex-col">
              <span class="text-[10px] font-mono uppercase text-muted-foreground">Cluster Node B</span>
              <span class="text-xs font-mono font-bold text-foreground mt-1">SGP-SG-02 [Edge]</span>
            </div>
            <div class="p-3.5 rounded-xl bg-card/80 border border-border/50 flex flex-col">
              <span class="text-[10px] font-mono uppercase text-muted-foreground">Replication</span>
              <span class="text-xs font-mono font-bold text-emerald-400 mt-1">Active Sync (0ms)</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import {
  Layers,
  Cpu,
  Layout,
  ShieldCheck,
  Cloud,
  Sparkles,
  Lock,
} from 'lucide-vue-next';

const { t } = useThemeI18n('sareupna');
</script>
