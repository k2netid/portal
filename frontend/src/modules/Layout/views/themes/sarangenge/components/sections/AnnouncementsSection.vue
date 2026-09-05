<template>
  <section
    data-ja-customizer-target="news"
    class="py-12 sm:py-14 bg-muted/20 border-t border-border/60"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-14 gap-6">
        <div class="max-w-2xl space-y-3">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
            <Calendar class="w-3.5 h-3.5" />
            Agenda & Kabar Terkini
          </span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
            {{ announcementsTitle }}
          </h2>
          <p class="text-muted-foreground text-base sm:text-lg leading-relaxed">
            {{ announcementsSubtitle }}
          </p>
        </div>

        <Button
          as="router-link"
          to="/blog"
          variant="outline"
          size="md"
          class="self-start md:self-auto shrink-0"
        >
          {{ t('common.viewAll', 'Lihat Semua Agenda') }}
          <ArrowRight class="w-4 h-4 ml-1" />
        </Button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div
          v-for="(event, idx) in resolvedEvents"
          :key="event.id || idx"
          class="sarangenge-panel p-6 flex flex-col justify-between hover:shadow-lg transition-all duration-300"
        >
          <div class="space-y-3">
            <div class="flex items-center gap-3">
              <div class="px-3 py-1.5 rounded-xl bg-[var(--sarangenge-sun,#e8a317)]/20 text-amber-900 dark:text-amber-200 font-extrabold text-center text-xs">
                <div class="text-base font-black leading-none">
                  {{ event.day }}
                </div>
                <div class="text-[9px] uppercase tracking-wider">
                  {{ event.month }}
                </div>
              </div>
              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--sarangenge-teal,#0f766e)] block">
                  {{ event.badge }}
                </span>
                <span class="text-xs text-muted-foreground">{{ event.subtitle }}</span>
              </div>
            </div>
            <h3 class="text-lg font-bold text-foreground font-heading">
              {{ event.title }}
            </h3>
            <p class="text-xs text-muted-foreground leading-relaxed line-clamp-3">
              {{ event.description }}
            </p>
          </div>
          <div class="pt-4 mt-4 border-t border-border/60 flex items-center justify-between text-xs text-muted-foreground">
            <span>{{ event.venue }}</span>
            <router-link
              :to="event.linkUrl"
              class="font-bold text-[var(--sarangenge-teal,#0f766e)] hover:underline"
            >
              {{ event.linkText }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import type { Content } from '@/modules/Publishing/types/content';
import { Calendar, ArrowRight } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();
const { displaySchoolName } = useSarangengeIdentity();

const eventsList = ref<Content[]>([]);
const loading = ref(true);

const announcementsTitle = computed(() => {
  return (getSetting('announcements_title', '') as string) || t('pages.home.tileBerita', 'Agenda & Kabar Sekolah Terkini');
});

const announcementsSubtitle = computed(() => {
  return (getSetting('announcements_subtitle', '') as string) || t('pages.home.tileBeritaDesc', 'Pantau aktivitas resmi, kalender akademik, jadwal asesmen, dan siaran pers sekolah.');
});

const defaultEvents = computed(() => [
  {
    id: 'event-1',
    day: '15',
    month: 'Okt',
    badge: 'PPDB 2026',
    subtitle: 'Gelombang 1 Dibuka',
    title: 'Pembukaan Pendaftaran Siswa Baru Jalur Prestasi & Tes Akademik',
    description: 'Pendaftaran daring melalui portal resmi sekolah dengan diskon biaya pendidikan bagi peraih medali olimpiade.',
    venue: 'Aula Utama & Online',
    linkText: 'Info Syarat →',
    linkUrl: '/contact',
  },
  {
    id: 'event-2',
    day: '28',
    month: 'Okt',
    badge: 'Pameran Karya',
    subtitle: 'P5 Expo 2026',
    title: `${displaySchoolName.value} Science, Tech & Cultural Exhibition`,
    description: 'Unjuk gelar proyek penelitian sains, demo robotika AI, dan pameran seni budaya nusantara karya seluruh siswa.',
    venue: 'Sport Hall & Galeri',
    linkText: 'Jadwal Acara →',
    linkUrl: '/blog',
  },
  {
    id: 'event-3',
    day: '10',
    month: 'Nov',
    badge: 'Parenting',
    subtitle: 'Seminar Orang Tua',
    title: 'Parent-Teacher Synergy: Mendampingi Remaja Era Digital',
    description: 'Diskusi interaktif bersama pakar psikologi pendidikan tentang kesehatan mental dan fokus belajar generasi Alpha.',
    venue: 'Auditorium & Zoom',
    linkText: 'Registrasi →',
    linkUrl: '/blog',
  },
]);

const resolvedEvents = computed(() => {
  if (eventsList.value.length > 0) {
    return eventsList.value.slice(0, 3).map((item: any) => {
      const raw = item._raw || item;
      const meta = raw.meta || {};
      const pubDate = raw.published_at ? new Date(raw.published_at) : new Date();
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      return {
        id: item.id,
        slug: item.slug || '',
        day: meta.day || pubDate.getDate().toString(),
        month: meta.month || months[pubDate.getMonth()],
        badge: meta.badge || 'Agenda Sekolah',
        subtitle: meta.subtitle || 'Kabar Terkini',
        title: item.title,
        description: item.excerpt || item.description || raw.intro || '',
        venue: meta.venue || 'Kampus Sekolah',
        linkText: meta.link_text || 'Lihat Detail →',
        linkUrl: meta.link_url || (item.slug ? `/blog/${item.slug}` : '/blog'),
      };
    });
  }

  return defaultEvents.value;
});

onMounted(async () => {
  try {
    const res = await api.get(publishingPaths.publicContents, {
      params: { category: 'agenda', status: 'published', limit: 3, sort: '-published_at' },
    });
    const data = res.data;
    const items = Array.isArray(data)
      ? data
      : Array.isArray(data?.data)
        ? data.data
        : Array.isArray(data?.data?.data)
          ? data.data.data
          : [];
    eventsList.value = items;
  } catch {
    eventsList.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
