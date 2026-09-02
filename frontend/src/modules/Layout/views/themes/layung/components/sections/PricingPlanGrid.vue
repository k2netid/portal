<template>
  <div
    class="grid grid-cols-1 gap-8"
    :class="gridClass"
  >
    <div
      v-for="(pkg, idx) in plans"
      :key="`${pkg.tier}-${idx}`"
      class="layung-panel p-8 space-y-6 flex flex-col justify-between relative transition-all hover:-translate-y-1"
      :class="{ 'border-2 border-sky-500 shadow-xl bg-gradient-to-b from-card to-sky-500/5': pkg.popular }"
    >
      <div
        v-if="pkg.popular"
        class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full text-[10px] font-black bg-sky-500 text-white uppercase tracking-widest shadow-md whitespace-nowrap"
      >
        {{ popularBadge }}
      </div>

      <div class="space-y-4">
        <div class="flex items-center justify-between gap-2">
          <span class="text-xs font-bold uppercase tracking-wider text-sky-500 font-mono">
            {{ pkg.tier }}
          </span>
          <span
            v-if="pkg.sla"
            class="text-xs font-mono px-2 py-0.5 rounded bg-muted text-muted-foreground shrink-0"
          >
            {{ pkg.sla }}
          </span>
        </div>

        <h3 class="text-2xl font-bold text-foreground font-heading">
          {{ pkg.name }}
        </h3>
        <p
          v-if="pkg.speed"
          class="text-xs font-mono text-sky-600 dark:text-sky-400"
        >
          {{ pkg.speed }}
        </p>
        <p class="text-xs text-muted-foreground leading-relaxed">
          {{ pkg.description }}
        </p>

        <div class="pt-4 border-t border-border/80 space-y-1">
          <span
            v-if="!pkg.contactSales"
            class="text-xs text-muted-foreground block"
          >{{ priceFromLabel }}</span>
          <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
            <strong class="text-2xl sm:text-3xl font-black text-foreground font-heading">{{ pkg.price }}</strong>
            <span
              v-if="pkg.priceNote && !pkg.contactSales"
              class="text-xs text-muted-foreground"
            >{{ pkg.priceNote }}</span>
          </div>
          <p
            v-if="pkg.priceNote && pkg.contactSales"
            class="text-xs text-muted-foreground"
          >
            {{ pkg.priceNote }}
          </p>
        </div>

        <ul class="space-y-2.5 pt-4 text-xs text-muted-foreground">
          <li
            v-for="(feat, fIdx) in pkg.features"
            :key="fIdx"
            class="flex items-start gap-2"
          >
            <Check class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
            <span>{{ feat }}</span>
          </li>
        </ul>
      </div>

      <Button
        as="router-link"
        :to="contactPath"
        :variant="pkg.popular ? 'primary' : 'outline'"
        size="md"
        class="w-full font-bold"
      >
        {{ pkg.contactSales ? contactSalesLabel : selectPlanLabel }}
      </Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/layung/ui';
import type { LayungPlanCard } from '@/modules/Layout/views/themes/layung/composables/layungPricingPlans';

withDefaults(defineProps<{
  plans: LayungPlanCard[];
  gridClass?: string;
  popularBadge?: string;
  priceFromLabel?: string;
  selectPlanLabel?: string;
  contactSalesLabel?: string;
  contactPath?: string;
}>(), {
  gridClass: 'md:grid-cols-3',
  popularBadge: 'Pilihan Populer',
  priceFromLabel: 'Mulai dari',
  selectPlanLabel: 'Pilih Layanan Ini',
  contactSalesLabel: 'Hubungi Sales',
  contactPath: '/contact',
});
</script>
