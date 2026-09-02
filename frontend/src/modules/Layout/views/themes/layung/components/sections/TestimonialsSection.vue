<template>
  <section
    id="klien"
    class="py-12 sm:py-14 scroll-mt-24"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
      <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 uppercase tracking-wider font-mono">
          {{ t('clients.badge', 'Mitra institusi pendidikan') }}
        </span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
          {{ t('clients.title', 'Mayoritas mitra kami adalah SMP Negeri di Kota Bandung') }}
        </h2>
        <p class="text-muted-foreground text-sm sm:text-base leading-relaxed">
          {{ t('clients.subtitle', 'K2NET mendampingi SMP Negeri di Kota Bandung untuk jaringan, server, dan operasional IT harian.') }}
        </p>
        <p class="text-sm font-mono text-sky-600 dark:text-sky-400">
          {{ visibleCount }} / {{ schools.length }} {{ t('clients.countLabel', 'sekolah dalam daftar') }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <article
          v-for="note in featuredNotes"
          :key="note.school.id"
          class="layung-panel p-6 sm:p-8 space-y-5 flex flex-col justify-between"
        >
          <p class="text-sm text-muted-foreground leading-relaxed">
            “{{ note.quote }}”
          </p>
          <div class="flex items-center gap-3.5 pt-4 border-t border-border/60">
            <div class="w-10 h-10 rounded-xl bg-slate-900 text-sky-400 flex items-center justify-center font-bold text-xs shadow shrink-0">
              {{ note.school.short.replace('SMPN ', '') }}
            </div>
            <div>
              <h4 class="text-sm font-bold text-foreground font-heading">
                {{ note.school.name }}
              </h4>
              <p class="text-xs text-muted-foreground">
                {{ note.role }}
              </p>
            </div>
          </div>
        </article>
      </div>

      <div class="space-y-4">
        <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
          <label class="sr-only" for="school-filter">{{ t('clients.search', 'Cari sekolah') }}</label>
          <input
            id="school-filter"
            v-model="query"
            type="search"
            :placeholder="t('clients.search', 'Cari SMPN…')"
            class="w-full sm:max-w-sm rounded-xl border border-border bg-background px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/40"
          >
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
          <div
            v-for="school in pagedSchools"
            :key="school.id"
            class="rounded-xl border border-border/80 bg-card px-3 py-2.5 text-left hover:border-sky-500/40 transition-colors"
          >
            <p class="text-[10px] font-mono uppercase tracking-wider text-sky-600 dark:text-sky-400">
              {{ school.short }}
            </p>
            <p class="text-xs font-semibold text-foreground leading-snug mt-0.5">
              {{ school.name }}
            </p>
          </div>
        </div>

        <div
          v-if="filteredSchools.length > pageSize"
          class="flex items-center justify-center gap-2 pt-2"
        >
          <Button
            type="button"
            variant="outline"
            size="sm"
            :disabled="page <= 1"
            class="font-semibold"
            @click="page = Math.max(1, page - 1)"
          >
            {{ t('clients.prev', 'Sebelumnya') }}
          </Button>
          <span class="text-xs font-mono text-muted-foreground px-2">
            {{ page }} / {{ totalPages }}
          </span>
          <Button
            type="button"
            variant="outline"
            size="sm"
            :disabled="page >= totalPages"
            class="font-semibold"
            @click="page = Math.min(totalPages, page + 1)"
          >
            {{ t('clients.next', 'Berikutnya') }}
          </Button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Button } from '@/modules/Layout/views/themes/layung/ui';
import {
  buildBandungSmpnClients,
  LAYUNG_DEMO_SCHOOL_NOTES,
} from '@/modules/Layout/views/themes/layung/composables/layungSchoolClients';

const { t } = useThemeI18n('layung');

const schools = buildBandungSmpnClients(57);
const query = ref('');
const page = ref(1);
const pageSize = 12;

const filteredSchools = computed(() => {
  const q = query.value.trim().toLowerCase();
  if (!q) return schools;
  return schools.filter((s) => s.name.toLowerCase().includes(q) || s.short.toLowerCase().includes(q));
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredSchools.value.length / pageSize)));

const pagedSchools = computed(() => {
  const start = (page.value - 1) * pageSize;
  return filteredSchools.value.slice(start, start + pageSize);
});

const visibleCount = computed(() => filteredSchools.value.length);

const featuredNotes = computed(() =>
  LAYUNG_DEMO_SCHOOL_NOTES.map((note) => ({
    ...note,
    school: schools.find((s) => s.id === note.schoolId) ?? schools[0],
  })),
);

watch(query, () => {
  page.value = 1;
});
</script>
