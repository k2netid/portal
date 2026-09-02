<template>
  <section
    data-ja-customizer-target="bento"
    class="py-12 sm:py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10"
  >
    <div class="text-center max-w-3xl mx-auto space-y-4">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 uppercase tracking-wider font-mono">
        {{ t('bento.badge', 'Tiga Lini Bisnis K2NET') }}
      </span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
        {{ t('bento.title', 'Konektivitas, Operasional IT, dan Kebutuhan Perangkat dalam Satu Mitra') }}
      </h2>
      <p class="text-muted-foreground text-sm sm:text-base leading-relaxed">
        {{ t('bento.subtitle', 'K2NET (PT Kirana Karina Network) mendampingi pelanggan dan mitra dari penyediaan koneksi internet, pengelolaan layanan IT, hingga pengadaan perangkat — dengan tim operasional di Bandung, Jawa Barat.') }}
      </p>
    </div>

    <div class="layung-bento layung-bento--pillars">
      <!-- ISP -->
      <div class="layung-bento__cell layung-bento__cell--orange space-y-5">
        <div class="space-y-3">
          <div class="w-12 h-12 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center">
            <Network class="w-6 h-6" />
          </div>
          <h3 class="text-xl sm:text-2xl font-bold font-heading text-white">
            {{ t('bento.ispTitle', 'Internet Service Provider (ISP)') }}
          </h3>
          <p class="text-slate-300 text-sm leading-relaxed">
            {{ t('bento.ispDesc', 'Dedicated Internet, Broadband Bisnis SOHO, dan Retail Broadband untuk kantor, institusi, dan rumah tangga di Bandung & Jawa Barat.') }}
          </p>
        </div>

        <div class="grid grid-cols-1 gap-2 pt-3 border-t border-white/10 font-mono text-xs text-slate-300">
          <span class="text-slate-400">{{ t('bento.ispPoint1', 'Dedicated Internet — hubungi sales') }}</span>
          <span class="text-slate-400">{{ t('bento.ispPoint2', 'Broadband Bisnis 50–100 Mbps') }}</span>
          <span class="text-slate-400">{{ t('bento.ispPoint3', 'Retail Broadband mulai Rp 150.000 + PPN') }}</span>
        </div>

        <div class="flex flex-col gap-2 mt-auto pt-2">
          <Button
            :as="RouterLink"
            to="/pricing/isp"
            variant="primary"
            size="md"
            class="w-full font-bold"
          >
            <Package class="w-4 h-4" />
            {{ t('bento.ispPricingCta', 'Lihat paket internet') }}
          </Button>
          <Button
            :as="RouterLink"
            to="/#isp"
            variant="outline"
            size="sm"
            class="w-full font-semibold border-white/20 text-slate-200 hover:bg-white/10 hover:text-white"
          >
            {{ t('bento.ispCta', 'Detail layanan konektivitas') }}
            <ArrowRight class="w-3.5 h-3.5" />
          </Button>
        </div>
      </div>

      <!-- MSP -->
      <div class="layung-bento__cell layung-bento__cell--cyber space-y-5">
        <div class="space-y-3">
          <div class="w-12 h-12 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
            <Settings2 class="w-6 h-6" />
          </div>
          <h3 class="text-xl sm:text-2xl font-bold font-heading text-white">
            {{ t('bento.mspTitle', 'Managed Service Provider (MSP)') }}
          </h3>
          <p class="text-slate-300 text-sm leading-relaxed">
            {{ t('bento.mspDesc', 'Dukungan IT operasional untuk sekolah dan institusi: jaringan, server & aplikasi, CCTV, serta pendampingan kegiatan berbasis IT.') }}
          </p>
        </div>

        <div class="flex flex-wrap gap-2 pt-3 border-t border-white/10 text-[10px] font-mono">
          <span class="px-2.5 py-1 rounded-md bg-white/5 text-cyan-300 border border-white/10">{{ t('bento.mspTag1', 'MS Basic') }}</span>
          <span class="px-2.5 py-1 rounded-md bg-white/5 text-cyan-300 border border-white/10">{{ t('bento.mspTag2', 'MS Standard') }}</span>
          <span class="px-2.5 py-1 rounded-md bg-white/5 text-cyan-300 border border-white/10">{{ t('bento.mspTag3', 'MS Custom') }}</span>
        </div>

        <div class="flex flex-col gap-2 mt-auto pt-2">
          <Button
            :as="RouterLink"
            to="/pricing/msp"
            variant="primary"
            size="md"
            class="w-full font-bold"
          >
            <Package class="w-4 h-4" />
            {{ t('bento.mspPricingCta', 'Lihat paket MSP') }}
          </Button>
          <Button
            :as="RouterLink"
            to="/#msp"
            variant="outline"
            size="sm"
            class="w-full font-semibold border-white/20 text-slate-200 hover:bg-white/10 hover:text-white"
          >
            {{ t('bento.mspCta', 'Detail managed services') }}
            <ArrowRight class="w-3.5 h-3.5" />
          </Button>
        </div>
      </div>

      <!-- IT Products -->
      <div class="layung-bento__cell space-y-5 bg-card border border-border/80">
        <div class="space-y-3">
          <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
            <ShoppingBag class="w-6 h-6" />
          </div>
          <h3 class="text-xl sm:text-2xl font-bold font-heading text-foreground">
            {{ t('bento.productsTitle', 'Penyedia Produk IT') }}
          </h3>
          <p class="text-muted-foreground text-sm leading-relaxed">
            {{ t('bento.productsDesc', 'Toko offline di Bandung Trade Mall, plus Tokopedia, Shopee, dan SIPLah untuk pengadaan perangkat jaringan dan kebutuhan IT.') }}
          </p>
        </div>

        <div class="space-y-2 pt-3 border-t border-border/60 text-xs text-muted-foreground">
          <p class="font-mono text-[10px] uppercase tracking-wider text-foreground/70">
            {{ t('bento.productsOfflineLabel', 'Toko offline') }}
          </p>
          <p class="text-xs text-muted-foreground leading-relaxed">
            {{ displayStoreAddress }}
          </p>
          <div class="flex flex-wrap gap-2 pt-1">
            <a
              v-for="channel in storeChannels"
              :key="channel.id"
              :href="channel.href"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center px-2.5 py-1 rounded-md bg-muted text-foreground hover:bg-sky-500/10 hover:text-sky-600 dark:hover:text-sky-400 border border-border/60 transition-colors font-mono text-[10px] font-semibold"
            >
              {{ channel.label }}
            </a>
          </div>
        </div>

        <div class="mt-auto pt-2">
          <Button
            as="a"
            :href="tokopediaUrl"
            target="_blank"
            rel="noopener noreferrer"
            variant="outline"
            size="md"
            class="w-full font-bold"
          >
            {{ t('bento.productsCta', 'Kunjungi toko daring K2NET') }}
            <ArrowUpRight class="w-4 h-4" />
          </Button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useLayungIdentity } from '@/modules/Layout/views/themes/layung/composables/useLayungIdentity';
import {
  LAYUNG_STORE_SHOPEE,
  LAYUNG_STORE_SIPLAH_BLIBLI,
  LAYUNG_STORE_SIPLAH_TOKOLADANG,
  LAYUNG_STORE_TOKOPEDIA,
} from '@/modules/Layout/views/themes/layung/composables/layungStoreUrls';
import { Network, Settings2, ShoppingBag, ArrowRight, ArrowUpRight, Package } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/layung/ui';

const { t } = useThemeI18n('layung');
const { displayStoreAddress } = useLayungIdentity();

const tokopediaUrl = LAYUNG_STORE_TOKOPEDIA;

const storeChannels = computed(() => [
  { id: 'tokopedia', label: 'Tokopedia', href: LAYUNG_STORE_TOKOPEDIA },
  { id: 'shopee', label: 'Shopee', href: LAYUNG_STORE_SHOPEE },
  { id: 'siplah-blibli', label: 'SIPLah Blibli', href: LAYUNG_STORE_SIPLAH_BLIBLI },
  { id: 'siplah-tokoladang', label: 'SIPLah Toko Ladang', href: LAYUNG_STORE_SIPLAH_TOKOLADANG },
]);
</script>
