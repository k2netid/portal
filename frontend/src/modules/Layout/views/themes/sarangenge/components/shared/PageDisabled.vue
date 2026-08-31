<template>
  <div class="min-h-[70vh] flex items-center justify-center p-6 bg-background relative overflow-hidden">
    <div class="relative z-10 max-w-xl w-full">
      <div class="bg-card border border-border/80 p-8 sm:p-12 text-center rounded-[var(--sarangenge-radius-lg,1.75rem)] shadow-xl relative space-y-6">
        <div class="w-16 h-16 rounded-2xl bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal,#0f766e)] flex items-center justify-center mx-auto shadow-inner">
          <GraduationCap class="w-8 h-8" />
        </div>

        <div class="space-y-2">
          <span class="text-xs font-bold tracking-widest uppercase text-[var(--sarangenge-sun,#e8a317)] block">
            {{ t('pages.disabled.statusLabel', 'Informasi Sekolah') }}
          </span>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-foreground font-heading">
            {{ title || t('pages.disabled.defaultTitle', 'Halaman Belum Tersedia') }}
          </h1>
          <p class="text-muted-foreground text-sm leading-relaxed max-w-md mx-auto">
            {{ message || t('pages.disabled.defaultMessage', 'Layanan atau informasi ini sedang dalam proses pembaruan oleh pihak sekolah.') }}
          </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
          <Button
            as="router-link"
            to="/"
            variant="primary"
            size="md"
          >
            {{ t('pages.disabled.backHome', 'Kembali ke Beranda') }}
          </Button>
          <Button
            as="router-link"
            to="/contact"
            variant="outline"
            size="md"
          >
            {{ t('pages.disabled.contactAdmin', 'Hubungi Sekolah') }}
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { GraduationCap } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';

const { t } = useThemeI18n('sarangenge');

defineProps<{
  title?: string;
  message?: string;
}>();
</script>
