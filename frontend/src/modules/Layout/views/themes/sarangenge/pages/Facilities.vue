<template>
  <SarangengePageGate
    setting-key="enable_facilities"
    :title="t('pages.facilities.heading', 'Fasilitas & Bengkel Praktik')"
  >
    <div class="sarangenge-theme flex-1 flex flex-col py-10 md:py-12">
      <BlockRenderer
        v-if="hasBuilderBlocks"
        :blocks="builderBlocks"
        :context="{ site: { name: displaySchoolName } }"
      />

      <template v-else>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 w-full">
          <div class="space-y-4 text-center md:text-left">
            <Breadcrumb :items="[{ name: t('pages.facilities.title', 'Fasilitas & Bengkel') }]" />
            <div class="max-w-3xl space-y-3 mx-auto md:mx-0">
              <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
                {{ t('pages.facilities.heading', 'Fasilitas & Bengkel Praktik') }}
              </h1>
              <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
                {{ t('pages.facilities.subtitle', 'Sarana prasarana berstandar industri untuk menunjang kompetensi keahlian siswa di lingkungan SMK Pusat Keunggulan.') }}
              </p>
            </div>
          </div>

          <div v-if="loading && !hasBinding" class="min-h-[300px] flex items-center justify-center">
            <div class="w-8 h-8 rounded-full border-2 border-[var(--sarangenge-teal,#0f766e)] border-t-transparent animate-spin" />
          </div>

          <div v-else-if="resolvedFacilities.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <router-link
              v-for="facility in resolvedFacilities"
              :key="facility.id || facility.slug"
              :to="facility.slug ? `/blog/${facility.slug}` : '#'"
              class="sarangenge-panel group flex flex-col overflow-hidden transition-all duration-300 hover:shadow-xl hover:border-[var(--sarangenge-teal,#0f766e)]/30 cursor-pointer block text-left"
            >
              <div class="h-48 sm:h-52 bg-muted/30 flex items-center justify-center relative overflow-hidden">
                <template v-if="facility.image">
                  <img
                    :src="facility.image"
                    :alt="facility.title"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    loading="lazy"
                  />
                  <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                </template>
                <template v-else>
                  <div class="absolute inset-0 opacity-20 bg-gradient-to-br from-[var(--sarangenge-teal,#0f766e)] to-transparent"></div>
                  <component
                    :is="facility.iconComponent"
                    class="w-14 h-14 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-400 z-10 group-hover:scale-110 transition-transform duration-500"
                  />
                </template>
              </div>
              <div class="p-6 sm:p-7 flex-1 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                  <h3 class="text-xl font-bold font-heading text-foreground group-hover:text-[var(--sarangenge-teal,#0f766e)] transition-colors">
                    {{ facility.title }}
                  </h3>
                  <p class="text-sm text-muted-foreground leading-relaxed line-clamp-3">
                    {{ facility.description }}
                  </p>
                </div>
              </div>
              <div class="px-6 py-4 border-t bg-muted/20 flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">SMK Pusat Keunggulan</span>
                <span
                  class="text-sm font-medium text-[var(--sarangenge-teal,#0f766e)] group-hover:underline inline-flex items-center gap-1"
                >
                  Detail Fasilitas
                  <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                </span>
              </div>
            </router-link>
          </div>

          <div v-else class="sarangenge-panel p-10 text-center text-muted-foreground space-y-3">
            <p class="text-base font-semibold text-foreground">
              {{ t('pages.facilities.noData', 'Data sarana & fasilitas praktik belum tersedia.') }}
            </p>
          </div>
        </div>
      </template>
    </div>
  </SarangengePageGate>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import { useThemeDataBindings } from '@/modules/Layout/composables/useThemeDataBindings';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import SarangengePageGate from '@/modules/Layout/views/themes/sarangenge/components/shared/SarangengePageGate.vue';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import type { Content } from '@/modules/Publishing/types/content';
import {
  Wrench,
  Zap,
  MonitorPlay,
  CarFront,
  Hammer,
  Cpu,
  Building2,
  BookOpen,
  ArrowRight,
} from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { displaySchoolName } = useSarangengeIdentity();
const { builderBlocks, hasBuilderBlocks } = useThemePageOverride('facilities');

const { data: dynamicFacilities, hasBinding } = useThemeDataBindings('facilities', 'items');

const facilities = ref<Content[]>([]);
const loading = ref(true);

interface FacilityItem {
  id: string | number;
  slug: string;
  title: string;
  image?: string | null;
  description: string;
  iconComponent: any;
}

const defaultFacilities: FacilityItem[] = [
  {
    id: 'f-1',
    slug: 'studio-desain-bim-dpib',
    title: 'Studio Desain & BIM (DPIB)',
    image: null,
    description: 'Laboratorium komputer berstandar industri dengan perangkat lunak AutoCAD, SketchUp, dan aplikasi Building Information Modeling (BIM) untuk perancangan konstruksi.',
    iconComponent: MonitorPlay,
  },
  {
    id: 'f-2',
    slug: 'laboratorium-listrik-otomasi-titl',
    title: 'Laboratorium Listrik & Otomasi (TITL)',
    image: null,
    description: 'Bengkel praktik instalasi penerangan, instalasi tenaga, dan kontrol motor listrik berbasis kontaktor serta PLC (Programmable Logic Controller).',
    iconComponent: Zap,
  },
  {
    id: 'f-3',
    slug: 'bengkel-cnc-mesin-presisi-tpm',
    title: 'Bengkel CNC & Mesin Produksi (TPM)',
    image: null,
    description: 'Bengkel manufaktur presisi yang dilengkapi dengan mesin bubut, mesin frais konvensional, serta mesin perkakas CNC berteknologi terkini.',
    iconComponent: Wrench,
  },
  {
    id: 'f-4',
    slug: 'bengkel-otomotif-tkro',
    title: 'Bengkel Servis & Engine Stand (TKRO)',
    image: null,
    description: 'Fasilitas praktik otomotif roda empat yang mencakup engine scanner (EFI), spooring/balancing, dan alat uji emisi berstandar bengkel resmi.',
    iconComponent: CarFront,
  },
  {
    id: 'f-5',
    slug: 'laboratorium-mikroelektronik-tav',
    title: 'Laboratorium Mikroelektronik (TAV)',
    image: null,
    description: 'Ruang praktik perakitan sistem audio video, desain PCB, serta pengembangan mikrokontroler dan sistem otomasi cerdas (IoT).',
    iconComponent: Cpu,
  },
  {
    id: 'f-6',
    slug: 'bengkel-fabrikasi-pengelasan-tflm',
    title: 'Bengkel Las GMAW/SMAW (TFLM)',
    image: null,
    description: 'Area fabrikasi logam dan pengelasan profesional (Welding) yang dirancang dengan sistem ventilasi aman untuk berbagai metode pengelasan (SMAW, GMAW).',
    iconComponent: Hammer,
  },
];

function resolveFacilityIcon(title: string, slug: string) {
  const text = `${title} ${slug}`.toLowerCase();
  if (text.includes('bim') || text.includes('desain') || text.includes('komputer')) return MonitorPlay;
  if (text.includes('listrik') || text.includes('otomasi') || text.includes('titl')) return Zap;
  if (text.includes('cnc') || text.includes('bubut') || text.includes('mesin') || text.includes('tpm')) return Wrench;
  if (text.includes('otomotif') || text.includes('mobil') || text.includes('kendaraan') || text.includes('tkro')) return CarFront;
  if (text.includes('mikro') || text.includes('audio') || text.includes('iot') || text.includes('tav')) return Cpu;
  if (text.includes('las') || text.includes('fabrikasi') || text.includes('welding') || text.includes('tflm')) return Hammer;
  if (text.includes('perpustakaan') || text.includes('library') || text.includes('buku')) return BookOpen;
  if (text.includes('gedung') || text.includes('coe') || text.includes('excellence')) return Building2;
  return Wrench;
}

const resolvedFacilities = computed<FacilityItem[]>(() => {
  if (hasBinding.value && dynamicFacilities.value && dynamicFacilities.value.length > 0) {
    return dynamicFacilities.value.map((item: any, idx: number) => {
      const raw = item._raw || item;
      const title = item.title || raw.title || '';
      const slug = raw.slug || (title ? String(title).toLowerCase().replace(/\s+/g, '-') : `facility-${idx + 1}`);
      return {
        id: raw.id || `dyn-${idx}`,
        title,
        slug,
        image: raw.featured_image || item.featured_image || item.image || null,
        description: item.excerpt || item.description || raw.description || raw.intro || '',
        iconComponent: resolveFacilityIcon(title, slug),
      };
    });
  }

  if (facilities.value && facilities.value.length > 0) {
    return facilities.value.map((item: Content, idx: number) => {
      const title = item.title || '';
      const slug = item.slug || `facility-${idx + 1}`;
      return {
        id: item.id || `api-${idx}`,
        title,
        slug,
        image: item.featured_image || null,
        description: item.excerpt || item.intro || '',
        iconComponent: resolveFacilityIcon(title, slug),
      };
    });
  }

  return defaultFacilities;
});

onMounted(async () => {
  loading.value = true;
  try {
    const res = await api.get(publishingPaths.publicContents, {
      params: { category: 'fasilitas', status: 'published', sort: 'title' },
    });
    const data = res.data;
    facilities.value = Array.isArray(data) ? data : data?.data || [];
  } catch {
    facilities.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
