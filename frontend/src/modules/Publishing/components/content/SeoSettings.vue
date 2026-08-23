<template>
  <ConsoleFormCard :title="$t('publishing.content.form.seoSettings')">
    <div class="space-y-6">
      <div class="space-y-1.5">
        <Label class="text-sm font-semibold tracking-tight">
          {{ $t('publishing.content.form.metaTitle') }}
        </Label>
        <div class="relative">
          <Type class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground/50" />
          <Input
            :model-value="modelValue.meta_title"
            type="text"
            maxlength="255"
            class="pl-9 bg-background/50"
            :placeholder="t('publishing.content.seo.seoTitlePlaceholder')"
            @update:model-value="(val: string | number) => $emit('update:modelValue', { ...modelValue, meta_title: val.toString() })"
          />
        </div>
        <div class="flex justify-end">
          <p
            class="text-[10px] font-medium transition-colors"
            :class="(modelValue.meta_title?.length || 0) > 60 ? 'text-warning' : 'text-muted-foreground'"
          >
            {{ t('publishing.content.seo.charactersCount', { current: modelValue.meta_title?.length || 0, max: 255 }) }}
          </p>
        </div>
      </div>

      <div class="space-y-1.5">
        <Label class="text-sm font-semibold tracking-tight">
          {{ $t('publishing.content.form.metaDescription') }}
        </Label>
        <Textarea
          :model-value="modelValue.meta_description"
          rows="3"
          maxlength="500"
          class="bg-background/50 resize-none"
          :placeholder="t('publishing.content.seo.seoDescPlaceholder')"
          @update:model-value="(val: string | number) => $emit('update:modelValue', { ...modelValue, meta_description: val.toString() })"
        />
        <div class="flex justify-end">
          <p
            class="text-[10px] font-medium transition-colors"
            :class="(modelValue.meta_description?.length || 0) > 160 ? 'text-warning' : 'text-muted-foreground'"
          >
            {{ t('publishing.content.seo.charactersCount', { current: modelValue.meta_description?.length || 0, max: 500 }) }}
          </p>
        </div>
      </div>

      <div class="space-y-1.5">
        <Label class="text-sm font-semibold tracking-tight">
          {{ $t('publishing.content.form.metaKeywords') }}
        </Label>
        <div class="relative">
          <Hash class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground/50" />
          <Input
            :model-value="modelValue.meta_keywords"
            type="text"
            class="pl-9 bg-background/50"
            :placeholder="$t('publishing.content.form.keywordsPlaceholder')"
            @update:model-value="(val: string | number) => $emit('update:modelValue', { ...modelValue, meta_keywords: val.toString() })"
          />
        </div>
      </div>

      <div class="space-y-3">
        <Label class="text-sm font-semibold tracking-tight">
          {{ $t('publishing.content.form.ogImage') }}
        </Label>
        <div
          v-if="modelValue.og_image"
          class="relative group aspect-video"
        >
          <img
            :src="modelValue.og_image"
            alt="OG Image"
            class="w-full h-full object-cover rounded-lg border border-border/40 shadow-sm"
          >
          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-lg">
            <Button
              variant="destructive"
              size="sm"
              @click="$emit('update:modelValue', { ...modelValue, og_image: null })"
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
            :label="$t('publishing.content.form.selectOgImage')"
            :constraints="{
              allowedExtensions: settings.allowed_image_types ? String(settings.allowed_image_types).split(',').map(s => s.trim()) : ['jpg', 'jpeg', 'png', 'webp'],
              minWidth: 1200,
              minHeight: 630
            }"
            @selected="(media) => $emit('update:modelValue', { ...modelValue, og_image: media.url })"
          >
            <template #trigger="{ open }">
              <Button
                type="button"
                variant="outline"
                size="sm"
                class="w-full inline-flex items-center justify-center gap-2 border-2 border-dashed h-10 hover:border-primary transition-colors"
                @click="open"
              >
                <ImageIcon data-icon="inline-start" class="size-4 shrink-0" />
                {{ $t('publishing.content.form.selectOgImage') }}
              </Button>
            </template>
          </MediaPicker>
          <div class="mt-4 text-[10px] text-muted-foreground text-center italic leading-relaxed">
            <p>{{ $t('publishing.content.form.recommendedHint', { dimensions: '1200x630px' }) }}</p>
            <p>{{ $t('publishing.content.form.maxSizeHint', { size: Math.round(maxUploadSizeMB), extensions: String(settings.allowed_image_types || 'JPG, PNG, WEBP').toUpperCase() }) }}</p>
          </div>
        </div>
      </div>
  </div>
</ConsoleFormCard>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { storeToRefs } from 'pinia';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import MediaPicker from '@/modules/Publishing/shims/media/components/picker/MediaPicker.vue';
import { ConsoleFormCard } from '@/shared/components/shell';
import {
    Label,
    Input,
    Textarea,
    Button,
} from '@/shared/components/ui';
import {
  Hash,
  ImageIcon,
  Trash2,
  Type,
} from 'lucide-vue-next';

interface SeoData {
    meta_title: string;
    meta_description: string;
    meta_keywords: string;
    og_image: string | null;
}

const { t } = useI18n();
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

withDefaults(defineProps<{
    modelValue?: SeoData;
}>(), {
    modelValue: () => ({
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        og_image: null,
    }),
});

defineEmits<{
    (e: 'update:modelValue', value: SeoData): void;
}>();
</script>
