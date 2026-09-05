<template>
  <SarangengePageGate
    setting-key="enable_tim"
    :title="t('pages.tim.title', 'Dewan Guru & Tenaga Kependidikan')"
  >
    <div
      data-ja-customizer-target="tim"
      class="sarangenge-theme flex-1 flex flex-col py-10 md:py-12"
    >
      <BlockRenderer
        v-if="hasBuilderBlocks"
        :blocks="builderBlocks"
        :context="{ post: pageData, site: { name: displaySchoolName } }"
      />

      <ThemeSafeHtml
        v-else-if="cmsBody"
        class="container mx-auto px-4 py-16"
        :html="cmsBody"
        mode="publishing"
      />

      <template v-else>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 w-full">
          <!-- Breadcrumb & Header -->
          <div class="space-y-4">
            <Breadcrumb :items="[{ name: t('pages.tim.title', 'Guru & Staf') }]" />
            <div class="max-w-3xl space-y-3">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
                <Users class="w-3.5 h-3.5" />
                {{ t('pages.tim.badge', 'Pendidik Berdedikasi') }}
              </span>
              <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
                {{ t('pages.tim.title', 'Dewan Guru & Tenaga Kependidikan') }}
              </h1>
              <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
                {{ t('pages.tim.subtitle', 'Didukung oleh pendidik profesional berkualifikasi sarjana & magister dengan sertifikasi pendidik nasional serta pengajar bersertifikat Cambridge.') }}
              </p>
            </div>
          </div>

          <!-- Loading Spinner -->
          <div v-if="loading && !hasBinding" class="min-h-[250px] flex items-center justify-center">
            <div class="w-8 h-8 rounded-full border-2 border-[var(--sarangenge-teal,#0f766e)] border-t-transparent animate-spin" />
          </div>

          <!-- Teachers Grid -->
          <div v-else-if="resolvedStaff.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <router-link
              v-for="(staff, idx) in resolvedStaff"
              :key="staff.id || idx"
              :to="staff.slug ? `/blog/${staff.slug}` : '#'"
              class="sarangenge-panel p-6 text-center space-y-4 hover:border-[var(--sarangenge-teal,#0f766e)]/40 hover:-translate-y-1 transition-all duration-300 group flex flex-col justify-between cursor-pointer block"
            >
              <div class="space-y-3">
                <div class="w-20 h-20 rounded-2xl bg-slate-900 border border-slate-700/80 p-1 mx-auto shadow-md group-hover:scale-105 transition-transform flex items-center justify-center text-amber-400 font-extrabold text-xl">
                  {{ staffInitials(staff.name) }}
                </div>

                <div>
                  <h3 class="text-base font-bold text-foreground font-heading group-hover:text-[var(--sarangenge-teal,#0f766e)] transition-colors">
                    {{ staff.name }}
                  </h3>
                  <span class="text-xs font-bold text-[var(--sarangenge-teal,#0f766e)] block mt-0.5">
                    {{ staff.role }}
                  </span>
                  <span class="text-[11px] text-muted-foreground block mt-1">
                    {{ staff.subject }}
                  </span>
                </div>
              </div>

              <div class="pt-3 border-t border-border/60 flex items-center justify-between text-[11px] text-muted-foreground">
                <span class="truncate">{{ staff.education }}</span>
                <span v-if="staff.slug" class="text-[var(--sarangenge-teal,#0f766e)] group-hover:underline font-semibold ml-1 shrink-0">
                  Profil →
                </span>
              </div>
            </router-link>
          </div>

          <div v-else class="sarangenge-panel p-10 text-center text-muted-foreground space-y-3">
            <p class="text-base font-semibold text-foreground">
              {{ t('pages.tim.noData', 'Data dewan guru & staf belum tersedia.') }}
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
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import SarangengePageGate from '@/modules/Layout/views/themes/sarangenge/components/shared/SarangengePageGate.vue';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import type { Content } from '@/modules/Publishing/types/content';
import { Users } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { displaySchoolName } = useSarangengeIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('tim');

const { data: dynamicStaff, hasBinding } = useThemeDataBindings('staff', 'items');

const staffList = ref<Content[]>([]);
const loading = ref(true);

const staffInitials = (name: string) => {
  const parts = name.trim().split(/\s+/).filter(Boolean);
  const first = parts[0]?.charAt(0) ?? '';
  const second = parts[1]?.charAt(0) ?? '';
  return `${first}${second}`;
};

const defaultStaffDirectory = [
  {
    id: 'staff-1',
    slug: 'drs-h-rahmat-sudrajat-mpd',
    name: 'Drs. H. Rahmat Sudrajat, M.Pd.',
    role: 'Kepala Sekolah',
    subject: 'Manajemen Pendidikan & Kepemimpinan Vokasi',
    education: 'S2 Manajemen Pendidikan UPI',
  },
  {
    id: 'staff-2',
    slug: 'dra-hj-nurul-hidayati-msi',
    name: 'Dra. Hj. Nurul Hidayati, M.Si.',
    role: 'Wakil Kepala Sekolah Bidang Kurikulum',
    subject: 'Kurikulum & Sains Terapan',
    education: 'S2 Ilmu Kimia ITB',
  },
  {
    id: 'staff-3',
    slug: 'bambang-irawan-skom-mt',
    name: 'Bambang Irawan, S.Kom., M.T.',
    role: 'Koordinator STEM & Lab AI',
    subject: 'Informatika, IoT & Robotika Terapan',
    education: 'S2 Teknik Elektro ITB',
  },
  {
    id: 'staff-4',
    slug: 'sarah-jenkins-bed-ma',
    name: 'Sarah Jenkins, B.Ed., M.A.',
    role: 'Cambridge Coordinator',
    subject: 'English for Vocational Communication',
    education: 'University of Cambridge (UK)',
  },
  {
    id: 'staff-5',
    slug: 'ustadz-ahmad-fauzi-lc-mag',
    name: 'Ustadz Ahmad Fauzi, Lc., M.Ag.',
    role: 'Koordinator Keagamaan & Karakter',
    subject: 'Pendidikan Agama & Etika Profesi',
    education: 'Universitas Al-Azhar Kairo',
  },
  {
    id: 'staff-6',
    slug: 'dewi-sartika-spd-gr',
    name: 'Dewi Sartika, S.Pd., Gr.',
    role: 'Guru Pembina Olimpiade',
    subject: 'Matematika Terapan & Logika Rekayasa',
    education: 'S1 Pendidikan Matematika UPI',
  },
  {
    id: 'staff-7',
    slug: 'rudi-hartono-sor',
    name: 'Rudi Hartono, S.Or.',
    role: 'Wakil Kepala Sekolah Bidang Kesiswaan',
    subject: 'Pendidikan Jasmani, Olahraga & Kesehatan',
    education: 'S1 Ilmu Keolahragaan UNJ',
  },
  {
    id: 'staff-8',
    slug: 'laksmi-paramita-spsi-mpsi',
    name: 'Laksmi Paramita, S.Psi., M.Psi.',
    role: 'Koordinator BK & Karir BKK',
    subject: 'Bimbingan Karir & Psikologi Industri',
    education: 'S2 Psikologi Profesi UNPAD',
  },
];

const resolvedStaff = computed(() => {
  if (hasBinding.value && dynamicStaff.value && dynamicStaff.value.length > 0) {
    return dynamicStaff.value.map((item: any) => {
      const raw = item._raw || item;
      return {
        id: item.id,
        slug: item.slug || '',
        name: item.title || raw.name || '',
        role: raw.meta?.role || raw.role || '',
        subject: raw.meta?.subject || raw.subject || item.excerpt || '',
        education: raw.meta?.education || raw.education || '',
      };
    });
  }

  if (staffList.value.length > 0) {
    return staffList.value.map((item: any) => {
      const raw = item._raw || item;
      const meta = raw.meta || {};
      return {
        id: item.id,
        slug: item.slug || '',
        name: item.title || '',
        role: meta.role || item.excerpt || '',
        subject: meta.subject || raw.intro || '',
        education: meta.education || '',
      };
    });
  }

  return defaultStaffDirectory;
});

onMounted(async () => {
  if (hasBinding.value) {
    loading.value = false;
    return;
  }
  try {
    const res = await api.get(publishingPaths.publicContents, {
      params: { category: 'guru-staf', status: 'published', sort: 'title' },
    });
    const data = res.data;
    const items = Array.isArray(data)
      ? data
      : Array.isArray(data?.data)
        ? data.data
        : Array.isArray(data?.data?.data)
          ? data.data.data
          : [];
    staffList.value = items;
  } catch {
    staffList.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
