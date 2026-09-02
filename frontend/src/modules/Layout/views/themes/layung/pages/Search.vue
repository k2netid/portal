<template>
  <div class="py-10 md:py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    <Breadcrumb :items="[{ name: t('pages.search.title', 'Pencarian') }]" />

    <div class="space-y-4">
      <h1 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
        {{ t('pages.search.title', 'Pencarian') }}
      </h1>
      <p class="text-sm text-muted-foreground">
        {{ t('pages.search.subtitle', 'Cari paket, layanan, dan informasi K2NET.') }}
      </p>
    </div>

    <div class="relative">
      <Search class="w-5 h-5 text-muted-foreground absolute left-4 top-1/2 -translate-y-1/2" />
      <input
        v-model="query"
        type="text"
        :placeholder="t('pages.search.placeholder', 'Cari layanan atau paket…')"
        class="w-full pl-12 pr-4 py-3 rounded-2xl border border-border bg-card text-foreground placeholder:text-muted-foreground/60 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/50 shadow-sm"
      >
    </div>

    <div class="flex flex-wrap gap-2 text-xs font-mono">
      <span class="text-muted-foreground py-1">{{ t('pages.search.popular', 'Pencarian populer') }}:</span>
      <button
        v-for="tag in popularTags"
        :key="tag"
        type="button"
        class="px-2.5 py-1 rounded-md bg-muted text-foreground hover:bg-sky-500/10 hover:text-sky-500 transition-colors"
        @click="query = tag"
      >
        {{ tag }}
      </button>
    </div>

    <div class="space-y-4 pt-4">
      <div
        v-for="(item, idx) in filteredItems"
        :key="idx"
        class="layung-panel p-5 hover:border-sky-500/40 transition-all flex items-center justify-between"
      >
        <div class="space-y-1">
          <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-sky-500 px-2 py-0.5 rounded bg-sky-500/10">
            {{ item.category }}
          </span>
          <h3 class="text-base font-bold text-foreground font-heading">
            {{ item.title }}
          </h3>
          <p class="text-xs text-muted-foreground">
            {{ item.desc }}
          </p>
        </div>
        <router-link
          :to="item.path"
          class="p-2 rounded-xl bg-muted/60 text-muted-foreground hover:text-primary hover:bg-primary/10 transition-colors shrink-0 ml-4"
        >
          <ArrowRight class="w-4 h-4" />
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Search, ArrowRight } from 'lucide-vue-next';
import Breadcrumb from '../components/shared/Breadcrumb.vue';

const { t } = useThemeI18n('layung');
const query = ref('');

const popularTags = computed(() => [
  t('pages.search.tagDedicated', 'Dedicated Internet'),
  t('pages.search.tagSla', 'SLA'),
  t('pages.search.tagMsp', 'Managed Services'),
  t('pages.search.tagAsn', 'AS153992'),
]);

const allItems = computed(() => [
  {
    title: t('pages.services.title', 'Layanan Internet K2NET'),
    category: t('header.services', 'Internet'),
    desc: t('pages.services.subtitle'),
    path: '/pricing/isp',
  },
  {
    title: t('pages.solusi.title', 'Managed Services'),
    category: t('header.solusi', 'Managed Services'),
    desc: t('pages.solusi.subtitle'),
    path: '/solusi',
  },
  {
    title: t('pages.achievements.title', 'SLA'),
    category: t('header.achievement', 'SLA'),
    desc: t('pages.achievements.subtitle'),
    path: '/achievement',
  },
  {
    title: t('pages.pricingIsp.title', 'Paket Internet'),
    category: t('header.pricing', 'Paket & Harga'),
    desc: t('pages.pricingIsp.subtitle'),
    path: '/pricing/isp',
  },
  {
    title: t('pages.pricingMsp.title', 'Paket MSP'),
    category: t('header.pricing', 'Paket & Harga'),
    desc: t('pages.pricingMsp.subtitle'),
    path: '/pricing/msp',
  },
  {
    title: t('pages.careers.title', 'Karir'),
    category: t('header.career', 'Karir'),
    desc: t('pages.careers.subtitle'),
    path: '/career',
  },
]);

const filteredItems = computed(() => {
  if (!query.value.trim()) return allItems.value;
  const q = query.value.toLowerCase();
  return allItems.value.filter(
    (i) => i.title.toLowerCase().includes(q) || i.desc.toLowerCase().includes(q) || i.category.toLowerCase().includes(q),
  );
});
</script>
