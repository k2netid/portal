<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { HardDrive, ImageIcon, VideoIcon } from 'lucide-vue-next';
import { ConsoleStatCard } from '@/shared/components/shell';
import type { MediaStats } from '@/modules/Media/types/media';

const props = defineProps<{
  stats: MediaStats | null;
}>();

const { t } = useI18n();

function formatFileSize(bytes: number) {
  if (!bytes) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return `${Math.round((bytes / k ** i) * 100) / 100} ${sizes[i]}`;
}

const imageCount = computed(
  () => props.stats?.types?.find((item) => item.type === 'image')?.count || 0,
);
const videoCount = computed(
  () => props.stats?.types?.find((item) => item.type === 'video')?.count || 0,
);
</script>

<template>
  <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
    <ConsoleStatCard
      :label="t('media.stats.total')"
      :value="stats?.total_count || 0"
      :icon="ImageIcon"
      tone="primary"
    />
    <ConsoleStatCard
      :label="t('media.stats.storage')"
      :value="formatFileSize(stats?.total_size || 0)"
      :icon="HardDrive"
      tone="muted"
    />
    <ConsoleStatCard
      :label="t('media.stats.images')"
      :value="imageCount"
      :icon="ImageIcon"
      tone="success"
    />
    <ConsoleStatCard
      :label="t('media.stats.videos')"
      :value="videoCount"
      :icon="VideoIcon"
      tone="info"
    />
  </div>
</template>
