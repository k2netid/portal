<template>
  <div
    id="about"
    class="about-page layung-page"
    data-ja-customizer-target="about"
  >
    <!-- ═══ Page disabled guard ══════════════════════════════════ -->
    <div v-if="!isEnabled" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <PageDisabled
        :title="t('pages.about.title', 'Tentang Kami')"
        :message="disabledPageMessage"
      />
    </div>

    <template v-else>
      <!-- ═══════════════════════════════════════════════════════════
           1. HERO — dark full-width header
      ═══════════════════════════════════════════════════════════ -->
      <section class="layung-hero px-4 sm:px-6 lg:px-8 py-16 lg:py-20 relative overflow-hidden">
        <div class="layung-hero__grid" />

        <div class="max-w-7xl mx-auto w-full relative z-10">
          <nav aria-label="breadcrumb" class="mb-8">
            <Breadcrumb
              :items="[{ name: t('pages.about.title', 'Tentang Kami') }]"
            />
          </nav>

          <div class="max-w-3xl space-y-5">
            <div ref="heroBadgeRef" class="flex items-center gap-3">
              <span
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-bold bg-sky-500/15 text-sky-300 border border-sky-500/30 uppercase tracking-wider font-mono"
              >
                <span class="layung-status-dot" />
                {{ t('pages.about.badge', 'Tentang perusahaan') }}
              </span>
            </div>

            <h1
              ref="heroTitleRef"
              class="text-[1.65rem] sm:text-[2.3rem] font-medium tracking-tight text-white font-heading leading-[1.15]"
            >
              <LayungSplitText :text="t('pages.about.storyTitle', 'Kami — ISP dan managed services dari Bandung')" />
            </h1>

            <p
              ref="heroSubtitleRef"
              class="text-[13px] sm:text-sm text-slate-400 max-w-xl leading-relaxed"
            >
              {{ t('pages.about.subtitle', 'Merek operasional Penyedia Layanan Internet. Kami menyediakan internet dedicated, fiber, dan layanan IT terkelola untuk bisnis.') }}
            </p>

            <!-- Quick-stat strip -->
            <div ref="heroStatsRef" class="flex flex-wrap gap-3 pt-2">
              <div
                v-for="stat in heroStats"
                :key="stat.label"
                class="about-hero-stat"
              >
                <span class="about-hero-stat__label font-mono">{{ stat.label }}</span>
                <span class="about-hero-stat__value font-heading font-medium">{{ stat.value }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Plugin slot: after hero -->
      <PluginSlot
        name="about-after-hero"
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
        :context="{ theme: 'layung', page: 'about' }"
      />

      <!-- ═══ Builder / CMS body override ═════════════════════════ -->
      <template v-if="hasBuilderBlocks">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
          <BlockRenderer
            :blocks="builderBlocks"
            :context="{ post: pageData, site: { name: displayCompanyName } }"
          />
        </div>
      </template>

      <template v-else-if="cmsBody">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
          <ThemeSafeHtml
            class="prose dark:prose-invert max-w-none text-muted-foreground leading-relaxed"
            :html="cmsBody"
            mode="publishing"
          />
        </div>
      </template>

      <template v-else>
<!-- ═══════════════════════════════════════════════════════
             2. IDENTITAS PERUSAHAAN — narasi + entity card
        ═══════════════════════════════════════════════════════ -->
        <section
          id="profil"
          class="scroll-mt-20 py-14 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
        >
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
<!-- Left: History narrative -->
            <div ref="historyTextRef" class="lg:col-span-7 space-y-6">
              <div class="space-y-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 uppercase tracking-wider font-mono">
                  {{ t('pages.about.historyTitle', 'Sejarah singkat') }}
                </span>
                <h2 class="text-2xl sm:text-3xl font-medium font-heading text-foreground tracking-tight">
                  {{ t('pages.about.foundedHeading', 'Dari Bandung untuk Jawa Barat') }}
                </h2>
              </div>

              <div class="space-y-4 text-sm text-muted-foreground leading-relaxed">
                <p>{{ t('pages.about.historyP1') }}</p>
                <p>{{ t('pages.about.historyP2') }}</p>
              </div>

              <!-- Timeline milestones -->
              <div class="about-timeline mt-8">
                <div
                  v-for="(milestone, idx) in timelines"
                  :key="idx"
                  class="about-timeline__item"
                >
                  <div class="about-timeline__dot" />
                  <div class="about-timeline__content">
                    <span class="about-timeline__year font-mono">{{ milestone.year }}</span>
                    <p class="about-timeline__text">{{ milestone.text }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right: Entity card -->
            <aside ref="entityCardRef" class="lg:col-span-5">
              <div class="layung-panel p-7 space-y-5">
                <div class="flex items-center gap-3 pb-4 border-b border-border/60">
                  <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                    <Building2 class="w-5 h-5" />
                  </div>
                  <h3 class="text-sm font-semibold font-heading text-foreground">
                    {{ t('pages.about.entityTitle', 'Badan usaha') }}
                  </h3>
                </div>

                <dl class="space-y-3.5">
                  <div
                    v-for="row in entityRows"
                    :key="row.label"
                    class="about-entity-row"
                  >
                    <dt class="about-entity-row__label">{{ row.label }}</dt>
                    <dd
                      class="about-entity-row__value"
                      :class="row.mono ? 'font-mono text-[11px]' : ''"
                    >
                      {{ row.value }}
                    </dd>
                  </div>
                </dl>
              </div>
            </aside>
          </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════
             3. VISI & MISI
        ═══════════════════════════════════════════════════════ -->
        <section
          id="visi-misi"
          ref="visionMissionRef"
          class="scroll-mt-20 py-14 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
        >
          <div class="text-center max-w-2xl mx-auto space-y-3 mb-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-violet-500/10 text-violet-600 dark:text-violet-400 border border-violet-500/20 uppercase tracking-wider font-mono">
              {{ t('pages.about.vmBadge', 'Arah & Tujuan') }}
            </span>
            <h2 class="text-2xl sm:text-3xl font-medium font-heading text-foreground tracking-tight">
              {{ t('pages.about.vmHeading', 'Visi dan Misi') }}
            </h2>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Vision — dark card -->
            <div class="about-vm-card layung-panel p-7 sm:p-9 space-y-5 relative overflow-hidden">
              <div class="about-vm-card__glow" aria-hidden="true" />
              <div class="relative z-10 space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-sky-500/20 text-sky-400 dark:text-sky-400 flex items-center justify-center">
                  <Telescope class="w-6 h-6" />
                </div>
                <h3 class="text-xl font-medium font-heading text-foreground">
                  {{ t('pages.about.visionTitle', 'Visi') }}
                </h3>
                <p class="text-sm text-muted-foreground leading-relaxed">
                  {{ t('pages.about.visionText') }}
                </p>
              </div>
            </div>

            <!-- Mission — light-accent card -->
            <div class="about-vm-card layung-panel p-7 sm:p-9 space-y-5">
              <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <Target class="w-6 h-6" />
              </div>
              <h3 class="text-xl font-medium font-heading text-foreground">
                {{ t('pages.about.missionTitle', 'Misi') }}
              </h3>
              <ul class="space-y-3">
                <li
                  v-for="(mission, idx) in missions"
                  :key="idx"
                  class="flex items-start gap-3 text-sm text-muted-foreground leading-relaxed"
                >
                  <span class="about-mission-bullet shrink-0 mt-0.5">{{ idx + 1 }}</span>
                  <span>{{ mission }}</span>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════
             4. IDENTITAS JARINGAN — ASN stat cards
        ═══════════════════════════════════════════════════════ -->
        <section
          id="jaringan"
          class="scroll-mt-20 py-14 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10"
        >
          <div ref="networkHeaderRef" class="space-y-3 max-w-3xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 uppercase tracking-wider font-mono">
              {{ t('pages.about.networkTitle', 'Identitas jaringan') }}
            </span>
            <h2 class="text-2xl sm:text-3xl font-medium font-heading text-foreground tracking-tight">
              {{ t('pages.about.networkHeading', 'Routing mandiri dengan ASN dan prefix sendiri') }}
            </h2>
            <p class="text-sm text-muted-foreground leading-relaxed">
              {{ t('pages.about.networkIntro') }}
            </p>
          </div>

          <!-- ASN Bento Stats -->
          <div ref="networkCardsRef" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- ASN Card -->
            <div class="about-net-card about-net-card--dark">
              <div class="about-net-card__icon-wrap">
                <Network class="w-5 h-5 text-sky-400" />
              </div>
              <p class="about-net-card__label font-mono">ASN</p>
              <p class="about-net-card__value font-heading">{{ displayAsn }}</p>
              <p class="about-net-card__hint">{{ displayAsName }}</p>
              <a
                :href="asnLookupUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="about-net-card__link"
              >
                bgp.he.net ↗
              </a>
            </div>

            <!-- IPv4 Card -->
            <div class="about-net-card about-net-card--dark">
              <div class="about-net-card__icon-wrap">
                <Globe class="w-5 h-5 text-cyan-400" />
              </div>
              <p class="about-net-card__label font-mono">IPv4 Prefix</p>
              <p class="about-net-card__value about-net-card__value--mono font-mono">{{ displayPrefix }}</p>
              <p class="about-net-card__hint">{{ t('pages.about.networkPrefixHint', 'Prefix untuk layanan dedicated') }}</p>
            </div>

            <!-- Region Card -->
            <div class="about-net-card layung-panel">
              <div class="about-net-card__icon-wrap about-net-card__icon-wrap--light">
                <MapPin class="w-5 h-5 text-sky-600 dark:text-sky-400" />
              </div>
              <p class="about-net-card__label about-net-card__label--light font-mono">{{ t('pages.about.networkRegion', 'Wilayah') }}</p>
              <p class="about-net-card__value about-net-card__value--light font-heading">{{ displayNocLatency }}</p>
              <p class="about-net-card__hint about-net-card__hint--light">{{ t('pages.about.networkNoc', 'NOC Cikutra, Bandung') }}</p>
            </div>

            <!-- Lookup Card -->
            <div class="about-net-card layung-panel">
              <div class="about-net-card__icon-wrap about-net-card__icon-wrap--light">
                <Search class="w-5 h-5 text-violet-600 dark:text-violet-400" />
              </div>
              <p class="about-net-card__label about-net-card__label--light font-mono">{{ t('pages.about.networkLookup', 'Lookup publik') }}</p>
              <a
                :href="asnLookupUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="about-net-card__value text-sky-600 dark:text-sky-400 hover:underline font-heading"
              >
                bgp.he.net/{{ displayAsn }}
              </a>
              <p class="about-net-card__hint about-net-card__hint--light">{{ t('pages.about.networkLookupHint', 'Tabel routing publik') }}</p>
            </div>
          </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════
             5. IZIN & KEANGGOTAAN
        ═══════════════════════════════════════════════════════ -->
        <section
          id="izin"
          ref="complianceRef"
          class="scroll-mt-20 py-14 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10"
        >
          <div ref="complianceHeaderRef" class="space-y-3 max-w-3xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider font-mono">
              {{ t('pages.about.complianceTitle', 'Izin ISP dan keanggotaan') }}
            </span>
            <h2 class="text-2xl sm:text-3xl font-medium font-heading text-foreground tracking-tight">
              {{ t('pages.about.complianceHeading', 'Beroperasi secara resmi dalam ekosistem internet Indonesia') }}
            </h2>
            <p class="text-sm text-muted-foreground leading-relaxed">
              {{ t('pages.about.complianceIntro') }}
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <article
              v-for="member in memberships"
              :key="member.key"
              class="about-compliance-card layung-panel p-6 sm:p-7 flex flex-col gap-4"
            >
              <div class="flex items-start gap-4">
                <div
                  class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 text-lg font-black font-heading"
                  :class="member.iconClass"
                >
                  {{ member.abbr }}
                </div>
                <div class="min-w-0 flex-1">
                  <h3 class="text-base font-medium font-heading text-foreground">
                    {{ member.title }}
                  </h3>
                  <p class="text-xs text-muted-foreground leading-relaxed mt-1.5">
                    {{ member.desc }}
                  </p>
                </div>
              </div>
              <a
                v-if="member.url"
                :href="member.url"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 text-xs font-medium text-sky-600 dark:text-sky-400 hover:underline mt-auto"
              >
                {{ member.urlLabel }}
                <ExternalLink class="w-3 h-3" />
              </a>
            </article>
          </div>
        </section>

        <!-- Plugin slot: before CTA -->
        <PluginSlot
          name="about-before-cta"
          class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
          :context="{ theme: 'layung', page: 'about' }"
        />

        <!-- CTA -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-14">
          <CtaSection />
        </div>
</template>
</template>
  </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue';
import { Building2, ExternalLink, Globe, MapPin, Network, Search, Target, Telescope } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import PluginSlot from '@/shared/components/PluginSlot.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import PageDisabled from '../components/shared/PageDisabled.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import LayungSplitText from '../components/shared/LayungSplitText.vue';
import { useLayungIdentity } from '../composables/useLayungIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';

const { t } = useThemeI18n('layung');
const { getSetting } = useTheme();
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
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('about');
const {
  ScrollTrigger,
  fadeInUp,
  fadeInLeft,
  staggerChildren,
  splitTextRevealSafe,
  scaleReveal,
} = useThemeMotion();

useThemeHashScroll(72);

// ─── Page enabled guard ───────────────────────────────────────────
const isEnabled = computed(() => getSetting('enable_about', true) !== false);
const disabledPageMessage = computed(
  () => typeof getSetting('disabled_page_message', '') === 'string'
    ? (getSetting('disabled_page_message', '') as string) || t('pages.disabled.defaultMessage')
    : t('pages.disabled.defaultMessage'),
);

// ─── Refs for GSAP targets ────────────────────────────────────────
const heroBadgeRef     = ref<HTMLElement | null>(null);
const heroTitleRef     = ref<HTMLElement | null>(null);
const heroSubtitleRef  = ref<HTMLElement | null>(null);
const heroStatsRef     = ref<HTMLElement | null>(null);
const historyTextRef   = ref<HTMLElement | null>(null);
const entityCardRef    = ref<HTMLElement | null>(null);
const visionMissionRef = ref<HTMLElement | null>(null);
const networkHeaderRef = ref<HTMLElement | null>(null);
const networkCardsRef  = ref<HTMLElement | null>(null);
const complianceHeaderRef = ref<HTMLElement | null>(null);
const complianceRef    = ref<HTMLElement | null>(null);

// ─── ASN lookup URL ───────────────────────────────────────────────
const asnLookupUrl = computed(() => {
  const asn = displayAsn.value.replace(/[^0-9]/g, '') || '153992';
  return `https://bgp.he.net/AS${asn}`;
});

// ─── Hero quick-stats ─────────────────────────────────────────────
const heroStats = computed(() => [
  { label: 'ASN', value: displayAsn.value },
  { label: 'IPv4', value: displayPrefix.value },
  { label: t('pages.about.networkRegion', 'Wilayah'), value: displayNocLatency.value },
]);

// ─── History timeline ─────────────────────────────────────────────
const timelines = computed(() => [
  {
    year: t('pages.about.timeline1Year', 'Berdiri'),
    text: t('pages.about.timeline1Text', 'Penyedia Layanan Internet didirikan di Bandung sebagai penyelenggara jasa internet.'),
  },
  {
    year: 'ASN AS153992',
    text: t('pages.about.timeline2Text', 'Mendapatkan ASN AS153992 dari IDNIC (AS-Name: IDNIC-Kami-ID) dan prefix IPv4 165.99.252.0/24.'),
  },
  {
    year: t('pages.about.timeline3Year', 'Sekarang'),
    text: t('pages.about.timeline3Text', 'Beroperasi sebagai ISP dan MSP — melayani bisnis, sekolah, dan institusi di Jawa Barat.'),
  },
]);

// ─── Entity info rows ─────────────────────────────────────────────
const entityRows = computed(() => [
  { label: t('pages.about.entityLegal', 'Nama badan hukum'), value: displayLegalName.value, mono: false },
  { label: t('pages.about.entityBrand', 'Merek'), value: displayCompanyName.value, mono: false },
  { label: t('pages.about.entitySeat', 'Kedudukan'), value: t('pages.about.entitySeatValue', 'Bandung, Jawa Barat'), mono: false },
  { label: t('pages.about.entityAddressBandung', 'Kantor Bandung'), value: displayAddress.value, mono: true },
  { label: t('pages.about.entityAddressGarut', 'Kantor Garut'), value: displayGarutAddress.value, mono: true },
  { label: t('pages.about.entityAddressStore', 'Toko offline'), value: displayStoreAddress.value, mono: true },
]);

// ─── Missions list ────────────────────────────────────────────────
const missions = computed(() => [
  t('pages.about.mission1'),
  t('pages.about.mission2'),
  t('pages.about.mission3'),
]);

// ─── Compliance / memberships ─────────────────────────────────────
const memberships = computed(() => [
  {
    key: 'isp-license',
    abbr: 'ISP',
    iconClass: 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
    title: t('pages.about.licenseTitle', 'Izin penyelenggaraan ISP'),
    desc: t('pages.about.licenseText'),
    url: '',
    urlLabel: '',
  },
  {
    key: 'apjii',
    abbr: 'APJII',
    iconClass: 'bg-orange-500/10 text-orange-600 dark:text-orange-400',
    title: 'APJII',
    desc: t('pages.about.apjiiText'),
    url: 'https://apjii.or.id',
    urlLabel: 'apjii.or.id',
  },
  {
    key: 'idnic',
    abbr: 'IDNIC',
    iconClass: 'bg-violet-500/10 text-violet-600 dark:text-violet-400',
    title: 'IDNIC',
    desc: t('pages.about.idnicText'),
    url: 'https://www.idnic.id',
    urlLabel: 'idnic.id',
  },
  {
    key: 'apnic',
    abbr: 'APNIC',
    iconClass: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    title: 'APNIC',
    desc: t('pages.about.apnicText'),
    url: 'https://www.apnic.net',
    urlLabel: 'apnic.net',
  },
]);

// ─── GSAP Animations ─────────────────────────────────────────────
onMounted(async () => {
  if (!isEnabled.value || hasBuilderBlocks.value || cmsBody.value) return;

  await nextTick();

  // Hero entrance
  if (heroBadgeRef.value)    fadeInUp(heroBadgeRef.value,    { delay: 0,   duration: 0.6 });
  if (heroTitleRef.value)    splitTextRevealSafe(heroTitleRef.value, { delay: 0.05, stagger: 0.04 });
  if (heroSubtitleRef.value) fadeInUp(heroSubtitleRef.value, { delay: 0.2, duration: 0.7 });
  if (heroStatsRef.value)    staggerChildren(heroStatsRef.value, '.about-hero-stat', { delay: 0.3, stagger: 0.08, distance: 20 });

  // Section 2 — history + entity card
  if (historyTextRef.value) fadeInLeft(historyTextRef.value,  { duration: 0.8, start: 'top 95%' });
  if (entityCardRef.value)  scaleReveal(entityCardRef.value,  { delay: 0.1, start: 'top 95%' });

  // Section 3 — visi misi
  if (visionMissionRef.value) staggerChildren(visionMissionRef.value, '.about-vm-card', { stagger: 0.14, distance: 28, start: 'top 95%' });

  // Section 4 — network
  if (networkHeaderRef.value) fadeInUp(networkHeaderRef.value, { duration: 0.7, start: 'top 95%' });
  if (networkCardsRef.value)  staggerChildren(networkCardsRef.value, '.about-net-card', { stagger: 0.1, distance: 24, start: 'top 95%' });

  // Section 5 — compliance
  if (complianceHeaderRef.value) fadeInUp(complianceHeaderRef.value, { duration: 0.7, start: 'top 95%' });
  if (complianceRef.value)       staggerChildren(complianceRef.value, '.about-compliance-card', { stagger: 0.12, distance: 28, start: 'top 95%' });

  ScrollTrigger?.refresh?.();
});
</script>
