<template>
  <LayungPageGate
    setting-key="enable_services"
    :title="pageHeaderTitle"
  >
    <div
      class="layung-page flex-1 flex flex-col space-y-8 sm:space-y-12 w-full py-8 sm:py-10 md:py-12 overflow-x-clip"
      data-ja-customizer-target="services"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-6">
        <Breadcrumb :items="[{ name: pageHeaderTitle }]" />

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
            <span
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold font-mono uppercase transition-colors"
              :class="activeTab === 'isp' ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20' : 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20'"
            >
              {{ pageHeaderBadge }}
            </span>
            <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
              {{ pageHeaderTitle }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ pageHeaderSubtitle }}
            </p>
          </div>
        </template>
      </div>

      <template v-if="!hasBuilderBlocks && !cmsBody">
        <!-- Managed & ISP Services Tabbed Section -->
        <ManagedServicesSection />

        <!-- SLA Guarantee Section (shown for ISP tab) -->
        <SlaGuaranteeSection v-if="activeTab === 'isp'" />
      </template>
    </div>
  </LayungPageGate>
</template>

<script setup lang="ts">
import { computed, provide, ref, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import LayungPageGate from '../components/shared/LayungPageGate.vue';
import ManagedServicesSection from '../components/sections/ManagedServicesSection.vue';
import SlaGuaranteeSection from '../components/sections/SlaGuaranteeSection.vue';
import { useLayungIdentity } from '../composables/useLayungIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';

type TabId = 'isp' | 'msp';

const route = useRoute();
const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('services');

const activeTab = ref<TabId>('isp');
provide('servicesActiveTab', activeTab);
provide('solusiActiveTab', activeTab);

const syncTabFromRoute = () => {
  const q = String(route.query.tab || '').toLowerCase();
  if (q === 'isp' || q === 'dia' || q === 'internet') {
    activeTab.value = 'isp';
    return;
  }
  if (q === 'msp' || q === 'soc' || q === 'sdwan' || q === 'solusi' || q === 'managed-services') {
    activeTab.value = 'msp';
    return;
  }

  const h = String(route.hash || '').replace(/^#/, '').toLowerCase();
  if (h === 'isp' || h === 'dia' || h === 'internet' || h === 'sla' || h === 'guarantee') {
    activeTab.value = 'isp';
    return;
  }
  if (h === 'msp' || h === 'soc' || h === 'sdwan' || h === 'solusi' || h === 'managed-services') {
    activeTab.value = 'msp';
    return;
  }

  // Default to ISP for services
  activeTab.value = 'isp';
};

onMounted(syncTabFromRoute);
watch(() => route.fullPath, syncTabFromRoute);

const pageHeaderBadge = computed(() => {
  return activeTab.value === 'isp'
    ? t('pages.services.badge', 'Internet')
    : t('pages.solusi.badge', 'Managed Services');
});

const pageHeaderTitle = computed(() => {
  return activeTab.value === 'isp'
    ? t('pages.services.title', 'Layanan Internet Kami')
    : t('pages.solusi.title', 'Managed Services');
});

const pageHeaderSubtitle = computed(() => {
  return activeTab.value === 'isp'
    ? t('pages.services.subtitle', 'Dedicated Internet, Broadband Bisnis, dan Retail Broadband di Bandung & Jawa Barat.')
    : t('pages.solusi.subtitle', 'Pendampingan IT operasional untuk sekolah dan institusi — jaringan, server, CCTV, dan dukungan harian.');
});

useThemeHashScroll(72);
</script>
