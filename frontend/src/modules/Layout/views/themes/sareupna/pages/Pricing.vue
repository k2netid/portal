<template>
  <div class="sareupna-page flex-1 w-full min-h-0 py-16 md:py-24">
    <div class="container mx-auto px-4 md:px-8">
      <!-- Header -->
      <div class="text-center max-w-2xl mx-auto mb-16">
        <span class="text-xs font-mono uppercase tracking-widest text-primary font-bold mb-4 block">
          Pricing & Cloud Subscriptions
        </span>
        <h1 class="text-4xl md:text-6xl font-heading font-black uppercase tracking-tight text-foreground mb-6">
          Pilihan Paket Transparan
        </h1>
        <p class="text-muted-foreground text-base leading-relaxed">
          Pilih skala komputasi dan kapabilitas yang sesuai dengan tahap perkembangan platform Anda.
        </p>
      </div>

      <!-- 3 Tier Pricing Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto mb-20">
        <!-- Tier 1: Starter -->
        <div class="p-8 rounded-3xl bg-card/60 border border-border/60 flex flex-col justify-between">
          <div>
            <span class="text-xs font-mono uppercase tracking-widest text-muted-foreground font-bold block mb-3">Starter</span>
            <div class="flex items-baseline gap-1 mb-4">
              <span class="text-4xl font-black text-foreground font-heading">IDR 0</span>
              <span class="text-xs text-muted-foreground">/ selamanya</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed mb-6">
              Untuk pengembang independen dan eksperimen arsitektur lokal.
            </p>
            <ul class="space-y-3 text-xs text-foreground/80 mb-8 border-t border-border/40 pt-6">
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-emerald-400" /> Single Instance Deployment
              </li>
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-emerald-400" /> Core CMS & Visual Canvas
              </li>
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-emerald-400" /> Community Discord Support
              </li>
            </ul>
          </div>
          <router-link
            to="/contact"
            class="w-full py-3 rounded-xl border border-border bg-card hover:border-primary/50 text-foreground text-xs font-bold uppercase tracking-wider text-center transition-all"
          >
            Mulai Gratis
          </router-link>
        </div>

        <!-- Tier 2: Professional (Featured) -->
        <div class="relative p-8 rounded-3xl bg-gradient-to-b from-primary/10 to-card border border-primary/50 shadow-2xl shadow-primary/20 flex flex-col justify-between">
          <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-primary text-primary-foreground text-[10px] font-mono uppercase tracking-widest font-bold shadow-md">
            Paling Populer
          </div>
          <div>
            <span class="text-xs font-mono uppercase tracking-widest text-primary font-bold block mb-3">Professional</span>
            <div class="flex items-baseline gap-1 mb-4">
              <span class="text-4xl font-black text-foreground font-heading">IDR 499K</span>
              <span class="text-xs text-muted-foreground">/ bulan</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed mb-6">
              Untuk bisnis berkembang yang membutuhkan edge delivery dan performa teruji.
            </p>
            <ul class="space-y-3 text-xs text-foreground/90 mb-8 border-t border-border/40 pt-6">
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-primary" /> Multi-Tenant Ready
              </li>
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-primary" /> CDN & Edge Asset Optimization
              </li>
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-primary" /> Granular RBAC & Security Audit
              </li>
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-primary" /> Email Support 8x5
              </li>
            </ul>
          </div>
          <router-link
            to="/contact"
            class="w-full py-3 rounded-xl bg-primary text-primary-foreground text-xs font-bold uppercase tracking-wider text-center shadow-md shadow-primary/30 hover:bg-primary/90 transition-all"
          >
            Pilih Paket Pro
          </router-link>
        </div>

        <!-- Tier 3: Enterprise Dedicated -->
        <div class="p-8 rounded-3xl bg-card/60 border border-border/60 flex flex-col justify-between">
          <div>
            <span class="text-xs font-mono uppercase tracking-widest text-muted-foreground font-bold block mb-3">Enterprise</span>
            <div class="flex items-baseline gap-1 mb-4">
              <span class="text-3xl font-black text-foreground font-heading">Custom</span>
              <span class="text-xs text-muted-foreground">/ SLA Berbasis Kontrak</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed mb-6">
              Infrastruktur terdedikasi penuh dengan audit regulasi dan dukungan 24/7.
            </p>
            <ul class="space-y-3 text-xs text-foreground/80 mb-8 border-t border-border/40 pt-6">
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-emerald-400" /> Dedicated Cloud Cluster
              </li>
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-emerald-400" /> 99.99% Uptime SLA Garansi
              </li>
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-emerald-400" /> Dedicated Technical Account Manager
              </li>
              <li class="flex items-center gap-2.5">
                <Check class="w-4 h-4 text-emerald-400" /> On-Premise / Hybrid Deployment
              </li>
            </ul>
          </div>
          <router-link
            to="/contact"
            class="w-full py-3 rounded-xl border border-border bg-card hover:border-primary/50 text-foreground text-xs font-bold uppercase tracking-wider text-center transition-all"
          >
            Hubungi Sales
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Check } from 'lucide-vue-next';
</script>
