<template>
  <section
    id="klien"
    class="py-12 sm:py-14 scroll-mt-24"
  >
    <span id="testimonials" class="sr-only">Testimonials</span>
    <span id="partners" class="sr-only">Partners</span>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
      <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 uppercase tracking-wider font-mono">
          {{ t('clients.badge', 'Layanan institusi pendidikan') }}
        </span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
          {{ t('clients.title', 'Pendampingan IT untuk sekolah dan institusi') }}
        </h2>
        <p class="text-muted-foreground text-sm sm:text-base leading-relaxed">
          {{ t('clients.subtitle', 'K2NET menyediakan jaringan, server, CCTV, dan dukungan operasional IT untuk institusi pendidikan di Bandung dan Jawa Barat. Daftar mitra resmi dipublikasikan setelah persetujuan masing-masing institusi.') }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <article
          v-for="item in focusItems"
          :key="item.titleKey"
          class="layung-panel p-6 sm:p-8 space-y-3 flex flex-col"
        >
          <h3 class="text-sm font-bold text-foreground font-heading">
            {{ t(item.titleKey, item.titleFallback) }}
          </h3>
          <p class="text-sm text-muted-foreground leading-relaxed">
            {{ t(item.bodyKey, item.bodyFallback) }}
          </p>
        </article>
      </div>

      <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <Button
          as="router-link"
          to="/pricing/msp"
          variant="outline"
          size="md"
          class="font-semibold"
        >
          {{ t('clients.mspCta', 'Lihat paket managed services') }}
        </Button>
        <Button
          as="router-link"
          to="/contact"
          variant="primary"
          size="md"
          class="font-semibold"
        >
          {{ t('clients.contactCta', 'Diskusikan kebutuhan institusi') }}
        </Button>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Button } from '@/modules/Layout/views/themes/layung/ui';

const { t } = useThemeI18n('layung');

const focusItems = [
  {
    titleKey: 'clients.focus1Title',
    titleFallback: 'Jaringan kampus & lab',
    bodyKey: 'clients.focus1Body',
    bodyFallback: 'Instalasi, dokumentasi, dan perawatan jaringan sekolah termasuk lab komputer dan akses Wi-Fi.',
  },
  {
    titleKey: 'clients.focus2Title',
    titleFallback: 'Server, aplikasi, dan CCTV',
    bodyKey: 'clients.focus2Body',
    bodyFallback: 'Lingkup managed services dapat mencakup server, aplikasi sekolah, dan kamera — sesuai kontrak.',
  },
  {
    titleKey: 'clients.focus3Title',
    titleFallback: 'Pendampingan kegiatan berbasis IT',
    bodyKey: 'clients.focus3Body',
    bodyFallback: 'Dukungan operasional saat ujian daring atau acara sekolah, dengan eskalasi lewat Service Desk.',
  },
] as const;
</script>
