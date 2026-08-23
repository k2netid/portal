<template>
  <ConsoleFormCard :title="$t('publishing.content.form.featuredImage')">
    <div class="space-y-4">
        <div
          v-if="modelValue"
          class="relative group aspect-video"
        >
          <img
            :src="modelValue"
            alt="Featured Image"
            class="w-full h-full object-cover rounded-lg border border-border/40 shadow-sm"
          >
          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-lg">
            <Button
              variant="destructive"
              size="sm"
              @click="$emit('update:modelValue', null)"
            >
              <Trash2 data-icon="inline-start" class="size-4 shrink-0" />
              {{ $t('publishing.content.form.remove') }}
            </Button>
          </div>
        </div>
        <div
          v-else
          class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-border/40 rounded-lg bg-background/30 hover:bg-background/50 transition-colors"
        >
          <MediaPicker
            :label="$t('publishing.content.form.selectImage')"
            :constraints="{
              allowedExtensions: settings.allowed_image_types ? String(settings.allowed_image_types).split(',').map(s => s.trim()) : ['jpg', 'jpeg', 'png', 'webp'],
              minWidth: 600,
              minHeight: 400
            }"
            @selected="(media) => $emit('update:modelValue', media.url)"
          >
            <template #trigger="{ open }">
              <Button
                type="button"
                variant="outline"
                size="sm"
                class="inline-flex items-center gap-2 border-2 border-dashed h-10 hover:border-primary transition-colors"
                @click="open"
              >
                <Plus data-icon="inline-start" class="size-4 shrink-0" />
                {{ $t('publishing.content.form.selectImage') }}
              </Button>
            </template>
          </MediaPicker>
          <div class="mt-4 text-[10px] text-muted-foreground text-center italic leading-relaxed">
            <p>{{ $t('publishing.content.form.recommendedHint', { dimensions: '1200x630px' }) }} {{ $t('publishing.content.form.minHint', { dimensions: '600x400px' }) }}</p>
            <p>{{ $t('publishing.content.form.maxSizeHint', { size: Math.round(maxUploadSizeMB), extensions: String(settings.allowed_image_types || 'JPG, PNG, WEBP').toUpperCase() }) }}</p>
          </div>
        </div>
      </div>
  </ConsoleFormCard>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import MediaPicker from '@/modules/Media/components/picker/MediaPicker.vue';
import { ConsoleFormCard } from '@/shared/components/shell';
import { Button } from '@/shared/components/ui';
import {
  Plus,
  Trash2,
} from 'lucide-vue-next';
import { useSystemStore } from '@/modules/Core/System/stores/system';

const systemStore = useSystemStore();
const { settings } = storeToRefs(systemStore);

const maxUploadSizeMB = computed(() => {
    // Setting is in KB, convert to MB
    const sizeKB = (settings.value as Record<string, unknown>).max_upload_size as number || 10240;
    return sizeKB / 1024;
});

onMounted(async () => {
    // Ensure media settings are loaded for the limits
    await systemStore.fetchSettingsGroup('media');
});

defineProps<{
    modelValue: string | null;
}>();

defineEmits<{
    (e: 'update:modelValue', value: string | null): void;
}>();
</script>
