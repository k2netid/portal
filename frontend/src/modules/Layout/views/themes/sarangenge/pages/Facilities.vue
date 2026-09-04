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

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div
              v-for="(facility, index) in resolvedFacilities"
              :key="index"
              class="sarangenge-panel group overflow-hidden transition-all duration-300 hover:shadow-xl hover:border-[var(--sarangenge-teal,#0f766e)]/30"
            >
              <div class="h-48 bg-muted/30 flex items-center justify-center relative overflow-hidden">
                <!-- Placeholder pattern/gradient -->
                <div class="absolute inset-0 opacity-20 bg-gradient-to-br from-[var(--sarangenge-teal,#0f766e)] to-transparent"></div>
                <component :is="facility.icon || Wrench" class="w-16 h-16 text-[var(--sarangenge-teal-deep,#115e59)] z-10 group-hover:scale-110 transition-transform duration-500" />
              </div>
              <div class="p-6 sm:p-8 space-y-3">
                <h3 class="text-xl font-bold font-heading text-foreground">
                  {{ facility.title }}
                </h3>
                <p class="text-sm text-muted-foreground leading-relaxed">
                  {{ facility.description }}
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
import { computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import { useThemeDataBindings } from '@/modules/Layout/composables/useThemeDataBindings';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import SarangengePageGate from '@/modules/Layout/views/themes/sarangenge/components/shared/SarangengePageGate.vue';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import { Wrench, Zap, MonitorPlay, CarFront, Hammer, Cpu } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { displaySchoolName } = useSarangengeIdentity();
const { builderBlocks, hasBuilderBlocks } = useThemePageOverride('facilities');

const { data: dynamicFacilities, hasBinding } = useThemeDataBindings('facilities', 'items');

const defaultFacilities = [
  {
    title: 'Studio Desain & BIM (DPIB)',
    description: 'Laboratorium komputer berstandar industri dengan perangkat lunak AutoCAD, SketchUp, dan aplikasi Building Information Modeling (BIM) untuk perancangan konstruksi.',
    icon: MonitorPlay
  },
  {
    title: 'Laboratorium Listrik & Otomasi (TITL)',
    description: 'Bengkel praktik instalasi penerangan, instalasi tenaga, dan kontrol motor listrik berbasis kontaktor serta PLC (Programmable Logic Controller).',
    icon: Zap
  },
  {
    title: 'Bengkel CNC & Mesin Produksi (TPM)',
    description: 'Bengkel manufaktur presisi yang dilengkapi dengan mesin bubut, mesin frais konvensional, serta mesin perkakas CNC berteknologi terkini.',
    icon: Wrench
  },
  {
    title: 'Bengkel Servis & Engine Stand (TKRO)',
    description: 'Fasilitas praktik otomotif roda empat yang mencakup engine scanner (EFI), spooring/balancing, dan alat uji emisi berstandar bengkel resmi.',
    icon: CarFront
  },
  {
    title: 'Laboratorium Mikroelektronik (TAV)',
    description: 'Ruang praktik perakitan sistem audio video, desain PCB, serta pengembangan mikrokontroler dan sistem otomasi cerdas (IoT).',
    icon: Cpu
  },
  {
    title: 'Bengkel Las GMAW/SMAW (TFLM)',
    description: 'Area fabrikasi logam dan pengelasan profesional (Welding) yang dirancang dengan sistem ventilasi aman untuk berbagai metode pengelasan (SMAW, GMAW).',
    icon: Hammer
  }
];

const resolvedFacilities = computed(() => {
  if (hasBinding.value && dynamicFacilities.value && dynamicFacilities.value.length > 0) {
    return dynamicFacilities.value.map((item: any) => {
      const raw = item._raw || item;
      return {
        title: item.title || raw.title || '',
        description: item.excerpt || item.description || raw.description || '',
        icon: Wrench,
      };
    });
  }
  return defaultFacilities;
});
</script>
