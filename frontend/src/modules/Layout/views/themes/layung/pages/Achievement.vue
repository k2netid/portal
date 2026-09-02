<template>
  <div
    class="py-8 sm:py-10 md:py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 sm:space-y-10 w-full max-w-full overflow-x-clip"
    data-ja-customizer-target="achievement"
  >
    <Breadcrumb :items="[{ name: t('pages.achievements.title', 'SLA & Sertifikasi') }]" />

    <template v-if="hasBuilderBlocks">
      <BlockRenderer
        :blocks="builderBlocks"
        :context="{ post: pageData, site: { name: displayCompanyName } }"
      />
    </template>

    <template v-else-if="cmsBody">
      <div class="prose dark:prose-invert max-w-none text-muted-foreground leading-relaxed">
        <ThemeSafeHtml :html="cmsBody" />
      </div>
    </template>

    <template v-else>
      <div class="space-y-4 max-w-3xl">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-mono uppercase">
          {{ t('sla.badge', 'SLA berbasis kontrak') }}
        </span>
        <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
          {{ t('pages.achievements.title', 'SLA & Komitmen Layanan') }}
        </h1>
        <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
          {{ t('pages.achievements.subtitle', 'Anggota korporat IDNIC, operasional BGP mandiri AS153992, dan komitmen SLA sesuai kontrak layanan.') }}
        </p>
      </div>

      <SlaGuaranteeSection />

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="layung-panel p-8 space-y-4 border border-border">
          <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
            <Award class="w-6 h-6" />
          </div>
          <h3 class="text-xl font-bold text-foreground font-heading">
            IDNIC
          </h3>
          <p class="text-xs text-muted-foreground leading-relaxed">
            {{ t('pages.about.idnicText', 'Indonesia Network Information Center — sumber alokasi ASN dan prefix K2NET (AS-Name IDNIC-K2NET-ID).') }}
          </p>
        </div>

        <div class="layung-panel p-8 space-y-4 border border-border">
          <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
            <Award class="w-6 h-6" />
          </div>
          <h3 class="text-xl font-bold text-foreground font-heading">
            APJII
          </h3>
          <p class="text-xs text-muted-foreground leading-relaxed">
            {{ t('pages.about.apjiiText', 'Asosiasi Penyelenggara Jasa Internet Indonesia — wadah industri ISP nasional.') }}
          </p>
        </div>

        <div class="layung-panel p-8 space-y-4 border border-border">
          <div class="w-12 h-12 rounded-xl bg-sky-500/10 text-sky-500 flex items-center justify-center">
            <Award class="w-6 h-6" />
          </div>
          <h3 class="text-xl font-bold text-foreground font-heading">
            APNIC · AS153992
          </h3>
          <p class="text-xs text-muted-foreground leading-relaxed">
            {{ t('pages.about.apnicText', 'Alokasi routing K2NET tercatat melalui hirarki APNIC → IDNIC, dengan operasional BGP mandiri AS153992.') }}
          </p>
        </div>
      </div>

      <CtaSection />
    </template>
  </div>
</template>

<script setup lang="ts">
import { Award } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import SlaGuaranteeSection from '../components/sections/SlaGuaranteeSection.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import { useLayungIdentity } from '../composables/useLayungIdentity';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('achievement');
</script>
