<template>
  <div class="py-10 md:py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14">
    <Breadcrumb :items="[{ name: t('pages.about.title', 'Tentang Kami') }]" />

    <template v-if="hasBuilderBlocks">
      <BlockRenderer
        :blocks="builderBlocks"
        :context="{ post: pageData, site: { name: displayCompanyName } }"
      />
    </template>

    <template v-else>
      <header
        id="profil"
        class="scroll-mt-20 space-y-4 max-w-3xl"
      >
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 font-mono uppercase">
          {{ t('pages.about.badge', 'Tentang perusahaan') }}
        </span>
        <h1 class="text-3xl sm:text-4xl font-medium text-foreground font-heading tracking-tight">
          {{ t('pages.about.storyTitle', 'K2NET — ISP dan managed services dari Bandung') }}
        </h1>
        <p class="text-sm sm:text-base text-muted-foreground leading-relaxed">
          {{ t('pages.about.subtitle', 'Merek operasional PT Kirana Karina Network. Kami menyediakan internet dedicated, fiber, dan layanan IT terkelola untuk bisnis.') }}
        </p>
      </header>

      <section
        id="sejarah"
        class="scroll-mt-20 grid grid-cols-1 lg:grid-cols-12 gap-8"
      >
        <div class="lg:col-span-7 space-y-4">
          <h2 class="text-xl font-medium font-heading text-foreground">
            {{ t('pages.about.historyTitle', 'Sejarah singkat') }}
          </h2>
          <div class="space-y-3 text-sm text-muted-foreground leading-relaxed">
            <p>{{ t('pages.about.historyP1') }}</p>
            <p>{{ t('pages.about.historyP2') }}</p>
          </div>
        </div>
        <aside class="lg:col-span-5 layung-panel p-6 space-y-3">
          <h3 class="text-sm font-semibold font-heading text-foreground">
            {{ t('pages.about.entityTitle', 'Badan usaha') }}
          </h3>
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4 border-b border-border/70 pb-2">
              <dt class="text-muted-foreground">{{ t('pages.about.entityLegal', 'Nama badan hukum') }}</dt>
              <dd class="font-medium text-foreground text-right">{{ displayLegalName }}</dd>
            </div>
            <div class="flex justify-between gap-4 border-b border-border/70 pb-2">
              <dt class="text-muted-foreground">{{ t('pages.about.entityBrand', 'Merek') }}</dt>
              <dd class="font-medium text-foreground text-right">{{ displayCompanyName }}</dd>
            </div>
            <div class="flex justify-between gap-4 border-b border-border/70 pb-2">
              <dt class="text-muted-foreground">{{ t('pages.about.entitySeat', 'Kedudukan') }}</dt>
              <dd class="font-medium text-foreground text-right">{{ t('pages.about.entitySeatValue', 'Bandung, Jawa Barat') }}</dd>
            </div>
            <div class="flex justify-between gap-4 border-b border-border/70 pb-2">
              <dt class="text-muted-foreground shrink-0">{{ t('pages.about.entityAddressBandung', 'Kantor Bandung') }}</dt>
              <dd class="font-medium text-foreground text-right text-xs leading-relaxed">{{ displayAddress }}</dd>
            </div>
            <div class="flex justify-between gap-4 border-b border-border/70 pb-2">
              <dt class="text-muted-foreground shrink-0">{{ t('pages.about.entityAddressGarut', 'Kantor Garut') }}</dt>
              <dd class="font-medium text-foreground text-right text-xs leading-relaxed">{{ displayGarutAddress }}</dd>
            </div>
            <div class="flex justify-between gap-4">
              <dt class="text-muted-foreground shrink-0">{{ t('pages.about.entityAddressStore', 'Toko offline') }}</dt>
              <dd class="font-medium text-foreground text-right text-xs leading-relaxed">{{ displayStoreAddress }}</dd>
            </div>
          </dl>
        </aside>
      </section>

      <section
        id="visi-misi"
        class="scroll-mt-20 grid grid-cols-1 md:grid-cols-2 gap-6"
      >
        <div class="layung-panel p-6 sm:p-8 space-y-3">
          <h2 class="text-xl font-medium font-heading text-foreground">
            {{ t('pages.about.visionTitle', 'Visi') }}
          </h2>
          <p class="text-sm text-muted-foreground leading-relaxed">
            {{ t('pages.about.visionText') }}
          </p>
        </div>
        <div class="layung-panel p-6 sm:p-8 space-y-3">
          <h2 class="text-xl font-medium font-heading text-foreground">
            {{ t('pages.about.missionTitle', 'Misi') }}
          </h2>
          <ul class="space-y-2 text-sm text-muted-foreground leading-relaxed list-disc pl-5">
            <li>{{ t('pages.about.mission1') }}</li>
            <li>{{ t('pages.about.mission2') }}</li>
            <li>{{ t('pages.about.mission3') }}</li>
          </ul>
        </div>
      </section>

      <section
        id="jaringan"
        class="scroll-mt-20 space-y-5"
      >
        <div class="space-y-2 max-w-3xl">
          <h2 class="text-xl font-medium font-heading text-foreground">
            {{ t('pages.about.networkTitle', 'Identitas jaringan') }}
          </h2>
          <p class="text-sm text-muted-foreground leading-relaxed">
            {{ t('pages.about.networkIntro') }}
          </p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="layung-panel p-5 space-y-1">
            <p class="text-[11px] uppercase tracking-wider text-muted-foreground font-mono">ASN</p>
            <p class="text-lg font-medium font-heading text-foreground">{{ displayAsn }}</p>
            <p class="text-xs text-muted-foreground">{{ displayAsName }}</p>
          </div>
          <div class="layung-panel p-5 space-y-1">
            <p class="text-[11px] uppercase tracking-wider text-muted-foreground font-mono">IPv4</p>
            <p class="text-lg font-medium font-heading text-foreground font-mono">{{ displayPrefix }}</p>
            <p class="text-xs text-muted-foreground">{{ t('pages.about.networkPrefixHint', 'Prefix untuk layanan dedicated') }}</p>
          </div>
          <div class="layung-panel p-5 space-y-1">
            <p class="text-[11px] uppercase tracking-wider text-muted-foreground font-mono">{{ t('pages.about.networkRegion', 'Wilayah') }}</p>
            <p class="text-lg font-medium font-heading text-foreground">{{ displayNocLatency }}</p>
            <p class="text-xs text-muted-foreground">{{ t('pages.about.networkNoc', 'NOC Cikutra, Bandung') }}</p>
          </div>
          <div class="layung-panel p-5 space-y-1">
            <p class="text-[11px] uppercase tracking-wider text-muted-foreground font-mono">{{ t('pages.about.networkLookup', 'Lookup publik') }}</p>
            <a
              :href="asnLookupUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="text-sm font-medium text-sky-600 dark:text-sky-400 hover:underline"
            >
              bgp.he.net/{{ displayAsn }}
            </a>
            <p class="text-xs text-muted-foreground">{{ t('pages.about.networkLookupHint', 'Tabel routing publik') }}</p>
          </div>
        </div>
      </section>

      <section
        id="izin"
        class="scroll-mt-20 space-y-5"
      >
        <div class="space-y-2 max-w-3xl">
          <h2 class="text-xl font-medium font-heading text-foreground">
            {{ t('pages.about.complianceTitle', 'Izin ISP dan keanggotaan') }}
          </h2>
          <p class="text-sm text-muted-foreground leading-relaxed">
            {{ t('pages.about.complianceIntro') }}
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <article class="layung-panel p-6 space-y-2">
            <h3 class="text-base font-medium font-heading text-foreground">
              {{ t('pages.about.licenseTitle', 'Izin penyelenggaraan ISP') }}
            </h3>
            <p class="text-sm text-muted-foreground leading-relaxed">
              {{ t('pages.about.licenseText') }}
            </p>
          </article>
          <article class="layung-panel p-6 space-y-2">
            <h3 class="text-base font-medium font-heading text-foreground">APJII</h3>
            <p class="text-sm text-muted-foreground leading-relaxed">
              {{ t('pages.about.apjiiText') }}
            </p>
            <a
              href="https://apjii.or.id"
              target="_blank"
              rel="noopener noreferrer"
              class="text-xs font-medium text-sky-600 dark:text-sky-400 hover:underline"
            >apjii.or.id</a>
          </article>
          <article class="layung-panel p-6 space-y-2">
            <h3 class="text-base font-medium font-heading text-foreground">IDNIC</h3>
            <p class="text-sm text-muted-foreground leading-relaxed">
              {{ t('pages.about.idnicText') }}
            </p>
            <a
              href="https://www.idnic.id"
              target="_blank"
              rel="noopener noreferrer"
              class="text-xs font-medium text-sky-600 dark:text-sky-400 hover:underline"
            >idnic.id</a>
          </article>
          <article class="layung-panel p-6 space-y-2">
            <h3 class="text-base font-medium font-heading text-foreground">APNIC</h3>
            <p class="text-sm text-muted-foreground leading-relaxed">
              {{ t('pages.about.apnicText') }}
            </p>
            <a
              href="https://www.apnic.net"
              target="_blank"
              rel="noopener noreferrer"
              class="text-xs font-medium text-sky-600 dark:text-sky-400 hover:underline"
            >apnic.net</a>
          </article>
        </div>
      </section>

      <CtaSection />
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import { useLayungIdentity } from '../composables/useLayungIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';

const { t } = useThemeI18n('layung');
const {
  displayCompanyName,
  displayLegalName,
  displayAsn,
  displayAsName,
  displayPrefix,
  displayAddress,
  displayGarutAddress,
  displayStoreAddress,
  displayNocLatency,
} = useLayungIdentity();
const { pageData, builderBlocks, hasBuilderBlocks } = useThemePageOverride('about');

const asnLookupUrl = computed(() => {
  const asn = displayAsn.value.replace(/[^0-9]/g, '') || '153992';
  return `https://bgp.he.net/AS${asn}`;
});

useThemeHashScroll(72);
</script>
