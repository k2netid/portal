<template>
  <section
    id="section-products"
    class="py-20 md:py-28 relative z-10 border-t border-border/40"
  >
    <div class="container mx-auto px-4 md:px-8">
      <div class="text-center max-w-2xl mx-auto mb-16">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 border border-primary/30 text-primary text-[11px] font-mono uppercase tracking-widest mb-4">
          <Boxes class="w-3.5 h-3.5" />
          <span>{{ t('products.badge') }}</span>
        </div>
        <h2 class="text-3xl md:text-5xl font-heading font-black tracking-tight text-foreground uppercase mb-4">
          {{ t('products.title') }}
        </h2>
        <p class="text-muted-foreground text-base leading-relaxed">
          {{ t('products.subtitle') }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <article
          v-for="card in productCards"
          :key="card.title"
          class="group p-8 rounded-2xl bg-card/60 border border-border/60 hover:border-primary/50 hover:shadow-xl hover:shadow-primary/10 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between"
        >
          <div>
            <div class="w-12 h-12 rounded-2xl bg-primary/10 border border-primary/25 flex items-center justify-center text-primary mb-6 group-hover:scale-110 transition-transform duration-300">
              <component :is="card.icon" class="w-6 h-6" />
            </div>
            <h3 class="text-xl font-bold text-foreground mb-3">
              {{ card.title }}
            </h3>
            <p class="text-sm text-muted-foreground leading-relaxed mb-6">
              {{ card.description }}
            </p>
          </div>

          <router-link
            :to="card.url"
            class="text-xs font-mono font-bold uppercase tracking-wider text-primary group-hover:text-primary/90 inline-flex items-center gap-2 pt-4 border-t border-border/40"
          >
            <span>{{ t('common.exploreMore') }}</span>
            <ArrowRight class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" />
          </router-link>
        </article>
      </div>

      <div class="text-center mt-12">
        <router-link
          to="/solusi"
          class="inline-flex items-center gap-2 px-8 py-3.5 text-xs font-bold uppercase tracking-wider rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 shadow-md shadow-primary/25 transition-all"
        >
          <span>{{ t('products.viewAll') }}</span>
          <ArrowRight class="w-4 h-4" />
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import {
  Boxes,
  ArrowRight,
  Server,
  Cloud,
  FileCode,
  ShieldCheck,
  Zap,
  Globe,
} from 'lucide-vue-next';

const { t } = useThemeI18n('sareupna');

const productCards = computed(() => [
  {
    icon: Server,
    title: 'Core Engine Kernel',
    description: 'Kernel performa tinggi dengan response time sub-15ms, data caching multi-tier, dan modular plugin lifecycle.',
    url: '/solusi',
  },
  {
    icon: Cloud,
    title: 'Edge Cloud Infrastructure',
    description: 'Deployment global dengan otomatisasi DNS, routing ASN terdedikasi, dan perlindungan DDoS multi-vektor.',
    url: '/solusi',
  },
  {
    icon: FileCode,
    title: 'Visual Canvas Studio',
    description: 'Penyunting visual tanpa kompromi performa. Kompatibel dengan GSAP animation stack dan live customizer.',
    url: '/solusi',
  },
  {
    icon: ShieldCheck,
    title: 'Enterprise Identity & IAM',
    description: 'Manajemen otentikasi zero-trust, kontrol hak akses berbasis peran (RBAC), dan audit trail menyeluruh.',
    url: '/solusi',
  },
  {
    icon: Zap,
    title: 'Streaming Content Pipeline',
    description: 'Penyaluran konten instan ke berbagai channel, multi-locale translation engine, dan optimasi aset on-the-fly.',
    url: '/solusi',
  },
  {
    icon: Globe,
    title: 'Multi-Tenant Portal Network',
    description: 'Satu platform terpadu untuk mengelola portal puluhan cabang atau organisasi dengan isolasi database terjamin.',
    url: '/solusi',
  },
]);
</script>
