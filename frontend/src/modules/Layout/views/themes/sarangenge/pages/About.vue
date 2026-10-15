<template>
  <SarangengePageGate
    setting-key="enable_about"
    :title="t('pages.about.title', { school: displaySchoolName }, `Profil & Filosofi Pendidikan ${displaySchoolName}`)"
  >
    <div
      data-ja-customizer-target="about"
      class="sarangenge-theme flex-1 flex flex-col py-10 md:py-12"
    >
      <BlockRenderer
        v-if="hasBuilderBlocks"
        :blocks="builderBlocks"
        :context="{ post: pageData, site: { name: displaySchoolName } }"
      />

      <ThemeSafeHtml
        v-else-if="cmsBody"
        class="container mx-auto px-4 py-10 md:py-12"
        :html="cmsBody"
        mode="publishing"
      />

      <template v-else>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 w-full">
          <!-- Breadcrumb & Header -->
          <div
            id="profil"
            class="scroll-mt-28 space-y-4"
          >
          <Breadcrumb :items="[{ name: t('pages.about.title', 'Profil Sekolah') }]" />
          <div class="max-w-3xl space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary">
              <Building2 class="w-3.5 h-3.5" />
              Mengenal Lebih Dekat
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
              {{ t('pages.about.title', { school: displaySchoolName }, `Profil & Filosofi Pendidikan ${displaySchoolName}`) }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.about.subtitle', 'Mendedikasikan ekosistem pembelajaran holistik yang memadukan keunggulan sains, teknologi, dan keteguhan akhlak mulia.') }}
            </p>
          </div>
        </div>

        <PluginSlot name="after_hero" class="w-full" />

        <!-- Sambutan Kepala Sekolah -->
        <div class="sarangenge-panel p-8 sm:p-12 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
          <div class="lg:col-span-4 flex flex-col items-center text-center space-y-3">
            <div class="w-36 h-36 sm:w-44 sm:h-44 rounded-3xl bg-slate-900 border-2 border-amber-500/60 p-1 shadow-xl overflow-hidden">
              <img
                v-if="displayPrincipalImage"
                :src="displayPrincipalImage"
                :alt="displayPrincipalName"
                class="w-full h-full object-cover rounded-[1.3rem]"
              >
              <div
                v-else
                class="w-full h-full rounded-[1.3rem] bg-slate-950 flex items-center justify-center text-amber-400 font-black text-3xl"
              >
                KS
              </div>
            </div>
            <div>
              <h3 class="text-lg font-bold text-foreground font-heading">
                {{ displayPrincipalName }}
              </h3>
              <p class="text-xs text-primary font-bold">
                {{ displayPrincipalTitle }}
              </p>
            </div>
          </div>

          <div class="lg:col-span-8 space-y-4">
            <h2 class="text-2xl font-bold text-foreground font-heading flex items-center gap-2">
              <Quote class="w-6 h-6 text-amber-500" />
              Sambutan Kepala Sekolah
            </h2>
            <div class="text-muted-foreground text-sm sm:text-base leading-relaxed space-y-3">
              <p class="italic text-foreground font-medium">
                "{{ displayPrincipalQuote }}"
              </p>
              <p>
                "Selamat datang di portal resmi {{ displaySchoolName }}. Kami mendedikasikan ekosistem pembelajaran vokasi berkualitas berstandar industri dengan sertifikasi kompetensi nasional dan pembinaan akhlak mulia."
              </p>
              <p>
                "Kami membina setiap peserta didik agar senantiasa mencintai ilmu, bertumbuh dalam integritas karakter, dan percaya diri menjadi pelopor kemajuan di dunia usaha dan industri."
              </p>
            </div>
          </div>
        </div>

        <!-- Identitas Resmi & Legalitas Grid (tampil jika NPSN atau Akreditasi terkonfigurasi) -->
        <div
          v-if="displayNpsn || displayAccreditation"
          class="sarangenge-panel p-6 sm:p-8 bg-gradient-to-r from-card to-primary/5 space-y-4"
        >
          <div class="flex items-center gap-2 text-primary font-bold text-sm">
            <ShieldCheck class="w-5 h-5" />
            <span>Identitas & Legalitas Resmi Satuan Pendidikan</span>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs sm:text-sm">
            <div v-if="displayNpsn">
              <span class="text-muted-foreground block text-[11px]">NPSN Resmi</span>
              <span class="font-bold text-foreground font-heading">{{ displayNpsn }}</span>
            </div>
            <div v-if="displayAccreditation">
              <span class="text-muted-foreground block text-[11px]">Status Akreditasi</span>
              <span class="font-bold text-foreground font-heading">{{ displayAccreditation }}</span>
            </div>
            <div>
              <span class="text-muted-foreground block text-[11px]">Sertifikasi Kejuruan</span>
              <span class="font-bold text-foreground font-heading">LSP-P1 / BNSP Terakreditasi</span>
            </div>
            <div>
              <span class="text-muted-foreground block text-[11px]">Model Pembelajaran</span>
              <span class="font-bold text-foreground font-heading">Teaching Factory & Kurikulum Industri</span>
            </div>
          </div>
        </div>

        <!-- Visi & Misi Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Visi -->
          <div class="sarangenge-panel p-8 space-y-4 bg-gradient-to-br from-card to-primary/5">
            <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center font-bold">
              <Target class="w-6 h-6" />
            </div>
            <h3 class="text-2xl font-bold text-foreground font-heading">
              {{ t('pages.about.visionTitle', 'Visi Sekolah') }}
            </h3>
            <p class="text-muted-foreground text-sm sm:text-base leading-relaxed italic">
              "{{ displaySchoolVision }}"
            </p>
          </div>

          <!-- Misi -->
          <div class="sarangenge-panel p-8 space-y-4 bg-gradient-to-br from-card to-amber-500/5">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/15 text-amber-800 dark:text-amber-300 flex items-center justify-center font-bold">
              <Compass class="w-6 h-6" />
            </div>
            <h3 class="text-2xl font-bold text-foreground font-heading">
              {{ t('pages.about.missionTitle', 'Misi Sekolah') }}
            </h3>
            <ul class="space-y-2 text-sm text-muted-foreground leading-relaxed">
              <li class="flex items-start gap-2">
                <span class="text-primary font-bold">1.</span>
                <span>Meningkatkan mutu Sumber Daya Manusia melalui dukungan Imtaq dan Iptek terapan berstandar global.</span>
              </li>
              <li class="flex items-start gap-2">
                <span class="text-primary font-bold">2.</span>
                <span>Memberikan layanan prima dalam semua aspek sarana dan prasarana untuk menghasilkan tenaga kerja yang kompeten dan mandiri.</span>
              </li>
              <li class="flex items-start gap-2">
                <span class="text-primary font-bold">3.</span>
                <span>Menciptakan tamatan yang berpengetahuan luas, memiliki etos kerja tinggi, kepribadian tangguh, dan akhlak terpuji.</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Nilai Utama Sekolah -->
        <div class="space-y-8">
          <div class="text-center max-w-2xl mx-auto space-y-2">
            <h2 class="text-3xl font-extrabold text-foreground font-heading">
              Nilai-Nilai Dasar (Core Values)
            </h2>
            <p class="text-sm text-muted-foreground">
              Fondasi karakter yang kami tanamkan kepada seluruh sivitas akademika.
            </p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
              v-for="(val, idx) in coreValues"
              :key="idx"
              class="sarangenge-panel p-6 text-center space-y-3"
            >
              <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mx-auto">
                <component
                  :is="val.icon"
                  class="w-6 h-6"
                />
              </div>
              <h4 class="text-lg font-bold text-foreground font-heading">
                {{ val.title }}
              </h4>
              <p class="text-xs text-muted-foreground leading-relaxed">
                {{ val.desc }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
  </SarangengePageGate>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import PluginSlot from '@/shared/components/PluginSlot.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import SarangengePageGate from '@/modules/Layout/views/themes/sarangenge/components/shared/SarangengePageGate.vue';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';
import { Building2, Quote, Target, Compass, ShieldCheck, Heart, Sparkles, Users } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const {
  displaySchoolName,
  displayPrincipalName,
  displayPrincipalTitle,
  displayPrincipalImage,
  displayPrincipalQuote,
  displaySchoolVision,
  displayNpsn,
  displayAccreditation,
} = useSarangengeIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('about');

useThemeHashScroll(128);

const coreValues = [
  { icon: ShieldCheck, title: 'Integritas & Karakter', desc: 'Menjunjung tinggi kejujuran, tanggung jawab moral, dan etos kerja industri.' },
  { icon: Sparkles, title: 'Kompetensi Terapan', desc: 'Terampil menguasai teknologi dan prosedur kerja profesional bersertifikat BNSP.' },
  { icon: Heart, title: 'Empati & Mandiri', desc: 'Menumbuhkan jiwa kepedulian sosial, gotong royong, dan kemandirian wirausaha.' },
  { icon: Users, title: 'Sinergi Industri', desc: 'Mampu berkolaborasi aktif dengan dunia usaha dan dunia industri (DUDI).' },
];
</script>
